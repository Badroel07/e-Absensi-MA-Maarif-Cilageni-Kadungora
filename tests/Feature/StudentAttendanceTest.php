<?php

use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\DailyAttendance;
use App\Models\LessonAttendance;
use App\Models\SchoolLocation;
use App\Models\Subject;
use App\Models\User;
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

    $this->student = User::create([
        'identity_number' => '1010101010',
        'name' => 'Ahmad Fauzi',
        'birth_date' => '2012-05-15',
        'password' => Hash::make('15052012'),
        'role' => 'siswa',
        'classroom_id' => $this->classroom->id,
        'is_active' => true,
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

    $todayDay = TeacherAttendanceService::getIndonesianDayName(Carbon::today());
    $this->schedule = ClassSchedule::create([
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day_of_week' => $todayDay,
        'start_time' => '07:30:00',
        'end_time' => '09:00:00',
    ]);

    $this->session = ClassSession::create([
        'schedule_id' => $this->schedule->id,
        'teacher_id' => $this->guru->id,
        'pin_code' => '8492',
        'duration_minutes' => 3,
        'started_at' => Carbon::now(),
        'expires_at' => Carbon::now()->addMinutes(3),
        'status' => 'ACTIVE',
    ]);
});

test('TC-SIS-DASH-001: Dashboard siswa memuat ringkasan kehadiran, jadwal, dan counter', function () {
    $this->actingAs($this->student);

    $response = $this->get('/siswa');
    $response->assertStatus(200);
    $response->assertSee('Ahmad Fauzi');
    $response->assertSee('Kelas 7A');
    $response->assertSee('Hadir');
    $response->assertSee('Izin');
    $response->assertSee('Sakit');
    $response->assertSee('Alpa');
});

test('TC-SIS-GEO-001: checkStatus — di dalam geofence dan ada sesi aktif mengembalikan detail sesi', function () {
    $this->actingAs($this->student);

    $response = $this->postJson('/siswa/check-status', [
        'latitude' => -7.010010,
        'longitude' => 107.900010,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'is_within_geofence' => true,
        'has_session' => true,
        'has_verified' => false,
        'session' => [
            'subject_name' => 'Fikih',
            'teacher_name' => 'Ust. H. Ahmad Dahlan',
        ],
    ]);
});

test('TC-SIS-GEO-002: checkStatus — di luar geofence menyembunyikan sesi presensi', function () {
    $this->actingAs($this->student);

    $response = $this->postJson('/siswa/check-status', [
        'latitude' => -7.030000,
        'longitude' => 107.930000,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'is_within_geofence' => false,
        'has_session' => false,
    ]);
});

test('TC-SIS-GEO-003: checkStatus — tanpa koordinat GPS', function () {
    $this->actingAs($this->student);

    $response = $this->postJson('/siswa/check-status', [
        'latitude' => null,
        'longitude' => null,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'is_within_geofence' => false,
        'has_session' => false,
    ]);
});

test('TC-SIS-GEO-004: checkStatus — siswa tanpa classroom_id mengembalikan session null', function () {
    $this->student->update(['classroom_id' => null]);
    $this->actingAs($this->student);

    $response = $this->postJson('/siswa/check-status', [
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'is_within_geofence' => true,
        'has_session' => false,
        'session' => null,
    ]);
});

test('TC-SIS-GEO-005: checkStatus — sesi kedaluwarsa otomatis diperbarui menjadi EXPIRED', function () {
    $this->session->update([
        'expires_at' => Carbon::now()->subMinute(),
    ]);

    $this->actingAs($this->student);

    $response = $this->postJson('/siswa/check-status', [
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'has_session' => false,
    ]);
    expect($this->session->fresh()->status)->toBe('EXPIRED');
});

