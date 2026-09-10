<?php

use App\Models\AttendanceAuditLog;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\DailyAttendance;
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

    $this->admin = User::create([
        'identity_number' => '198501012010011001',
        'name' => 'Staf Tata Usaha',
        'email' => 'tu@maarif.sch.id',
        'birth_date' => '1985-01-01',
        'password' => Hash::make('Admin123!'),
        'role' => 'admin',
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
        'day_of_week' => 'Senin',
        'start_time' => '07:30',
        'end_time' => '09:00',
    ]);

    $this->session = ClassSession::create([
        'schedule_id' => $this->schedule->id,
        'teacher_id' => $this->guru->id,
        'pin_code' => '8492',
        'duration_minutes' => 3,
        'started_at' => Carbon::now()->subMinutes(10),
        'expires_at' => Carbon::now()->subMinutes(7),
        'status' => 'LOCKED',
    ]);
});

test('TC-ADM-LAP-001: Laporan — index statistik dengan filter tanggal dan kelas', function () {
    LessonAttendance::create([
        'session_id' => $this->session->id,
        'schedule_id' => $this->schedule->id,
        'student_id' => $this->siswa->id,
        'attendance_date' => Carbon::today(),
        'status' => 'HADIR',
    ]);

    $this->actingAs($this->admin);

    $response = $this->get('/admin/laporan?start_date='.Carbon::today()->format('Y-m-d').'&end_date='.Carbon::today()->format('Y-m-d').'&classroom_id='.$this->classroom->id);
    $response->assertStatus(200);
    $response->assertSee('Laporan Kehadiran');
    $response->assertSee('Ahmad Fauzi');
});

test('TC-ADM-LAP-002: Laporan — cetak PDF resmi berkop madrasah dan penandatangan yang dapat disesuaikan', function () {
    $this->actingAs($this->admin);

    // Default signatory PDF generation
    $response = $this->get('/admin/laporan/cetak-pdf');
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
    expect($response->getContent())->toStartWith('%PDF-');

    // Customized signatory (e.g. Kepala Tata Usaha)
    $customResponse = $this->get('/admin/laporan/cetak-pdf?'.http_build_query([
        'signer_title' => 'Kepala Tata Usaha',
        'signer_name' => 'Dra. Hj. Siti Aminah',
        'signer_nip' => '197905122008012003',
        'headmaster_name' => 'Dr. KH. Abdullah, M.Ag',
    ]));
    $customResponse->assertStatus(200);
    $customResponse->assertHeader('content-type', 'application/pdf');
    expect($customResponse->getContent())->toStartWith('%PDF-');
});

test('TC-ADM-LAP-003: Laporan — ekspor CSV dengan BOM UTF-8 dan header standar', function () {
    $this->actingAs($this->admin);

    $response = $this->get('/admin/laporan/ekspor-excel');
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->getContent())->toContain('NISN,"Nama Siswa",Kelas,Hadir,Izin,Sakit,Alpa');
});

test('TC-ADM-PSW-001: Presensi Siswa — index filter harian dan kelas', function () {
    LessonAttendance::create([
        'session_id' => $this->session->id,
        'schedule_id' => $this->schedule->id,
        'student_id' => $this->siswa->id,
        'attendance_date' => Carbon::today(),
        'status' => 'ALPA',
    ]);

    $this->actingAs($this->admin);

    $response = $this->get(route('admin.presensi-siswa.index', [
        'classroom_id' => $this->classroom->id,
        'date' => Carbon::today()->format('Y-m-d'),
        'status' => 'ALPA',
        'search' => 'Ahmad',
    ]));

    $response->assertStatus(200);
    $response->assertSee('Ahmad Fauzi');
    $response->assertSee('1010101010');
    $response->assertSee('ALPA');
});

