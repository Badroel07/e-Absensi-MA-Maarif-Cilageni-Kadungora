<?php

use App\Models\SchoolLocation;

test('calculates Haversine distance correctly for exact coordinates', function () {
    $location = new SchoolLocation([
        'name' => "MA Ma'arif Cilageni Kadungora",
        'latitude' => -7.1147000,
        'longitude' => 107.8845000,
        'radius_meters' => 75,
    ]);

    // Exact same coordinates -> distance must be 0 meters
    $distance = $location->calculateDistance(-7.1147000, 107.8845000);
    expect($distance)->toBe(0.0);
    expect($location->isWithinRadius(-7.1147000, 107.8845000))->toBeTrue();
});

test('identifies coordinates within and outside the geofence radius', function () {
    $location = new SchoolLocation([
        'name' => "MA Ma'arif Cilageni Kadungora",
        'latitude' => -7.1147000,
        'longitude' => 107.8845000,
        'radius_meters' => 75,
    ]);

    // Slight shift (~30 meters away) -> within 75m radius
    $nearbyLat = -7.1149000;
    $nearbyLng = 107.8846000;
    $distanceNear = $location->calculateDistance($nearbyLat, $nearbyLng);
    expect($distanceNear)->toBeLessThan(75.0);
    expect($location->isWithinRadius($nearbyLat, $nearbyLng))->toBeTrue();

    // Far away (~1 kilometer away in Bandung/Garut) -> outside radius
    $farLat = -7.1250000;
    $farLng = 107.8950000;
    $distanceFar = $location->calculateDistance($farLat, $farLng);
    expect($distanceFar)->toBeGreaterThan(500.0);
    expect($location->isWithinRadius($farLat, $farLng))->toBeFalse();
});
