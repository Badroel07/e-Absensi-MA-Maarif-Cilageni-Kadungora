<?php

namespace App\Services;

use App\Models\ClassSchedule;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ScheduleService
{
    /**
     * Retrieve paginated class schedules with optional filters
     */
    public function getSchedulesPaginated(?string $classroomId = null, ?string $day = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = ClassSchedule::with(['classroom', 'subject', 'teacher']);

        if (! empty($classroomId)) {
            $query->where('classroom_id', $classroomId);
        }

        if (! empty($day)) {
            $query->where('day_of_week', $day);
        }

        return $query->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create class schedule after validating teacher and classroom overlap
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function createSchedule(array $data): ClassSchedule
    {
        // 1. Overlap check for teacher: Teacher cannot teach 2 classes at overlapping times
        $teacherOverlap = ClassSchedule::where('teacher_id', $data['teacher_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                    ->where('end_time', '>', $data['start_time']);
            })
            ->exists();

        if ($teacherOverlap) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Jadwal bersamaan: Bapak/Ibu Guru yang bersangkutan sudah memiliki jadwal mengajar di kelas lain pada hari dan jam tersebut.',
            ]);
        }

        // 2. Overlap check for classroom: Classroom cannot have 2 subjects at overlapping times
        $classOverlap = ClassSchedule::where('classroom_id', $data['classroom_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                    ->where('end_time', '>', $data['start_time']);
            })
            ->exists();

        if ($classOverlap) {
            throw ValidationException::withMessages([
                'classroom_id' => 'Jadwal bersamaan: Kelas ini sudah memiliki jadwal pelajaran lain pada hari dan jam tersebut.',
            ]);
        }

        return ClassSchedule::create($data);
    }

    /**
     * Update class schedule after validating teacher and classroom overlap
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function updateSchedule(ClassSchedule $schedule, array $data): ClassSchedule
    {
        // 1. Overlap check for teacher (excluding current schedule)
        $teacherOverlap = ClassSchedule::where('id', '!=', $schedule->id)
            ->where('teacher_id', $data['teacher_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                    ->where('end_time', '>', $data['start_time']);
            })
            ->exists();

        if ($teacherOverlap) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Jadwal bersamaan: Bapak/Ibu Guru yang bersangkutan sudah memiliki jadwal mengajar di kelas lain pada hari dan jam tersebut.',
            ]);
        }

        // 2. Overlap check for classroom (excluding current schedule)
        $classOverlap = ClassSchedule::where('id', '!=', $schedule->id)
            ->where('classroom_id', $data['classroom_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                    ->where('end_time', '>', $data['start_time']);
            })
            ->exists();

        if ($classOverlap) {
            throw ValidationException::withMessages([
                'classroom_id' => 'Jadwal bersamaan: Kelas ini sudah memiliki jadwal pelajaran lain pada hari dan jam tersebut.',
            ]);
        }

        $schedule->update($data);

        return $schedule;
    }

    /**
     * Delete a class schedule
     */
    public function deleteSchedule(ClassSchedule $schedule): bool
    {
        return (bool) $schedule->delete();
    }

    /**
     * Get weekly schedule grouped and ordered for a student
     */
    public function getStudentWeeklySchedule(User $student): Collection
    {
        if (! $student->classroom_id) {
            return collect();
        }

        $dayWeights = [
            'SENIN' => 1,
            'SELASA' => 2,
            'RABU' => 3,
            'KAMIS' => 4,
            'JUMAT' => 5,
            'SABTU' => 6,
            'AHAD' => 7,
            'MINGGU' => 7,
        ];

        return ClassSchedule::with(['subject', 'teacher'])
            ->where('classroom_id', $student->classroom_id)
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->sortBy(fn ($items, $day) => $dayWeights[strtoupper($day)] ?? 99);
    }

    /**
     * Get weekly schedule grouped and ordered for a teacher
     */
    public function getTeacherWeeklySchedule(User $teacher): Collection
    {
        $dayWeights = [
            'SENIN' => 1,
            'SELASA' => 2,
            'RABU' => 3,
            'KAMIS' => 4,
            'JUMAT' => 5,
            'SABTU' => 6,
            'AHAD' => 7,
            'MINGGU' => 7,
        ];

        return ClassSchedule::with(['subject', 'classroom'])
            ->where('teacher_id', $teacher->id)
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->sortBy(fn ($items, $day) => $dayWeights[strtoupper($day)] ?? 99);
    }
}
