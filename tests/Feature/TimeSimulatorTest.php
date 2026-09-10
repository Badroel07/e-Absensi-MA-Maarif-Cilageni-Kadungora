<?php

use App\Services\TimeSimulatorService;

beforeEach(function () {
    TimeSimulatorService::reset();
});

afterEach(function () {
    TimeSimulatorService::reset();
});

test('TC-ADM-DEV-001 / TC-DEV-001: Time simulator jump waktu beroperasi secara global lintas request dan session', function () {
    $response = $this->post(route('dev.time-simulator'), [
        'action' => 'jump',
        'time' => '07:30',
    ]);

    $response->assertRedirect();
    expect(TimeSimulatorService::isSimulated())->toBeTrue();
    expect(now()->format('H:i'))->toBe('07:30');

    // Clear session to simulate another device
    $this->flushSession();

    $page = $this->get('/login');
    $page->assertStatus(200);
    expect(now()->format('H:i'))->toBe('07:30');
});

test('TC-DEV-001: Time simulator add menit memajukan waktu simulasi untuk pengujian expiry sesi', function () {
    $this->post(route('dev.time-simulator'), [
        'action' => 'jump',
        'time' => '08:00',
    ]);

    expect(now()->format('H:i'))->toBe('08:00');

    // Add 3 minutes
    $this->post(route('dev.time-simulator'), [
        'action' => 'add',
        'minutes' => 3,
    ]);

    expect(now()->format('H:i'))->toBe('08:03');
});

test('TC-DEV-001: Time simulator reset mengembalikan waktu ke real-time sistem', function () {
    $this->post(route('dev.time-simulator'), [
        'action' => 'jump',
        'time' => '10:00',
    ]);

    expect(TimeSimulatorService::isSimulated())->toBeTrue();

    $this->post(route('dev.time-simulator'), [
        'action' => 'reset',
    ]);

    expect(TimeSimulatorService::isSimulated())->toBeFalse();
});
