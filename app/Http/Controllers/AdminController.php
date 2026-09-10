<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\LessonAttendance;
use App\Models\Subject;
use App\Models\User;
use App\Services\AcademicMasterService;
use App\Services\AdminDashboardService;
use App\Services\AttendanceCorrectionService;
use App\Services\ReportService;
use App\Services\ScheduleService;
use App\Services\TeacherAttendanceService;
use App\Services\UserManagementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        protected AdminDashboardService $dashboardService,
        protected UserManagementService $userService,
        protected ScheduleService $scheduleService,
        protected AcademicMasterService $academicService,
        protected AttendanceCorrectionService $attendanceCorrectionService,
        protected TeacherAttendanceService $teacherAttendanceService,
        protected ReportService $reportService
    ) {}

    public function dashboard(): View
    {
        $metrics = $this->dashboardService->getDashboardMetrics();

        return view('admin.dashboard', $metrics);
    }

    // --- MASTER SISWA ---
    public function siswaIndex(Request $request): View
    {
        $students = $this->userService->getStudentsPaginated(
            $request->input('search'),
            $request->input('classroom_id')
        );
        $classrooms = $this->academicService->getAllClassrooms();

        return view('admin.siswa.index', compact('students', 'classrooms'));
    }

    public function siswaStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identity_number' => ['required', 'string', 'digits:10', 'unique:users,identity_number'],
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo');
        }

        $result = $this->userService->createStudent($validated);

        return back()->with('success', 'Data siswa berhasil ditambahkan dengan kata sandi bawaan: '.$result['default_password']);
    }

    public function siswaUpdate(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'identity_number' => ['required', 'string', 'digits:10', 'unique:users,identity_number,'.$user->id],
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo');
        }

        $this->userService->updateStudent($user, $validated);

        return back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function siswaDestroy(User $user): RedirectResponse
    {
        $this->userService->deleteUser($user);

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }

    // --- 1-KLIK RESET PASSWORD KE DEFAULT (DDMMYYYY) ---
    public function resetPassword(User $user): RedirectResponse
    {
        $defaultPassword = $this->userService->resetPasswordToDefault($user);

        return back()->with('success', "Kata sandi untuk {$user->name} berhasil diatur ulang ke format tanggal lahir ({$defaultPassword}).");
    }

    // --- DATA POKOK GURU ---
    public function guruIndex(Request $request): View
    {
        $teachers = $this->userService->getTeachersPaginated($request->input('search'));

        return view('admin.guru.index', compact('teachers'));
    }

    public function guruStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identity_number' => ['required', 'string', 'unique:users,identity_number'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'birth_date' => ['required', 'date'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo');
        }

        $result = $this->userService->createTeacher($validated);

        return back()->with('success', 'Data Bapak/Ibu Guru berhasil ditambahkan dengan kata sandi bawaan: '.$result['default_password']);
    }

    public function guruUpdate(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'identity_number' => ['required', 'string', 'unique:users,identity_number,'.$user->id],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email,'.$user->id],
            'birth_date' => ['required', 'date'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo');
        }

        $this->userService->updateTeacher($user, $validated);

        return back()->with('success', 'Data Bapak/Ibu Guru berhasil diperbarui.');
    }

    public function guruDestroy(User $user): RedirectResponse
    {
        $this->userService->deleteUser($user);

        return back()->with('success', 'Data Bapak/Ibu Guru berhasil dihapus.');
    }

    // --- MASTER KELAS ---
    public function kelasIndex(): View
    {
        $classrooms = $this->academicService->getAllClassroomsWithCount();

        return view('admin.kelas.index', compact('classrooms'));
    }

    public function kelasStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'grade_level' => ['required', 'string', 'max:20'],
            'academic_year' => ['required', 'string', 'max:20'],
        ]);

        $this->academicService->createClassroom($validated);

        return back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function kelasUpdate(Request $request, Classroom $classroom): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'grade_level' => ['required', 'string', 'max:20'],
            'academic_year' => ['required', 'string', 'max:20'],
        ]);

        $this->academicService->updateClassroom($classroom, $validated);

        return back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function kelasDestroy(Classroom $classroom): RedirectResponse
    {
        $this->academicService->deleteClassroom($classroom);

        return back()->with('success', 'Kelas berhasil dihapus.');
    }

    // --- MASTER MAPEL ---
    public function mapelIndex(): View
    {
        $subjects = $this->academicService->getAllSubjectsWithCount();

        return view('admin.mapel.index', compact('subjects'));
    }

    public function mapelStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:subjects,code'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->academicService->createSubject($validated);

        return back()->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function mapelUpdate(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:subjects,code,'.$subject->id],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->academicService->updateSubject($subject, $validated);

        return back()->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function mapelDestroy(Subject $subject): RedirectResponse
    {
        $this->academicService->deleteSubject($subject);

        return back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    // --- MASTER JADWAL ---
    public function jadwalIndex(Request $request): View
    {
        $schedules = $this->scheduleService->getSchedulesPaginated(
            $request->input('classroom_id'),
            $request->input('day')
        );
        $classrooms = $this->academicService->getAllClassrooms();
        $subjects = $this->academicService->getAllSubjects();
        $teachers = User::where('role', 'guru')->where('is_active', true)->orderBy('name')->get();

        return view('admin.jadwal.index', compact('schedules', 'classrooms', 'subjects', 'teachers'));
    }

    public function jadwalStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            'day_of_week' => ['required', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $this->scheduleService->createSchedule($validated);

        return back()->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function jadwalUpdate(Request $request, ClassSchedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            'day_of_week' => ['required', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $this->scheduleService->updateSchedule($schedule, $validated);

        return back()->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    public function jadwalDestroy(ClassSchedule $schedule): RedirectResponse
    {
        $this->scheduleService->deleteSchedule($schedule);

        return back()->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }

    // --- KONFIGURASI LOKASI GEOFENCE ---
    public function lokasiIndex(): View
    {
        $location = $this->academicService->getActiveLocation();

        return view('admin.lokasi.index', compact('location'));
    }

    public function lokasiUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'radius_meters' => ['required', 'integer', 'min:30', 'max:500'],
        ]);

        $this->academicService->updateOrCreateLocation($validated);

        return back()->with('success', 'Pengaturan batas area madrasah berhasil diperbarui.');
    }

    // --- LAPORAN & EKSPOR ---
    public function laporanIndex(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        $classroomId = $request->input('classroom_id');

        $stats = $this->reportService->getSummaryStats($startDate, $endDate, $classroomId);
        $rows = $this->reportService->getStudentAttendanceRows($startDate, $endDate, $classroomId);
        $classrooms = $this->academicService->getAllClassrooms();

        return view('admin.laporan.index', compact(
            'startDate',
            'endDate',
            'classroomId',
            'stats',
            'rows',
            'classrooms'
        ));
    }

    public function laporanPdf(Request $request): Response
    {
        $startDate = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        $classroomId = $request->input('classroom_id');

        $signerTitle = $request->input('signer_title') ?: 'Waka. Kesiswaan';
        $signerName = $request->input('signer_name') ?: 'Ahmad Subandi, S.AP';
        $signerNip = $request->input('signer_nip') ?: '198501012010011001';

        $headmasterTitle = $request->input('headmaster_title') ?: "Kepala MA Ma'arif Cilageni";
        $headmasterName = $request->input('headmaster_name') ?: 'Drs. H. M. Syamsuddin, M.M.Pd';
        $headmasterNip = $request->input('headmaster_nip') ?: '196803151994031003';

        $signatureCity = $request->input('signature_city') ?: 'Kadungora';
        $signatureDate = $request->input('signature_date') ?: Carbon::today()->format('Y-m-d');

        $stats = $this->reportService->getSummaryStats($startDate, $endDate, $classroomId);
        $rows = $this->reportService->getStudentAttendanceRows($startDate, $endDate, $classroomId);
        $selectedClass = $classroomId ? Classroom::find($classroomId) : null;
        $school = $this->academicService->getActiveLocation();

        $pdf = Pdf::loadView('admin.laporan.pdf', compact(
            'startDate',
            'endDate',
            'selectedClass',
            'stats',
            'rows',
            'school',
            'signerTitle',
            'signerName',
            'signerNip',
            'headmasterTitle',
            'headmasterName',
            'headmasterNip',
            'signatureCity',
            'signatureDate'
        ))->setPaper('a4', 'portrait')->setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        $filename = 'Laporan_Presensi_Maarif_'.$startDate.'_sd_'.$endDate.'.pdf';

        return $pdf->stream($filename);
    }

    public function laporanExportCsv(Request $request): Response
    {
        $startDate = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        $classroomId = $request->input('classroom_id');

        $csv = $this->reportService->generateCsvExport($startDate, $endDate, $classroomId);
        $filename = 'Laporan_Presensi_Maarif_'.$startDate.'_sd_'.$endDate.'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // --- KOREKSI KEHADIRAN SISWA ---
    public function presensiSiswaIndex(Request $request): View
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $classroomId = $request->input('classroom_id');
        $status = $request->input('status');
        $search = $request->input('search');

        $attendances = $this->attendanceCorrectionService->getPresensiSiswaPaginated(
            $date,
            $classroomId,
            $status,
            $search
        );
        $classrooms = $this->academicService->getAllClassrooms();
        $summary = $this->attendanceCorrectionService->getPresensiSiswaSummary($date);

        return view('admin.presensi-siswa.index', compact(
            'attendances',
            'classrooms',
            'date',
            'classroomId',
            'status',
            'search',
            'summary'
        ));
    }

    public function presensiSiswaUpdate(Request $request, LessonAttendance $lessonAttendance): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:HADIR,IZIN,SAKIT,ALPA'],
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ], [
            'status.required' => 'Pilih status kehadiran baru.',
            'status.in' => 'Status kehadiran tidak valid.',
            'reason.required' => 'Alasan perubahan status kehadiran wajib diisi untuk catatan riwayat.',
            'reason.min' => 'Alasan perubahan minimal 3 karakter.',
        ]);

        $result = $this->attendanceCorrectionService->correctAttendance(
            $lessonAttendance,
            $request->input('status'),
            $request->input('reason'),
            (string) Auth::id()
        );

        return back()->with(
            'success',
            "Kehadiran siswa {$lessonAttendance->student?->name} berhasil diperbarui dari {$result['old_status']} menjadi {$result['new_status']} dan tercatat di riwayat perubahan data."
        );
    }

    // --- RIWAYAT PERUBAHAN DATA (AUDIT TRAIL) ---
    public function auditIndex(): View
    {
        $auditLogs = $this->attendanceCorrectionService->getAuditLogsPaginated(20);

        return view('admin.audit.index', compact('auditLogs'));
    }

    // --- PRESENSI DEWAN GURU ---
    public function presensiGuruIndex(Request $request): View
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $status = $request->input('status');
        $search = $request->input('search');

        $attendances = $this->teacherAttendanceService->getDailyTeacherAttendances($date, $status, $search);
        $summary = $this->teacherAttendanceService->getTeacherAttendanceDailySummary($date);

        return view('admin.presensi-guru.index', compact('attendances', 'summary', 'date', 'status', 'search'));
    }

    public function presensiGuruUpdate(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:HADIR,TERLAMBAT,IZIN,SAKIT,ALPA'],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
        ]);

        $this->teacherAttendanceService->updateManualTeacherAttendance(
            $user,
            $request->input('date'),
            $request->input('status'),
            $request->input('check_in_time'),
            $request->input('check_out_time')
        );

        return back()->with('success', "Catatan kehadiran Bapak/Ibu Guru {$user->name} berhasil diperbarui.");
    }

    // --- RIWAYAT PRESENSI PERORANGAN ---
    public function siswaRiwayat(Request $request, User $user): View
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $data = $this->attendanceCorrectionService->getStudentIndividualHistory($user, $startDate, $endDate);

        return view('admin.siswa.riwayat', $data);
    }

    public function guruRiwayat(Request $request, User $user): View
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $data = $this->teacherAttendanceService->getTeacherIndividualHistory($user, $startDate, $endDate);

        return view('admin.guru.riwayat', $data);
    }
}
