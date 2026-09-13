<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Student extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'nisn',
        'classroom_id',
        'wali_name',
        'wali_phone',
        'phone_number',
        'birth_date',
        'status',
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

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function lessonAttendances(): HasMany
    {
        return $this->hasMany(LessonAttendance::class, 'student_id');
    }

    public function dailyAttendances(): HasManyThrough
    {
        return $this->hasManyThrough(DailyAttendance::class, User::class, 'id', 'user_id', 'user_id', 'id');
    }

    // --- Accessor kompatibilitas dengan tampilan lama yang membaca profil ---

    public function getNameAttribute(): string
    {
        return $this->user->name;
    }

    public function getIdentityNumberAttribute(): ?string
    {
        return $this->nisn;
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
