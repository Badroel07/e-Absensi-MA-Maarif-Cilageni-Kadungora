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
        'code' => 'FIQ',
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

    $this->siswa = User::create([
        'identity_number' => '1010101010',
        'name' => 'Ahmad Fauzi',
        'birth_date' => '2012-05-15',
        'password' => Hash::make('15052012'),
        'role' => 'siswa',
        'classroom_id' => $this->classroom->id,
        'is_active' => true,
    ]);

    $this->schedule = ClassSchedule::create([
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day_of_week' => TeacherAttendanceService::getIndonesianDayName(Carbon::today()),
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

    $this->kioskService = new KioskService;
});

test('TC-EDGE-001: Retry presensi saat koneksi terputus bersifat idempoten tanpa duplikasi', function () {
    $this->actingAs($this->siswa);

    // Initial submission
    $res1 = $this->postJson('/siswa/verify-pin', [
        'pin' => '8492',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);
    $res1->assertStatus(200);

    // Browser retry when network restored
    $res2 = $this->postJson('/siswa/verify-pin', [
        'pin' => '8492',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);
    $res2->assertStatus(200);
    $res2->assertJson(['already_verified' => true]);

    expect(LessonAttendance::where('schedule_id', $this->schedule->id)->where('student_id', $this->siswa->id)->count())->toBe(1);
});

test('TC-EDGE-002: GPS drift akurasi rendah dalam batas toleransi soft radius + 25 meter tetap diterima', function () {
    // School is at -7.010000, 107.900000 with 75m radius (+25m tolerance = 100m)
    // -7.010750 is approx 83m distance
    $this->actingAs($this->siswa);

    $response = $this->postJson('/siswa/verify-pin', [
        'pin' => '8492',
        'latitude' => -7.010750,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
});

test('TC-EDGE-003: Check-in Gating memblokir guru membuka sesi sebelum presensi masuk di Kiosk', function () {
    $this->actingAs($this->guru);

    $response = $this->post('/guru/sessions/'.$this->schedule->id.'/open', ['duration' => 3]);
    $response->assertSessionHas('error');
    expect(session('error'))->toContain('belum melakukan presensi masuk di Layar Presensi Madrasah');
});

test('TC-EDGE-004: Teaching Completion Lock memblokir guru check-out jika masih ada jadwal kelas belum LOCKED', function () {
    DailyAttendance::create([
        'user_id' => $this->guru->id,
        'attendance_date' => Carbon::today(),
        'check_in_time' => '07:05:00',
        'check_in_status' => 'HADIR',
    ]);

    // Active session exists (not yet LOCKED)
    $tokenPayload = $this->kioskService->generateTokenPayload();

    $this->actingAs($this->guru);
    $response = $this->postJson('/guru/scan/check-out', [
        'qr_token' => $tokenPayload['token'],
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);

    $response->assertStatus(422);
    $response->assertJson(['code' => 'TEACHING_COMPLETION_LOCKED']);
});

test('TC-EDGE-005: PIN expired boundary check — +3 detik grace masih lolos vs +6 detik ditolak', function () {
    $this->actingAs($this->siswa);

    // 1. +3 seconds (within 5s grace period)
    $this->session->update(['expires_at' => Carbon::now()->subSeconds(3)]);
    $resGrace = $this->postJson('/siswa/verify-pin', [
        'pin' => '8492',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);
    $resGrace->assertStatus(200);
    $resGrace->assertJson(['success' => true]);

    // Reset attendance
    LessonAttendance::where('session_id', $this->session->id)->delete();

    // 2. +6 seconds (exceeds 5s grace period)
    $this->session->update(['expires_at' => Carbon::now()->subSeconds(6)]);
    $resExpired = $this->postJson('/siswa/verify-pin', [
        'pin' => '8492',
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);
    $resExpired->assertStatus(422);
    $resExpired->assertJson(['code' => 'SESSION_EXPIRED']);
});

test('TC-EDGE-006: Akses luar madrasah (>100m) memicu penolakan geofence pada checkStatus dan verifyPin', function () {
    $this->actingAs($this->siswa);

    // Far distance coordinates (approx 2.5km away)
    $resStatus = $this->postJson('/siswa/check-status', [
        'latitude' => -7.035000,
        'longitude' => 107.925000,
    ]);
    $resStatus->assertStatus(200);
    $resStatus->assertJson(['is_within_geofence' => false, 'has_session' => false]);

    $resVerify = $this->postJson('/siswa/verify-pin', [
        'pin' => '8492',
        'latitude' => -7.035000,
        'longitude' => 107.925000,
    ]);
    $resVerify->assertStatus(422);
    $resVerify->assertJson(['code' => 'OUTSIDE_GEOFENCE']);
});

test('TC-EDGE-007: QR Token expired saat scan — window-1 masih diterima, window-2 ditolak', function () {
    DailyAttendance::create([
        'user_id' => $this->guru->id,
        'attendance_date' => Carbon::today(),
        'check_in_time' => '07:05:00',
        'check_in_status' => 'HADIR',
    ]);
    $this->session->update(['status' => 'LOCKED']);

    $this->actingAs($this->guru);

    // Window - 1 (within 40s tolerance)
    $nowTs = Carbon::now()->timestamp;
    $prevWindow = (int) floor($nowTs / 20) - 1;
    $secret = config('app.key') ?: (env('APP_KEY') ?: 'maarif-secret-kiosk-key-2026');
    $tokenPrev = hash_hmac('sha256', $prevWindow.':kiosk-ruang-guru-ma-maarif', $secret);

    $resPrev = $this->postJson('/guru/scan/check-out', [
        'qr_token' => $tokenPrev,
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);
    $resPrev->assertStatus(200);

    // Window - 2 (exceeds 40s tolerance)
    $expiredWindow = (int) floor($nowTs / 20) - 2;
    $tokenExpired = hash_hmac('sha256', $expiredWindow.':kiosk-ruang-guru-ma-maarif', $secret);

    $resExpired = $this->postJson('/guru/scan/check-out', [
        'qr_token' => $tokenExpired,
        'latitude' => -7.010000,
        'longitude' => 107.900000,
    ]);
    $resExpired->assertStatus(422);
    $resExpired->assertJson(['code' => 'INVALID_QR_TOKEN']);
});

test('TC-EDGE-008: Pemulihan terminal kiosk — dapat diakses dan di-refresh kapan pun tanpa sesi', function () {
    // 1. Terminal screen
    $resIndex = $this->get('/kiosk');
    $resIndex->assertStatus(200);

    // 2. Token refresh endpoint
    $resToken = $this->getJson('/kiosk/token');
    $resToken->assertStatus(200);
    expect($resToken->json('token'))->not->toBeNull();
});
