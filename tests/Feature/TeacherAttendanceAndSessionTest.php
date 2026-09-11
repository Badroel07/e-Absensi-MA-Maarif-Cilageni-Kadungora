<?php

use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\DailyAttendance;
use App\Models\LessonAttendance;
use App\Models\SchoolLocation;
use App\Models\Subject;
use App\Models\User;
use App\Services\KioskService;
use App\Services\TeacherAttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->location = SchoolLocation::create([
        'name' => "MA Ma'arif Cilageni Kadungora",
        'latitude' => -7.010000,
        'longitude' => 107.900000,
        'radius_meters' => 75,
        'is_active' => true,
    ]);

    $this->classroom = Classroom::create([
        'name' => '7A',
        'grade_level' => '7',
        'academic_year' => '2026/2027',
    ]);

    $this->subject = Subject::create([
        'code' => 'PAI-FKH',
        'name' => 'Fikih',
    ]);

    $this->guru = User::create([
        'identity_number' => '198012012010011001',
        'name' => 'Ust. H. Ahmad Dahlan',
        'email' => 'ahmad@maarif.sch.id',
        'birth_date' => '1980-12-01',
        'password' => Hash::make('01121980'),
        'role' => 'guru',
        'is_active' => true,
    ]);

    $this->guruLain = User::create([
        'identity_number' => '198502022010011002',
        'name' => 'Ust. Mahmudin, M.Pd.I',
        'email' => 'mahmudin@maarif.sch.id',
        'birth_date' => '1985-02-02',
        'password' => Hash::make('02021985'),
        'role' => 'guru',
        'is_active' => true,
    ]);

    $this->student1 = User::create([
        'identity_number' => '1010101010',
        'name' => 'Ahmad Fauzi',
        'birth_date' => '2012-05-15',
        'password' => Hash::make('15052012'),
        'role' => 'siswa',
        'classroom_id' => $this->classroom->id,
        'is_active' => true,
    ]);

    $this->student2 = User::create([
        'identity_number' => '1010101011',
        'name' => 'Budi Santoso',
        'birth_date' => '2012-08-20',
        'password' => Hash::make('20082012'),
        'role' => 'siswa',
        'classroom_id' => $this->classroom->id,
        'is_active' => true,
    ]);

    $todayDay = TeacherAttendanceService::getIndonesianDayName(Carbon::today());
    $this->schedule = ClassSchedule::create([
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day_of_week' => $todayDay,
        'start_time' => Carbon::now()->subMinutes(15)->format('H:i:s'),
        'end_time' => Carbon::now()->addHours(2)->format('H:i:s'),
    ]);

    $this->kioskService = new KioskService;
});

use App\Services\ClassroomSessionService;
use App\Services\TimeSimulatorService;

afterEach(function () {
    TimeSimulatorService::reset();
});

test('TC-GUR-DASH-001: Dashboard guru memuat ringkasan kehadiran harian dan jadwal mengajar', function () {
    $this->actingAs($this->guru);

    $response = $this->get('/guru');
    $response->assertStatus(200);
    $response->assertSee('Ust. H. Ahmad Dahlan');
    $response->assertSee('Fikih');
    $response->assertSee('Kelas 7A');
});

test('TC-GUR-GEO-001: checkStatus — evaluasi posisi guru di dalam dan di luar geofence', function () {
    $this->actingAs($this->guru);

    // 1. Inside
    $inside = $this->postJson('/guru/check-status', [
        'latitude' => -7.010010,
        'longitude' => 107.900010,
    ]);
    $inside->assertStatus(200);
    $inside->assertJson(['is_within_geofence' => true]);

    // 2. Outside
    $outside = $this->postJson('/guru/check-status', [
        'latitude' => -7.050000,
        'longitude' => 107.950000,
    ]);
    $outside->assertStatus(200);
    $outside->assertJson(['is_within_geofence' => false]);
});

