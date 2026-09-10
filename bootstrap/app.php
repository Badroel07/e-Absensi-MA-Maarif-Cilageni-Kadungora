<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SimulateTime;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'logout',
            'guru/check-status',
            'guru/scan/check-in',
            'guru/scan/check-out',
            'guru/sessions/*',
            'siswa/check-status',
            'siswa/verify-pin',
            'dev/time-simulator',
        ]);
        $middleware->web(append: [
            SimulateTime::class,
        ]);
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'code' => 'CSRF_MISMATCH',
                    'message' => 'Sesi keamanan Anda telah diperbarui. Silakan muat ulang (refresh) halaman.',
                ], 419);
            }

            if ($request->is('logout')) {
                return redirect()->route('login')->with('info', 'Sesi Anda telah berakhir.');
            }

            if (auth()->check()) {
                return back()->withInput()->with('warning', 'Sesi keamanan telah diperbarui. Silakan coba kembali.');
            }

            return redirect()->route('login')->with('warning', 'Sesi Anda telah kedaluwarsa karena tidak ada aktivitas. Silakan masuk kembali.');
        });
    })->create();
