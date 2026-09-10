<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserManagementService
{
    /**
     * Retrieve paginated student list with optional search and classroom filter
     */
    public function getStudentsPaginated(?string $search = null, ?string $classroomId = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with('classroom')->where('role', 'siswa');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('identity_number', 'like', "%{$search}%");
            });
        }

        if (! empty($classroomId)) {
            $query->where('classroom_id', $classroomId);
        }

        return $query->orderBy('name')->paginate($perPage)->withQueryString();
    }

    /**
     * Create a new student account with automatic DDMMYYYY default password
     *
     * @param  array<string, mixed>  $data
     * @return array{user: User, default_password: string}
     */
    public function createStudent(array $data): array
    {
        $defaultPassword = Carbon::parse($data['birth_date'])->format('dmY');

        $photoPath = null;
        if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            $photoPath = $data['photo']->store('profile-photos', 'public');
        }

        $user = User::create([
            'identity_number' => $data['identity_number'],
            'name' => $data['name'],
            'birth_date' => $data['birth_date'],
            'password' => Hash::make($defaultPassword),
            'role' => 'siswa',
            'classroom_id' => $data['classroom_id'],
            'phone_number' => $data['phone_number'] ?? null,
            'profile_photo_path' => $photoPath,
            'is_active' => true,
        ]);

        return [
            'user' => $user,
            'default_password' => $defaultPassword,
        ];
    }

    /**
     * Update an existing student record
     *
     * @param  array<string, mixed>  $data
     */
    public function updateStudent(User $user, array $data): bool
    {
        $updateData = [
            'identity_number' => $data['identity_number'],
            'name' => $data['name'],
            'birth_date' => $data['birth_date'],
            'classroom_id' => $data['classroom_id'],
            'phone_number' => $data['phone_number'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];

        if (! empty($data['remove_photo'])) {
            $this->deletePhysicalPhoto($user->profile_photo_path);
            $updateData['profile_photo_path'] = null;
        } elseif (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            $this->deletePhysicalPhoto($user->profile_photo_path);
            $updateData['profile_photo_path'] = $data['photo']->store('profile-photos', 'public');
        }

        return $user->update($updateData);
    }

    /**
     * Retrieve paginated teacher list with optional search query
     */
    public function getTeachersPaginated(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::where('role', 'guru');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('identity_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->paginate($perPage)->withQueryString();
    }

    /**
     * Create a new teacher account with automatic DDMMYYYY default password
     *
     * @param  array<string, mixed>  $data
     * @return array{user: User, default_password: string}
     */
    public function createTeacher(array $data): array
    {
        $defaultPassword = Carbon::parse($data['birth_date'])->format('dmY');

        $photoPath = null;
        if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            $photoPath = $data['photo']->store('profile-photos', 'public');
        }

        $user = User::create([
            'identity_number' => $data['identity_number'],
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'birth_date' => $data['birth_date'],
            'password' => Hash::make($defaultPassword),
            'role' => 'guru',
            'phone_number' => $data['phone_number'] ?? null,
            'profile_photo_path' => $photoPath,
            'is_active' => true,
        ]);

        return [
            'user' => $user,
            'default_password' => $defaultPassword,
        ];
    }

    /**
     * Update an existing teacher record
     *
     * @param  array<string, mixed>  $data
     */
    public function updateTeacher(User $user, array $data): bool
    {
        $updateData = [
            'identity_number' => $data['identity_number'],
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'birth_date' => $data['birth_date'],
            'phone_number' => $data['phone_number'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];

        if (! empty($data['remove_photo'])) {
            $this->deletePhysicalPhoto($user->profile_photo_path);
            $updateData['profile_photo_path'] = null;
        } elseif (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            $this->deletePhysicalPhoto($user->profile_photo_path);
            $updateData['profile_photo_path'] = $data['photo']->store('profile-photos', 'public');
        }

        return $user->update($updateData);
    }

    /**
     * Delete a user record
     */
    public function deleteUser(User $user): bool
    {
        $this->deletePhysicalPhoto($user->profile_photo_path);

        return (bool) $user->delete();
    }

    /**
     * Delete physical photo file from public storage if it exists.
     */
    protected function deletePhysicalPhoto(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Reset user password to default format (DDMMYYYY)
     */
    public function resetPasswordToDefault(User $user): string
    {
        $user->resetPasswordToDefault();

        return $user->getDefaultPassword();
    }
}
