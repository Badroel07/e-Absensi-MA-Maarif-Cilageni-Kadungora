<?php

use App\Models\User;

test('generates correct role-based default passwords', function () {
    $siswa = new User(['role' => 'siswa']);
    $guru = new User(['role' => 'guru']);
    $admin = new User(['role' => 'admin']);

    expect($siswa->getDefaultPassword())->toBe('akunsiswa@maarif');
    expect($guru->getDefaultPassword())->toBe('akunguru@maarif');
    expect($admin->getDefaultPassword())->toBe('p@55w0rd');
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
