<?php

namespace App\Services;

use App\Models\ClassSchedule;
use App\Models\DailyAttendance;
use App\Models\SchoolLocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TeacherAttendanceService
{
    public function __construct(
        protected KioskService $kioskService
    ) {}

    public static function getIndonesianDayName(?Carbon $date = null): string
    {
        $date = $date ?: Carbon::today();
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        return $days[$date->dayOfWeek] ?? 'Senin';
    }

    public function hasCheckedInToday(User $teacher): bool
    {
        return DailyAttendance::where('user_id', $teacher->id)
            ->whereDate('attendance_date', Carbon::today())
            ->whereNotNull('check_in_time')
            ->exists();
    }

    public function getTodayDailyAttendance(User $teacher): ?DailyAttendance
    {
        return DailyAttendance::where('user_id', $teacher->id)
            ->whereDate('attendance_date', Carbon::today())
            ->first();
    }

    /**
     * Compile complete dashboard state for a teacher
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(User $teacher): array
    {
        $location = SchoolLocation::getActiveLocation();
        $hasCheckedIn = $this->hasCheckedInToday($teacher);
        $dailyAttendance = $this->getTodayDailyAttendance($teacher);
        $todayDay = self::getIndonesianDayName(Carbon::today());

        $schedules = ClassSchedule::with(['classroom', 'subject', 'todaySession.attendances'])
            ->where('teacher_id', $teacher->id)
            ->where('day_of_week', $todayDay)
            ->orderBy('start_time')
            ->get();

        $pendingSchedules = $this->getPendingSchedulesToday($teacher);

        $activeSessionsCount = $schedules->filter(fn ($s) => $s->todaySession?->status === 'ACTIVE' && ! $s->todaySession?->isExpired())->count();
        $lockedSessionsCount = $schedules->filter(fn ($s) => $s->todaySession?->status === 'LOCKED')->count();
        $activeSession = $schedules->first(fn ($s) => $s->todaySession?->status === 'ACTIVE' && ! $s->todaySession?->isExpired())?->todaySession;

        return compact(
            'teacher',
            'location',
            'hasCheckedIn',
            'dailyAttendance',
            'todayDay',
            'schedules',
            'pendingSchedules',
            'activeSessionsCount',
            'lockedSessionsCount',
            'activeSession'
        );
    }

    /**
     * Check geofence status for teacher
     *
     * @return array{is_within_geofence: bool, distance: ?float, radius: int, school_name: string}
     */
    public function checkGeofence(?float $lat = null, ?float $lng = null): array
    {
        $location = SchoolLocation::getActiveLocation();
        $isWithinGeofence = false;
        $distance = null;
        $radius = $location?->radius_meters ?? 75;

        if ($location && $lat !== null && $lng !== null) {
            $distance = $location->calculateDistance($lat, $lng);
            // Tolerance: radius + 25m
            $isWithinGeofence = ($distance <= ($radius + 25));
        }

        return [
            'is_within_geofence' => $isWithinGeofence,
            'distance' => $distance !== null ? round($distance, 1) : null,
            'radius' => $radius,
            'school_name' => $location?->name ?? "MA Ma'arif Cilageni",
        ];
    }

    /**
     * Teaching Completion Lock: Get all today's schedules that are not yet LOCKED
     */
    public function getPendingSchedulesToday(User $teacher, ?Carbon $date = null): Collection
    {
        $date = $date ?: Carbon::today();
        $todayDay = self::getIndonesianDayName($date);

        $schedules = ClassSchedule::with(['classroom', 'subject', 'sessions' => function ($q) use ($date) {
            $q->whereDate('created_at', $date);
        }])
            ->where('teacher_id', $teacher->id)
            ->where('day_of_week', $todayDay)
            ->get();

        return $schedules->filter(function (ClassSchedule $schedule) {
            $session = $schedule->sessions->first();

            // If no session created today, or session is not LOCKED, it is pending
            return ! $session || $session->status !== 'LOCKED';
        });
    }

    public function checkIn(User $teacher, string $qrToken, ?float $lat = null, ?float $lng = null): array
    {
        if ($lat === null || $lng === null) {
            return [
                'success' => false,
                'code' => 'GEOFENCE_REQUIRED',
                'message' => 'Presensi ditolak. Lokasi GPS tidak terdeteksi. Harap aktifkan GPS dan izinkan akses lokasi pada peramban HP Bapak/Ibu Guru.',
            ];
        }

        $geofence = $this->checkGeofence($lat, $lng);
        if (! $geofence['is_within_geofence']) {
            $distance = $geofence['distance'] ?? 0;
            $radius = $geofence['radius'] ?? 75;

            return [
                'success' => false,
                'code' => 'OUTSIDE_GEOFENCE',
                'distance' => $distance,
                'radius' => $radius,
                'message' => "Presensi ditolak. Anda berada di luar area madrasah (Jarak: {$distance} meter, Batas Maksimal: {$radius} meter).",
            ];
        }

        if (! $this->kioskService->validateToken($qrToken)) {
            return [
                'success' => false,
                'code' => 'INVALID_QR_TOKEN',
                'message' => 'Kode QR telah berganti atau kedaluwarsa. Silakan arahkan kamera ke Layar Presensi Madrasah untuk memindai kode QR terbaru.',
            ];
        }

        $now = Carbon::now();
        $today = Carbon::today();

        $daily = DailyAttendance::firstOrNew([
            'user_id' => $teacher->id,
            'attendance_date' => $today,
        ]);

        if ($daily->check_in_time) {
            Cache::put('kiosk_latest_event', [
                'id' => (string) Str::uuid(),
                'type' => 'check_in',
                'teacher_name' => $teacher->name,
                'time' => substr($daily->check_in_time, 0, 5),
                'status' => $daily->check_in_status ?? 'HADIR',
                'title' => 'Selamat Datang Kembali!',
                'message' => 'Presensi masuk telah tercatat sebelumnya pada pukul '.substr($daily->check_in_time, 0, 5).' WIB. Selamat mendidik di MA Ma\'arif Cilageni!',
                'timestamp_ms' => $now->getTimestampMs(),
            ], 120);

            return [
                'success' => true,
                'already_checked_in' => true,
                'check_in_time' => $daily->check_in_time,
                'message' => 'Bapak/Ibu Guru sudah melakukan presensi masuk hari ini pada pukul '.substr($daily->check_in_time, 0, 5).' WIB.',
            ];
        }

        // Late threshold: 07:15 WIB
        $thresholdTime = Carbon::createFromTime(7, 15, 0);
        $status = $now->greaterThan($thresholdTime) ? 'TERLAMBAT' : 'HADIR';

        $daily->check_in_time = $now->format('H:i:s');
        $daily->check_in_status = $status;
        $daily->check_in_latitude = $lat;
        $daily->check_in_longitude = $lng;
        $daily->save();

        Cache::put('kiosk_latest_event', [
            'id' => (string) Str::uuid(),
            'type' => 'check_in',
            'teacher_name' => $teacher->name,
            'time' => $now->format('H:i:s'),
            'status' => $status,
            'title' => 'Selamat Datang!',
            'message' => 'Presensi masuk berhasil dicatat. Selamat mendidik dan beraktivitas di MA Ma\'arif Cilageni!',
            'timestamp_ms' => $now->getTimestampMs(),
        ], 120);

        return [
            'success' => true,
            'status' => $status,
            'check_in_time' => $daily->check_in_time,
            'message' => 'Presensi masuk berhasil dicatat pada '.$now->format('H:i:s').' WIB ('.$status.'). Fitur buka sesi presensi kelas kini telah aktif.',
        ];
    }

    public function checkOut(User $teacher, string $qrToken, ?float $lat = null, ?float $lng = null): array
    {
        if ($lat === null || $lng === null) {
            return [
                'success' => false,
                'code' => 'GEOFENCE_REQUIRED',
                'message' => 'Presensi ditolak. Lokasi GPS tidak terdeteksi. Harap aktifkan GPS dan izinkan akses lokasi pada peramban HP Bapak/Ibu Guru.',
            ];
        }

        $geofence = $this->checkGeofence($lat, $lng);
        if (! $geofence['is_within_geofence']) {
            $distance = $geofence['distance'] ?? 0;
            $radius = $geofence['radius'] ?? 75;

            return [
                'success' => false,
                'code' => 'OUTSIDE_GEOFENCE',
                'distance' => $distance,
                'radius' => $radius,
                'message' => "Presensi ditolak. Anda berada di luar area madrasah (Jarak: {$distance} meter, Batas Maksimal: {$radius} meter).",
            ];
        }

        if (! $this->kioskService->validateToken($qrToken)) {
            return [
                'success' => false,
                'code' => 'INVALID_QR_TOKEN',
                'message' => 'Kode QR telah berganti atau kedaluwarsa. Silakan arahkan kamera ke Layar Presensi Madrasah untuk memindai kode QR terbaru.',
            ];
        }

        $today = Carbon::today();
        $daily = DailyAttendance::where('user_id', $teacher->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (! $daily || ! $daily->check_in_time) {
            return [
                'success' => false,
                'code' => 'NOT_CHECKED_IN',
                'message' => 'Bapak/Ibu Guru belum melakukan presensi masuk hari ini.',
            ];
        }

        // Teaching Completion Lock enforcement
        $pendingSchedules = $this->getPendingSchedulesToday($teacher, $today);
        if ($pendingSchedules->isNotEmpty()) {
            $scheduleNames = $pendingSchedules->map(function ($s) {
                return ($s->subject->name ?? 'Mapel').' ('.($s->classroom->name ?? 'Kelas').')';
            })->values()->all();

            return [
                'success' => false,
                'code' => 'TEACHING_COMPLETION_LOCKED',
                'pending_schedules' => $scheduleNames,
                'message' => 'Presensi Pulang Terkunci: Masih ada '.count($scheduleNames).' jadwal kelas yang belum tuntas dikonfirmasi keterangannya: '.implode(', ', $scheduleNames).'. Silakan selesaikan sesi dan konfirmasi keterangan kehadiran siswa terlebih dahulu.',
            ];
        }

        $now = Carbon::now();
        $daily->check_out_time = $now->format('H:i:s');
        $daily->check_out_status = 'TEPAT_WAKTU';
        if ($lat && $lng) {
            $daily->check_out_latitude = $lat;
            $daily->check_out_longitude = $lng;
        }
        $daily->save();

        Cache::put('kiosk_latest_event', [
            'id' => (string) Str::uuid(),
            'type' => 'check_out',
            'teacher_name' => $teacher->name,
            'time' => $now->format('H:i:s'),
            'status' => 'TEPAT_WAKTU',
            'title' => 'Selamat Pulang!',
            'message' => 'Terima kasih atas dedikasi dan keikhlasan mendidik siswa-siswi hari ini. Hati-hati di perjalanan pulang!',
            'timestamp_ms' => $now->getTimestampMs(),
        ], 120);

        return [
            'success' => true,
            'check_out_time' => $daily->check_out_time,
            'message' => 'Presensi pulang berhasil dicatat pada '.$now->format('H:i:s').' WIB. Seluruh jadwal mengajar hari ini telah tuntas.',
        ];
    }

    /**
     * Retrieve daily teacher attendances list with mapping of pending schedules and presence status
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getDailyTeacherAttendances(string $date, ?string $status = null, ?string $search = null): Collection
    {
        $carbonDate = Carbon::parse($date);
        $todayDay = self::getIndonesianDayName($carbonDate);

        $teacherQuery = User::where('role', 'guru')
            ->where('is_active', true);

        if (! empty($search)) {
            $teacherQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('identity_number', 'like', "%{$search}%");
            });
        }

        $teachers = $teacherQuery->orderBy('name')->get();

        $dailyAttendances = DailyAttendance::whereDate('attendance_date', $date)
            ->whereIn('user_id', $teachers->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $results = $teachers->map(function (User $teacher) use ($dailyAttendances, $carbonDate, $todayDay) {
            $attendance = $dailyAttendances->get($teacher->id);

            // Compute status
            if ($attendance && $attendance->check_in_time) {
                $computedStatus = $attendance->check_in_status ?: 'HADIR';
            } elseif ($attendance && in_array($attendance->check_in_status, ['IZIN', 'SAKIT', 'ALPA'])) {
                $computedStatus = $attendance->check_in_status;
            } else {
                $computedStatus = 'BELUM_HADIR';
            }

            // Pending schedules on this date
            $schedules = ClassSchedule::with(['classroom', 'subject', 'sessions' => function ($q) use ($carbonDate) {
                $q->whereDate('created_at', $carbonDate);
            }])
                ->where('teacher_id', $teacher->id)
                ->where('day_of_week', $todayDay)
                ->get();

            $pendingCount = $schedules->filter(function ($sch) {
                $ses = $sch->sessions->first();

                return ! $ses || $ses->status !== 'LOCKED';
            })->count();

            return [
                'teacher' => $teacher,
                'attendance' => $attendance,
                'status' => $computedStatus,
                'check_in_time' => $attendance?->check_in_time ? substr($attendance->check_in_time, 0, 5) : null,
                'check_out_time' => $attendance?->check_out_time ? substr($attendance->check_out_time, 0, 5) : null,
                'is_completed' => ! empty($attendance?->check_out_time),
                'total_schedules_today' => $schedules->count(),
                'pending_schedules_count' => $pendingCount,
            ];
        });

        if (! empty($status)) {
            $filterStatus = strtoupper($status);
            $results = $results->filter(fn ($item) => $item['status'] === $filterStatus)->values();
        }

        return $results;
    }

    /**
     * Compute daily summary KPI for teacher attendance on a specific date
     *
     * @return array{total_guru: int, hadir: int, terlambat: int, izin_sakit: int, belum_hadir: int, checkout_tuntas: int}
     */
    public function getTeacherAttendanceDailySummary(string $date): array
    {
        $totalGuru = User::where('role', 'guru')->where('is_active', true)->count();

        $dailyQuery = DailyAttendance::whereDate('attendance_date', $date)
            ->whereHas('user', function ($q) {
                $q->where('role', 'guru');
            });

        $hadir = (clone $dailyQuery)->where('check_in_status', 'HADIR')->count();
        $terlambat = (clone $dailyQuery)->where('check_in_status', 'TERLAMBAT')->count();
        $izinSakit = (clone $dailyQuery)->whereIn('check_in_status', ['IZIN', 'SAKIT'])->count();
        $checkoutTuntas = (clone $dailyQuery)->whereNotNull('check_out_time')->count();

        $belumHadir = max(0, $totalGuru - ($hadir + $terlambat + $izinSakit));

        return compact('totalGuru', 'hadir', 'terlambat', 'izinSakit', 'belumHadir', 'checkoutTuntas');
    }

    /**
     * Update or create manual teacher attendance recorded by TU staff
     */
    public function updateManualTeacherAttendance(
        User $teacher,
        string $date,
        string $status,
        ?string $checkInTime = null,
        ?string $checkOutTime = null
    ): DailyAttendance {
        $daily = DailyAttendance::firstOrNew([
            'user_id' => $teacher->id,
            'attendance_date' => $date,
        ]);

        $status = strtoupper($status);
        $daily->check_in_status = $status;

        if ($status === 'HADIR' && empty($checkInTime)) {
            $checkInTime = '07:00:00';
        } elseif ($status === 'TERLAMBAT' && empty($checkInTime)) {
            $checkInTime = '07:30:00';
        }

        if (! empty($checkInTime)) {
            $daily->check_in_time = strlen($checkInTime) === 5 ? $checkInTime.':00' : $checkInTime;
        }

        if (! empty($checkOutTime)) {
            $daily->check_out_time = strlen($checkOutTime) === 5 ? $checkOutTime.':00' : $checkOutTime;
            $daily->check_out_status = 'TEPAT_WAKTU';
        }

        $daily->save();

        return $daily;
    }

    /**
     * Get comprehensive individual attendance history and teaching stats for a teacher
     *
     * @return array<string, mixed>
     */
    public function getTeacherIndividualHistory(User $teacher, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?: Carbon::today()->subDays(30)->format('Y-m-d');
        $endDate = $endDate ?: Carbon::today()->format('Y-m-d');

        $attendances = DailyAttendance::where('user_id', $teacher->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date', 'desc')
            ->get();

        $totalHadir = $attendances->where('check_in_status', 'HADIR')->count();
        $totalTerlambat = $attendances->where('check_in_status', 'TERLAMBAT')->count();
        $totalIzinSakit = $attendances->whereIn('check_in_status', ['IZIN', 'SAKIT'])->count();
        $totalPresensi = $attendances->count();

        $persenHadir = $totalPresensi > 0 ? round((($totalHadir + $totalTerlambat) / $totalPresensi) * 100, 1) : 0.0;

        return compact(
            'teacher',
            'startDate',
            'endDate',
            'attendances',
            'totalHadir',
            'totalTerlambat',
            'totalIzinSakit',
            'totalPresensi',
            'persenHadir'
        );
    }
}
