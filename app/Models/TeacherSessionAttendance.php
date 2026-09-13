<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherSessionAttendance extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_HADIR = 'HADIR';

    public const STATUS_IZIN = 'IZIN';

    public const STATUS_SAKIT = 'SAKIT';

    public const STATUS_DINAS_LUAR = 'DINAS_LUAR';

    public const STATUS_ALPA = 'ALPA';

    protected $fillable = [
        'schedule_id',
        'teacher_id',
        'class_session_id',
        'attendance_date',
        'status',
        'attended_at',
        'latitude',
        'longitude',
        'distance_meters',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'attended_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'distance_meters' => 'float',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'schedule_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'class_session_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * "Terlambat" bukan kategori terpisah: penanda pada status HADIR
     * ketika sesi dibuka melewati batas jam mulai mapel + toleransi jadwal.
     */
    public function isLate(): bool
    {
        if ($this->status !== self::STATUS_HADIR || $this->attended_at === null || $this->schedule === null) {
            return false;
        }

        $start = Carbon::parse($this->schedule->start_time)
            ->setDateFrom($this->attended_at)
            ->addMinutes((int) ($this->schedule->late_tolerance_minutes ?? 0));

        return $this->attended_at->greaterThan($start);
    }
}
