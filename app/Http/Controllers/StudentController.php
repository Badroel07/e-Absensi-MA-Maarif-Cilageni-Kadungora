<?php

namespace App\Http\Controllers;

use App\Models\LessonAttendance;
use App\Models\User;
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

    public function history(Request $request): View
    {
        $student = Auth::user();
        if ($student instanceof User) {
            $student->loadMissing('classroom');
        }

        // 1. Overall Student Statistics (Real lifetime / recorded data)
        $totalSessions = LessonAttendance::where('student_id', $student->id)->count();
        $totalHadir = LessonAttendance::where('student_id', $student->id)->where('status', 'HADIR')->count();
        $totalIzin = LessonAttendance::where('student_id', $student->id)->where('status', 'IZIN')->count();
        $totalSakit = LessonAttendance::where('student_id', $student->id)->where('status', 'SAKIT')->count();
        $totalAlpa = LessonAttendance::where('student_id', $student->id)->where('status', 'ALPA')->count();
        $persenHadir = $totalSessions > 0 ? round(($totalHadir / $totalSessions) * 100, 1) : 100.0;

        // 2. Query with interactive filters
        $query = LessonAttendance::with(['schedule.subject', 'schedule.teacher'])
            ->where('student_id', $student->id);

        $selectedStatus = $request->query('status');
        if ($selectedStatus && in_array(strtoupper($selectedStatus), ['HADIR', 'IZIN', 'SAKIT', 'ALPA'])) {
            $query->where('status', strtoupper($selectedStatus));
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('schedule.subject', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                })->orWhereHas('schedule.teacher', function ($tq) use ($search) {
                    $tq->where('name', 'like', "%{$search}%");
                })->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        if ($startDate) {
            $query->whereDate('attendance_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('attendance_date', '<=', $endDate);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('siswa.history', compact(
            'student',
            'attendances',
            'totalSessions',
            'totalHadir',
            'totalIzin',
            'totalSakit',
            'totalAlpa',
            'persenHadir',
            'selectedStatus',
            'search',
            'startDate',
            'endDate'
        ));
    }

    public function schedule(): View
    {
        $student = Auth::user()->load('classroom');
        $schedules = $this->scheduleService->getStudentWeeklySchedule($student);

        return view('siswa.schedule', compact('student', 'schedules'));
    }
}
