<?php

namespace App\Services;

use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\DailyAttendance;
use App\Models\LessonAttendance;
use App\Models\SchoolLocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class ClassroomSessionService
{
    public function __construct(
        protected TeacherAttendanceService $teacherAttendanceService
    ) {}

    /**
     * Open a new attendance session for a class schedule
     */
    public function openSession(ClassSchedule $schedule, User $teacher, int $durationMinutes = 3): ClassSession
    {
        // Check-in Gating: Teacher must have checked in at Kiosk today
        if (! $this->teacherAttendanceService->hasCheckedInToday($teacher)) {
            throw ValidationException::withMessages([
                'check_in' => 'Akses Belum Tersedia: Bapak/Ibu Guru belum melakukan presensi masuk di Layar Presensi Madrasah hari ini. Silakan pindai presensi masuk terlebih dahulu.',
            ]);
        }

        // Schedule Time Gating: guru hanya bisa membuka sesi dalam waktu pelajarannya sendiri (STRICT)
        $now = Carbon::now();
        $todayName = TeacherAttendanceService::getIndonesianDayName($now);
        if ($schedule->day_of_week !== $todayName) {
            throw ValidationException::withMessages(['schedule' => "Sesi presensi hanya dapat dibuka pada hari {$schedule->day_of_week}. Hari ini {$todayName}."]);
        }
        $start = Carbon::parse($schedule->start_time)->setDate($now->year, $now->month, $now->day);
        $end = Carbon::parse($schedule->end_time)->setDate($now->year, $now->month, $now->day);
        // Handle overnight schedule (e.g. 23:00-01:00 next day)
        if ($end->lte($start)) {
            $end->addDay();
        }
        if ($now->lt($start) || $now->gt($end)) {
            throw ValidationException::withMessages(['schedule' => "Sesi presensi hanya dapat dibuka sesuai jadwal pelajaran: pukul {$schedule->start_time}–{$schedule->end_time} WIB. Waktu saat ini: pukul {$now->format('H:i')} WIB."]);
        }

        // Clamp duration between 2 and 5 minutes
        $durationMinutes = max(2, min(5, $durationMinutes));

        // Generate dynamic 4-digit PIN
        $pinCode = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $startedAt = Carbon::now();
        $expiresAt = $startedAt->copy()->addMinutes($durationMinutes);

        // Deactivate previous active sessions for this schedule today if any
        ClassSession::where('schedule_id', $schedule->id)
            ->whereDate('created_at', Carbon::today())
            ->where('status', 'ACTIVE')
            ->update(['status' => 'EXPIRED']);

        return ClassSession::create([
            'schedule_id' => $schedule->id,
            'teacher_id' => $teacher->id,
            'pin_code' => $pinCode,
            'duration_minutes' => $durationMinutes,
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
            'status' => 'ACTIVE',
        ]);
    }

    /**
     * Get active session and geofence state for student's view
     */
    public function getActiveSessionForStudent(User $student, ?float $lat = null, ?float $lng = null): array
    {
        $location = SchoolLocation::getActiveLocation();
        $isWithinGeofence = false;
        $distance = null;

        if ($location && $lat !== null && $lng !== null) {
            $distance = $location->calculateDistance($lat, $lng);
            // Tolerance: radius + 25m
            $isWithinGeofence = ($distance <= ($location->radius_meters + 25));
        }

        if (! $student->classroom_id) {
            return [
                'is_within_geofence' => $isWithinGeofence,
                'distance' => $distance,
                'radius' => $location?->radius_meters ?? 75,
                'session' => null,
                'has_verified' => false,
            ];
        }

        // Find active session for student's classroom today
        $session = ClassSession::with(['schedule.subject', 'schedule.teacher'])
            ->whereHas('schedule', function ($q) use ($student) {
                $q->where('classroom_id', $student->classroom_id);
            })
            ->whereDate('created_at', Carbon::today())
            ->where('status', 'ACTIVE')
            ->latest()
            ->first();

        // Check if expired
        if ($session && $session->isExpired()) {
            $session->update(['status' => 'EXPIRED']);
            $session = null;
        }

        $hasVerified = false;
        if ($session) {
            $hasVerified = LessonAttendance::where('session_id', $session->id)
                ->where('student_id', $student->id)
                ->where('status', 'HADIR')
                ->exists();
        }

        return [
            'is_within_geofence' => $isWithinGeofence,
            'distance' => $distance,
            'radius' => $location?->radius_meters ?? 75,
            'session' => $session,
            'remaining_seconds' => $session ? $session->remaining_seconds : 0,
            'has_verified' => $hasVerified,
        ];
    }

    /**
     * Verify student's 4-digit PIN with Geofence and Rate Limiting
     */
    public function verifyStudentPin(User $student, string $pin, float $lat, float $lng): array
    {
        // 1. Geofence evaluation
        $location = SchoolLocation::getActiveLocation();
        if ($location) {
            $distance = $location->calculateDistance($lat, $lng);
            $maxRadius = $location->radius_meters + 25; // 25m soft tolerance for GPS drift
            if ($distance > $maxRadius) {
                return [
                    'success' => false,
                    'code' => 'OUTSIDE_GEOFENCE',
                    'distance' => $distance,
                    'message' => 'Lokasi Anda berada di luar batas area madrasah (terdeteksi '.round($distance).' meter dari madrasah, batas area '.$location->radius_meters.' meter). Presensi hanya dapat dilakukan di dalam area madrasah.',
                ];
            }
        } else {
            $distance = 0.0;
        }

        // 2. Find active session
        $session = ClassSession::whereHas('schedule', function ($q) use ($student) {
            $q->where('classroom_id', $student->classroom_id);
        })
            ->whereDate('created_at', Carbon::today())
            ->where('status', 'ACTIVE')
            ->latest()
            ->first();

        if (! $session) {
            return [
                'success' => false,
                'code' => 'NO_ACTIVE_SESSION',
                'message' => 'Tidak ada sesi presensi aktif untuk kelas Anda saat ini.',
            ];
        }

        // 3. Grace period check (5 seconds tolerance past expires_at)
        if (Carbon::now()->isAfter($session->expires_at->copy()->addSeconds(5))) {
            $session->update(['status' => 'EXPIRED']);

            return [
                'success' => false,
                'code' => 'SESSION_EXPIRED',
                'message' => 'Waktu sesi presensi telah berakhir. Silakan lapor kepada Bapak/Ibu Guru untuk konfirmasi kehadiran langsung.',
            ];
        }

        // 4. Brute-force rate limiting: Max 3 failed attempts per session
        $rateLimitKey = "pin_fail:{$session->id}:{$student->id}";
        $failedAttempts = (int) Cache::get($rateLimitKey, 0);

        if ($failedAttempts >= 3) {
            return [
                'success' => false,
                'code' => 'RATE_LIMITED',
                'message' => 'Batas 3 kali salah memasukkan PIN tercapai. Kesempatan memasukkan PIN dibekukan sementara selama 5 menit demi keamanan.',
            ];
        }

        // 5. Check already verified
        $existing = LessonAttendance::where('schedule_id', $session->schedule_id)
            ->where('student_id', $student->id)
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        if ($existing && $existing->status === 'HADIR') {
            return [
                'success' => true,
                'already_verified' => true,
                'message' => 'Alhamdulillah, Anda sudah tercatat hadir pada sesi ini!',
            ];
        }

        // 6. Check PIN match
        if (trim($pin) !== $session->pin_code) {
            $failedAttempts++;
            Cache::put($rateLimitKey, $failedAttempts, 300); // 5 minutes TTL
            $remaining = max(0, 3 - $failedAttempts);

            return [
                'success' => false,
                'code' => 'INVALID_PIN',
                'message' => 'Kode PIN presensi salah. Sisa kesempatan: '.$remaining.' kali percobaan.',
            ];
        }

        // PIN is correct! Clear rate limit
        Cache::forget($rateLimitKey);

        // Record LessonAttendance
        $attendance = LessonAttendance::updateOrCreate(
            [
                'schedule_id' => $session->schedule_id,
                'student_id' => $student->id,
                'attendance_date' => Carbon::today(),
            ],
            [
                'session_id' => $session->id,
                'status' => 'HADIR',
                'verified_at' => Carbon::now(),
                'latitude' => $lat,
                'longitude' => $lng,
                'distance_meters' => $distance,
            ]
        );

        // Section 5.2 requirement: First-period attendance automatically records daily attendance
        $daily = DailyAttendance::firstOrNew([
            'user_id' => $student->id,
            'attendance_date' => Carbon::today(),
        ]);

        if (! $daily->check_in_time) {
            $daily->check_in_time = Carbon::now()->format('H:i:s');
            $daily->check_in_status = 'HADIR';
            $daily->check_in_latitude = $lat;
            $daily->check_in_longitude = $lng;
            $daily->check_in_distance_meters = $distance;
            $daily->save();
        }

        return [
            'success' => true,
            'message' => 'Presensi berhasil! Anda tercatat HADIR di kelas.',
            'attendance_id' => $attendance->id,
        ];
    }

    /**
     * Reconcile remaining unverified students and lock the session
     */
    public function reconcileSession(ClassSession $session, User $teacher, array $statuses, array $notes = []): void
    {
        $classroom = $session->schedule->classroom;
        $students = $classroom->students()->where('is_active', true)->get();

        foreach ($students as $student) {
            $existing = LessonAttendance::where('schedule_id', $session->schedule_id)
                ->where('student_id', $student->id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();

            if ($existing && $existing->status === 'HADIR') {
                continue; // Keep HADIR
            }

            // Status from teacher input or default to ALPA
            $status = strtoupper($statuses[$student->id] ?? 'ALPA');
            if (! in_array($status, ['HADIR', 'IZIN', 'SAKIT', 'ALPA'])) {
                $status = 'ALPA';
            }
            $note = $notes[$student->id] ?? null;

            LessonAttendance::updateOrCreate(
                [
                    'schedule_id' => $session->schedule_id,
                    'student_id' => $student->id,
                    'attendance_date' => Carbon::today(),
                ],
                [
                    'session_id' => $session->id,
                    'status' => $status,
                    'notes' => $note,
                    'confirmed_by' => $teacher->id,
                    'verified_at' => Carbon::now(),
                ]
            );

            // If manual status is HADIR, ensure daily attendance is also recorded
            if ($status === 'HADIR') {
                $daily = DailyAttendance::firstOrNew([
                    'user_id' => $student->id,
                    'attendance_date' => Carbon::today(),
                ]);

                if (! $daily->check_in_time) {
                    $daily->check_in_time = Carbon::now()->format('H:i:s');
                    $daily->check_in_status = 'HADIR';
                    $daily->save();
                }
            }
        }

        // Lock session permanently
        $session->update(['status' => 'LOCKED']);
    }

    /**
     * Gather complete student dashboard data
     *
     * @return array<string, mixed>
     */
    public function getStudentDashboardData(User $student): array
    {
        $student->load('classroom');
        $location = SchoolLocation::getActiveLocation();
        $today = Carbon::today();
        $todayDay = TeacherAttendanceService::getIndonesianDayName($today);

        $todayAttendances = LessonAttendance::with(['schedule.subject', 'schedule.teacher'])
            ->where('student_id', $student->id)
            ->whereDate('attendance_date', $today)
            ->get();

        $todaySchedules = $student->classroom_id
            ? ClassSchedule::with(['subject', 'teacher', 'sessions' => function ($q) use ($today) {
                $q->whereDate('created_at', $today);
            }])
                ->where('classroom_id', $student->classroom_id)
                ->where('day_of_week', $todayDay)
                ->orderBy('start_time')
                ->get()
            : collect();

        $hadirCount = $todayAttendances->where('status', 'HADIR')->count();
        $izinCount = $todayAttendances->where('status', 'IZIN')->count();
        $sakitCount = $todayAttendances->where('status', 'SAKIT')->count();
        $alpaCount = $todayAttendances->where('status', 'ALPA')->count();

        return compact(
            'student',
            'location',
            'todayAttendances',
            'todaySchedules',
            'todayDay',
            'hadirCount',
            'izinCount',
            'sakitCount',
            'alpaCount'
        );
    }

    /**
     * Retrieve live polling payload for a class session
     *
     * @return array<string, mixed>
     */
    public function getSessionLiveStatus(ClassSession $session): array
    {
        $session->load(['attendances.student']);
        $verifiedStudents = $session->attendances()
            ->where('status', 'HADIR')
            ->get()
            ->map(function ($att) {
                return [
                    'name' => $att->student->name,
                    'identity_number' => $att->student->identity_number,
                    'verified_at' => $att->verified_at ? $att->verified_at->format('H:i:s') : '-',
                ];
            });

        return [
            'status' => $session->status,
            'is_expired' => $session->isExpired(),
            'remaining_seconds' => $session->remaining_seconds,
            'verified_count' => $verifiedStudents->count(),
            'verified_students' => $verifiedStudents,
        ];
    }

    /**
     * Prepare data required for teacher reconciliation view
     *
     * @return array{session: ClassSession, students: \Illuminate\Database\Eloquent\Collection, existingAttendances: Collection, isLocked: bool}
     */
    public function getReconcileData(ClassSession $session): array
    {
        $session->load(['schedule.classroom', 'schedule.subject']);
        $students = $session->schedule->classroom->students()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $existingAttendances = LessonAttendance::where('schedule_id', $session->schedule_id)
            ->whereDate('attendance_date', Carbon::today())
            ->get()
            ->keyBy('student_id');

        $isLocked = ($session->status === 'LOCKED');

        return compact('session', 'students', 'existingAttendances', 'isLocked');
    }
}
