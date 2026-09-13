<?php

use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\MaarifRealScheduleSeeder;

it('membuat 10 rombel, 28 guru, dan jadwal dari dokumen asli', function () {
    $this->seed(MaarifRealScheduleSeeder::class);

    foreach (['X-1', 'X-2', 'X-3', 'XI SAINS', 'XI SOSHUM 1', 'XI SOSHUM 2', 'XII SAINS 1', 'XII SAINS 2', 'XII SOSHUM 1', 'XII SOSHUM 2'] as $name) {
        expect(Classroom::where('name', $name)->where('academic_year', '2026/2027')->exists())->toBeTrue();
    }

    expect(User::where('role', 'guru')->count())->toBe(28);
    expect(ClassSchedule::count())->toBe(508);
});

it('memetakan sel jadwal sesuai dokumen sumber', function () {
    $this->seed(MaarifRealScheduleSeeder::class);

    $spot = function (string $day, string $start, string $class) {
        return ClassSchedule::query()
            ->where('day_of_week', $day)
            ->where('start_time', $start)
            ->whereHas('classroom', fn ($q) => $q->where('name', $class))
            ->with(['subject', 'teacher.user'])
            ->first();
    };

    // Senin jam 2: X-1 = guru 11 (Yeti Rosmiati), XI SOSHUM 1 = guru 27 (Jihan)
    expect($spot('Senin', '07:35:00', 'X-1')->teacher->user->name)->toContain('Yeti Rosmiati');
    expect($spot('Senin', '07:35:00', 'X-1')->subject->code)->toBe('GEO');
    expect($spot('Senin', '07:35:00', 'XI SOSHUM 1')->teacher->user->name)->toContain('Jihan Julyanti');

    // Kamis jam 1: X-1 = guru 18 (Agnia — PKn)
    expect($spot('Kamis', '07:00:00', 'X-1')->subject->code)->toBe('PKN');

    // Senin jam 13: XI SAINS = guru 23 mapel peminatan (Biologi Peminatan)
    expect($spot('Senin', '14:30:00', 'XI SAINS')->subject->code)->toBe('BIOP');

    // Jumat jam 1: XI SOSHUM 1 = guru 27 (Sosiologi, 5 JP)
    expect($spot('Jumat', '07:00:00', 'XI SOSHUM 1')->teacher->user->name)->toContain('Jihan Julyanti');

    // Blok non-tatap muka tidak dibuatkan jadwal (Senin jam 10 = Shalat Dzuhur + MBG)
    expect(ClassSchedule::where('day_of_week', 'Senin')->where('start_time', '12:45:00')->exists())->toBeFalse();
});

it('bersifat idempoten saat dijalankan ulang', function () {
    $this->seed(MaarifRealScheduleSeeder::class);
    $this->seed(MaarifRealScheduleSeeder::class);

    expect(ClassSchedule::count())->toBe(508);
    expect(Subject::count())->toBe(32);
});
