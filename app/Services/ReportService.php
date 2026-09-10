<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\DailyAttendance;
use App\Models\LessonAttendance;
use App\Models\User;
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

        return [
            'total_records' => $totalRecords,
            'total_hadir' => $totalHadir,
            'total_izin' => $totalIzin,
            'total_sakit' => $totalSakit,
            'total_alpa' => $totalAlpa,
            'percentage_hadir' => $percentageHadir,
            'teacher_checkins' => $totalTeacherCheckins,
            'teacher_late' => $totalTeacherLate,
        ];
    }

    /**
     * Get detailed student attendance rows for report
     */
    public function getStudentAttendanceRows(string $startDate, string $endDate, ?string $classroomId = null): array
    {
        $studentsQuery = User::with(['classroom'])
            ->where('role', 'siswa')
            ->where('is_active', true);

        if ($classroomId) {
            $studentsQuery->where('classroom_id', $classroomId);
        }

        $students = $studentsQuery->orderBy('name')->get();
        $rows = [];

        foreach ($students as $student) {
            $records = LessonAttendance::where('student_id', $student->id)
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->get();

            $hadir = $records->where('status', 'HADIR')->count();
            $izin = $records->where('status', 'IZIN')->count();
            $sakit = $records->where('status', 'SAKIT')->count();
            $alpa = $records->where('status', 'ALPA')->count();
            $total = $records->count();
            $persen = $total > 0 ? round(($hadir / $total) * 100, 1) : 0.0;

            $rows[] = [
                'nisn' => $student->identity_number,
                'name' => $student->name,
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
