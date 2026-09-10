<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Attempt to authenticate a user using identity number or email
     *
     * @return array{success: bool, user?: User, error?: string}
     */
    public function attemptLogin(string $login, string $password, bool $remember = true): array
    {
        $loginInput = trim($login);

        $user = User::where(function ($query) use ($loginInput) {
            $query->where('identity_number', $loginInput)
                ->orWhere('email', $loginInput);
            $query->whereRaw('LOWER(email) = ?', [strtolower($loginInput)])
                ->orWhere('identity_number', $loginInput);
        })->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'error' => 'Nomor identitas (NISN / NIP / Email) atau kata sandi tidak cocok.',
                'error' => 'Alamat email atau kata sandi tidak cocok.',
            ];
        }

        if (! $user->is_active) {
            return [
                'success' => false,
                'error' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Admin.',
            ];
        }

        Auth::login($user, $remember);

        return [
            'success' => true,
            'user' => $user,
        ];
    }

    /**
     * Terminate the authenticated user session
     */
    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Update the authenticated user's password
     *
     * @throws ValidationException
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi saat ini tidak sesuai.',
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }

    /**
     * Determine home route name based on user role
     */
    public function getHomeRouteName(User $user): string
    {
        return match ($user->role) {
            'admin' => 'admin.dashboard',
            'guru' => 'guru.dashboard',
            default => 'siswa.dashboard',
        };
    }

    /**
     * Update user profile photo and clean up old file if exists.
     */
    public function updateProfilePhoto(User $user, UploadedFile $photo): string
    {
        $this->deletePhysicalPhoto($user->profile_photo_path);

        $path = $photo->store('profile-photos', 'public');

        $user->update([
            'profile_photo_path' => $path,
        ]);

        return $path;
    }

    /**
     * Delete user profile photo and clean up storage.
     */
    public function deleteProfilePhoto(User $user): void
    {
        $this->deletePhysicalPhoto($user->profile_photo_path);

        $user->update([
            'profile_photo_path' => null,
        ]);
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
}
