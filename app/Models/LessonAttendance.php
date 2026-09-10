<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonAttendance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'session_id',
        'schedule_id',
        'student_id',
        'attendance_date',
        'status',
        'notes',
        'confirmed_by',
        'verified_at',
        'latitude',
        'longitude',
        'distance_meters',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'verified_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'distance_meters' => 'float',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'session_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'schedule_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AttendanceAuditLog::class, 'lesson_attendance_id');
    }
}
