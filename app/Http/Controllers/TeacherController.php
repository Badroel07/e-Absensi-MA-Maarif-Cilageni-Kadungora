<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Services\ClassroomSessionService;
use App\Services\ScheduleService;
use App\Services\TeacherAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function __construct(
        protected TeacherAttendanceService $attendanceService,
        protected ClassroomSessionService $sessionService,
        protected ScheduleService $scheduleService
    ) {}

    public function dashboard(): View
    {
        $data = $this->attendanceService->getDashboardData(Auth::user());

        return view('guru.dashboard', $data);
    }

    public function checkStatus(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $status = $this->attendanceService->checkGeofence(
            $request->latitude ? (float) $request->latitude : null,
            $request->longitude ? (float) $request->longitude : null
        );

        return response()->json($status);
    }

    public function scan(): View
    {
        $teacher = Auth::user();
        $hasCheckedIn = $this->attendanceService->hasCheckedInToday($teacher);
        $dailyAttendance = $this->attendanceService->getTodayDailyAttendance($teacher);
        $pendingSchedules = $this->attendanceService->getPendingSchedulesToday($teacher);

        return view('guru.scan', compact('teacher', 'hasCheckedIn', 'dailyAttendance', 'pendingSchedules'));
    }

    public function processCheckIn(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'qr_token' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $teacher = Auth::user();
        $result = $this->attendanceService->checkIn(
            $teacher,
            $request->qr_token,
            $request->latitude ? (float) $request->latitude : null,
            $request->longitude ? (float) $request->longitude : null
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('guru.dashboard')->with('success', $result['message']);
    }

    public function processCheckOut(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'qr_token' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $teacher = Auth::user();
        $result = $this->attendanceService->checkOut(
            $teacher,
            $request->qr_token,
            $request->latitude ? (float) $request->latitude : null,
            $request->longitude ? (float) $request->longitude : null
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('guru.dashboard')->with('success', $result['message']);
    }

    public function openSession(Request $request, ClassSchedule $schedule): RedirectResponse
    {
        $request->validate([
            'duration' => ['nullable', 'integer', 'min:2', 'max:5'],
        ]);

        $teacher = Auth::user();

        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'Bapak/Ibu Guru bukan pengampu jadwal pelajaran ini.');
        }

        try {
            $duration = (int) $request->input('duration', 3);
            $session = $this->sessionService->openSession($schedule, $teacher, $duration);

            return redirect()->route('guru.session.show', $session)
                ->with('success', "Sesi presensi kelas berhasil dibuka! Berikan kode PIN 4 angka ini kepada siswa: {$session->pin_code}");
        } catch (ValidationException $e) {
            $message = $e->getMessage();
            if (empty($message) || $message === 'The given data was invalid.') {
                $message = collect($e->errors())->flatten()->first() ?: 'Validasi gagal.';
            }

            return back()->with('error', $message);
        }
    }

    public function showSession(ClassSession $session): View
    {
        $teacher = Auth::user();
        if ($session->teacher_id !== $teacher->id) {
            abort(403);
        }

        $session->load(['schedule.classroom', 'schedule.subject', 'attendances.student']);
        $totalStudents = $session->schedule->classroom->students()->where('is_active', true)->count();
        $verifiedCount = $session->attendances()->where('status', 'HADIR')->count();

        return view('guru.session-live', compact('session', 'totalStudents', 'verifiedCount'));
    }

    public function sessionStatus(ClassSession $session): JsonResponse
    {
        $teacher = Auth::user();
        if ($session->teacher_id !== $teacher->id) {
            abort(403);
        }

        $status = $this->sessionService->getSessionLiveStatus($session);

        return response()->json($status);
    }

    public function reconcileView(ClassSession $session): View
    {
        $teacher = Auth::user();
        if ($session->teacher_id !== $teacher->id) {
            abort(403);
        }

        $data = $this->sessionService->getReconcileData($session);

        return view('guru.reconcile', $data);
    }

    public function processReconcile(Request $request, ClassSession $session): RedirectResponse
    {
        $teacher = Auth::user();
        if ($session->teacher_id !== $teacher->id) {
            abort(403);
        }

        if ($session->status === 'LOCKED') {
            return redirect()->route('guru.dashboard')
                ->with('error', 'Data kehadiran kelas ini telah ditutup dan disimpan permanen. Perubahan data kehadiran hanya dapat dilakukan melalui Admin.');
        }

        $statuses = $request->input('statuses', []);
        $notes = $request->input('notes', []);

        $this->sessionService->reconcileSession($session, $teacher, $statuses, $notes);

        return redirect()->route('guru.dashboard')
            ->with('success', 'Data konfirmasi kehadiran siswa berhasil disimpan dan ditutup permanen.');
    }

    public function history(): View
    {
        $teacher = Auth::user();
        $sessions = ClassSession::with(['schedule.classroom', 'schedule.subject'])
            ->where('teacher_id', $teacher->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('guru.history', compact('sessions'));
    }

    public function schedule(): View
    {
        $teacher = Auth::user();
        $schedules = $this->scheduleService->getTeacherWeeklySchedule($teacher);
        $totalSessions = $schedules->flatten()->count();
        $activeDaysCount = $schedules->count();

        return view('guru.schedule', compact('teacher', 'schedules', 'totalSessions', 'activeDaysCount'));
    }
}
