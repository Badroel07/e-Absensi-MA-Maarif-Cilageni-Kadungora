<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'schedule_id',
        'teacher_id',
        'pin_code',
        'duration_minutes',
        'started_at',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'schedule_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(LessonAttendance::class, 'session_id');
    }

    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE' && ! $this->isExpired();
    }

    public function isLocked(): bool
    {
        return $this->status === 'LOCKED';
    }

    public function getRemainingSecondsAttribute(): int
    {
        if ($this->isExpired() || $this->status !== 'ACTIVE') {
            return 0;
        }

        return max(0, Carbon::now()->diffInSeconds($this->expires_at, false));
    }
}
