<?php

namespace App\Services;

use App\Models\AttendanceAuditLog;
use App\Models\DailyAttendance;
use App\Models\LessonAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttendanceCorrectionService
{
    /**
     * Retrieve paginated lesson attendances with date, classroom, status, and student search filters
     */
    public function getPresensiSiswaPaginated(
        string $date,
        ?string $classroomId = null,
        ?string $status = null,
        ?string $search = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = LessonAttendance::with([
            'student.classroom',
            'schedule.subject',
            'schedule.teacher',
            'session',
        ])->whereDate('attendance_date', $date);

        if (! empty($classroomId)) {
            $query->whereHas('student', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        }

        if (! empty($status)) {
            $query->where('status', strtoupper($status));
        }

        if (! empty($search)) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('identity_number', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Calculate summary statistics for student attendance on a specific date
     *
     * @return array{total: int, hadir: int, izin: int, sakit: int, alpa: int}
     */
    public function getPresensiSiswaSummary(string $date): array
    {
        return [
            'total' => LessonAttendance::whereDate('attendance_date', $date)->count(),
            'hadir' => LessonAttendance::whereDate('attendance_date', $date)->where('status', 'HADIR')->count(),
            'izin' => LessonAttendance::whereDate('attendance_date', $date)->where('status', 'IZIN')->count(),
            'sakit' => LessonAttendance::whereDate('attendance_date', $date)->where('status', 'SAKIT')->count(),
            'alpa' => LessonAttendance::whereDate('attendance_date', $date)->where('status', 'ALPA')->count(),
        ];
    }

    /**
     * Correct attendance status with mandatory audit log and synchronize daily attendance
     *
     * @return array{old_status: string, new_status: string, updated: bool}
     */
    public function correctAttendance(
        LessonAttendance $lessonAttendance,
        string $newStatus,
        string $reason,
        string $changedById
    ): array {
        $oldStatus = $lessonAttendance->status;
        $newStatus = strtoupper($newStatus);
        $reason = trim($reason);

        if ($oldStatus === $newStatus && $lessonAttendance->notes === $reason) {
            return [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'updated' => false,
            ];
        }

        // 1. Record Audit Trail Log
        AttendanceAuditLog::create([
            'lesson_attendance_id' => $lessonAttendance->id,
            'changed_by' => $changedById,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
        ]);

        // 2. Update LessonAttendance
        $lessonAttendance->update([
            'status' => $newStatus,
            'notes' => $reason,
            'confirmed_by' => $changedById,
        ]);

        // 3. Keep DailyAttendance in sync if exists
        $daily = DailyAttendance::where('user_id', $lessonAttendance->student_id)
            ->whereDate('attendance_date', $lessonAttendance->attendance_date)
            ->first();

        if ($daily) {
            if ($newStatus === 'HADIR') {
                if (! $daily->check_in_time) {
                    $daily->check_in_time = Carbon::now()->format('H:i:s');
                }
                $daily->check_in_status = 'HADIR';
            } else {
                $hasOtherHadir = LessonAttendance::where('student_id', $lessonAttendance->student_id)
                    ->whereDate('attendance_date', $lessonAttendance->attendance_date)
                    ->where('id', '!=', $lessonAttendance->id)
                    ->where('status', 'HADIR')
                    ->exists();

                if (! $hasOtherHadir) {
                    $daily->check_in_status = $newStatus;
                }
            }
            $daily->save();
        }

        return [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'updated' => true,
        ];
    }

    /**
     * Retrieve paginated attendance audit trail logs
     */
    public function getAuditLogsPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return AttendanceAuditLog::with(['lessonAttendance.student', 'changedBy'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get individual attendance timeline history and statistics for a student
     *
     * @return array<string, mixed>
     */
    public function getStudentIndividualHistory(User $student, ?string $startDate = null, ?string $endDate = null): array
    {
        $student->load('classroom');

        $startDate = $startDate ?: Carbon::today()->subDays(30)->format('Y-m-d');
        $endDate = $endDate ?: Carbon::today()->format('Y-m-d');

        $attendances = LessonAttendance::with(['schedule.subject', 'schedule.teacher', 'session'])
            ->where('student_id', $student->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalHadir = $attendances->where('status', 'HADIR')->count();
        $totalIzin = $attendances->where('status', 'IZIN')->count();
        $totalSakit = $attendances->where('status', 'SAKIT')->count();
        $totalAlpa = $attendances->where('status', 'ALPA')->count();
        $total = $attendances->count();

        $persenHadir = $total > 0 ? round(($totalHadir / $total) * 100, 1) : 0.0;

        return compact(
            'student',
            'startDate',
            'endDate',
            'attendances',
            'totalHadir',
            'totalIzin',
            'totalSakit',
            'totalAlpa',
            'total',
            'persenHadir'
        );
    }
}
