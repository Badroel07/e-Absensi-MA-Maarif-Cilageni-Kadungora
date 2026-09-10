<?php

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->classroom = Classroom::create([
        'name' => '7A',
        'grade_level' => '7',
        'academic_year' => '2026/2027',
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

    $this->guru = User::create([
        'identity_number' => '198012012010011001',
        'name' => 'Ust. H. Ahmad Dahlan',
        'email' => 'ahmad@maarif.sch.id',
        'birth_date' => '1980-12-01',
        'password' => Hash::make('01121980'),
        'role' => 'guru',
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
});

test('TC-COM-AUTH-001: Login — Siswa sukses dengan NISN dan password DDMMYYYY', function () {
    $response = $this->post('/login', [
        'login' => '1010101010',
        'password' => '15052012',
        'remember' => '1',
    ]);

    $response->assertRedirect(route('siswa.dashboard'));
    $this->assertAuthenticatedAs($this->siswa);
});

test('TC-COM-AUTH-002: Login — Guru sukses dengan NIP dan password DDMMYYYY', function () {
    $response = $this->post('/login', [
        'login' => '198012012010011001',
        'password' => '01121980',
    ]);

    $response->assertRedirect(route('guru.dashboard'));
    $this->assertAuthenticatedAs($this->guru);
});

test('TC-COM-AUTH-003: Login — Admin sukses dengan email dan password', function () {
    $response = $this->post('/login', [
        'login' => 'tu@maarif.sch.id',
        'password' => 'Admin123!',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($this->admin);
});

test('TC-COM-AUTH-004: Login — Ditolak jika password salah', function () {
    $response = $this->post('/login', [
        'login' => '1010101010',
        'password' => 'salah123',
    ]);

    $response->assertSessionHasErrors('login');
    $this->assertGuest();
});

test('TC-COM-AUTH-005: Login — Ditolak jika akun nonaktif (is_active=0)', function () {
    $this->siswa->update(['is_active' => false]);

    $response = $this->post('/login', [
        'login' => '1010101010',
        'password' => '15052012',
    ]);

    $response->assertSessionHasErrors('login');
    $this->assertGuest();
});

test('TC-COM-AUTH-006: Login — Validasi input required saat field kosong', function () {
    $response = $this->post('/login', [
        'login' => '',
        'password' => '',
    ]);

    $response->assertSessionHasErrors(['login', 'password']);
    $this->assertGuest();
});

test('TC-COM-AUTH-007: Root redirect per role', function () {
    // Guest redirects to /login
    $this->get('/')->assertRedirect(route('login'));

    // Siswa redirects to siswa.dashboard
    $this->actingAs($this->siswa)->get('/')->assertRedirect(route('siswa.dashboard'));

    // Guru redirects to guru.dashboard
    $this->actingAs($this->guru)->get('/')->assertRedirect(route('guru.dashboard'));

    // Admin redirects to admin.dashboard
    $this->actingAs($this->admin)->get('/')->assertRedirect(route('admin.dashboard'));
});

test('TC-COM-AUTH-008: Authenticated user yang mengakses /login dialihkan ke dashboard role', function () {
    $this->actingAs($this->siswa);
    $response = $this->get('/login');
    $response->assertRedirect(route('siswa.dashboard'));

    $this->actingAs($this->guru);
    $responseGuru = $this->get('/login');
    $responseGuru->assertRedirect(route('guru.dashboard'));

    $this->actingAs($this->admin);
    $responseAdmin = $this->get('/login');
    $responseAdmin->assertRedirect(route('admin.dashboard'));
});

test('TC-COM-AUTH-009: Logout via POST dan GET me-regenerate sesi dan redirect ke login', function () {
    // POST Logout
    $this->actingAs($this->siswa);
    $resPost = $this->post('/logout');
    $resPost->assertRedirect(route('login'));
    $this->assertGuest();

    // GET Logout
    $this->actingAs($this->guru);
    $resGet = $this->get('/logout');
    $resGet->assertRedirect(route('login'));
    $this->assertGuest();
});

test('TC-COM-AUTH-010: Remember Me persistent login setting', function () {
    $response = $this->post('/login', [
        'login' => '1010101010',
        'password' => '15052012',
        'remember' => '1',
    ]);

    $response->assertRedirect(route('siswa.dashboard'));
    $this->assertAuthenticatedAs($this->siswa);
    expect($this->siswa->fresh()->remember_token)->not->toBeNull();
});

test('TC-COM-AUTH-011: Ganti password sukses dengan current password benar dan konfirmasi valid', function () {
    $this->actingAs($this->siswa);

    $response = $this->post('/profile/password', [
        'current_password' => '15052012',
        'password' => 'Baru1234',
        'password_confirmation' => 'Baru1234',
    ]);

    $response->assertSessionHas('success');
    expect(Hash::check('Baru1234', $this->siswa->fresh()->password))->toBeTrue();
});

test('TC-COM-AUTH-012: Ganti password gagal jika current password salah', function () {
    $this->actingAs($this->siswa);

    $response = $this->post('/profile/password', [
        'current_password' => 'salah',
        'password' => 'Baru1234',
        'password_confirmation' => 'Baru1234',
    ]);

    $response->assertSessionHasErrors('current_password');
    expect(Hash::check('15052012', $this->siswa->fresh()->password))->toBeTrue();
});

test('TC-COM-AUTH-013: Ganti password gagal jika konfirmasi password tidak cocok', function () {
    $this->actingAs($this->siswa);

    $response = $this->post('/profile/password', [
        'current_password' => '15052012',
        'password' => 'Baru1234',
        'password_confirmation' => 'mismatchPass',
    ]);

    $response->assertSessionHasErrors('password');
});

test('TC-COM-AUTH-014: Profil — lihat halaman, upload foto, dan hapus foto profil', function () {
    $this->actingAs($this->siswa);

    // 1. GET /profile
    $resView = $this->get('/profile');
    $resView->assertStatus(200);
    $resView->assertSee($this->siswa->name);

    // 2. Upload photo
    $photo = UploadedFile::fake()->image('avatar.jpg', 400, 400);
    $resUpload = $this->post('/profile/photo', ['photo' => $photo]);
    $resUpload->assertSessionHas('success');

    $this->siswa->refresh();
    expect($this->siswa->profile_photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($this->siswa->profile_photo_path);

    // 3. Delete photo
    $resDelete = $this->delete('/profile/photo');
    $resDelete->assertSessionHas('success');

    $this->siswa->refresh();
    expect($this->siswa->profile_photo_path)->toBeNull();
});

test('TC-COM-AUTH-015: Profil foto — validasi tolak format non-gambar dan ukuran >2MB', function () {
    $this->actingAs($this->siswa);

    // Non-image file
    $pdf = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');
    $resPdf = $this->post('/profile/photo', ['photo' => $pdf]);
    $resPdf->assertSessionHasErrors('photo');

    // Image oversize > 2048 KB
    $largeImage = UploadedFile::fake()->create('large.jpg', 2500, 'image/jpeg');
    $resLarge = $this->post('/profile/photo', ['photo' => $largeImage]);
    $resLarge->assertSessionHasErrors('photo');
});

test('TC-COM-AUTH-016: Akses route terproteksi tanpa login dialihkan ke /login (302)', function () {
    $this->get('/siswa')->assertRedirect(route('login'));
    $this->get('/guru')->assertRedirect(route('login'));
    $this->get('/admin')->assertRedirect(route('login'));
    $this->get('/profile')->assertRedirect(route('login'));
});
