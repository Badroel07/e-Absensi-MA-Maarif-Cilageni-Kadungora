<?php

use App\Models\SchoolLocation;
use App\Models\User;
use App\Services\KioskService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->location = SchoolLocation::create([
        'name' => "MA Ma'arif Cilageni Kadungora",
        'latitude' => -7.010000,
        'longitude' => 107.900000,
        'radius_meters' => 75,
        'is_active' => true,
    ]);

    $this->guru = User::create([
        'identity_number' => '198012012010011001',
        'name' => 'Ust. H. Ahmad Dahlan',
        'email' => 'ahmad@maarif.sch.id',
        'birth_date' => '1980-12-01',
        'password' => Hash::make('01121980'),
        'role' => 'guru',
        'is_active' => true,
    ]);

    $this->kioskService = new KioskService;
});

test('TC-KIOSK-001: Kiosk index — render QR code SVG dinamis, info madrasah, dan hitung mundur', function () {
    $response = $this->get('/kiosk');

    $response->assertStatus(200);
    $response->assertSee("MA Ma'arif Cilageni Kadungora", false);
    $response->assertSee('KODE QR PRESENSI');
    expect($response->viewData('token'))->not->toBeNull();
    expect($response->viewData('qrSvg'))->toContain('<svg');
});

test('TC-KIOSK-002: Kiosk token — endpoint polling JSON mengembalikan token segar dan payload SVG', function () {
    $response = $this->getJson('/kiosk/token');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'token',
        'remaining_seconds',
        'qr_svg',
        'server_time',
        'server_time_ms',
    ]);
});

test('TC-KIOSK-003: Kiosk poll-event — menampilkan event sapaan guru check-in terbaru', function () {
    $tokenPayload = $this->kioskService->generateTokenPayload();

    $this->actingAs($this->guru);
    $this->postJson('/guru/scan/check-in', [
        'qr_token' => $tokenPayload['token'],
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ])->assertStatus(200);

    // Kiosk polling returns the greeting event
    $pollResponse = $this->getJson('/kiosk/poll-event');
    $pollResponse->assertStatus(200);
    $pollResponse->assertJson([
        'latest_event' => [
            'type' => 'check_in',
            'teacher_name' => 'Ust. H. Ahmad Dahlan',
            'title' => 'Selamat Datang!',
        ],
    ]);
    expect($pollResponse->json('recent_attendances'))->toHaveCount(1);
});

test('TC-KIOSK-004: Kiosk — HMAC token window saat ini (window 0) valid', function () {
    $payload = $this->kioskService->generateTokenPayload();
    $currentToken = $payload['token'];

    expect($this->kioskService->validateToken($currentToken))->toBeTrue();
});

test('TC-KIOSK-005: Kiosk — HMAC token window sebelumnya (window -1, toleransi 40s) tetap valid', function () {
    $nowTs = Carbon::now()->timestamp;
    $prevWindow = (int) floor($nowTs / 20) - 1;
    $secret = config('app.key') ?: (env('APP_KEY') ?: 'maarif-secret-kiosk-key-2026');
    $prevToken = hash_hmac('sha256', $prevWindow.':kiosk-ruang-guru-ma-maarif', $secret);

    expect($this->kioskService->validateToken($prevToken))->toBeTrue();
});

test('TC-KIOSK-006: Kiosk — HMAC token kedaluwarsa >= window -2 (>40s) ditolak', function () {
    $nowTs = Carbon::now()->timestamp;
    $expiredWindow = (int) floor($nowTs / 20) - 2;
    $secret = config('app.key') ?: (env('APP_KEY') ?: 'maarif-secret-kiosk-key-2026');
    $expiredToken = hash_hmac('sha256', $expiredWindow.':kiosk-ruang-guru-ma-maarif', $secret);

    expect($this->kioskService->validateToken($expiredToken))->toBeFalse();
});

test('TC-KIOSK-007: Kiosk — seluruh route kiosk dapat diakses publik tanpa login', function () {
    $this->get('/kiosk')->assertStatus(200);
    $this->getJson('/kiosk/token')->assertStatus(200);
    $this->getJson('/kiosk/poll-event')->assertStatus(200);
});
