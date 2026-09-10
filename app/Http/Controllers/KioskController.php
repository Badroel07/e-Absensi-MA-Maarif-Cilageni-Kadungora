<?php

namespace App\Http\Controllers;

use App\Services\KioskService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class KioskController extends Controller
{
    public function __construct(
        protected KioskService $kioskService
    ) {}

    public function index(): View
    {
        return view('kiosk.index', $this->kioskService->getKioskIndexData());
    }

    public function token(): JsonResponse
    {
        return response()->json($this->kioskService->getTokenPayloadResponse());
    }

    public function pollEvent(): JsonResponse
    {
        return response()->json($this->kioskService->getPollEventData());
    }
}
