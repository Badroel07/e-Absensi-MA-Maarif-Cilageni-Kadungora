<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\SchoolLocation;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;

class AcademicMasterService
{
    // ==========================================
    // CLASSROOM MANAGEMENT
    // ==========================================

    public function getAllClassroomsWithCount(): Collection
    {
        return Classroom::withCount('students')->orderBy('name')->get();
    }

    public function getAllClassrooms(): Collection
    {
        return Classroom::orderBy('name')->get();
    }

    public function createClassroom(array $data): Classroom
    {
        return Classroom::create($data);
    }

    public function updateClassroom(Classroom $classroom, array $data): bool
    {
        return $classroom->update($data);
    }

    public function deleteClassroom(Classroom $classroom): bool
    {
        return (bool) $classroom->delete();
    }

    // ==========================================
    // SUBJECT MANAGEMENT
    // ==========================================

    public function getAllSubjectsWithCount(): Collection
    {
        return Subject::withCount('schedules')->orderBy('name')->get();
    }

    public function getAllSubjects(): Collection
    {
        return Subject::orderBy('name')->get();
    }

    public function createSubject(array $data): Subject
    {
        return Subject::create($data);
    }

    public function updateSubject(Subject $subject, array $data): bool
    {
        return $subject->update($data);
    }

    public function deleteSubject(Subject $subject): bool
    {
        return (bool) $subject->delete();
    }

    // ==========================================
    // SCHOOL LOCATION & GEOFENCE
    // ==========================================

    public function getActiveLocation(): ?SchoolLocation
    {
        return SchoolLocation::getActiveLocation();
    }

    public function updateOrCreateLocation(array $data): SchoolLocation
    {
        $location = SchoolLocation::getActiveLocation();

        if ($location) {
            $location->update($data);

            return $location;
        }

        return SchoolLocation::create(array_merge($data, ['is_active' => true]));
    }
}
