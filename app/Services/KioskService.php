<?php

namespace App\Services;

use App\Models\DailyAttendance;
use App\Models\SchoolLocation;
use Carbon\Carbon;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class KioskService
{
    protected string $kioskId;

    protected string $secretKey;

    public function __construct()
    {
        $this->kioskId = 'kiosk-ruang-guru-ma-maarif';
        $this->secretKey = config('app.key') ?: (env('APP_KEY') ?: 'maarif-secret-kiosk-key-2026');
    }

    /**
     * Generate dynamic HMAC-SHA256 token for 20-second window
     */
    public function generateTokenPayload(): array
    {
        $nowTs = Carbon::now()->timestamp;
        $window = (int) floor($nowTs / 20);
        $token = hash_hmac('sha256', $window.':'.$this->kioskId, $this->secretKey);
        $remainingSeconds = 20 - ($nowTs % 20);

        return [
            'token' => $token,
            'window' => $window,
            'remaining_seconds' => $remainingSeconds,
            'kiosk_id' => $this->kioskId,
        ];
    }

    /**
     * Validate scanned token with tolerance for previous 20s window
     */
    public function validateToken(string $token): bool
    {
        $nowTs = Carbon::now()->timestamp;
        $currentWindow = (int) floor($nowTs / 20);
        $expectedCurrent = hash_hmac('sha256', $currentWindow.':'.$this->kioskId, $this->secretKey);

        if (hash_equals($expectedCurrent, $token)) {
            return true;
        }

        // Previous window (network delay tolerance)
        $previousWindow = $currentWindow - 1;
        $expectedPrevious = hash_hmac('sha256', $previousWindow.':'.$this->kioskId, $this->secretKey);

        return hash_equals($expectedPrevious, $token);
    }

    /**
     * Render SVG QR Code data URI or SVG string
     */
    public function renderQrSvg(string $data): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64' => false,
            'eccLevel' => EccLevel::L,
            'scale' => 6,
        ]);

        return (new QRCode($options))->render($data);
    }

    /**
     * Get recent teacher attendances for today
     */
    public function getRecentAttendances(int $limit = 5): Collection
    {
        return DailyAttendance::with('user')
            ->whereDate('attendance_date', Carbon::today())
            ->whereNotNull('check_in_time')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Gather complete view payload for the Kiosk terminal screen
     *
     * @return array<string, mixed>
     */
    public function getKioskIndexData(): array
    {
        $payload = $this->generateTokenPayload();
        $qrSvg = $this->renderQrSvg($payload['token']);
        $school = SchoolLocation::getActiveLocation();
        $recentAttendances = $this->getRecentAttendances(5);

        return [
            'token' => $payload['token'],
            'qrSvg' => $qrSvg,
            'remainingSeconds' => $payload['remaining_seconds'],
            'school' => $school,
            'currentDate' => Carbon::now()->translatedFormat('l, d F Y'),
            'recentAttendances' => $recentAttendances,
        ];
    }

    /**
     * Get fresh token and SVG data for API polling
     *
     * @return array<string, mixed>
     */
    public function getTokenPayloadResponse(): array
    {
        $payload = $this->generateTokenPayload();
        $qrSvg = $this->renderQrSvg($payload['token']);

        return [
            'token' => $payload['token'],
            'remaining_seconds' => $payload['remaining_seconds'],
            'qr_svg' => $qrSvg,
            'latest_event' => Cache::get('kiosk_latest_event'),
            'server_time' => Carbon::now()->format('H:i:s'),
            'server_time_ms' => Carbon::now()->getTimestampMs(),
        ];
    }

    /**
     * Gather latest event and transformed recent attendances for polling
     *
     * @return array<string, mixed>
     */
    public function getPollEventData(): array
    {
        $latestEvent = Cache::get('kiosk_latest_event');

        $recentAttendances = $this->getRecentAttendances(5)->map(function ($att) {
            return [
                'name' => $att->user?->name ?? 'Dewan Guru',
                'identity_number' => $att->user?->identity_number ?? '',
                'check_in_time' => substr($att->check_in_time ?? '', 0, 5),
                'check_in_status' => $att->check_in_status ?? 'HADIR',
                'check_out_time' => $att->check_out_time ? substr($att->check_out_time, 0, 5) : null,
                'is_completed' => ! empty($att->check_out_time),
            ];
        });

        return [
            'latest_event' => $latestEvent,
            'recent_attendances' => $recentAttendances,
            'server_time' => Carbon::now()->format('H:i:s'),
            'server_time_ms' => Carbon::now()->getTimestampMs(),
        ];
    }
}
