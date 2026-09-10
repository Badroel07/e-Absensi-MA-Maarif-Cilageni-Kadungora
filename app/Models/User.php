<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'identity_number',
        'name',
        'email',
        'birth_date',
        'password',
        'role',
        'phone_number',
        'profile_photo_path',
        'classroom_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teachingSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class, 'teacher_id');
    }

    public function dailyAttendances(): HasMany
    {
        return $this->hasMany(DailyAttendance::class, 'user_id');
    }

    public function lessonAttendances(): HasMany
    {
        return $this->hasMany(LessonAttendance::class, 'student_id');
    }

    public function getDefaultPassword(): string
    {
        if (! $this->birth_date) {
            return '12345678';
        }

        return Carbon::parse($this->birth_date)->format('dmY');
    }

    public function resetPasswordToDefault(): void
    {
        $this->update([
            'password' => Hash::make($this->getDefaultPassword()),
        ]);
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if ($this->profile_photo_path && Storage::disk('public')->exists($this->profile_photo_path)) {
            return Storage::disk('public')->url($this->profile_photo_path);
        }

        return null;
    }
}
