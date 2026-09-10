<?php

use App\Models\AttendanceAuditLog;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\LessonAttendance;
use App\Models\SchoolLocation;
use App\Models\Subject;
use App\Models\User;
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

    $this->guruA = User::create([
        'identity_number' => '198012012010011001',
        'name' => 'Ust. Guru A',
        'email' => 'gurua@maarif.sch.id',
        'birth_date' => '1980-12-01',
        'password' => Hash::make('01121980'),
        'role' => 'guru',
        'is_active' => true,
    ]);

    $this->guruB = User::create([
        'identity_number' => '198502022010011002',
        'name' => 'Ust. Guru B',
        'email' => 'gurub@maarif.sch.id',
        'birth_date' => '1985-02-02',
        'password' => Hash::make('02021985'),
        'role' => 'guru',
        'is_active' => true,
    ]);

    $this->siswa = User::create([
        'identity_number' => '1010101010',
        'name' => 'Ahmad Siswa',
        'birth_date' => '2012-05-15',
        'password' => Hash::make('15052012'),
        'role' => 'siswa',
        'classroom_id' => $this->classroom->id,
        'is_active' => true,
    ]);

    $this->admin = User::create([
        'identity_number' => '198501012010011001',
        'name' => 'Staf TU Admin',
        'email' => 'tu@maarif.sch.id',
        'birth_date' => '1985-01-01',
        'password' => Hash::make('Admin123!'),
        'role' => 'admin',
        'is_active' => true,
    ]);

    $this->scheduleA = ClassSchedule::create([
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guruA->id,
        'day_of_week' => 'Senin',
        'start_time' => '07:30:00',
        'end_time' => '09:00:00',
    ]);

    $this->sessionA = ClassSession::create([
        'schedule_id' => $this->scheduleA->id,
        'teacher_id' => $this->guruA->id,
        'pin_code' => '8492',
        'duration_minutes' => 3,
        'started_at' => Carbon::now(),
        'expires_at' => Carbon::now()->addMinutes(3),
        'status' => 'ACTIVE',
    ]);
});

test('TC-RBAC-001: Siswa dilarang mengakses halaman scan presensi guru dan check-in (403)', function () {
    $this->actingAs($this->siswa);

    $this->get('/guru/scan')->assertStatus(403);
    $this->postJson('/guru/scan/check-in', ['qr_token' => 'dummy'])->assertStatus(403);
});

test('TC-RBAC-002: Siswa dilarang mengakses seluruh endpoint Master Data Admin (403)', function () {
    $this->actingAs($this->siswa);

    $this->get('/admin')->assertStatus(403);
    $this->get('/admin/siswa')->assertStatus(403);
    $this->post('/admin/siswa', [])->assertStatus(403);
});

test('TC-RBAC-003: Siswa dilarang membuka sesi presensi kelas (403)', function () {
    $this->actingAs($this->siswa);

    $this->post('/guru/sessions/'.$this->scheduleA->id.'/open', ['duration' => 3])->assertStatus(403);
});

test('TC-RBAC-004: Siswa dilarang mengakses koreksi presensi dan audit log (403)', function () {
    $this->actingAs($this->siswa);

    $this->get('/admin/presensi-siswa')->assertStatus(403);
    $this->get('/admin/audit')->assertStatus(403);
});

test('TC-RBAC-005: Guru dilarang mengakses modul Master Data dan Konfigurasi Admin (403)', function () {
    $this->actingAs($this->guruA);

    $this->get('/admin')->assertStatus(403);
    $this->get('/admin/siswa')->assertStatus(403);
    $this->get('/admin/lokasi')->assertStatus(403);
    $this->post('/admin/lokasi', [])->assertStatus(403);
});

test('TC-RBAC-006: Guru dilarang memverifikasi PIN siswa via /siswa/verify-pin (403)', function () {
    $this->actingAs($this->guruA);

    $this->postJson('/siswa/verify-pin', ['pin' => '8492'])->assertStatus(403);
});

test('TC-RBAC-007: Horizontal Privilege Escalation — Guru B dilarang mengakses sesi dan rekonsiliasi milik Guru A (403)', function () {
    $this->actingAs($this->guruB);

    $this->get('/guru/sessions/'.$this->sessionA->id)->assertStatus(403);
    $this->getJson('/guru/sessions/'.$this->sessionA->id.'/status')->assertStatus(403);
    $this->get('/guru/sessions/'.$this->sessionA->id.'/reconcile')->assertStatus(403);
    $this->post('/guru/sessions/'.$this->sessionA->id.'/reconcile', [])->assertStatus(403);
});

test('TC-RBAC-008: Guru dilarang mengakses modul Presensi Admin dan Audit Trail (403)', function () {
    $this->actingAs($this->guruA);

    $this->get('/admin/presensi-siswa')->assertStatus(403);
    $this->get('/admin/presensi-guru')->assertStatus(403);
    $this->get('/admin/audit')->assertStatus(403);
});

test('TC-RBAC-009: Admin dilarang mengakses route yang dikhususkan untuk Siswa / Guru (403)', function () {
    $this->actingAs($this->admin);

    $this->postJson('/siswa/verify-pin', ['pin' => '8492'])->assertStatus(403);
    $this->get('/guru/scan')->assertStatus(403);
});

test('TC-RBAC-010: Guest dialihkan ke /login jika mengakses seluruh route terproteksi (302)', function () {
    $this->get('/siswa')->assertRedirect(route('login'));
    $this->get('/guru')->assertRedirect(route('login'));
    $this->get('/admin')->assertRedirect(route('login'));
    $this->get('/profile')->assertRedirect(route('login'));
});

test('TC-RBAC-011: Locked Session — Guru terkunci dari pengubahan, Admin tetap dapat mengoreksi via audit trail', function () {
    // 1. Lock the session
    $this->sessionA->update(['status' => 'LOCKED']);
    $attendance = LessonAttendance::create([
        'session_id' => $this->sessionA->id,
        'schedule_id' => $this->scheduleA->id,
        'student_id' => $this->siswa->id,
        'attendance_date' => Carbon::today(),
        'status' => 'ALPA',
    ]);

    // 2. Guru tries to reconcile -> blocked with error
    $this->actingAs($this->guruA);
    $guruRes = $this->post(route('guru.session.reconcile.save', $this->sessionA), [
        'statuses' => [$this->siswa->id => 'HADIR'],
    ]);
    $guruRes->assertRedirect(route('guru.dashboard'));
    $guruRes->assertSessionHas('error');
    expect($attendance->fresh()->status)->toBe('ALPA');

    // 3. Admin overrides status with reason -> success + audit log
    $this->actingAs($this->admin);
    $adminRes = $this->put(route('admin.presensi-siswa.update', $attendance), [
        'status' => 'IZIN',
        'reason' => 'Koreksi resmi oleh Staf TU',
    ]);
    $adminRes->assertRedirect();
    $adminRes->assertSessionHas('success');

    expect($attendance->fresh()->status)->toBe('IZIN');
    expect(AttendanceAuditLog::where('lesson_attendance_id', $attendance->id)->count())->toBe(1);
});