test('TC-SIS-PIN-001: verifyPin — sukses HADIR dan mencatat DailyAttendance jam pertama', function () {
    $this->actingAs($this->student);

    $response = $this->postJson('/siswa/verify-pin', [
        'pin' => '8492',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    // LessonAttendance recorded
    $lesson = LessonAttendance::where('schedule_id', $this->schedule->id)
        ->where('student_id', $this->student->id)
        ->first();
    expect($lesson)->not->toBeNull();
    expect($lesson->status)->toBe('HADIR');
    expect($lesson->verified_at)->not->toBeNull();

    // DailyAttendance auto recorded
    $daily = DailyAttendance::where('user_id', $this->student->id)
        ->whereDate('attendance_date', Carbon::today())
        ->first();
    expect($daily)->not->toBeNull();
    expect($daily->check_in_status)->toBe('HADIR');
});

test('TC-SIS-PIN-002: verifyPin — ditolak jika berada di luar batas geofence (OUTSIDE_GEOFENCE)', function () {
    $this->actingAs($this->student);

    $response = $this->postJson('/siswa/verify-pin', [
        'pin' => '8492',
        'latitude' => -7.050000,
        'longitude' => 107.950000,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'code' => 'OUTSIDE_GEOFENCE',
    ]);
});

test('TC-SIS-PIN-003: verifyPin — ditolak jika tidak ada sesi aktif (NO_ACTIVE_SESSION)', function () {
    $this->session->update(['status' => 'LOCKED']);
    $this->actingAs($this->student);

    $response = $this->postJson('/siswa/verify-pin', [
        'pin' => '8492',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'code' => 'NO_ACTIVE_SESSION',
    ]);
});

test('TC-SIS-PIN-004: verifyPin — ditolak jika waktu sesi habis melebihi grace period 5 detik (SESSION_EXPIRED)', function () {
    $this->session->update([
        'expires_at' => Carbon::now()->subSeconds(7),
    ]);
    $this->actingAs($this->student);

    $response = $this->postJson('/siswa/verify-pin', [
        'pin' => '8492',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'code' => 'SESSION_EXPIRED',
    ]);
    expect($this->session->fresh()->status)->toBe('EXPIRED');
});

test('TC-SIS-PIN-005: verifyPin — tetap sukses jika dikirim dalam batas toleransi grace period 5 detik', function () {
    $this->session->update([
        'expires_at' => Carbon::now()->subSeconds(2), // 2 seconds past expires_at (within 5s grace)
    ]);
    $this->actingAs($this->student);

    $response = $this->postJson('/siswa/verify-pin', [
        'pin' => '8492',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
});

test('TC-SIS-PIN-006: verifyPin — PIN salah mengembalikan INVALID_PIN dan mengurangi kesempatan', function () {
    $this->actingAs($this->student);

    $response = $this->postJson('/siswa/verify-pin', [
        'pin' => '0000',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'code' => 'INVALID_PIN',
    ]);
});

test('TC-SIS-PIN-007: verifyPin — dibekukan sementara (RATE_LIMITED) setelah 3 kali salah berturut-turut', function () {
    $this->actingAs($this->student);

    // 1st wrong attempt
    $this->postJson('/siswa/verify-pin', ['pin' => '1111', 'latitude' => -7.010000, 'longitude' => 107.900000])->assertStatus(422);
    // 2nd wrong attempt
    $this->postJson('/siswa/verify-pin', ['pin' => '2222', 'latitude' => -7.010000, 'longitude' => 107.900000])->assertStatus(422);
    // 3rd wrong attempt
    $this->postJson('/siswa/verify-pin', ['pin' => '3333', 'latitude' => -7.010000, 'longitude' => 107.900000])->assertStatus(422);

    // 4th attempt (even with correct PIN) is RATE_LIMITED
    $response = $this->postJson('/siswa/verify-pin', [
        'pin' => '8492',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'code' => 'RATE_LIMITED',
    ]);
});

test('TC-SIS-PIN-008: verifyPin — idempoten jika sudah berhasil HADIR pada sesi yang sama', function () {
    $this->actingAs($this->student);

    // First successful verify
    $this->postJson('/siswa/verify-pin', [
        'pin' => '8492',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ])->assertStatus(200);

    // Second verify with same pin
    $response = $this->postJson('/siswa/verify-pin', [
        'pin' => '8492',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'already_verified' => true,
    ]);
    expect(LessonAttendance::where('schedule_id', $this->schedule->id)->count())->toBe(1);
});

test('TC-SIS-PIN-009: verifyPin — presensi mapel kedua tidak menimpa check_in_time harian awal', function () {
    // 0. Close previous session
    $this->session->update(['status' => 'LOCKED']);

    // 1. Initial Daily Attendance from morning
    DailyAttendance::create([
        'user_id' => $this->student->id,
        'attendance_date' => Carbon::today(),
        'check_in_time' => '07:10:00',
        'check_in_status' => 'HADIR',
    ]);

    // 2. Second schedule in afternoon
    $schedule2 = ClassSchedule::create([
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day_of_week' => TeacherAttendanceService::getIndonesianDayName(Carbon::today()),
        'start_time' => '10:00:00',
        'end_time' => '11:30:00',
    ]);

    $session2 = ClassSession::create([
        'schedule_id' => $schedule2->id,
        'teacher_id' => $this->guru->id,
        'pin_code' => '5555',
        'duration_minutes' => 3,
        'started_at' => Carbon::now(),
        'expires_at' => Carbon::now()->addMinutes(3),
        'status' => 'ACTIVE',
    ]);

    $this->actingAs($this->student);
    $response = $this->postJson('/siswa/verify-pin', [
        'pin' => '5555',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(200);

    // Daily attendance still maintains original 07:10:00 check_in_time
    $daily = DailyAttendance::where('user_id', $this->student->id)->whereDate('attendance_date', Carbon::today())->first();
    expect($daily->check_in_time)->toBe('07:10:00');
});

test('TC-SIS-PIN-010: verifyPin — validasi format PIN wajib 4 digit dan koordinat numerik', function () {
    $this->actingAs($this->student);

    $response = $this->postJson('/siswa/verify-pin', [
        'pin' => '12',
        'latitude' => '',
        'longitude' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['pin', 'latitude', 'longitude']);
});

test('TC-SIS-HIST-001: Riwayat presensi siswa ter-paginate', function () {
    LessonAttendance::create([
        'schedule_id' => $this->schedule->id,
        'student_id' => $this->student->id,
        'attendance_date' => Carbon::today(),
        'status' => 'HADIR',
        'verified_at' => Carbon::now(),
    ]);

    $this->actingAs($this->student);
    $response = $this->get('/siswa/riwayat');

    $response->assertStatus(200);
    $response->assertSee('Riwayat Presensi');
    $response->assertSee('Fikih');
});

test('TC-SIS-JADW-001: Jadwal mingguan siswa menampilkan kelompok jadwal harian', function () {
    $this->actingAs($this->student);
    $response = $this->get('/siswa/jadwal');

    $response->assertStatus(200);
    $response->assertSee('Jadwal Pelajaran');
    $response->assertSee('Fikih');
    $response->assertSee('Ust. H. Ahmad Dahlan');
});
