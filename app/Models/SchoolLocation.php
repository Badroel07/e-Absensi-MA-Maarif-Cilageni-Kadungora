<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolLocation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius_meters',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_meters' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public static function getActiveLocation(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Calculate Haversine distance in meters from given coordinates to this school location
     */
    public function calculateDistance(float $lat, float $lng): float
    {
        $earthRadius = 6371000; // in meters
        $latDelta = deg2rad($this->latitude - $lat);
        $lonDelta = deg2rad($this->longitude - $lng);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat)) * cos(deg2rad($this->latitude)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    public function isWithinRadius(float $lat, float $lng, int $toleranceMeters = 0): bool
    {
        $distance = $this->calculateDistance($lat, $lng);

        return $distance <= ($this->radius_meters + $toleranceMeters);
    }
}