test('TC-ADM-PSW-002: Presensi Siswa — koreksi status ALPA ke IZIN dan mencatat audit log', function () {
    $attendance = LessonAttendance::create([
        'session_id' => $this->session->id,
        'schedule_id' => $this->schedule->id,
        'student_id' => $this->siswa->id,
        'attendance_date' => Carbon::today(),
        'status' => 'ALPA',
    ]);

    $this->actingAs($this->admin);

    $response = $this->put(route('admin.presensi-siswa.update', $attendance), [
        'status' => 'IZIN',
        'reason' => 'Surat izin dari orang tua menyusul',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Verify LessonAttendance updated
    expect($attendance->fresh()->status)->toBe('IZIN');
    expect($attendance->fresh()->notes)->toBe('Surat izin dari orang tua menyusul');
    expect($attendance->fresh()->confirmed_by)->toBe($this->admin->id);

    // Verify AuditLog recorded
    $log = AttendanceAuditLog::where('lesson_attendance_id', $attendance->id)->first();
    expect($log)->not->toBeNull();
    expect($log->old_status)->toBe('ALPA');
    expect($log->new_status)->toBe('IZIN');
    expect($log->reason)->toBe('Surat izin dari orang tua menyusul');
    expect($log->changed_by)->toBe($this->admin->id);
});

test('TC-ADM-PSW-003: Presensi Siswa — koreksi ditolak jika alasan (reason) kosong atau < 3 karakter', function () {
    $attendance = LessonAttendance::create([
        'session_id' => $this->session->id,
        'schedule_id' => $this->schedule->id,
        'student_id' => $this->siswa->id,
        'attendance_date' => Carbon::today(),
        'status' => 'ALPA',
    ]);

    $this->actingAs($this->admin);

    $response = $this->put(route('admin.presensi-siswa.update', $attendance), [
        'status' => 'HADIR',
        'reason' => 'ab', // < 3 chars
    ]);

    $response->assertSessionHasErrors('reason');
    expect($attendance->fresh()->status)->toBe('ALPA');
});

test('TC-ADM-PSW-004: Presensi Siswa — koreksi ditolak jika status di luar enum', function () {
    $attendance = LessonAttendance::create([
        'session_id' => $this->session->id,
        'schedule_id' => $this->schedule->id,
        'student_id' => $this->siswa->id,
        'attendance_date' => Carbon::today(),
        'status' => 'ALPA',
    ]);

    $this->actingAs($this->admin);

    $response = $this->put(route('admin.presensi-siswa.update', $attendance), [
        'status' => 'INVALID_STATUS',
        'reason' => 'Alasan perubahan data',
    ]);

    $response->assertSessionHasErrors('status');
    expect($attendance->fresh()->status)->toBe('ALPA');
});

test('TC-ADM-PSW-005: Presensi Siswa — update idempoten jika status dan alasan sama persis (tidak menambah audit log)', function () {
    $attendance = LessonAttendance::create([
        'session_id' => $this->session->id,
        'schedule_id' => $this->schedule->id,
        'student_id' => $this->siswa->id,
        'attendance_date' => Carbon::today(),
        'status' => 'IZIN',
        'notes' => 'Surat dokter',
    ]);

    $this->actingAs($this->admin);

    $response = $this->put(route('admin.presensi-siswa.update', $attendance), [
        'status' => 'IZIN',
        'reason' => 'Surat dokter',
    ]);

    $response->assertRedirect();
    expect(AttendanceAuditLog::where('lesson_attendance_id', $attendance->id)->count())->toBe(0);
});

test('TC-ADM-PGR-001: Presensi Guru — index monitoring dan rekap kehadiran dewan guru', function () {
    DailyAttendance::create([
        'user_id' => $this->guru->id,
        'attendance_date' => Carbon::today()->format('Y-m-d'),
        'check_in_time' => '07:10:00',
        'check_in_status' => 'HADIR',
    ]);

    $this->actingAs($this->admin);

    $response = $this->get(route('admin.presensi-guru.index'));
    $response->assertStatus(200);
    $response->assertSee('Presensi Dewan Guru');
    $response->assertSee($this->guru->name);
    $response->assertSee('07:10 WIB');
});

test('TC-ADM-PGR-002: Presensi Guru — koreksi manual catatan presensi guru oleh admin', function () {
    $this->actingAs($this->admin);

    $today = Carbon::today()->format('Y-m-d');

    $response = $this->put(route('admin.presensi-guru.update', $this->guru), [
        'date' => $today,
        'status' => 'IZIN',
        'check_in_time' => '07:30',
        'check_out_time' => '14:00',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('daily_attendances', [
        'user_id' => $this->guru->id,
        'attendance_date' => $today,
        'check_in_status' => 'IZIN',
    ]);
});

test('TC-ADM-PGR-003: Presensi Guru — penetapan default check-in time saat dikosongkan', function () {
    $this->actingAs($this->admin);

    $today = Carbon::today()->format('Y-m-d');

    // Status HADIR without check_in_time defaults to 07:00:00
    $this->put(route('admin.presensi-guru.update', $this->guru), [
        'date' => $today,
        'status' => 'HADIR',
        'check_in_time' => '',
    ]);

    $daily = DailyAttendance::where('user_id', $this->guru->id)->whereDate('attendance_date', $today)->first();
    expect($daily->check_in_time)->toBe('07:00:00');

    // Status TERLAMBAT without check_in_time defaults to 07:30:00
    $this->put(route('admin.presensi-guru.update', $this->guru), [
        'date' => $today,
        'status' => 'TERLAMBAT',
        'check_in_time' => '',
    ]);

    expect($daily->fresh()->check_in_time)->toBe('07:30:00');
});

test('TC-ADM-AUD-001: Audit Trail — index log kronologis perubahan status presensi', function () {
    $attendance = LessonAttendance::create([
        'session_id' => $this->session->id,
        'schedule_id' => $this->schedule->id,
        'student_id' => $this->siswa->id,
        'attendance_date' => Carbon::today(),
        'status' => 'IZIN',
        'notes' => 'Alasan koreksi audit trail test',
    ]);

    AttendanceAuditLog::create([
        'lesson_attendance_id' => $attendance->id,
        'changed_by' => $this->admin->id,
        'old_status' => 'ALPA',
        'new_status' => 'IZIN',
        'reason' => 'Alasan koreksi audit trail test',
    ]);

    $this->actingAs($this->admin);

    $response = $this->get(route('admin.audit.index'));
    $response->assertStatus(200);
    $response->assertSee('Riwayat Perubahan Data');
    $response->assertSee('Alasan koreksi audit trail test');
});
