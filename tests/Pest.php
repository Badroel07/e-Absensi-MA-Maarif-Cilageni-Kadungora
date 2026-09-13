<?php

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. Of course, you may
| extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Helpers for creating users together with their domain profile
| (users = akun login; students/teachers = profil domain).
|
*/

function createGuru(array $attributes = []): User
{
    $nip = $attributes['nip'] ?? '19'.random_int(10000000000000, 99999999999999);

    $user = User::create([
        'name' => $attributes['name'] ?? 'Ust. Guru Tes '.substr((string) $nip, -3),
        'email' => $attributes['email'] ?? ('guru'.random_int(100000, 999999).'@maarif.sch.id'),
        'password' => Hash::make($attributes['password'] ?? 'akunguru@maarif'),
        'role' => 'guru',
        'is_active' => $attributes['is_active'] ?? true,
    ]);

    $user->teacher()->create([
        'nip' => $nip,
        'birth_date' => $attributes['birth_date'] ?? '1980-01-01',
        'phone_number' => $attributes['phone_number'] ?? null,
    ]);

    return $user->fresh();
}

function createSiswa(array $attributes = []): User
{
    $nisn = $attributes['nisn'] ?? (string) random_int(1000000000, 9999999999);

    $user = User::create([
        'name' => $attributes['name'] ?? 'Siswa Tes '.substr($nisn, -3),
        'email' => $attributes['email'] ?? ($nisn.'@siswa.maarif.sch.id'),
        'password' => Hash::make($attributes['password'] ?? 'akunsiswa@maarif'),
        'role' => 'siswa',
        'is_active' => $attributes['is_active'] ?? true,
    ]);

    $user->student()->create([
        'nisn' => $nisn,
        'classroom_id' => $attributes['classroom_id'] ?? null,
        'birth_date' => $attributes['birth_date'] ?? '2010-01-01',
        'phone_number' => $attributes['phone_number'] ?? null,
        'status' => 'AKTIF',
    ]);

    return $user->fresh();
}

function createAdmin(array $attributes = []): User
{
    $nip = $attributes['nip'] ?? '19'.random_int(10000000000000, 99999999999999);

    $user = User::create([
        'name' => $attributes['name'] ?? 'Admin Tes',
        'email' => $attributes['email'] ?? ('admin'.random_int(100000, 999999).'@maarif.sch.id'),
        'password' => Hash::make($attributes['password'] ?? 'p@55w0rd'),
        'role' => 'admin',
        'is_active' => $attributes['is_active'] ?? true,
    ]);

    if ($attributes['with_profile'] ?? true) {
        $user->teacher()->create([
            'nip' => $nip,
            'birth_date' => $attributes['birth_date'] ?? '1980-01-01',
        ]);
    }

    return $user->fresh();
}

function createStudentProfile(User $user, array $attributes = []): Student
{
    return $user->student()->create([
        'nisn' => $attributes['nisn'] ?? (string) random_int(1000000000, 9999999999),
        'classroom_id' => $attributes['classroom_id'] ?? null,
        'birth_date' => $attributes['birth_date'] ?? '2010-01-01',
        'status' => 'AKTIF',
    ]);
}

function createTeacherProfile(User $user, array $attributes = []): Teacher
{
    return $user->teacher()->create([
        'nip' => $attributes['nip'] ?? '19'.random_int(10000000000000, 99999999999999),
        'birth_date' => $attributes['birth_date'] ?? '1980-01-01',
    ]);
}
