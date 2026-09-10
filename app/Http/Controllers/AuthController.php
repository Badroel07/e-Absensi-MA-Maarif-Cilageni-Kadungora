<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended(route($this->authService->getHomeRouteName(Auth::user())));
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $loginValue = $request->input('email', $request->input('login'));
        $request->merge(['login' => $loginValue]);

        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Silakan masukkan alamat email Anda.',
            'password.required' => 'Silakan masukkan kata sandi Anda.',
        ]);

        $remember = $request->boolean('remember', true);

        $result = $this->authService->attemptLogin($credentials['login'], $credentials['password'], $remember);

        if (! $result['success']) {
            return back()->withInput()->withErrors([
                'login' => $result['error'],
                'email' => $result['error'],
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route($this->authService->getHomeRouteName($result['user'])));
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout($request);

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    public function showProfile(): View
    {
        $user = Auth::user();

        return view('profile.index', compact('user'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = Auth::user();

        try {
            $this->authService->updatePassword($user, $request->current_password, $request->password);

            return back()->with('success', 'Kata sandi berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function updateProfilePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'photo.required' => 'Silakan pilih berkas foto terlebih dahulu.',
            'photo.image' => 'Berkas harus berupa gambar.',
            'photo.mimes' => 'Format foto harus berupa JPEG, PNG, JPG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal adalah 2 MB (2048 KB).',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $this->authService->updateProfilePhoto($user, $request->file('photo'));

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function deleteProfilePhoto(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $this->authService->deleteProfilePhoto($user);

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }
}
