<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'nip',
        'jabatan',
        'status_kepegawaian',
        'phone_number',
        'birth_date',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class, 'teacher_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'teacher_id');
    }

    public function sessionAttendances(): HasMany
    {
        return $this->hasMany(TeacherSessionAttendance::class, 'teacher_id');
    }

    // --- Accessor kompatibilitas dengan tampilan lama yang membaca profil ---

    public function getNameAttribute(): string
    {
        return $this->user->name;
    }

    public function getIdentityNumberAttribute(): ?string
    {
        return $this->nip;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->user->profile_photo_url;
    }

    public function getEmailAttribute(): ?string
    {
        return $this->user->email;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->user->is_active;
    }

    public function getDefaultPassword(): string
    {
        return $this->user->getDefaultPassword();
    }

    public function resetPasswordToDefault(): void
    {
        $this->user->resetPasswordToDefault();
    }
}
