<?php

use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\DailyAttendance;
use App\Models\LessonAttendance;
use App\Models\SchoolLocation;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

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

    $this->classroomB = Classroom::create([
        'name' => '7B',
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

    $this->guru2 = User::create([
        'identity_number' => '198502022010011002',
        'name' => 'Ust. Mahmudin, M.Pd.I',
        'email' => 'mahmudin@maarif.sch.id',
        'birth_date' => '1985-02-02',
        'password' => Hash::make('02021985'),
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
});

test('TC-ADM-DASH-001: Dashboard admin memuat metrik KPI dan membatasi data presensi terbaru 10 baris', function () {
    $this->actingAs($this->admin);

    // Create 12 teachers and daily attendances
    for ($i = 1; $i <= 12; $i++) {
        $teacher = User::create([
            'identity_number' => '1980000000000000'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            'name' => "Guru {$i}",
            'birth_date' => '1980-01-01',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'is_active' => true,
        ]);

        DailyAttendance::create([
            'user_id' => $teacher->id,
            'attendance_date' => Carbon::today(),
            'check_in_time' => '07:'.str_pad((string) $i, 2, '0', STR_PAD_LEFT).':00',
            'check_in_status' => 'HADIR',
        ]);
    }

    $response = $this->get('/admin');
    $response->assertStatus(200);
    $response->assertSee('Dashboard');
    expect($response->viewData('recentGuruAttendances'))->toHaveCount(10);
});

test('TC-ADM-SIS-001: Siswa index — pencarian dan filter kelas', function () {
    $this->actingAs($this->admin);

    $response = $this->get('/admin/siswa?search=Ahmad&classroom_id='.$this->classroom->id);
    $response->assertStatus(200);
    $response->assertSee('Ahmad Fauzi');
});

test('TC-ADM-SIS-002: Siswa store — sukses membuat siswa baru dengan password tanggal lahir bawaan', function () {
    $this->actingAs($this->admin);

    $response = $this->post('/admin/siswa', [
        'identity_number' => '2020202020',
        'name' => 'Ahmad Fauzi Baru',
        'birth_date' => '2013-06-10',
        'classroom_id' => $this->classroom->id,
        'phone_number' => '08123456789',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $newStudent = User::where('identity_number', '2020202020')->first();
    expect($newStudent)->not->toBeNull();
    expect($newStudent->role)->toBe('siswa');
    expect(Hash::check('10062013', $newStudent->password))->toBeTrue();
});

test('TC-ADM-SIS-003: Siswa store — validasi unique NISN', function () {
    $this->actingAs($this->admin);

    $response = $this->post('/admin/siswa', [
        'identity_number' => '1010101010', // duplicate
        'name' => 'Duplikat Siswa',
        'birth_date' => '2013-06-10',
        'classroom_id' => $this->classroom->id,
    ]);

    $response->assertSessionHasErrors('identity_number');
});

test('TC-ADM-SIS-004: Siswa store — validasi NISN wajib 10 digit', function () {
    $this->actingAs($this->admin);

    $response = $this->post('/admin/siswa', [
        'identity_number' => '12345', // not 10 digits
        'name' => 'Siswa Invalid Digit',
        'birth_date' => '2013-06-10',
        'classroom_id' => $this->classroom->id,
    ]);

    $response->assertSessionHasErrors('identity_number');
});

test('TC-ADM-SIS-005: Siswa store — upload foto profil tersimpan di storage public', function () {
    $photo = UploadedFile::fake()->image('siswa.jpg', 300, 300);

    $this->actingAs($this->admin);
    $response = $this->post('/admin/siswa', [
        'identity_number' => '3030303030',
        'name' => 'Siswa Dengan Foto',
        'birth_date' => '2013-01-01',
        'classroom_id' => $this->classroom->id,
        'photo' => $photo,
    ]);

    $response->assertSessionHas('success');
    $student = User::where('identity_number', '3030303030')->first();
    expect($student->profile_photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($student->profile_photo_path);
});

test('TC-ADM-SIS-006: Siswa update — memperbarui data dan mengganti foto profil', function () {
    $this->actingAs($this->admin);

    $newPhoto = UploadedFile::fake()->image('siswa_updated.jpg');
    $response = $this->put(route('admin.siswa.update', $this->siswa), [
        'identity_number' => $this->siswa->identity_number,
        'name' => 'Ahmad Fauzi Updated',
        'birth_date' => '2012-05-15',
        'classroom_id' => $this->classroom->id,
        'photo' => $newPhoto,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect($this->siswa->fresh()->name)->toBe('Ahmad Fauzi Updated');
    expect($this->siswa->fresh()->profile_photo_path)->not->toBeNull();
});

test('TC-ADM-SIS-007: Siswa update — opsi hapus foto (remove_photo)', function () {
    $this->siswa->update(['profile_photo_path' => 'profile-photos/test.jpg']);
    Storage::disk('public')->put('profile-photos/test.jpg', 'dummy');

    $this->actingAs($this->admin);
    $response = $this->put(route('admin.siswa.update', $this->siswa), [
        'identity_number' => $this->siswa->identity_number,
        'name' => $this->siswa->name,
        'birth_date' => '2012-05-15',
        'classroom_id' => $this->classroom->id,
        'remove_photo' => 1,
    ]);

    $response->assertSessionHas('success');
    expect($this->siswa->fresh()->profile_photo_path)->toBeNull();
    Storage::disk('public')->assertMissing('profile-photos/test.jpg');
});

test('TC-ADM-SIS-008: Siswa destroy — menghapus data siswa', function () {
    $this->actingAs($this->admin);

    $response = $this->delete(route('admin.siswa.destroy', $this->siswa));
    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect(User::find($this->siswa->id))->toBeNull();
});

test('TC-ADM-SIS-009: Siswa riwayat individual — menampilkan statistik dan histori presensi', function () {
    LessonAttendance::create([
        'schedule_id' => ClassSchedule::create([
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->guru->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:30',
            'end_time' => '09:00',
        ])->id,
        'student_id' => $this->siswa->id,
        'attendance_date' => Carbon::today(),
        'status' => 'HADIR',
    ]);

    $this->actingAs($this->admin);
    $response = $this->get(route('admin.siswa.riwayat', $this->siswa));

    $response->assertStatus(200);
    $response->assertSee('Ahmad Fauzi');
    $response->assertSee('Fikih');
});

test('TC-ADM-GUR-001: Guru index — list dan pencarian', function () {
    $this->actingAs($this->admin);

    $response = $this->get('/admin/guru?search=Ahmad');
    $response->assertStatus(200);
    $response->assertSee('Ust. H. Ahmad Dahlan');
});

test('TC-ADM-GUR-002: Guru store — sukses dengan password tanggal lahir bawaan', function () {
    $this->actingAs($this->admin);

    $response = $this->post('/admin/guru', [
        'identity_number' => '198512022010011002',
        'name' => 'Siti Aisyah, S.Pd.I',
        'birth_date' => '1985-12-02',
        'email' => 'siti@maarif.sch.id',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $newTeacher = User::where('identity_number', '198512022010011002')->first();
    expect($newTeacher)->not->toBeNull();
    expect(Hash::check('02121985', $newTeacher->password))->toBeTrue();
});

test('TC-ADM-GUR-003: Guru store — validasi unique NIP dan email', function () {
    $this->actingAs($this->admin);

    $response = $this->post('/admin/guru', [
        'identity_number' => '198012012010011001', // duplicate
        'name' => 'Guru Duplikat',
        'birth_date' => '1980-12-01',
        'email' => 'ahmad@maarif.sch.id', // duplicate
    ]);

    $response->assertSessionHasErrors(['identity_number', 'email']);
});

test('TC-ADM-GUR-004: Guru update, destroy, dan riwayat individual', function () {
    $this->actingAs($this->admin);

    // 1. Update
    $resUpdate = $this->put(route('admin.guru.update', $this->guru), [
        'identity_number' => $this->guru->identity_number,
        'name' => 'Dr. H. Ahmad Dahlan, M.Pd.',
        'birth_date' => '1980-12-01',
        'email' => $this->guru->email,
        'is_active' => 1,
    ]);
    $resUpdate->assertSessionHas('success');
    expect($this->guru->fresh()->name)->toBe('Dr. H. Ahmad Dahlan, M.Pd.');

    // 2. Riwayat
    DailyAttendance::create([
        'user_id' => $this->guru->id,
        'attendance_date' => Carbon::today(),
        'check_in_time' => '07:05:00',
        'check_in_status' => 'HADIR',
    ]);
    $resRiwayat = $this->get(route('admin.guru.riwayat', $this->guru));
    $resRiwayat->assertStatus(200);
    $resRiwayat->assertSee('Dr. H. Ahmad Dahlan, M.Pd.');

    // 3. Destroy
    $resDestroy = $this->delete(route('admin.guru.destroy', $this->guru));
    $resDestroy->assertSessionHas('success');
    expect(User::find($this->guru->id))->toBeNull();
});

test('TC-ADM-KLS-001: Master Kelas — CRUD kelas', function () {
    $this->actingAs($this->admin);

    // Create
    $resCreate = $this->post(route('admin.kelas.store'), [
        'name' => '8A',
        'grade_level' => '8',
        'academic_year' => '2026/2027',
    ]);
    $resCreate->assertSessionHas('success');
    $class8A = Classroom::where('name', '8A')->first();
    expect($class8A)->not->toBeNull();

    // Update
    $resUpdate = $this->put(route('admin.kelas.update', $class8A), [
        'name' => '8-Unggulan',
        'grade_level' => '8',
        'academic_year' => '2026/2027',
    ]);
    $resUpdate->assertSessionHas('success');
    expect($class8A->fresh()->name)->toBe('8-Unggulan');

    // Destroy
    $resDestroy = $this->delete(route('admin.kelas.destroy', $class8A));
    $resDestroy->assertSessionHas('success');
    expect(Classroom::find($class8A->id))->toBeNull();
});

test('TC-ADM-MPL-001: Master Mapel — CRUD dan validasi unique code', function () {
    $this->actingAs($this->admin);

    // Create
    $resCreate = $this->post(route('admin.mapel.store'), [
        'code' => 'AA',
        'name' => 'Akidah Akhlak',
    ]);
    $resCreate->assertSessionHas('success');
    $subjectAA = Subject::where('code', 'AA')->first();
    expect($subjectAA)->not->toBeNull();

    // Duplicate code rejected
    $resDup = $this->post(route('admin.mapel.store'), [
        'code' => 'AA',
        'name' => 'Akidah Akhlak 2',
    ]);
    $resDup->assertSessionHasErrors('code');

    // Update
    $resUpdate = $this->put(route('admin.mapel.update', $subjectAA), [
        'code' => 'AA-01',
        'name' => 'Akidah Akhlak Lanjutan',
    ]);
    $resUpdate->assertSessionHas('success');
    expect($subjectAA->fresh()->code)->toBe('AA-01');

    // Destroy
    $resDestroy = $this->delete(route('admin.mapel.destroy', $subjectAA));
    $resDestroy->assertSessionHas('success');
    expect(Subject::find($subjectAA->id))->toBeNull();
});

test('TC-ADM-JDW-001: Jadwal store — sukses jika tidak ada bentrok guru maupun kelas', function () {
    $this->actingAs($this->admin);

    $response = $this->post(route('admin.jadwal.store'), [
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day_of_week' => 'Senin',
        'start_time' => '07:30',
        'end_time' => '08:30',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect(ClassSchedule::where('classroom_id', $this->classroom->id)->count())->toBe(1);
});

test('TC-ADM-JDW-002: Jadwal — validasi format jam after dan tolak bentrok overlap guru', function () {
    $this->actingAs($this->admin);

    // End time before start time
    $resAfter = $this->post(route('admin.jadwal.store'), [
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day_of_week' => 'Senin',
        'start_time' => '09:00',
        'end_time' => '08:00',
    ]);
    $resAfter->assertSessionHasErrors('end_time');

    // Create base schedule for guru: Senin 07:30 - 09:00 in 7A
    ClassSchedule::create([
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day_of_week' => 'Senin',
        'start_time' => '07:30',
        'end_time' => '09:00',
    ]);

    // Try to create overlapping schedule for SAME teacher: Senin 08:00 - 09:30 in 7B
    $resOverlapTeacher = $this->post(route('admin.jadwal.store'), [
        'classroom_id' => $this->classroomB->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day_of_week' => 'Senin',
        'start_time' => '08:00',
        'end_time' => '09:30',
    ]);
    $resOverlapTeacher->assertSessionHasErrors('teacher_id');
});

test('TC-ADM-JDW-003: Jadwal — filter berdasarkan kelas dan hari', function () {
    $this->actingAs($this->admin);

    ClassSchedule::create([
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day_of_week' => 'Senin',
        'start_time' => '07:30',
        'end_time' => '09:00',
    ]);

    $response = $this->get('/admin/jadwal?classroom_id='.$this->classroom->id.'&day=Senin');
    $response->assertStatus(200);
    $response->assertSee('Fikih');
    $response->assertSee('7A');
});

test('TC-ADM-JDW-004: Jadwal — update dan destroy dengan pengecekan double overlap', function () {
    $this->actingAs($this->admin);

    $schedule = ClassSchedule::create([
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day_of_week' => 'Senin',
        'start_time' => '07:30',
        'end_time' => '09:00',
    ]);

    // Update time
    $resUpdate = $this->put(route('admin.jadwal.update', $schedule), [
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day_of_week' => 'Senin',
        'start_time' => '08:00',
        'end_time' => '09:30',
    ]);
    $resUpdate->assertSessionHas('success');
    expect($schedule->fresh()->start_time)->toBe('08:00:00');

    // Destroy
    $resDestroy = $this->delete(route('admin.jadwal.destroy', $schedule));
    $resDestroy->assertSessionHas('success');
    expect(ClassSchedule::find($schedule->id))->toBeNull();
});

test('TC-ADM-JDW-005: Jadwal — tolak bentrok overlap rombel kelas pada hari dan jam yang sama', function () {
    $this->actingAs($this->admin);

    // Class 7A already has schedule by guru1 on Monday 07:30 - 09:00
    ClassSchedule::create([
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day_of_week' => 'Senin',
        'start_time' => '07:30',
        'end_time' => '09:00',
    ]);

    // Attempting to assign DIFFERENT teacher (guru2) to SAME class 7A overlapping Monday 08:00 - 09:30
    $response = $this->post(route('admin.jadwal.store'), [
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru2->id,
        'day_of_week' => 'Senin',
        'start_time' => '08:00',
        'end_time' => '09:30',
    ]);

    $response->assertSessionHasErrors('classroom_id');
});

test('TC-ADM-LOK-001: Lokasi geofence — index dan update konfigurasi radius', function () {
    $this->actingAs($this->admin);

    $resIndex = $this->get('/admin/lokasi');
    $resIndex->assertStatus(200);
    $resIndex->assertSee("MA Ma'arif Cilageni Kadungora");

    $resUpdate = $this->post('/admin/lokasi', [
        'name' => "MA Ma'arif Cilageni Updated",
        'latitude' => -7.010500,
        'longitude' => 107.901000,
        'radius_meters' => 120,
    ]);

    $resUpdate->assertSessionHas('success');
    expect(SchoolLocation::getActiveLocation()->radius_meters)->toBe(120);
});

test('TC-ADM-LOK-002: Lokasi — validasi radius out of range (min 30m, max 500m)', function () {
    $this->actingAs($this->admin);

    // Radius < 30m
    $resMin = $this->post('/admin/lokasi', [
        'name' => "MA Ma'arif",
        'latitude' => -7.010000,
        'longitude' => 107.900000,
        'radius_meters' => 10,
    ]);
    $resMin->assertSessionHasErrors('radius_meters');

    // Radius > 500m
    $resMax = $this->post('/admin/lokasi', [
        'name' => "MA Ma'arif",
        'latitude' => -7.010000,
        'longitude' => 107.900000,
        'radius_meters' => 600,
    ]);
    $resMax->assertSessionHasErrors('radius_meters');
});

test('TC-ADM-PWD-001: Reset password 1-klik mengembalikan password ke format tanggal lahir', function () {
    // Student changed password to something custom
    $this->siswa->update(['password' => Hash::make('CustomNewPassword999')]);

    $this->actingAs($this->admin);
    $response = $this->post('/admin/users/'.$this->siswa->id.'/reset-password');

    $response->assertSessionHas('success');
    expect(Hash::check('15052012', $this->siswa->fresh()->password))->toBeTrue();
});
