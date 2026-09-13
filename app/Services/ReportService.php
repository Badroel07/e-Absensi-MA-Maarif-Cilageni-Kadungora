<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\DailyAttendance;
use App\Models\LessonAttendance;
use App\Models\Student;
use App\Models\TeacherSessionAttendance;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    /**
     * Get attendance overview statistics for date range and optional classroom
     */
    public function getSummaryStats(string $startDate, string $endDate, ?string $classroomId = null): array
    {
        $lessonQuery = LessonAttendance::whereBetween('attendance_date', [$startDate, $endDate]);

        if ($classroomId) {
            $lessonQuery->whereHas('student', function (Builder $q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        }

        $totalRecords = (clone $lessonQuery)->count();
        $totalHadir = (clone $lessonQuery)->where('status', 'HADIR')->count();
        $totalIzin = (clone $lessonQuery)->where('status', 'IZIN')->count();
        $totalSakit = (clone $lessonQuery)->where('status', 'SAKIT')->count();
        $totalAlpa = (clone $lessonQuery)->where('status', 'ALPA')->count();

        $percentageHadir = $totalRecords > 0 ? round(($totalHadir / $totalRecords) * 100, 1) : 0.0;

        // Daily attendances for teachers
        $teacherQuery = DailyAttendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->whereHas('user', function (Builder $q) {
                $q->where('role', 'guru');
            });

        $totalTeacherCheckins = (clone $teacherQuery)->count();
        $totalTeacherLate = (clone $teacherQuery)->where('check_in_status', 'TERLAMBAT')->count();

        // Teacher session attendances (per teaching schedule)
        $sessionAttendances = TeacherSessionAttendance::with('schedule')
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->whereHas('teacher.user', function (Builder $q) {
                $q->where('role', 'guru');
            })->get();

        $teacherSessionsHadir = $sessionAttendances->where('status', 'HADIR')->count();
        $teacherSessionsTerlambat = $sessionAttendances->filter(fn ($att) => $att->isLate())->count();

        return [
            'total_records' => $totalRecords,
            'total_hadir' => $totalHadir,
            'total_izin' => $totalIzin,
            'total_sakit' => $totalSakit,
            'total_alpa' => $totalAlpa,
            'percentage_hadir' => $percentageHadir,
            'teacher_checkins' => $totalTeacherCheckins,
            'teacher_late' => $totalTeacherLate,
            'teacher_sessions_hadir' => $teacherSessionsHadir,
            'teacher_sessions_terlambat' => $teacherSessionsTerlambat,
        ];
    }

    /**
     * Get detailed student attendance rows for report
     */
    public function getStudentAttendanceRows(string $startDate, string $endDate, ?string $classroomId = null): array
    {
        $studentsQuery = Student::with(['user', 'classroom'])
            ->whereHas('user', function (Builder $q) {
                $q->where('is_active', true);
            });

        if ($classroomId) {
            $studentsQuery->where('classroom_id', $classroomId);
        }

        $students = $studentsQuery->get()->sortBy('user.name')->values();

        if ($students->isEmpty()) {
            return [];
        }

        $grouped = LessonAttendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->whereIn('student_id', $students->pluck('id'))
            ->selectRaw('student_id, status, COUNT(*) as cnt')
            ->groupBy('student_id', 'status')
            ->get()
            ->groupBy('student_id');

        $rows = [];
        foreach ($students as $student) {
            $byStatus = $grouped->get($student->id, collect())->keyBy('status');
            $hadir = (int) ($byStatus->get('HADIR')->cnt ?? 0);
            $izin = (int) ($byStatus->get('IZIN')->cnt ?? 0);
            $sakit = (int) ($byStatus->get('SAKIT')->cnt ?? 0);
            $alpa = (int) ($byStatus->get('ALPA')->cnt ?? 0);
            $total = $hadir + $izin + $sakit + $alpa;
            $persen = $total > 0 ? round(($hadir / $total) * 100, 1) : 0.0;

            $rows[] = [
                'nisn' => $student->nisn,
                'name' => $student->user->name,
                'class_name' => $student->classroom->name ?? '-',
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'total' => $total,
                'persentase' => $persen,
            ];
        }

        return $rows;
    }

    /**
     * Generate CSV export content for Excel download
     */
    public function generateCsvExport(string $startDate, string $endDate, ?string $classroomId = null): string
    {
        $rows = $this->getStudentAttendanceRows($startDate, $endDate, $classroomId);
        $output = fopen('php://temp', 'r+');

        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header
        fputcsv($output, [
            'No',
            'NISN',
            'Nama Siswa',
            'Kelas',
            'Hadir',
            'Izin',
            'Sakit',
            'Alpa',
            'Total Pertemuan',
            'Persentase Kehadiran (%)',
        ]);

        $no = 1;
        foreach ($rows as $row) {
            fputcsv($output, [
                $no++,
                $row['nisn'],
                $row['name'],
                $row['class_name'],
                $row['hadir'],
                $row['izin'],
                $row['sakit'],
                $row['alpa'],
                $row['total'],
                $row['persentase'].'%',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
