<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\DailyAttendance;
use App\Models\LessonAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class AdminDashboardService
{
    /**
     * Retrieve aggregated statistics and recent active sessions for the admin dashboard
     *
     * @return array{
     *     totalSiswa: int,
     *     totalGuru: int,
     *     siswaHadir: int,
     *     siswaIzin: int,
     *     siswaSakit: int,
     *     siswaAlpa: int,
     *     guruHadir: int,
     *     activeSessions: Collection,
     *     recentSiswaAttendances: Collection,
     *     recentGuruAttendances: Collection
     * }
     */
    public function getDashboardMetrics(): array
    {
        $today = Carbon::today();

        $totalSiswa = User::where('role', 'siswa')->where('is_active', true)->count();
        $totalGuru = User::where('role', 'guru')->where('is_active', true)->count();

        // Student attendances today (unique students per status)
        $siswaHadir = LessonAttendance::whereDate('attendance_date', $today)
            ->where('status', 'HADIR')
            ->distinct('student_id')
            ->count('student_id');

        $siswaIzin = LessonAttendance::whereDate('attendance_date', $today)
            ->where('status', 'IZIN')
            ->distinct('student_id')
            ->count('student_id');

        $siswaSakit = LessonAttendance::whereDate('attendance_date', $today)
            ->where('status', 'SAKIT')
            ->distinct('student_id')
            ->count('student_id');

        $siswaAlpa = LessonAttendance::whereDate('attendance_date', $today)
            ->where('status', 'ALPA')
            ->distinct('student_id')
            ->count('student_id');

        // Teacher check-in count today
        $guruHadir = DailyAttendance::whereDate('attendance_date', $today)
            ->whereNotNull('check_in_time')
            ->whereHas('user', function ($q) {
                $q->where('role', 'guru');
            })->count();

        // Active or recently opened class sessions today
        $activeSessions = ClassSession::with(['schedule.classroom', 'schedule.subject', 'teacher'])
            ->whereDate('created_at', $today)
            ->latest()
            ->take(10)
            ->get();

        // 10 recent student lesson attendances today
        $recentSiswaAttendances = LessonAttendance::with(['student.classroom', 'schedule.subject', 'schedule.classroom', 'session.teacher'])
            ->whereDate('attendance_date', $today)
            ->latest('verified_at')
            ->latest('created_at')
            ->take(10)
            ->get();

        // 10 recent teacher daily attendances today
        $recentGuruAttendances = DailyAttendance::with('user')
            ->whereDate('attendance_date', $today)
            ->whereHas('user', function ($q) {
                $q->where('role', 'guru');
            })
            ->latest('updated_at')
            ->take(10)
            ->get();

        return compact(
            'totalSiswa',
            'totalGuru',
            'siswaHadir',
            'siswaIzin',
            'siswaSakit',
            'siswaAlpa',
            'guruHadir',
            'activeSessions',
            'recentSiswaAttendances',
            'recentGuruAttendances'
        );
    }
}
