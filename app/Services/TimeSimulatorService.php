<?php

namespace App\Services;

use Carbon\Carbon;

class TimeSimulatorService
{
    private const FILE_NAME = 'framework/simulated_time.json';

    /**
     * Determine if global time simulation is currently active.
     */
    public static function isSimulated(): bool
    {
        if (! app()->environment('local', 'testing')) {
            return false;
        }

        return self::getSimulatedOffset() !== null;
    }

    /**
     * Set the global simulated time.
     * Stores the target timestamp and the real system timestamp at which it was set,
     * allowing simulated time to naturally flow forward continuously across all devices.
     */
    public static function setSimulatedTime(Carbon $targetTime): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $filePath = storage_path(self::FILE_NAME);
        $directory = dirname($filePath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $data = [
            'simulated_anchor' => $targetTime->timestamp,
            'real_anchor' => time(),
            'formatted' => $targetTime->toDateTimeString(),
        ];

        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
        self::apply();
    }

    /**
     * Add minutes to the currently running simulated time.
     */
    public static function addMinutes(int $minutes): void
    {
        $current = self::getNow();
        self::setSimulatedTime($current->copy()->addMinutes($minutes));
    }

    /**
     * Reset the global simulated time back to real system time.
     */
    public static function reset(): void
    {
        $filePath = storage_path(self::FILE_NAME);
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        Carbon::setTestNow(null);
    }

    /**
     * Retrieve the stored simulation offset parameters if active.
     *
     * @return array{simulated_anchor: int, real_anchor: int, formatted: string}|null
     */
    public static function getSimulatedOffset(): ?array
    {
        $filePath = storage_path(self::FILE_NAME);
        if (! file_exists($filePath)) {
            return null;
        }

        $raw = @file_get_contents($filePath);
        if (! $raw) {
            return null;
        }

        $data = json_decode($raw, true);
        if (! is_array($data) || ! isset($data['simulated_anchor'], $data['real_anchor'])) {
            return null;
        }

        return $data;
    }

    /**
     * Calculate and return the current simulated Carbon instance.
     */
    public static function getNow(): Carbon
    {
        $offset = self::getSimulatedOffset();
        if (! $offset) {
            return Carbon::now();
        }

        $elapsed = time() - $offset['real_anchor'];
        $simulatedTimestamp = $offset['simulated_anchor'] + $elapsed;

        return Carbon::createFromTimestamp($simulatedTimestamp, config('app.timezone'));
    }

    /**
     * Apply the simulated time to Carbon::setTestNow() for the current execution cycle.
     */
    public static function apply(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $offset = self::getSimulatedOffset();
        if ($offset) {
            $elapsed = time() - $offset['real_anchor'];
            $simulatedTimestamp = $offset['simulated_anchor'] + $elapsed;
            Carbon::setTestNow(Carbon::createFromTimestamp($simulatedTimestamp, config('app.timezone')));
        } else {
            Carbon::setTestNow(null);
        }
    }
}