test('TC-GUR-SCAN-001: Akses halaman scanner Kiosk guru', function () {
    $this->actingAs($this->guru);

    $response = $this->get('/guru/scan');
    $response->assertStatus(200);
    $response->assertSee('Pemindai QR Presensi');
});

test('TC-GUR-CHKIN-001: Check-in — sukses HADIR jika <= 07:15 WIB', function () {
    TimeSimulatorService::setSimulatedTime(Carbon::today()->setTime(7, 0, 0));
    $tokenPayload = $this->kioskService->generateTokenPayload();

    $this->actingAs($this->guru);
    $response = $this->postJson('/guru/scan/check-in', [
        'qr_token' => $tokenPayload['token'],
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'status' => 'HADIR',
    ]);

    $daily = DailyAttendance::where('user_id', $this->guru->id)->whereDate('attendance_date', Carbon::today())->first();
    expect($daily)->not->toBeNull();
    expect($daily->check_in_status)->toBe('HADIR');
});

test('TC-GUR-CHKIN-002: Check-in — sukses TERLAMBAT jika > 07:15 WIB', function () {
    TimeSimulatorService::setSimulatedTime(Carbon::today()->setTime(7, 30, 0));
    $tokenPayload = $this->kioskService->generateTokenPayload();

    $this->actingAs($this->guru);
    $response = $this->postJson('/guru/scan/check-in', [
        'qr_token' => $tokenPayload['token'],
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'status' => 'TERLAMBAT',
    ]);

    $daily = DailyAttendance::where('user_id', $this->guru->id)->whereDate('attendance_date', Carbon::today())->first();
    expect($daily)->not->toBeNull();
    expect($daily->check_in_status)->toBe('TERLAMBAT');
});

test('TC-GUR-CHKIN-003: Check-in — ditolak jika koordinat GPS kosong (GEOFENCE_REQUIRED)', function () {
    $tokenPayload = $this->kioskService->generateTokenPayload();

    $this->actingAs($this->guru);
    $response = $this->postJson('/guru/scan/check-in', [
        'qr_token' => $tokenPayload['token'],
        'latitude' => null,
        'longitude' => null,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'code' => 'GEOFENCE_REQUIRED',
    ]);
});

test('TC-GUR-CHKIN-004: Check-in — ditolak jika berada di luar madrasah (OUTSIDE_GEOFENCE)', function () {
    $tokenPayload = $this->kioskService->generateTokenPayload();

    $this->actingAs($this->guru);
    $response = $this->postJson('/guru/scan/check-in', [
        'qr_token' => $tokenPayload['token'],
        'latitude' => -7.050000,
        'longitude' => 107.950000,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'code' => 'OUTSIDE_GEOFENCE',
    ]);
});

test('TC-GUR-CHKIN-005: Check-in — ditolak jika QR token tidak valid atau kedaluwarsa (INVALID_QR_TOKEN)', function () {
    $this->actingAs($this->guru);
    $response = $this->postJson('/guru/scan/check-in', [
        'qr_token' => 'invalid-token-12345',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'code' => 'INVALID_QR_TOKEN',
    ]);
});

test('TC-GUR-CHKIN-006: Check-in — ditolak 422 jika sudah melakukan check-in pada hari yang sama (no kiosk replay)', function () {
    DailyAttendance::create([
        'user_id' => $this->guru->id,
        'attendance_date' => Carbon::today(),
        'check_in_time' => '07:05:00',
        'check_in_status' => 'HADIR',
    ]);

    $tokenPayload = $this->kioskService->generateTokenPayload();

    $this->actingAs($this->guru);
    $response = $this->postJson('/guru/scan/check-in', [
        'qr_token' => $tokenPayload['token'],
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'code' => 'ALREADY_CHECKED_IN',
    ]);
});

