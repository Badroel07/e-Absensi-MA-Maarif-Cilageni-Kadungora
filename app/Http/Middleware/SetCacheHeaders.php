<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetCacheHeaders
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $path = $request->path();

        // 1. Immutable hashed Vite assets (/build/assets/*) + images/fonts
        if ($request->is('build/*') || str_starts_with($path, 'build/')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            $response->headers->set('CDN-Cache-Control', 'public, max-age=31536000, immutable');
            $response->headers->set('Cloudflare-CDN-Cache-Control', 'public, max-age=31536000, immutable');

            return $response;
        }

        if ($request->is('img/*', 'storage/*', 'favicon.*', 'favicon/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=2592000, must-revalidate');
            $response->headers->set('CDN-Cache-Control', 'public, max-age=2592000');

            return $response;
        }

        if ($request->is('icon-*.png', 'icon-*.jpg', 'icon-*.webp', 'icon-*.jpeg')) {
            $response->headers->set('Cache-Control', 'public, max-age=2592000');

            return $response;
        }

        // Static images/fonts by extension (covers /storage/profile-photos/*.jpg etc even when Auth::check())
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'avif', 'gif', 'ico', 'woff', 'woff2'], true)) {
            $response->headers->set('Cache-Control', 'public, max-age=2592000, must-revalidate');
            $response->headers->set('CDN-Cache-Control', 'public, max-age=2592000');

            return $response;
        }

        // 2. Manifest & robots - short cache
        if ($request->is('manifest.json', 'robots.txt')) {
            $response->headers->set('Cache-Control', 'public, max-age=86400, must-revalidate');
            $response->headers->set('CDN-Cache-Control', 'public, max-age=86400');

            return $response;
        }

        // 3. Service Worker MUST NOT be cached
        if ($request->is('service-worker.js')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            $response->headers->set('CDN-Cache-Control', 'no-store');
            $response->headers->set('Cloudflare-CDN-Cache-Control', 'no-store');

            return $response;
        }

        // 4. Dynamic / Auth / polling / JSON / POST -> no-store (Cloudflare BYPASS)
        $isDynamicPath = $request->is(
            'kiosk/token',
            'kiosk/poll-event',
            'kiosk/*',
            'siswa/*',
            'guru/*',
            'admin/*',
            'profile',
            'profile/*',
            'login',
            'logout',
            'dev/*'
        );

        $isJsonOrPolling = $request->expectsJson()
            || $request->is('*/check-status', '*/verify-pin', '*/scan/*', '*/sessions/*', '*/token', '*/poll-event');

        $isWriteMethod = ! in_array($request->method(), ['GET', 'HEAD'], true);

        if ($isDynamicPath || $isJsonOrPolling || $isWriteMethod || Auth::check()) {
            $response->headers->set('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            $response->headers->set('CDN-Cache-Control', 'no-store');
            $response->headers->set('Cloudflare-CDN-Cache-Control', 'no-store');
            $response->headers->set('Vary', 'Cookie, Authorization');

            return $response;
        }

        // 5. Fallback for anonymous GET (e.g. / -> redirect) - no cache
        $response->headers->set('Cache-Control', 'private, no-cache, must-revalidate, max-age=0');
        $response->headers->set('CDN-Cache-Control', 'no-store');

        return $response;
    }
}
