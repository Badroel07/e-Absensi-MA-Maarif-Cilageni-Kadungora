<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserManagementService
{
    /**
     * Retrieve paginated student list with optional search and classroom filter
     */
    public function getStudentsPaginated(?string $search = null, ?string $classroomId = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Student::query()
            ->select('students.*')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->with(['user', 'classroom']);

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('students.nisn', 'like', "%{$search}%");
            });
        }

        if (! empty($classroomId)) {
            $query->where('students.classroom_id', $classroomId);
        }

        return $query->orderBy('users.name')->paginate($perPage)->withQueryString();
    }

    /**
     * Create a new student account (akun login + profil siswa) with default password
     *
     * @param  array<string, mixed>  $data
     * @return array{user: User, default_password: string}
     */
    public function createStudent(array $data): array
    {
        $defaultPassword = 'akunsiswa@maarif';
        $email = ! empty($data['email']) ? $data['email'] : $data['nisn'].'@siswa.maarif.sch.id';

        $photoPath = null;
        if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            $photoPath = $data['photo']->store('profile-photos', 'public');
        }

        return DB::transaction(function () use ($data, $defaultPassword, $email, $photoPath) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make($defaultPassword),
                'role' => 'siswa',
                'profile_photo_path' => $photoPath,
                'is_active' => true,
            ]);

            $user->student()->create([
                'nisn' => $data['nisn'],
                'classroom_id' => $data['classroom_id'],
                'phone_number' => $data['phone_number'] ?? null,
                'birth_date' => $data['birth_date'],
                'status' => 'AKTIF',
            ]);

            return [
                'user' => $user,
                'default_password' => $defaultPassword,
            ];
        });
    }

    /**
     * Update an existing student (akun + profil)
     *
     * @param  array<string, mixed>  $data
     */
    public function updateStudent(User $user, array $data): bool
    {
        $userUpdate = [
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];

        if (isset($data['email'])) {
            $userUpdate['email'] = $data['email'];
        }

        if (! empty($data['remove_photo'])) {
            $this->deletePhysicalPhoto($user->profile_photo_path);
            $userUpdate['profile_photo_path'] = null;
        } elseif (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            $this->deletePhysicalPhoto($user->profile_photo_path);
            $userUpdate['profile_photo_path'] = $data['photo']->store('profile-photos', 'public');
        }

        return DB::transaction(function () use ($user, $data, $userUpdate) {
            $user->update($userUpdate);

            return (bool) $user->student->update([
                'nisn' => $data['nisn'],
                'classroom_id' => $data['classroom_id'],
                'phone_number' => $data['phone_number'] ?? null,
                'birth_date' => $data['birth_date'],
            ]);
        });
    }

    /**
     * Retrieve paginated teacher list with optional search query
     */
    public function getTeachersPaginated(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Teacher::query()
            ->select('teachers.*')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->with('user');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('teachers.nip', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('users.name')->paginate($perPage)->withQueryString();
    }

    /**
     * Create a new teacher account (akun login + profil guru) with default password
     *
     * @param  array<string, mixed>  $data
     * @return array{user: User, default_password: string}
     */
    public function createTeacher(array $data): array
    {
        $defaultPassword = 'akunguru@maarif';

        $photoPath = null;
        if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            $photoPath = $data['photo']->store('profile-photos', 'public');
        }

        return DB::transaction(function () use ($data, $defaultPassword, $photoPath) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? $data['nip'].'@maarif.sch.id',
                'password' => Hash::make($defaultPassword),
                'role' => 'guru',
                'profile_photo_path' => $photoPath,
                'is_active' => true,
            ]);

            $user->teacher()->create([
                'nip' => $data['nip'],
                'jabatan' => $data['jabatan'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'birth_date' => $data['birth_date'],
                'status_kepegawaian' => 'AKTIF',
            ]);

            return [
                'user' => $user,
                'default_password' => $defaultPassword,
            ];
        });
    }

    /**
     * Update an existing teacher (akun + profil)
     *
     * @param  array<string, mixed>  $data
     */
    public function updateTeacher(User $user, array $data): bool
    {
        $userUpdate = [
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];

        if (! empty($data['remove_photo'])) {
            $this->deletePhysicalPhoto($user->profile_photo_path);
            $userUpdate['profile_photo_path'] = null;
        } elseif (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            $this->deletePhysicalPhoto($user->profile_photo_path);
            $userUpdate['profile_photo_path'] = $data['photo']->store('profile-photos', 'public');
        }

        return DB::transaction(function () use ($user, $data, $userUpdate) {
            $user->update($userUpdate);

            return (bool) $user->teacher->update([
                'nip' => $data['nip'],
                'jabatan' => $data['jabatan'] ?? $user->teacher->jabatan,
                'phone_number' => $data['phone_number'] ?? null,
                'birth_date' => $data['birth_date'],
            ]);
        });
    }

    /**
     * Delete a user record (profil domain ikut terhapus via cascade)
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
     * Reset user password to role-based default.
     */
    public function resetPasswordToDefault(User $user): string
    {
        $user->resetPasswordToDefault();

        return $user->getDefaultPassword();
    }
}
