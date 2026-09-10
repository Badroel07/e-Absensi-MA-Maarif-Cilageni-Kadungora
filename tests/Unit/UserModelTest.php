<?php

use App\Models\User;

test('generates correct default password format DDMMYYYY from birth date', function () {
    $user = new User([
        'name' => 'Ahmad Siswa',
        'identity_number' => '0091234501',
        'birth_date' => '2011-05-10',
        'role' => 'siswa',
    ]);

    expect($user->getDefaultPassword())->toBe('10052011');
});

test('role helpers accurately identify user roles', function () {
    $siswa = new User(['role' => 'siswa']);
    $guru = new User(['role' => 'guru']);
    $admin = new User(['role' => 'admin']);

    expect($siswa->isSiswa())->toBeTrue();
    expect($siswa->isGuru())->toBeFalse();
    expect($guru->isGuru())->toBeTrue();
    expect($admin->isAdmin())->toBeTrue();
});
