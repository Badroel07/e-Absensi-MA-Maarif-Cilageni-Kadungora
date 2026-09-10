<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAttendance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'check_in_status',
        'check_out_status',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'check_in_distance_meters',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in_latitude' => 'float',
            'check_in_longitude' => 'float',
            'check_out_latitude' => 'float',
            'check_out_longitude' => 'float',
            'check_in_distance_meters' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
