<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'grade_level',
        'academic_year',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'classroom_id')->where('role', 'siswa');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }
}