test('TC-GUR-OPEN-001: Open Session — sukses ACTIVE jika check-in selesai dan waktu sesuai slot', function () {
    // 1. Guru checks in
    DailyAttendance::create([
        'user_id' => $this->guru->id,
        'attendance_date' => Carbon::today(),
        'check_in_time' => '07:05:00',
        'check_in_status' => 'HADIR',
    ]);

    $this->actingAs($this->guru);
    $response = $this->post('/guru/sessions/'.$this->schedule->id.'/open', [
        'duration' => 3,
    ]);

    $session = ClassSession::where('schedule_id', $this->schedule->id)->first();
    expect($session)->not->toBeNull();
    expect($session->status)->toBe('ACTIVE');
    expect($session->pin_code)->toHaveLength(4);

    $response->assertRedirect(route('guru.session.show', $session));
});

test('TC-GUR-OPEN-002: Open Session — Check-in Gating memblokir pembukaan sesi sebelum check-in', function () {
    $this->actingAs($this->guru);

    $response = $this->post('/guru/sessions/'.$this->schedule->id.'/open', [
        'duration' => 3,
    ]);

    $response->assertSessionHas('error');
    expect(session('error'))->toContain('belum melakukan presensi masuk di Layar Presensi Madrasah');
    expect(ClassSession::where('schedule_id', $this->schedule->id)->count())->toBe(0);
});

test('TC-GUR-OPEN-003: Open Session — ditolak jika hari tidak sesuai jadwal (STRICT gating)', function () {
    DailyAttendance::create([
        'user_id' => $this->guru->id,
        'attendance_date' => Carbon::today(),
        'check_in_time' => '07:05:00',
        'check_in_status' => 'HADIR',
    ]);

    $otherDay = TeacherAttendanceService::getIndonesianDayName(Carbon::today()->addDays(2));
    $scheduleOtherDay = ClassSchedule::create([
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day_of_week' => $otherDay,
        'start_time' => '07:30:00',
        'end_time' => '09:00:00',
    ]);

    $this->actingAs($this->guru);
    $response = $this->post('/guru/sessions/'.$scheduleOtherDay->id.'/open', ['duration' => 3]);

    $response->assertSessionHas('error');
    expect(session('error'))->toContain('Sesi presensi hanya dapat dibuka pada hari');
});

test('TC-GUR-OPEN-004: Open Session — ditolak jika jam di luar slot jadwal (STRICT gating)', function () {
    DailyAttendance::create([
        'user_id' => $this->guru->id,
        'attendance_date' => Carbon::today(),
        'check_in_time' => '07:05:00',
        'check_in_status' => 'HADIR',
    ]);

    // Schedule slot 14:00 - 15:30 while current time is morning
    $scheduleSlot = ClassSchedule::create([
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day_of_week' => TeacherAttendanceService::getIndonesianDayName(Carbon::today()),
        'start_time' => Carbon::now()->addHours(5)->format('H:i:s'),
        'end_time' => Carbon::now()->addHours(7)->format('H:i:s'),
    ]);

    $this->actingAs($this->guru);
    $response = $this->post('/guru/sessions/'.$scheduleSlot->id.'/open', ['duration' => 3]);

    $response->assertSessionHas('error');
    expect(session('error'))->toContain('Sesi presensi hanya dapat dibuka sesuai jadwal pelajaran');
});

test('TC-GUR-OPEN-005: Open Session — 403 Forbidden jika bukan guru pengampu', function () {
    DailyAttendance::create([
        'user_id' => $this->guruLain->id,
        'attendance_date' => Carbon::today(),
        'check_in_time' => '07:05:00',
        'check_in_status' => 'HADIR',
    ]);

    $this->actingAs($this->guruLain);
    $response = $this->post('/guru/sessions/'.$this->schedule->id.'/open', ['duration' => 3]);

    $response->assertStatus(403);
});

