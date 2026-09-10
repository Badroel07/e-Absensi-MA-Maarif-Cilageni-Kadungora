<?php

namespace App\Models;

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

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->email) && ! empty($user->identity_number)) {
                $user->email = $user->isSiswa()
                    ? $user->identity_number.'@siswa.maarif.sch.id'
                    : $user->identity_number.'@maarif.sch.id';
            }
        });
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
        return match ($this->role) {
            'admin' => 'p@55w0rd',
            'guru' => 'akunguru@maarif',
            'siswa' => 'akunsiswa@maarif',
            default => 'akunsiswa@maarif',
        };
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
