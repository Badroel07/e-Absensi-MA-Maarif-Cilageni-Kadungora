<?php

use App\Services\KioskService;

test('generates valid dynamic HMAC-SHA256 token payload for 20-second window', function () {
    $service = new KioskService;
    $payload = $service->generateTokenPayload();

    expect($payload)->toHaveKeys(['token', 'window', 'remaining_seconds', 'kiosk_id'])
        ->and($payload['token'])->toBeString()->toHaveLength(64)
        ->and($payload['remaining_seconds'])->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual(20);

    // Validates freshly generated token
    expect($service->validateToken($payload['token']))->toBeTrue();
});

test('rejects forged or expired kiosk tokens', function () {
    $service = new KioskService;

    expect($service->validateToken('forged-token-value-1234567890abcdef'))->toBeFalse();
    expect($service->validateToken(''))->toBeFalse();
});

test('renders valid SVG for dynamic QR code', function () {
    $service = new KioskService;
    $payload = $service->generateTokenPayload();
    $svg = $service->renderQrSvg($payload['token']);

    expect($svg)->toBeString()
        ->and($svg)->toContain('<svg')
        ->and($svg)->toContain('</svg>');
});