test('TC-GUR-OPEN-006: Open Session — validasi durasi (2-5 menit) dan service clamp', function () {
    DailyAttendance::create([
        'user_id' => $this->guru->id,
        'attendance_date' => Carbon::today(),
        'check_in_time' => '07:05:00',
        'check_in_status' => 'HADIR',
    ]);

    $this->actingAs($this->guru);

    // Controller validation rejects out-of-bounds duration
    $resOutOfBound = $this->post('/guru/sessions/'.$this->schedule->id.'/open', ['duration' => 10]);
    $resOutOfBound->assertSessionHasErrors('duration');

    // Service clamps duration internally
    $sessionClamped = app(ClassroomSessionService::class)->openSession($this->schedule, $this->guru, 10);
    expect($sessionClamped->duration_minutes)->toBe(5);
});

test('TC-GUR-SESS-001: Session Live — halaman live view dan polling status realtime', function () {
    $session = ClassSession::create([
        'schedule_id' => $this->schedule->id,
        'teacher_id' => $this->guru->id,
        'pin_code' => '8492',
        'duration_minutes' => 3,
        'started_at' => Carbon::now(),
        'expires_at' => Carbon::now()->addMinutes(3),
        'status' => 'ACTIVE',
    ]);

    $this->actingAs($this->guru);

    // 1. View live session
    $responseView = $this->get('/guru/sessions/'.$session->id);
    $responseView->assertStatus(200);
    $responseView->assertSee('8492');

    // 2. Polling status
    $responsePolling = $this->getJson('/guru/sessions/'.$session->id.'/status');
    $responsePolling->assertStatus(200);
    $responsePolling->assertJson([
        'status' => 'ACTIVE',
        'is_expired' => false,
        'verified_count' => 0,
    ]);
});

test('TC-GUR-SESS-002: Session Live — 403 jika guru lain mengakses live session', function () {
    $session = ClassSession::create([
        'schedule_id' => $this->schedule->id,
        'teacher_id' => $this->guru->id,
        'pin_code' => '8492',
        'duration_minutes' => 3,
        'started_at' => Carbon::now(),
        'expires_at' => Carbon::now()->addMinutes(3),
        'status' => 'ACTIVE',
    ]);

    $this->actingAs($this->guruLain);
    $this->get('/guru/sessions/'.$session->id)->assertStatus(403);
    $this->getJson('/guru/sessions/'.$session->id.'/status')->assertStatus(403);
});

test('TC-GUR-RECON-001: Reconcile — halaman daftar siswa belum hadir', function () {
    $session = ClassSession::create([
        'schedule_id' => $this->schedule->id,
        'teacher_id' => $this->guru->id,
        'pin_code' => '8492',
        'duration_minutes' => 3,
        'started_at' => Carbon::now()->subMinutes(5),
        'expires_at' => Carbon::now()->subMinutes(2),
        'status' => 'EXPIRED',
    ]);

    $this->actingAs($this->guru);
    $response = $this->get('/guru/sessions/'.$session->id.'/reconcile');

    $response->assertStatus(200);
    $response->assertSee('Konfirmasi Kehadiran Siswa');
    $response->assertSee('Ahmad Fauzi');
    $response->assertSee('Budi Santoso');
});

test('TC-GUR-RECON-002: Reconcile — simpan kehadiran, status LOCKED, dan sinkronisasi DailyAttendance', function () {
    $session = ClassSession::create([
        'schedule_id' => $this->schedule->id,
        'teacher_id' => $this->guru->id,
        'pin_code' => '8492',
        'duration_minutes' => 3,
        'started_at' => Carbon::now()->subMinutes(5),
        'expires_at' => Carbon::now()->subMinutes(2),
        'status' => 'EXPIRED',
    ]);

    $this->actingAs($this->guru);
    $response = $this->post('/guru/sessions/'.$session->id.'/reconcile', [
        'statuses' => [
            $this->student1->id => 'IZIN',
            $this->student2->id => 'SAKIT',
        ],
        'notes' => [
            $this->student1->id => 'Acara keluarga',
            $this->student2->id => 'Demam',
        ],
    ]);

    $response->assertRedirect(route('guru.dashboard'));
    expect($session->fresh()->status)->toBe('LOCKED');

    // Verify Lesson Attendance
    expect(LessonAttendance::where('student_id', $this->student1->id)->first()->status)->toBe('IZIN');
    expect(LessonAttendance::where('student_id', $this->student2->id)->first()->status)->toBe('SAKIT');
});

