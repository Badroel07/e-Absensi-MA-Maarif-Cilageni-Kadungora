<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceAuditLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'lesson_attendance_id',
        'changed_by',
        'old_status',
        'new_status',
        'reason',
    ];

    public function lessonAttendance(): BelongsTo
    {
        return $this->belongsTo(LessonAttendance::class, 'lesson_attendance_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
