<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Services\TimeSimulatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            default => redirect()->route('siswa.dashboard'),
        };
    }

    return redirect()->route('login');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated common routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/photo', [AuthController::class, 'updateProfilePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [AuthController::class, 'deleteProfilePhoto'])->name('profile.photo.destroy');
});

// Kiosk Ruang Guru (Public / Terminal screen)
Route::prefix('kiosk')->name('kiosk.')->group(function () {
    Route::get('/', [KioskController::class, 'index'])->name('index');
    Route::get('/token', [KioskController::class, 'token'])->name('token');
    Route::get('/poll-event', [KioskController::class, 'pollEvent'])->name('poll-event');
});

// Siswa (Mobile PWA)
Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::post('/check-status', [StudentController::class, 'checkStatus'])->name('check-status');
    Route::post('/verify-pin', [StudentController::class, 'verifyPin'])->name('verify-pin');
    Route::get('/riwayat', [StudentController::class, 'history'])->name('history');
    Route::get('/jadwal', [StudentController::class, 'schedule'])->name('schedule');
});

// Guru (Mobile & Tablet)
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/', [TeacherController::class, 'dashboard'])->name('dashboard');
    Route::post('/check-status', [TeacherController::class, 'checkStatus'])->name('check-status');
    Route::get('/scan', [TeacherController::class, 'scan'])->name('scan');
    Route::post('/scan/check-in', [TeacherController::class, 'processCheckIn'])->name('checkin');
    Route::post('/scan/check-out', [TeacherController::class, 'processCheckOut'])->name('checkout');
    Route::post('/scan/auto', [TeacherController::class, 'autoAttend'])->name('auto');
    Route::post('/sessions/{schedule}/open', [TeacherController::class, 'openSession'])->name('session.open');
    Route::get('/sessions/{session}', [TeacherController::class, 'showSession'])->name('session.show');
    Route::get('/sessions/{session}/status', [TeacherController::class, 'sessionStatus'])->name('session.status');
    Route::get('/sessions/{session}/reconcile', [TeacherController::class, 'reconcileView'])->name('session.reconcile');
    Route::post('/sessions/{session}/reconcile', [TeacherController::class, 'processReconcile'])->name('session.reconcile.save');
    Route::get('/riwayat', [TeacherController::class, 'history'])->name('history');
    Route::get('/jadwal', [TeacherController::class, 'schedule'])->name('schedule');
});

// Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Master Siswa
    Route::get('/siswa', [AdminController::class, 'siswaIndex'])->name('siswa.index');
    Route::post('/siswa', [AdminController::class, 'siswaStore'])->name('siswa.store');
    Route::put('/siswa/{user}', [AdminController::class, 'siswaUpdate'])->name('siswa.update');
    Route::delete('/siswa/{user}', [AdminController::class, 'siswaDestroy'])->name('siswa.destroy');
    Route::get('/siswa/{user}/riwayat', [AdminController::class, 'siswaRiwayat'])->name('siswa.riwayat');
    Route::post('/users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');

    // Master Guru
    Route::get('/guru', [AdminController::class, 'guruIndex'])->name('guru.index');
    Route::post('/guru', [AdminController::class, 'guruStore'])->name('guru.store');
    Route::put('/guru/{user}', [AdminController::class, 'guruUpdate'])->name('guru.update');
    Route::delete('/guru/{user}', [AdminController::class, 'guruDestroy'])->name('guru.destroy');
    Route::get('/guru/{user}/riwayat', [AdminController::class, 'guruRiwayat'])->name('guru.riwayat');

    // Master Kelas
    Route::get('/kelas', [AdminController::class, 'kelasIndex'])->name('kelas.index');
    Route::post('/kelas', [AdminController::class, 'kelasStore'])->name('kelas.store');
    Route::put('/kelas/{classroom}', [AdminController::class, 'kelasUpdate'])->name('kelas.update');
    Route::delete('/kelas/{classroom}', [AdminController::class, 'kelasDestroy'])->name('kelas.destroy');

    // Master Mapel
    Route::get('/mapel', [AdminController::class, 'mapelIndex'])->name('mapel.index');
    Route::post('/mapel', [AdminController::class, 'mapelStore'])->name('mapel.store');
    Route::put('/mapel/{subject}', [AdminController::class, 'mapelUpdate'])->name('mapel.update');
    Route::delete('/mapel/{subject}', [AdminController::class, 'mapelDestroy'])->name('mapel.destroy');

    // Master Jadwal
    Route::get('/jadwal', [AdminController::class, 'jadwalIndex'])->name('jadwal.index');
    Route::post('/jadwal', [AdminController::class, 'jadwalStore'])->name('jadwal.store');
    Route::put('/jadwal/{schedule}', [AdminController::class, 'jadwalUpdate'])->name('jadwal.update');
    Route::delete('/jadwal/{schedule}', [AdminController::class, 'jadwalDestroy'])->name('jadwal.destroy');

    // Konfigurasi Lokasi Geofence
    Route::get('/lokasi', [AdminController::class, 'lokasiIndex'])->name('lokasi.index');
    Route::post('/lokasi', [AdminController::class, 'lokasiUpdate'])->name('lokasi.update');

    // Pusat Laporan Eksekutif
    Route::get('/laporan', [AdminController::class, 'laporanIndex'])->name('laporan.index');
    Route::get('/laporan/cetak-pdf', [AdminController::class, 'laporanPdf'])->name('laporan.pdf');
    Route::get('/laporan/ekspor-excel', [AdminController::class, 'laporanExportCsv'])->name('laporan.excel');

    // Presensi Dewan Guru (Monitoring Kiosk & Koreksi Admin)
    Route::get('/presensi-guru', [AdminController::class, 'presensiGuruIndex'])->name('presensi-guru.index');
    Route::put('/presensi-guru/{user}', [AdminController::class, 'presensiGuruUpdate'])->name('presensi-guru.update');

    // Koreksi Kehadiran Siswa
    Route::get('/presensi-siswa', [AdminController::class, 'presensiSiswaIndex'])->name('presensi-siswa.index');
    Route::put('/presensi-siswa/{lessonAttendance}', [AdminController::class, 'presensiSiswaUpdate'])->name('presensi-siswa.update');

    // Audit Trail
    Route::get('/audit', [AdminController::class, 'auditIndex'])->name('audit.index');
});

// Local Development Time Simulator Route
if (app()->environment('local', 'testing')) {
    Route::post('/dev/time-simulator', function (Request $request) {
        $action = $request->input('action');

        switch ($action) {
            case 'jump':
                $time = $request->input('time');
                $date = Carbon::now()->format('Y-m-d');
                $simulated = Carbon::parse("$date $time:00");
                TimeSimulatorService::setSimulatedTime($simulated);
                break;

            case 'custom':
                $datetime = $request->input('datetime');
                if ($datetime) {
                    $simulated = Carbon::parse($datetime);
                    TimeSimulatorService::setSimulatedTime($simulated);
                }
                break;

            case 'add':
                $minutes = (int) $request->input('minutes', 1);
                TimeSimulatorService::addMinutes($minutes);
                break;

            case 'reset':
                TimeSimulatorService::reset();
                break;
        }

        return back();
    })->name('dev.time-simulator');
}