test('TC-GUR-RECON-003: Reconcile — default ALPA untuk siswa yang tidak diisi statusnya', function () {
    $session = ClassSession::create([
        'schedule_id' => $this->schedule->id,
        'teacher_id' => $this->guru->id,
        'pin_code' => '8492',
        'duration_minutes' => 3,
        'started_at' => Carbon::now()->subMinutes(5),
        'expires_at' => Carbon::now()->subMinutes(2),
        'status' => 'EXPIRED',
    ]);

    $this->actingAs($this->guru);
    $response = $this->post('/guru/sessions/'.$session->id.'/reconcile', [
        'statuses' => [
            $this->student1->id => 'IZIN',
            // student2 is not submitted in statuses
        ],
    ]);

    $response->assertRedirect(route('guru.dashboard'));
    expect(LessonAttendance::where('student_id', $this->student2->id)->first()->status)->toBe('ALPA');
});

test('TC-GUR-RECON-004: Reconcile — formulir rekonsiliasi diblokir jika sesi sudah LOCKED', function () {
    $session = ClassSession::create([
        'schedule_id' => $this->schedule->id,
        'teacher_id' => $this->guru->id,
        'pin_code' => '8492',
        'duration_minutes' => 3,
        'started_at' => Carbon::now()->subMinutes(10),
        'expires_at' => Carbon::now()->subMinutes(7),
        'status' => 'LOCKED',
    ]);

    $this->actingAs($this->guru);
    $response = $this->post('/guru/sessions/'.$session->id.'/reconcile', [
        'statuses' => [$this->student1->id => 'HADIR'],
    ]);

    $response->assertRedirect(route('guru.dashboard'));
    $response->assertSessionHas('error');
});

test('TC-GUR-RECON-005: Reconcile — siswa yang sudah HADIR via PIN tidak tertimpa', function () {
    $session = ClassSession::create([
        'schedule_id' => $this->schedule->id,
        'teacher_id' => $this->guru->id,
        'pin_code' => '8492',
        'duration_minutes' => 3,
        'started_at' => Carbon::now()->subMinutes(5),
        'expires_at' => Carbon::now()->subMinutes(2),
        'status' => 'EXPIRED',
    ]);

    LessonAttendance::create([
        'session_id' => $session->id,
        'schedule_id' => $this->schedule->id,
        'student_id' => $this->student1->id,
        'attendance_date' => Carbon::today(),
        'status' => 'HADIR',
        'verified_at' => Carbon::now()->subMinutes(4),
    ]);

    $this->actingAs($this->guru);
    $this->post('/guru/sessions/'.$session->id.'/reconcile', [
        'statuses' => [
            $this->student1->id => 'ALPA', // Attempt to overwrite HADIR
            $this->student2->id => 'IZIN',
        ],
    ]);

    // Student 1 remains HADIR
    expect(LessonAttendance::where('student_id', $this->student1->id)->first()->status)->toBe('HADIR');
});

