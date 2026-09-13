<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClassSchedule extends Model
{
    use HasFactory, HasUuids;

    protected $casts = [
        'late_tolerance_minutes' => 'integer',
    ];

    protected $fillable = [
        'classroom_id',
        'subject_id',
        'teacher_id',
        'day_of_week',
        'start_time',
        'end_time',
        'late_tolerance_minutes',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'schedule_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(LessonAttendance::class, 'schedule_id');
    }

    public function todaySession(): HasOne
    {
        return $this->hasOne(ClassSession::class, 'schedule_id')
            ->whereDate('created_at', Carbon::today())
            ->latestOfMany();
    }

    public function teacherAttendances(): HasMany
    {
        return $this->hasMany(TeacherSessionAttendance::class, 'schedule_id');
    }

    public function todayTeacherAttendance(): HasOne
    {
        return $this->hasOne(TeacherSessionAttendance::class, 'schedule_id')
            ->whereDate('attendance_date', Carbon::today());
    }

    public function isLockedToday(): bool
    {
        $session = $this->todaySession;

        return $session !== null && $session->status === 'LOCKED';
    }
}
