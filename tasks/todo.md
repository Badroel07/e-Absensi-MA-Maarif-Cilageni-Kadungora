# Task List: GPS Auto-Recovery & Lifecycle Enhancement

## Phase 1: Siswa Dashboard Enhancement

### Task 1: Siswa Dashboard Geolocation Lifecycle & Auto-Recovery
**Description:** Memperbarui logika geolokasi pada dashboard siswa dengan menambahkan manajemen `activeWatchId`, pembersihan `clearWatch`, auto-retry saat timeout/cold start, event listener `visibilitychange` & `focus`, serta integrasi Permissions API agar GPS langsung terdeteksi otomatis tanpa reload.

**Acceptance criteria:**
- [x] `activeWatchId` dikelola dengan `clearWatch` sebelum membuat instance baru untuk mencegah memory leak.
- [x] Menambahkan penanganan auto-retry (3-4 detik) jika posisi gagal terdeteksi atau timeout saat GPS perangkat sedang aktifasi.
- [x] Menambahkan event listener `visibilitychange` dan `focus` dengan debounce/throttle agar saat siswa membuka kembali browser setelah menyalakan GPS, koordinat langsung diperbarui otomatis.
- [x] Menambahkan listener `navigator.permissions.query({ name: 'geolocation' })` jika didukung browser.

**Verification:**
- [x] Build/Syntax check: File blade valid tanpa error parsing JavaScript.
- [x] Manual check: Buka dashboard siswa, pastikan status lokasi merespons event perpindahan tab dan simulator.

**Dependencies:** None
**Files likely touched:**
- `resources/views/siswa/dashboard.blade.php`
**Estimated scope:** S (1 file)

---

## Phase 2: Guru Dashboard & Scan Enhancement

### Task 2: Guru Dashboard Geolocation Lifecycle & Auto-Recovery
**Description:** Menerapkan arsitektur lifecycle yang sama pada dashboard guru (`resources/views/guru/dashboard.blade.php`) agar guru tidak perlu me-refresh halaman ketika masuk kelas atau baru mengaktifkan GPS di ponselnya.

**Acceptance criteria:**
- [x] `activeWatchId` dikelola dengan `clearWatch` sebelum inisialisasi ulang.
- [x] Auto-retry otomatis terjadwal jika error code 2 (unavailable) atau 3 (timeout) saat sinyal satelit belum terkunci.
- [x] Listener `visibilitychange` dan `focus` dengan throttling terpasang rapi.
- [x] Permissions API change listener aktif saat status izin berubah menjadi `granted`.

**Verification:**
- [x] Manual check: Status geofence guru terupdate secara dinamis saat tab kembali aktif.

**Dependencies:** Task 1
**Files likely touched:**
- `resources/views/guru/dashboard.blade.php`
**Estimated scope:** S (1 file)

---

### Task 3: Guru QR Scanner Continuous Detection & Auto-Recovery
**Description:** Meningkatkan fitur geolokasi pada scanner QR guru ([`guru/scan.blade.php`](file:///c:/Users/muham/OneDrive/Desktop/Folder%20Serbabisa/Sistem%20Absensi%20Kehadiran%20Maarif/resources/views/guru/scan.blade.php)) dari yang sebelumnya hanya single-shot `getCurrentPosition` (yang langsung macet jika timeout) menjadi dual-tier continuous watch dengan caching dan auto-retry saat kembali ke layar pemindaian.

**Acceptance criteria:**
- [x] Pengecekan awal memanfaatkan cache koordinat terbaru (`maximumAge: 30000`) agar langsung siap presensi tanpa menunggu.
- [x] Pelacakan lanjutan menggunakan `watchPosition` dengan pengelolaan `activeWatchId` dan `clearWatch`.
- [x] Auto-retry berjalan jika guru tiba di sekolah dan baru menyalakan GPS.
- [x] Listener `visibilitychange` dan `focus` otomatis mendeteksi posisi saat guru kembali dari aplikasi pengaturan HP.

**Verification:**
- [x] Manual check: Halaman scan tidak menampilkan error "Lokasi belum aktif" permanen jika GPS diaktifkan belakangan.

**Dependencies:** Task 2
**Files likely touched:**
- `resources/views/guru/scan.blade.php`
**Estimated scope:** S (1 file)

---

## Phase 3: GPS Searching State & Attendance Lock Prevention

### Task 4: Sembunyikan & Cegah Presensi Saat GPS Sedang Mencari Lokasi
**Description:** Memastikan bahwa ketika status GPS masih 'sedang mencari lokasi' (initial cold-start, sinyal timeout, atau re-acquiring), seluruh elemen antarmuka untuk presensi disembunyikan atau dinonaktifkan secara total sehingga siswa/guru tidak dapat melakukan presensi tanpa verifikasi lokasi valid.

**Acceptance criteria:**
- [x] Dashboard Siswa: Menambahkan notifikasi `gpsSearchingNotice` yang informatif dengan animasi pulse satelit saat posisi sedang dicari.
- [x] Dashboard Siswa: Kartu input PIN (`sessionCard`) dan notifikasi 'tidak ada sesi' disembunyikan sepenuhnya selama sinyal GPS belum terkunci (`isGpsLocked === false`).
- [x] Dashboard Siswa: Input PIN (on-screen keypad & keyboard fisik) serta submit PIN diproteksi ganda dengan pengecekan `isGpsLocked && isWithinGeofence && currentCoords.lat`.
- [x] Dashboard Guru: Tombol buka sesi kelas (`.sessionSubmitBtn`) berstatus `disabled` dan berpenampilan `opacity-50 cursor-not-allowed` secara default sebelum GPS terkunci dalam radius sekolah.
- [x] Dashboard Guru: Form submission pembukaan sesi dicegah dengan alert jika GPS belum terverifikasi atau berada di luar geofence.
- [x] Scanner Guru: Pemindaian QR Kiosk dan submit token manual divalidasi ketat dan dibatalkan jika `isWithinGeofence !== true` atau koordinat GPS belum terverifikasi.

**Verification:**
- [x] Test suite passing: `rtk vendor/bin/pest --filter Geofence` dan `rtk vendor/bin/pest --filter Attendance` (46 test passed).
- [x] Pint code format: `rtk vendor/bin/pint --dirty --format agent` lolos tanpa error.

---

## Checkpoint: Verification & Testing
- [x] Jalankan pest test suite untuk geofence dan presensi: `rtk vendor/bin/pest --filter Geofence` dan `rtk vendor/bin/pest --filter Attendance` (Semua 46 tes lulus!)
- [x] Pastikan tidak ada konflik dengan dev simulator di `resources/views/partials/time-simulator.blade.php`
- [x] Validasi kepatuhan formatting Laravel Pint: `rtk vendor/bin/pint --dirty --format agent`