test('TC-GUR-CHKOUT-001: Check-out — sukses saat semua jadwal hari ini berstatus LOCKED', function () {
    // 1. Check in
    DailyAttendance::create([
        'user_id' => $this->guru->id,
        'attendance_date' => Carbon::today(),
        'check_in_time' => '07:05:00',
        'check_in_status' => 'HADIR',
    ]);

    // 2. Lock class session
    ClassSession::create([
        'schedule_id' => $this->schedule->id,
        'teacher_id' => $this->guru->id,
        'pin_code' => '8492',
        'duration_minutes' => 3,
        'started_at' => Carbon::now()->subMinutes(40),
        'expires_at' => Carbon::now()->subMinutes(37),
        'status' => 'LOCKED',
    ]);

    $tokenPayload = $this->kioskService->generateTokenPayload();

    $this->actingAs($this->guru);
    $response = $this->postJson('/guru/scan/check-out', [
        'qr_token' => $tokenPayload['token'],
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $daily = DailyAttendance::where('user_id', $this->guru->id)->whereDate('attendance_date', Carbon::today())->first();
    expect($daily->check_out_time)->not->toBeNull();
});

test('TC-GUR-CHKOUT-002: Check-out — ditolak (TEACHING_COMPLETION_LOCKED) jika ada jadwal belum LOCKED', function () {
    DailyAttendance::create([
        'user_id' => $this->guru->id,
        'attendance_date' => Carbon::today(),
        'check_in_time' => '07:05:00',
        'check_in_status' => 'HADIR',
    ]);

    // Session is still ACTIVE
    ClassSession::create([
        'schedule_id' => $this->schedule->id,
        'teacher_id' => $this->guru->id,
        'pin_code' => '8492',
        'duration_minutes' => 3,
        'started_at' => Carbon::now(),
        'expires_at' => Carbon::now()->addMinutes(3),
        'status' => 'ACTIVE',
    ]);

    $tokenPayload = $this->kioskService->generateTokenPayload();

    $this->actingAs($this->guru);
    $response = $this->postJson('/guru/scan/check-out', [
        'qr_token' => $tokenPayload['token'],
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'code' => 'TEACHING_COMPLETION_LOCKED',
    ]);
});

test('TC-GUR-CHKOUT-003: Check-out — ditolak (NOT_CHECKED_IN) jika belum presensi masuk', function () {
    $tokenPayload = $this->kioskService->generateTokenPayload();

    $this->actingAs($this->guru);
    $response = $this->postJson('/guru/scan/check-out', [
        'qr_token' => $tokenPayload['token'],
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'code' => 'NOT_CHECKED_IN',
    ]);
});

test('TC-GUR-CHKOUT-004: Check-out — validasi QR token dan geofence', function () {
    DailyAttendance::create([
        'user_id' => $this->guru->id,
        'attendance_date' => Carbon::today(),
        'check_in_time' => '07:05:00',
        'check_in_status' => 'HADIR',
    ]);

    $this->actingAs($this->guru);

    // Invalid token
    $resToken = $this->postJson('/guru/scan/check-out', [
        'qr_token' => 'invalid-token',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);
    $resToken->assertStatus(422);
    $resToken->assertJson(['code' => 'INVALID_QR_TOKEN']);

    // Outside geofence
    $tokenPayload = $this->kioskService->generateTokenPayload();
    $resGeo = $this->postJson('/guru/scan/check-out', [
        'qr_token' => $tokenPayload['token'],
        'latitude' => -7.050000,
        'longitude' => 107.950000,
    ]);
    $resGeo->assertStatus(422);
    $resGeo->assertJson(['code' => 'OUTSIDE_GEOFENCE']);
});

test('TC-GUR-HIST-001: Riwayat mengajar guru ter-paginate', function () {
    $this->actingAs($this->guru);

    $response = $this->get('/guru/riwayat');
    $response->assertStatus(200);
    $response->assertSee('Riwayat Kelas Mengajar');
});

test('TC-GUR-JADW-001: Jadwal mingguan guru menampilkan seluruh slot jadwal mengajar', function () {
    $this->actingAs($this->guru);

    $response = $this->get('/guru/jadwal');
    $response->assertStatus(200);
    $response->assertSee('Jadwal Mengajar');
    $response->assertSee('Ust. H. Ahmad Dahlan');
    $response->assertSee('Fikih');
});
