<?php

namespace App\Http\Controllers;

use App\Models\LessonAttendance;
use App\Services\ClassroomSessionService;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(
        protected ClassroomSessionService $sessionService,
        protected ScheduleService $scheduleService
    ) {}

    public function dashboard(): View
    {
        $data = $this->sessionService->getStudentDashboardData(Auth::user());

        return view('siswa.dashboard', $data);
    }

    public function checkStatus(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $student = Auth::user();
        $lat = $request->latitude ? (float) $request->latitude : null;
        $lng = $request->longitude ? (float) $request->longitude : null;

        $state = $this->sessionService->getActiveSessionForStudent($student, $lat, $lng);

        $sessionData = null;
        if ($state['is_within_geofence'] && $state['session']) {
            $sessionData = [
                'id' => $state['session']->id,
                'subject_name' => $state['session']->schedule->subject->name ?? 'Mata Pelajaran',
                'teacher_name' => $state['session']->schedule->teacher->name ?? 'Guru Pengampu',
                'remaining_seconds' => $state['remaining_seconds'],
            ];
        }

        return response()->json([
            'is_within_geofence' => $state['is_within_geofence'],
            'distance' => $state['distance'],
            'radius' => $state['radius'],
            'has_session' => $sessionData !== null,
            'session' => $sessionData,
            'has_verified' => $state['has_verified'],
        ]);
    }

    public function verifyPin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:4'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $student = Auth::user();
        $pin = $request->pin;
        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;

        $result = $this->sessionService->verifyStudentPin($student, $pin, $lat, $lng);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function history(): View
    {
        $student = Auth::user();
        $attendances = LessonAttendance::with(['schedule.subject', 'schedule.teacher'])
            ->where('student_id', $student->id)
            ->orderBy('attendance_date', 'desc')
            ->paginate(15);

        return view('siswa.history', compact('student', 'attendances'));
    }

    public function schedule(): View
    {
        $student = Auth::user()->load('classroom');
        $schedules = $this->scheduleService->getStudentWeeklySchedule($student);

        return view('siswa.schedule', compact('student', 'schedules'));
    }
}
