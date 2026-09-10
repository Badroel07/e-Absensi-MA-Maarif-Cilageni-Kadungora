<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            return redirect()->route('login')->withErrors(['login' => 'Akun dinonaktifkan.']);
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Akses tidak diizinkan untuk peran Anda.');
        }

        return $next($request);
    }
}
