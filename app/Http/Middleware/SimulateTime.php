<?php

namespace App\Http\Middleware;

use App\Services\TimeSimulatorService;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SimulateTime
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local', 'testing')) {
            // Check for direct URL query parameter override: ?simulate_time=2026-09-09 07:30:00 or ?simulate_time=reset
            if ($request->has('simulate_time')) {
                $param = $request->query('simulate_time');
                if ($param === 'reset') {
                    TimeSimulatorService::reset();
                } else {
                    try {
                        $parsed = Carbon::parse($param);
                        TimeSimulatorService::setSimulatedTime($parsed);
                    } catch (\Throwable) {
                        // ignore invalid date string
                    }
                }
            }

            // Apply global simulated time
            TimeSimulatorService::apply();
        }

        return $next($request);
    }
}
