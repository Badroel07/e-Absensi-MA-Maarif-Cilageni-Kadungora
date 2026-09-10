# Testing Specification — Sistem Absensi Kehadiran Siswa & Guru MA Ma'arif Cilageni Kadungora

> **Versi:** 2.1 — 2026-09-09 | Selaras PRD v2.1 | Stack: Laravel 11 Blade Custom Admin Panel (migrasi Filament), CSV BOM, radius 30-500+25m, late 07:15  
> **Stack Detail:** Laravel 11, Blade Custom Admin Panel, Blade+PWA, MySQL 8, Cache Redis/File  
> **Sumber Kebenaran:** `Sistem-Absensi-Maarif-PRD.md` v2.1 (6 Engine + Appendix 9.5), `routes/web.php`, 5 Controller, 11 Service, 9 Model  
> **Cakupan Role:** Admin (`admin`), Dewan Guru (`guru`), Siswa (`siswa`), Kiosk Ruang Guru (public), Common/Auth  
> **Format Tabel:** Sesuai permintaan — `ID Uji | Modul/Sub-Fitur | Skenario Uji | Prasyarat | Langkah Pengujian | Data Uji | Hasil yang Diharapkan`

---

## Konvensi ID & Prasyarat Umum

| Kode | Arti |
|------|------|
| `TC-COM-AUTH-xxx` | Common / Autentikasi & Sesi |
| `TC-SIS-xxx` | Role Siswa |
| `TC-GUR-xxx` | Role Guru |
| `TC-ADM-xxx` | Role Admin |
| `TC-KIOSK-xxx` | Kiosk Ruang Guru (public display) |
| `TC-EDGE-xxx` | Edge Cases PRD §6 |
| `TC-RBAC-xxx` | Negative / Akses Ditolak per RBAC Matrix PRD §4.2 |

**Data Referensi Standar (dipakai berulang):**

- Koordinat Madrasah Aktif `school_locations.is_active=1`: lat `-7.010000`, lng `107.900000`, radius `75m` (toleransi soft `+25m` = batas efektif `100m`). Implementasi Haversine server-side `SchoolLocation::calculateDistance()` + `ClassroomSessionService:81` & `TeacherAttendanceService:103`.
- Siswa: `identity_number` NISN 10-digit, `birth_date` `2012-05-15` → password default `15052012`, `classroom_id` = 7A, `is_active=1`.
- Guru: `identity_number` NIP `198012012010011001`, `birth_date` `1980-12-01` → `01121980`, `is_active=1`, mengampu `ClassSchedule` Senin `07:30-08:30` mapel Fikih kelas 7A.
- Admin: email `tu@maarif.sch.id`, password `Admin123!`.
- HMAC Secret = `config('app.key')`, window 20 detik `KioskService:32`.
- Threshold keterlambatan guru `07:15 WIB` (`TeacherAttendanceService:199`).

---

## 1. Common — Autentikasi, Sesi & Profil (`AuthController` + `AuthService`)

| ID Uji | Modul/Sub-Fitur | Skenario Uji | Prasyarat | Langkah Pengujian | Data Uji | Hasil yang Diharapkan |
|--------|-----------------|--------------|-----------|-------------------|----------|----------------------|
| TC-COM-AUTH-001 | Login — Siswa sukses (NISN) | Login dengan NISN + password default DDMMYYYY valid | Siswa NISN `1010101010` ada, `is_active=1`, belum login | 1. Buka `GET /login` 2. Isi `login=1010101010`, `password=15052012`, `remember=1` 3. Klik Masuk | `login=1010101010`, `password=15052012` | `302` redirect ke `route('siswa.dashboard')`, session regenerated, `Auth::check()=true`, cookie `remember_token` ter-set |
| TC-COM-AUTH-002 | Login — Guru sukses (NIP) | Login dengan NIP + DDMMYYYY | Guru NIP `198012012010011001` ada | 1. POST `/login` dengan NIP | `login=198012012010011001`, `password=01121980` | Redirect ke `guru.dashboard` |
| TC-COM-AUTH-003 | Login — Admin sukses (email) | Login dengan email | Admin email `tu@maarif.sch.id` | 1. POST `/login` dengan email | `login=tu@maarif.sch.id`, `password=Admin123!` | Redirect ke `admin.dashboard` |
| TC-COM-AUTH-004 | Login — Kredensial salah | Password tidak cocok | User ada | 1. POST `/login` password salah | `login=1010101010`, `password=salah123` | `back()` dengan error `Nomor identitas (NISN / NIP / Email) atau kata sandi tidak cocok.` (AuthService:31), tidak login |
| TC-COM-AUTH-005 | Login — Akun nonaktif | `is_active=0` | Siswa `is_active=0` | 1. POST `/login` dengan akun nonaktif | `login=1010101010`, `password=15052012` | Error `Akun Anda sedang dinonaktifkan. Silakan hubungi Admin.` (AuthService:39) |
| TC-COM-AUTH-006 | Login — Validasi required | Field kosong | Guest | 1. POST `/login` tanpa `login` | `login=""`, `password=""` | Validasi 422 `login required`, `password required` |
| TC-COM-AUTH-007 | Root redirect per role | `GET /` redirect sesuai role | Login sebagai siswa/guru/admin | 1. Login lalu `GET /` | session auth | Siswa→`siswa.dashboard`, Guru→`guru.dashboard`, Admin→`admin.dashboard` (web.php:19). Guest→`login` |
| TC-COM-AUTH-008 | Sudah login akses /login | Authenticated GET /login dialihkan | Sudah login | 1. `GET /login` saat auth | — | Redirect `intended` ke home role (AuthController:22) |
| TC-COM-AUTH-009 | Logout | Invalidate session | Authenticated | 1. `POST /logout` atau `GET /logout` | — | `Auth::logout`, session invalidate, token regenerate, redirect `login` + flash `Anda telah berhasil keluar` |
| TC-COM-AUTH-010 | Remember Me persistent | Session tidak minta login ulang | Login dengan remember=1 | 1. Login remember=1 2. Tutup browser 3. Buka `/siswa` | — | Tetap authenticated via `remember_token` (AuthService:43) |
| TC-COM-AUTH-011 | Ganti password — sukses | Current benar, new confirmed | Login siswa | 1. `POST /profile/password` | `current_password=15052012`, `password=Baru1234`, `password_confirmation=Baru1234` | `success: Kata sandi berhasil diperbarui.`, hash baru valid (AuthService:74) |
| TC-COM-AUTH-012 | Ganti password — current salah | Current tidak cocok | Login siswa | 1. `POST /profile/password` | `current_password=salah` | Error validasi `Kata sandi saat ini tidak sesuai.` |
| TC-COM-AUTH-013 | Ganti password — konfirmasi mismatch | `password_confirmation` beda | Login siswa | 1. `POST /profile/password` | `password=Baru1234`, `password_confirmation=beda` | Validasi `confirmed` gagal, 422 |
| TC-COM-AUTH-014 | Profil — lihat & foto | Update & hapus foto profil | Login any role | 1. `GET /profile` 2. `POST /profile/photo` file jpeg 1.2MB 3. `DELETE /profile/photo` | `photo=test.jpg (image/jpeg, 1200KB)` | View `profile.index` 200; upload sukses `Foto profil berhasil diperbarui.` path `profile-photos/...` di `public`; delete sukses path `null` & file terhapus (AuthController:83, AuthService:94) |
| TC-COM-AUTH-015 | Profil foto — validasi mime/size | Format tidak diizinkan / oversize | Login | 1. `POST /profile/photo` | `photo=test.pdf` atau `photo=3MB.jpg` | Error `Format foto harus berupa JPEG...` / `Ukuran foto maksimal adalah 2 MB` |
| TC-COM-AUTH-016 | Akses terproteksi tanpa login | Guest akses /siswa /guru /admin | Guest | 1. `GET /siswa` tanpa auth | — | Redirect `302` ke `login` (middleware `auth`) |

---

## 2. Role Siswa — Zero-Queue Classroom Attendance (`StudentController` + `ClassroomSessionService`)

| ID Uji | Modul/Sub-Fitur | Skenario Uji | Prasyarat | Langkah Pengujian | Data Uji | Hasil yang Diharapkan |
|--------|-----------------|--------------|-----------|-------------------|----------|----------------------|
| TC-SIS-DASH-001 | Dashboard siswa | Load dashboard harian | Login siswa 7A, ada `SchoolLocation` aktif, ada jadwal Senin | 1. `GET /siswa` | — | `200` view `siswa.dashboard` dengan `todayAttendances`, `todaySchedules`, counters `hadir/izin/sakit/alpa` (ClassroomSessionService:325) |
| TC-SIS-GEO-001 | checkStatus — di dalam geofence + ada sesi aktif | Kartu sesi muncul (radius+25m toleransi) | Siswa 7A lat/lng dalam radius, guru sudah `openSession` status ACTIVE | 1. `POST /siswa/check-status` | `latitude=-7.01001`, `longitude=107.90001` (≈2m, dalam radius+25m) | JSON `is_within_geofence=true` (`is_within_geofence = distance <= radius+25`, efektif 100m untuk 75m), `distance~2`, `has_session=true`, `session.subject_name=Fikih`, `remaining_seconds>0`, `has_verified=false` |
| TC-SIS-GEO-002 | checkStatus — di luar geofence | Kartu & PIN terkunci (radius+25m toleransi) | Siswa di rumah jauh | 1. `POST /siswa/check-status` | `latitude=-7.020`, `longitude=107.910` (>1000m, > radius+25m = 100m efektif) | `is_within_geofence=false` (`distance <= radius+25` gagal), `has_session=false` (session tetap ada tapi tidak di-expose), UI pesan `Anda berada di luar lingkungan madrasah — Sesi presensi terkunci` |
| TC-SIS-GEO-003 | checkStatus — tanpa koordinat | Geofence false, tidak ada sesi terekspos | Login siswa | 1. `POST /siswa/check-status` | `latitude=null`, `longitude=null` | `is_within_geofence=false`, `distance=null`, `has_session=false` |
| TC-SIS-GEO-004 | checkStatus — tanpa classroom_id | Siswa belum ditempatkan kelas | Siswa `classroom_id=null` | 1. `POST /siswa/check-status` dengan lat valid | lat valid dalam radius+25m toleransi (efektif 100m untuk 75m) | `is_within_geofence=true` (`distance <= radius+25`) tapi `session=null` (early return ClassroomSessionService:86) |
| TC-SIS-GEO-005 | checkStatus — sesi expired auto | Sesi hangus otomatis jadi EXPIRED | Session ACTIVE tapi `expires_at` lewat | 1. Tunggu timer habis / simulasi `Carbon::setTestNow(+4m)` 2. `POST /siswa/check-status` | lat valid | Service update `status=EXPIRED`, `has_session=false` |
| TC-SIS-PIN-001 | verifyPin — sukses HADIR + DailyAttendance | PIN benar & GPS valid & dalam durasi | Session ACTIVE pin `8492`, durasi 3m, siswa belum HADIR, dalam geofence | 1. `POST /siswa/verify-pin` | `pin=8492`, `latitude=-7.01001`, `longitude=107.90001` | `200 skucess=true`, `message=Presensi berhasil! Anda tercatat HADIR`, `LessonAttendance status=HADIR` + `verified_at`, `distance_meters` tersimpan, `DailyAttendance check_in_time` terisi (harian otomatis dari mapel jam pertama) |
| TC-SIS-PIN-002 | verifyPin — OUTSIDE_GEOFENCE | Di luar radius+25m toleransi | Session ACTIVE | 1. `POST /siswa/verify-pin` | `pin=8492`, lat jauh `>100m` (di luar radius+25m, efektif 100m untuk 75m → outside) | `422 success=false code=OUTSIDE_GEOFENCE`, pesan `Lokasi Anda berada di luar batas area madrasah (terdeteksi X meter...)` (`is_within_geofence = distance <= radius+25` gagal, Service:141) |
| TC-SIS-PIN-003 | verifyPin — NO_ACTIVE_SESSION | Tidak ada sesi untuk kelas | Tidak ada session ACTIVE hari ini | 1. `POST /siswa/verify-pin` | `pin=0000`, lat valid | `422 code=NO_ACTIVE_SESSION`, `Tidak ada sesi presensi aktif untuk kelas Anda` |
| TC-SIS-PIN-004 | verifyPin — SESSION_EXPIRED grace 5s | Kirim tepat saat 00:00 + 6 detik | Session ACTIVE `expires_at` = 08:03:00, now 08:03:06 | 1. `POST /siswa/verify-pin` | `pin=8492`, lat valid, now +6s | `422 code=SESSION_EXPIRED`, session diupdate `EXPIRED`, pesan `Waktu sesi presensi telah berakhir. Silakan lapor kepada Bapak/Ibu Guru...` |
| TC-SIS-PIN-005 | verifyPin — grace 5s masih sukses | Kirim di +3 detik toleransi | `expires_at` +3s | 1. `POST /siswa/verify-pin` | `pin=8492`, lat valid, now +3s | `200 success` (masih dalam window `expires_at+5s` Service:170) |
| TC-SIS-PIN-006 | verifyPin — INVALID_PIN decrement | PIN salah pertama | Session ACTIVE pin `8492` | 1. `POST /siswa/verify-pin` | `pin=0000` | `422 code=INVALID_PIN`, `Sisa kesempatan: 2 kali` (Cache `pin_fail:{session}:{student}` increment) |
| TC-SIS-PIN-007 | verifyPin — RATE_LIMITED setelah 3 salah | Brute-force protection | Sudah 3× INVALID_PIN | 1. `POST /siswa/verify-pin` ke-4 | `pin=0000` | `422 code=RATE_LIMITED`, `Batas 3 kali salah... dibekukan sementara selama 5 menit` (Service:184). `Cache::get=3` TTL 300s |
| TC-SIS-PIN-008 | verifyPin — already_verified idempoten | Sudah HADIR sesi yang sama | Sudah HADIR via TC-SIS-PIN-001 | 1. `POST /siswa/verify-pin` lagi pin benar | `pin=8492` | `200 success=true already_verified=true`, `Alhamdulillah, Anda sudah tercatat hadir pada sesi ini!` (Service:198), tidak duplikat row |
| TC-SIS-PIN-009 | verifyPin — DailyAttendance tidak overwrite | Harian sudah terisi jam pertama | `DailyAttendance check_in_time` sudah ada | 1. `POST /siswa/verify-pin` sesi jam kedua pin benar | `pin=xxxx` | LessonAttendance HADIR baru tercipta, tapi `DailyAttendance check_in_time` tetap jam pertama (Service:245 `if (! check_in_time)`) |
| TC-SIS-PIN-010 | verifyPin — validasi input | PIN bukan 4 char / lat required | Auth siswa | 1. `POST /siswa/verify-pin` | `pin=12`, `latitude=""` | Validasi 422 `pin size:4`, `latitude required numeric` |
| TC-SIS-HIST-001 | Riwayat siswa | Pagination riwayat presensi | Ada LessonAttendance | 1. `GET /siswa/riwayat` | — | `200` view `siswa.history`, list 15 per page order `attendance_date desc` with `schedule.subject, teacher` |
| TC-SIS-JADW-001 | Jadwal mingguan siswa | Jadwal kelas sendiri | Siswa 7A ada jadwal Senin-Jumat | 1. `GET /siswa/jadwal` | — | `200` view `siswa.schedule`, `schedules` via `ScheduleService::getStudentWeeklySchedule` terkelompok harian |

---

## 3. Role Guru — Kiosk Scan, Sesi PIN & Rekonsiliasi (`TeacherController` + `TeacherAttendanceService` + `ClassroomSessionService`)

| ID Uji | Modul/Sub-Fitur | Skenario Uji | Prasyarat | Langkah Pengujian | Data Uji | Hasil yang Diharapkan |
|--------|-----------------|--------------|-----------|-------------------|----------|----------------------|
| TC-GUR-DASH-001 | Dashboard guru | Metrik harian + gating | Login guru, ada jadwal hari ini | 1. `GET /guru` | — | `200` view `guru.dashboard` dengan `hasCheckedIn`, `dailyAttendance`, `schedules` today, `pendingSchedules`, `activeSessionsCount`, `lockedSessionsCount` (TeacherAttendanceService:56) |
| TC-GUR-GEO-001 | checkStatus — guru geofence | Evaluasi jarak guru | Ada SchoolLocation | 1. `POST /guru/check-status` | `latitude=-7.01001`, `longitude=107.90001` | JSON `is_within_geofence=true`, `distance`, `radius=75`, `school_name` |
| TC-GUR-SCAN-001 | Scan — view awal | Halaman scan presensi | Login guru | 1. `GET /guru/scan` | — | `200` view `guru.scan` dengan `hasCheckedIn`, `dailyAttendance`, `pendingSchedules` |
| TC-GUR-CHKIN-001 | Check-in — sukses HADIR (<07:15) | Datang tepat waktu (late threshold 07:15 WIB hardcode) | Belum check-in hari ini, `now=07:00`, dalam geofence, token HMAC valid window kini | 1. `POST /guru/scan/check-in` | `qr_token=<valid HMAC>`, `latitude=-7.01001`, `longitude=107.90001` | `200 success=true status=HADIR` (HADIR <=07:15, TeacherAttendanceService:199), `DailyAttendance check_in_time=07:00:00`, `check_in_status=HADIR`, `kiosk_latest_event` Cache ter-set type `check_in` |
| TC-GUR-CHKIN-002 | Check-in — sukses TERLAMBAT (>07:15) | Datang telat (late threshold 07:15 WIB hardcode) | `now=07:30` | 1. `POST /guru/scan/check-in` | `qr_token` valid, `now=07:30` | `status=TERLAMBAT` (TERLAMBAT >07:15, TeacherAttendanceService:199), `check_in_time=07:30:00` |
| TC-GUR-CHKIN-003 | Check-in — GEOFENCE_REQUIRED | GPS null | Belum check-in | 1. `POST /guru/scan/check-in` | `latitude=null`, `qr_token=valid` | `422 code=GEOFENCE_REQUIRED`, pesan `Lokasi GPS tidak terdeteksi...` |
| TC-GUR-CHKIN-004 | Check-in — OUTSIDE_GEOFENCE | Di luar madrasah | Jauh >100m | 1. `POST /guru/scan/check-in` | `latitude=-7.02`, `longitude=107.91` | `422 code=OUTSIDE_GEOFENCE`, `Presensi ditolak. Anda berada di luar area madrasah...` |
| TC-GUR-CHKIN-005 | Check-in — INVALID_QR_TOKEN | Token expired >40s | Token window `current-2` | 1. `POST /guru/scan/check-in` | `qr_token=expired_hash` | `422 code=INVALID_QR_TOKEN`, `Kode QR telah berganti atau kedaluwarsa...` |
| TC-GUR-CHKIN-006 | Check-in — already_checked_in idempoten | Sudah check-in | Sudah `check_in_time` ada | 1. `POST /guru/scan/check-in` lagi | `qr_token` valid | `200 success=true already_checked_in=true`, pesan `sudah melakukan presensi masuk hari ini pada pukul 07:00 WIB` (Service:178), tidak overwrite waktu |
| TC-GUR-OPEN-001 | Open Session — sukses ACTIVE | Check-in done + hari & jam sesuai slot | Sudah check-in, `now=Senin 07:35` dalam `07:30-08:30`, durasi 3 | 1. `POST /guru/sessions/{schedule}/open` | `duration=3` | `302` redirect `guru.session.show`, `ClassSession status=ACTIVE`, `pin_code` 4-digit `1000-9999`, `started_at=now`, `expires_at=now+3m`, previous ACTIVE hari ini jadi EXPIRED (Service:55) |
| TC-GUR-OPEN-002 | Open Session — Check-in Gating blok | Belum check-in kiosk | Belum check-in hari ini | 1. `POST /guru/sessions/{schedule}/open` | `duration=3` | `ValidationException` error `Akses Belum Tersedia: Bapak/Ibu Guru belum melakukan presensi masuk di Layar Presensi Madrasah...` (Service:29) |
| TC-GUR-OPEN-003 | Open Session — day mismatch | Time Gating STRICT (hanya hari & jam slot, sesuai ClassroomSessionService:34-44) — hari bukan slot | Jadwal `Selasa` tapi `now=Senin` | 1. `POST /guru/sessions/.../open` | — | Error `Sesi presensi hanya dapat dibuka pada hari Selasa. Hari ini Senin.` (ClassroomSessionService:34-44) |
| TC-GUR-OPEN-004 | Open Session — time mismatch | Time Gating STRICT (hanya hari & jam slot, sesuai ClassroomSessionService:34-44) — jam di luar slot | `schedule 07:30-08:30`, `now=06:00` atau `09:00` | 1. `POST /guru/sessions/.../open` | — | Error `Sesi presensi hanya dapat dibuka sesuai jadwal pelajaran: pukul 07:30–08:30 WIB. Waktu saat ini: pukul 06:00 WIB.` (ClassroomSessionService:34-44) |
| TC-GUR-OPEN-005 | Open Session — 403 bukan pengampu | Guru lain coba buka | Login guru B, schedule milik guru A | 1. `POST /guru/sessions/{schedule}/open` | — | `403 Bapak/Ibu Guru bukan pengampu jadwal pelajaran ini.` (TeacherController:120) |
| TC-GUR-OPEN-006 | Open Session — duration clamp | Durasi di luar 2-5 | Sudah check-in, dalam slot | 1. `POST .../open` | `duration=10` / `duration=1` | Clamp ke `5` / `2` (`max(2,min(5,duration))` Service:47), session tetap ACTIVE |
| TC-GUR-SESS-001 | Session Live — show + polling | Lihat sesi & counter live | Session ACTIVE milik guru login | 1. `GET /guru/sessions/{session}` 2. `GET /guru/sessions/{session}/status` | — | `200` view `guru.session-live` dengan `totalStudents`, `verifiedCount`; polling JSON `status=ACTIVE`, `is_expired=false`, `remaining_seconds`, `verified_count`, `verified_students[]` (Service:370) |
| TC-GUR-SESS-002 | Session Live — 403 bukan pemilik | Guru lain akses session | Session milik guru A, login guru B | 1. `GET /guru/sessions/{session}` | — | `403` |
| TC-GUR-RECON-001 | Reconcile — view daftar belum hadir | Setelah expired/closed | Session ACTIVE/EXPIRED milik guru | 1. `GET /guru/sessions/{session}/reconcile` | — | `200` view `guru.reconcile` dengan `students` active order name, `existingAttendances keyBy student_id`, `isLocked=false` |
| TC-GUR-RECON-002 | Reconcile — simpan & LOCKED | Tetapkan IZIN/SAKIT/ALPA | Ada 30 siswa, 20 sudah HADIR via PIN, 10 belum | 1. `POST /guru/sessions/{session}/reconcile` | `statuses[student_id]=IZIN/SAKIT/ALPA`, `notes[student_id]=Surat dokter...` | `302` ke `guru.dashboard` success `Data konfirmasi kehadiran siswa berhasil disimpan dan ditutup permanen.`, `LessonAttendance` 10 baris ter-create status sesuai (default ALPA jika tidak diisi), `session status=LOCKED` (Service:317), HADIR via reconcile juga isi DailyAttendance jika belum ada |
| TC-GUR-RECON-003 | Reconcile — default ALPA | Guru tidak isi status | Siswa belum hadir tidak dikirim | 1. `POST .../reconcile` tanpa `statuses` untuk siswa X | — | Siswa X jadi `ALPA` (Service:279) |
| TC-GUR-RECON-004 | Reconcile — blok jika LOCKED | Double submit | Session `status=LOCKED` | 1. `POST /guru/sessions/{session}/reconcile` | — | Redirect `guru.dashboard` error `Data kehadiran kelas ini telah ditutup dan disimpan permanen. Perubahan hanya dapat dilakukan melalui Admin.` (Controller:180) |
| TC-GUR-RECON-005 | Reconcile — HADIR siswa pertahankan | Siswa sudah HADIR tidak tertimpa | Siswa sudah HADIR | 1. `POST .../reconcile` dengan `statuses[siswaHADIR]=ALPA` | — | Tetap `HADIR` (Service:275 `if HADIR continue`) |
| TC-GUR-CHKOUT-001 | Check-out — sukses | Semua jadwal hari ini LOCKED | Sudah check-in, semua `class_schedules` hari ini punya session `LOCKED` | 1. `POST /guru/scan/check-out` | `qr_token` valid, lat valid | `200 success=true`, `DailyAttendance check_out_time=now`, `check_out_status=TEPAT_WAKTU`, `kiosk_latest_event type=check_out` |
| TC-GUR-CHKOUT-002 | Check-out — TEACHING_COMPLETION_LOCKED | Masih ada jadwal belum LOCKED | Ada 1 jadwal tanpa session atau status ACTIVE/EXPIRED | 1. `POST /guru/scan/check-out` | `qr_token` valid | `422 code=TEACHING_COMPLETION_LOCKED`, `pending_schedules=[Fikih (7A)]`, pesan `Presensi Pulang Terkunci: Masih ada 1 jadwal kelas yang belum tuntas...` (Service:279) |
| TC-GUR-CHKOUT-003 | Check-out — NOT_CHECKED_IN | Belum check-in tapi coba pulang | Tanpa DailyAttendance hari ini | 1. `POST /guru/scan/check-out` | — | `422 code=NOT_CHECKED_IN`, `Bapak/Ibu Guru belum melakukan presensi masuk hari ini.` |
| TC-GUR-CHKOUT-004 | Check-out — validasi token & geofence | Token invalid / luar geofence | — | 1. `POST .../check-out` | `qr_token=expired` atau lat jauh | `INVALID_QR_TOKEN` / `OUTSIDE_GEOFENCE` mirip check-in |
| TC-GUR-HIST-001 | Riwayat guru | Daftar sesi mengajar | Ada ClassSession | 1. `GET /guru/riwayat` | — | `200` view `guru.history`, paginate 15 order `created_at desc` with `schedule.classroom, subject` |
| TC-GUR-JADW-001 | Jadwal guru | Jadwal mingguan pengampu | — | 1. `GET /guru/jadwal` | — | `200` view `guru.schedule` via `ScheduleService::getTeacherWeeklySchedule` |

---

## 4. Role Admin (`AdminController` — 6 Sub-Modul Master + Laporan + Koreksi + Audit)

### 4A. Dashboard Monitoring Real-Time

| ID Uji | Modul/Sub-Fitur | Skenario Uji | Prasyarat | Langkah Pengujian | Data Uji | Hasil yang Diharapkan |
|--------|-----------------|--------------|-----------|-------------------|----------|----------------------|
| TC-ADM-DASH-001 | Dashboard admin | Metrik harian + sesi aktif + recent | Login admin, ada siswa/guru/attendance hari ini | 1. `GET /admin` | — | `200` view `admin.dashboard` dengan `totalSiswa`, `totalGuru`, `siswaHadir/Izin/Sakit/Alpa`, `guruHadir`, `activeSessions` 10 terbaru, `recentSiswaAttendances` 10, `recentGuruAttendances` 10 (AdminDashboardService:30) |

### 4B. Master Siswa

| ID Uji | Modul/Sub-Fitur | Skenario Uji | Prasyarat | Langkah Pengujian | Data Uji | Hasil yang Diharapkan |
|--------|-----------------|--------------|-----------|-------------------|----------|----------------------|
| TC-ADM-SIS-001 | Siswa index — list + search + filter kelas | Pagination & query | Ada siswa | 1. `GET /admin/siswa?search=Ahmad&classroom_id=xxx` | `search=Ahmad` | `200` view `admin.siswa.index`, paginate 15, filter `name like %Ahmad%` & `classroom_id` (UserManagementService:18) |
| TC-ADM-SIS-002 | Siswa store — sukses + password default | Tambah siswa valid | Login admin, kelas 7A ada | 1. `POST /admin/siswa` | `identity_number=2020202020 (10-digit)`, `name=Ahmad Fauzi`, `birth_date=2013-06-10`, `classroom_id=7A`, `phone_number=0812...` | `302` success `Data siswa berhasil ditambahkan dengan kata sandi bawaan: 10062013` (AdminController:72), `User role=siswa`, `password=Hash::make(10062013)`, `is_active=1` |
| TC-ADM-SIS-003 | Siswa store — validasi unique NISN | NISN duplikat | NISN `1010101010` sudah ada | 1. `POST /admin/siswa` | `identity_number=1010101010` | Validasi 422 `The identity number has already been taken.` |
| TC-ADM-SIS-004 | Siswa store — validasi digits 10 | NISN bukan 10-digit | — | 1. `POST /admin/siswa` | `identity_number=123` | Validasi `digits:10` gagal |
| TC-ADM-SIS-005 | Siswa store — foto optional | Upload foto profil (jpeg/png/jpg/webp max 2048) | — | 1. `POST /admin/siswa` | `photo=avatar.jpg (800KB, jpeg, valid mime jpeg/png/jpg/webp, max 2048KB)` | File `profile-photos/...` di `public`, `profile_photo_path` terisi (validasi `photo optional jpeg/png/jpg/webp max 2048`) |
| TC-ADM-SIS-006 | Siswa update — sukses + ganti foto/hapus | Edit data siswa | Siswa ada | 1. `PUT /admin/siswa/{user}` | `name=Ahmad Updated`, `is_active=1`, `photo=new.jpg` | `success Data siswa berhasil diperbarui.`, row ter-update, file lama terhapus (UserManagementService:84) |
| TC-ADM-SIS-007 | Siswa update — remove_photo | Hapus foto via flag | Siswa punya foto | 1. `PUT /admin/siswa/{user}` | `remove_photo=1` | `profile_photo_path=null`, file fisik terhapus |
| TC-ADM-SIS-008 | Siswa destroy — hapus | Hapus siswa | Siswa ada | 1. `DELETE /admin/siswa/{user}` | — | `success Data siswa berhasil dihapus.`, row terhapus, foto terhapus |
| TC-ADM-SIS-009 | Siswa riwayat individual | Timeline & statistik 30 hari | Siswa ada LessonAttendance | 1. `GET /admin/siswa/{user}/riwayat?start_date=2026-08-10&end_date=2026-09-09` | — | `200` view `admin.siswa.riwayat` dengan `attendances`, `totalHadir/Izin/Sakit/Alpa`, `persenHadir` (AttendanceCorrectionService:155) |

### 4C. Master Guru

| ID Uji | Modul/Sub-Fitur | Skenario Uji | Prasyarat | Langkah Pengujian | Data Uji | Hasil yang Diharapkan |
|--------|-----------------|--------------|-----------|-------------------|----------|----------------------|
| TC-ADM-GUR-001 | Guru index | List + search | Ada guru | 1. `GET /admin/guru?search=Siti` | `search=Siti` | `200` view `admin.guru.index`, search `name/identity/email like` |
| TC-ADM-GUR-002 | Guru store — sukses | Tambah guru | — | 1. `POST /admin/guru` | `identity_number=198512022010011002`, `name=Siti Aisyah`, `birth_date=1985-12-02`, `email=siti@maarif.sch.id` | Success `Data Bapak/Ibu Guru berhasil ditambahkan dengan kata sandi bawaan: 02121985` |
| TC-ADM-GUR-003 | Guru store — validasi unique identity/email | Duplikat | Identity sudah ada | 1. `POST /admin/guru` | `identity_number=198012012010011001` | Validasi `unique:users,identity_number` gagal |
| TC-ADM-GUR-004 | Guru update/destroy/riwayat | CRUD + history | Guru ada | 1. `PUT /admin/guru/{user}` 2. `DELETE ...` 3. `GET /admin/guru/{user}/riwayat` | — | Update/destroy sukses; riwayat view `admin.guru.riwayat` dengan `totalHadir/Terlambat/IzinSakit/persenHadir` (TeacherAttendanceService:454) |

### 4D. Master Kelas, Mapel, Jadwal, Lokasi

| ID Uji | Modul/Sub-Fitur | Skenario Uji | Prasyarat | Langkah Pengujian | Data Uji | Hasil yang Diharapkan |
|--------|-----------------|--------------|-----------|-------------------|----------|----------------------|
| TC-ADM-KLS-001 | Kelas — CRUD | Tambah/edit/hapus kelas | Login admin | 1. `POST /admin/kelas` 2. `PUT /admin/kelas/{classroom}` 3. `DELETE ...` | `name=7A`, `grade_level=7`, `academic_year=2025/2026` | `Kelas berhasil ditambahkan/di­perbarui/dihapus.` Validasi `name max:50` required |
| TC-ADM-MPL-001 | Mapel — CRUD + unique code | Tambah/edit/hapus mapel | — | 1. `POST /admin/mapel` | `code=FIQ`, `name=Fikih` | Sukses; duplikat `code` → validasi `unique:subjects,code` |
| TC-ADM-JDW-001 | Jadwal — store sukses | Buat jadwal mingguan (validasi double overlap) | Kelas 7A, mapel FIQ, guru ada, tidak ada bentrok | 1. `POST /admin/jadwal` | `classroom_id=7A`, `subject_id=FIQ`, `teacher_id=guru1`, `day_of_week=Senin`, `start_time=07:30`, `end_time=08:30` | `Jadwal pelajaran berhasil ditambahkan.` via `ScheduleService::createSchedule` — lolos double overlap cegah : overlap guru DAN overlap kelas/rombel pada hari+jam sama (ScheduleService:58-71) |
| TC-ADM-JDW-002 | Jadwal — validasi after & enum + double overlap guru | end < start / hari invalid / overlap guru | — | 1. `POST /admin/jadwal` | `start_time=08:30`, `end_time=07:30`, `day_of_week=MingguX`; lalu coba `teacher_id` sama bentrok `Senin 07:30-08:30` sudah ada | Validasi `after:start_time` & `in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu` gagal; overlap guru → `422 schedule overlap guru` (ScheduleService:58-71, double overlap cegah : overlap guru DAN overlap kelas/rombel pada hari+jam sama) |
| TC-ADM-JDW-003 | Jadwal — filter & pagination | List filter kelas & hari | Ada jadwal | 1. `GET /admin/jadwal?classroom_id=7A&day=Senin` | — | View `admin.jadwal.index` ter-filter, paginate withQueryString |
| TC-ADM-JDW-004 | Jadwal — update/destroy | Edit/hapus | Jadwal ada | 1. `PUT /admin/jadwal/{schedule}` 2. `DELETE ...` | — | Sukses update/destroy; update tetap validasi double overlap (ScheduleService:58-71) |
| TC-ADM-JDW-005 | Jadwal — tolak overlap kelas | Double overlap cegah kelas/rombel | Kelas 10A `Senin 07:30-09:00` sudah ada oleh guru A | 1. `POST /admin/jadwal` | `classroom_id=10A (sama)`, `subject_id=MAT`, `teacher_id=guruB (beda)`, `day_of_week=Senin`, `start_time=08:00`, `end_time=09:30` tumpang tindih jam sama | `422` validasi gagal `Jadwal bentrok: kelas 10A sudah terisi pada hari/jam tersebut` — overlap kelas/rombel pada hari+jam sama (ScheduleService:58-71) |
| TC-ADM-LOK-001 | Lokasi geofence — index & update | Lihat & ubah koordinat radius | Login admin | 1. `GET /admin/lokasi` 2. `POST /admin/lokasi` | `name=MA Ma'arif Cilageni`, `latitude=-7.0105`, `longitude=107.901`, `radius_meters=80` | View `admin.lokasi.index`; update sukses `Pengaturan batas area madrasah berhasil diperbarui.`, validasi `radius 30-500` (PRD v2.1, AdminController:316) |
| TC-ADM-LOK-002 | Lokasi — validasi radius out of range | Radius 10 / 600 | — | 1. `POST /admin/lokasi` | `radius_meters=10` | Validasi `min:30` gagal |

### 4E. Laporan Eksekutif & Koreksi Presensi + Audit Trail

| ID Uji | Modul/Sub-Fitur | Skenario Uji | Prasyarat | Langkah Pengujian | Data Uji | Hasil yang Diharapkan |
|--------|-----------------|--------------|-----------|-------------------|----------|----------------------|
| TC-ADM-LAP-001 | Laporan — index statistik | Filter tanggal & kelas | Ada LessonAttendance rentang | 1. `GET /admin/laporan?start_date=2026-09-01&end_date=2026-09-09&classroom_id=7A` | — | `200` view `admin.laporan.index` dengan `stats {total_records, total_hadir/izin/sakit/alpa, percentage_hadir, teacher_checkins}` + `rows` per siswa (ReportService:16) |
| TC-ADM-LAP-002 | Laporan — cetak PDF resmi | Preview PDF berkop | Sama | 1. `GET /admin/laporan/cetak-pdf?start_date=...&end_date=...` | — | `200` view `admin.laporan.pdf` dengan `selectedClass`, `school`, kolom tanda tangan Admin & Kepala Madrasah |
| TC-ADM-LAP-003 | Laporan — Ekspor CSV | Unduh file CSV UTF-8 BOM (route legacy) | Sama | 1. `GET /admin/laporan/ekspor-excel?start_date=...` | — | `200` `Content-Type: text/csv; charset=UTF-8`, `Content-Disposition: attachment; filename="Laporan_Presensi_Maarif_...sd_....csv"`, BOM UTF-8, 10 kolom `No,NISN,Nama,Kelas,Hadir...Persentase` — route `/laporan/ekspor-excel` nama legacy tapi output CSV BOM UTF-8 (bukan XLSX, PRD v2.1) (ReportService:102) |
| TC-ADM-PSW-001 | Presensi Siswa — index filter | List harian filter status/search | Ada LessonAttendance hari ini | 1. `GET /admin/presensi-siswa?date=2026-09-09&classroom_id=7A&status=HADIR&search=Ahmad` | — | `200` view `admin.presensi-siswa.index`, paginate 20, `summary {total,hadir,izin,sakit,alpa}` (AttendanceCorrectionService:17) |
| TC-ADM-PSW-002 | Presensi Siswa — koreksi + audit log (sukses) | Admin ubah ALPA→IZIN wajib reason | LessonAttendance ALPA | 1. `PUT /admin/presensi-siswa/{lessonAttendance}` | `status=IZIN`, `reason=Surat dokter menyusul` | `302 success Kehadiran siswa Ahmad berhasil diperbarui dari ALPA menjadi IZIN dan tercatat di riwayat perubahan data.`, row `status=IZIN`, `notes=reason`, `AttendanceAuditLog` ter-create `old_status, new_status, reason, changed_by=admin.id`, DailyAttendance sync (Service:74) |
| TC-ADM-PSW-003 | Presensi Siswa — koreksi validasi reason | Reason <3 char / kosong | — | 1. `PUT ...` | `status=HADIR`, `reason=""` | Validasi 422 `Alasan perubahan status kehadiran wajib diisi... minimal 3 karakter` |
| TC-ADM-PSW-004 | Presensi Siswa — status invalid | Status tidak dalam enum | — | 1. `PUT ...` | `status=HADIRX` | Validasi `in:HADIR,IZIN,SAKIT,ALPA` gagal |
| TC-ADM-PSW-005 | Presensi Siswa — idempoten tidak audit | Ubah ke status & reason sama | Sudah IZIN + reason sama | 1. `PUT ...` | `status=IZIN`, `reason=Sama persis` | Return `updated=false`, tidak buat AuditLog baru (Service:84) |
| TC-ADM-PGR-001 | Presensi Guru — index & summary | List harian + KPI | Ada DailyAttendance | 1. `GET /admin/presensi-guru?date=2026-09-09&status=HADIR&search=Siti` | — | `200` view `admin.presensi-guru.index` dengan `attendances` collection `{teacher, attendance, status, check_in/out_time, pending_schedules_count}`, `summary {totalGuru, hadir, terlambat, izinSakit, belumHadir, checkoutTuntas}` (TeacherAttendanceService:319,392) |
| TC-ADM-PGR-002 | Presensi Guru — koreksi manual sukses | Admin override HADIR/IZIN | Guru tanpa attendance hari ini | 1. `PUT /admin/presensi-guru/{user}` | `date=2026-09-09`, `status=IZIN`, `check_in_time=07:00`, `check_out_time=14:00` | `200 DailyAttendance check_in_status=IZIN` ter-create/update, `success Catatan kehadiran Bapak/Ibu Guru ... berhasil diperbarui.` |
| TC-ADM-PGR-003 | Presensi Guru — auto default jam | Status HADIR tanpa jam | — | 1. `PUT ...` | `status=HADIR`, `check_in_time=""` | Auto `07:00:00`; `TERLAMBAT` auto `07:30:00` jika kosong (Service:429) |
| TC-ADM-AUD-001 | Audit Trail — index | Log perubahan presensi | Ada AttendanceAuditLog | 1. `GET /admin/audit` | — | `200` view `admin.audit.index`, paginate 20 order latest with `lessonAttendance.student, changedBy` |
| TC-ADM-PWD-001 | Reset password 1-klik — siswa/guru | Reset ke DDMMYYYY | User ada birth_date | 1. `POST /admin/users/{user}/reset-password` | — | `302 success Kata sandi untuk Ahmad berhasil diatur ulang ke format tanggal lahir (15052012).`, `User password=Hash::make(default)` (UserManagementService:196, AdminController:105) |
| TC-ADM-DEV-001 | Time Simulator (local/testing) | Jump/add/reset waktu | `APP_ENV=local` | 1. `POST /dev/time-simulator` | `action=jump`, `time=07:30`; `action=add`, `minutes=5`; `action=reset` | `TimeSimulatorService` set/add/reset, `back()` (web.php:133) |

---

## 5. Kiosk Ruang Guru — Display Board Public (`KioskController` + `KioskService`)

| ID Uji | Modul/Sub-Fitur | Skenario Uji | Prasyarat | Langkah Pengujian | Data Uji | Hasil yang Diharapkan |
|--------|-----------------|--------------|-----------|-------------------|----------|----------------------|
| TC-KIOSK-001 | Kiosk index — render QR | Tampilkan QR + info madrasah | Ada SchoolLocation | 1. `GET /kiosk` (tanpa auth) | — | `200` view `kiosk.index` dengan `token` HMAC window kini, `qrSvg` (chillerlan QRCode SVG, scale 6, EccLevel L), `remainingSeconds 1-20`, `school`, `currentDate`, `recentAttendances` 5 terbaru |
| TC-KIOSK-002 | Kiosk token — polling JSON | Refresh token tiap 20s | — | 1. `GET /kiosk/token` polling tiap 5s | — | JSON `token`, `remaining_seconds`, `qr_svg`, `latest_event` (Cache kiosk_latest_event), `server_time`, `server_time_ms` (Service:120) |
| TC-KIOSK-003 | Kiosk poll-event — after check-in | Event muncul di display | Guru baru check-in | 1. `GET /kiosk/poll-event` setelah TC-GUR-CHKIN-001 | — | JSON `latest_event {id uuid, type=check_in, teacher_name, time, status, title=Selamat Datang!, message}` TTL 120s, `recent_attendances[5]` mapped `{name, identity_number, check_in_time (HH:mm), status, is_completed}` |
| TC-KIOSK-004 | Kiosk — HMAC valid window (toleransi 40s) | Token window kini valid — toleransi 40s (current window + previous window 20s) | — | 1. Ambil `token` dari `/kiosk/token` 2. Langsung `POST /guru/scan/check-in` dengan token itu | `qr_token=token_kini` (current window) | `validateToken()=true` (hash_equals current window, dalam toleransi 40s) → check-in sukses |
| TC-KIOSK-005 | Kiosk — HMAC previous window toleransi 40s | Token 15 detik lalu masih valid — toleransi 40s (current + previous window 20s) | — | 1. Tunggu 21s, pakai token lama (window-1) | `qr_token=token_window-1` (previous window, dalam 40s) | `validateToken()=true` via previousWindow branch (KioskService:58) → masih sukses (toleransi 40s) |
| TC-KIOSK-006 | Kiosk — HMAC expired window-2 ditolak (toleransi 40s lewat) | Token 45 detik lalu — di luar toleransi 40s (current + previous window 20s) | — | 1. Pakai token window-2 (≈40-60s lalu) | `qr_token=expired` (window-2) | `validateToken()=false` → `INVALID_QR_TOKEN` (hanya current + previous window diterima) |
| TC-KIOSK-007 | Kiosk — public tanpa auth | Tidak redirect login | Guest | 1. `GET /kiosk` 2. `GET /kiosk/token` 3. `GET /kiosk/poll-event` | — | Semua `200` (route prefix `kiosk` tanpa middleware auth, web.php:43) |

---

## 6. Edge Cases — PRD §6 & NFR

| ID Uji | Modul/Sub-Fitur | Skenario Uji | Prasyarat | Langkah Pengujian | Data Uji | Hasil yang Diharapkan |
|--------|-----------------|--------------|-----------|-------------------|----------|----------------------|
| TC-EDGE-001 | Koneksi putus saat verify PIN | Retry 3×15s di browser | Session ACTIVE, koneksi offline | 1. `POST /siswa/verify-pin` dengan network throttling offline (mock) 2. Browser retry 3× | `pin=8492` | UI banner `Koneksi terputus. Mencoba mengirim kembali...` + tombol kirim manual; server akhirnya terima saat online (PRD §6 baris 1) |
| TC-EDGE-002 | GPS drift akurasi rendah | Jarak dalam radius+25 toleransi | Lat/lng drift 90m (radius 75+25=100) | 1. `POST /siswa/verify-pin` | `distance=90m` | `success=true` (toleransi soft), opsi `VERIFIED_GPS_MARGINAL` jika diimplementasi; UI notif `Akurasi GPS rendah...` |
| TC-EDGE-003 | Guru buka sesi sebelum check-in | Check-in Gating | Belum check-in | Lihat TC-GUR-OPEN-002 | — | Tombol buka sesi disabled backend + modal `Akses Ditolak: Anda belum melakukan presensi kedatangan di Kiosk...` |
| TC-EDGE-004 | Guru check-out sebelum tuntas mengajar | Teaching Completion Lock | Pending 1 jadwal | Lihat TC-GUR-CHKOUT-002 | — | Pop-up daftar kelas pending `Presensi Pulang Terkunci: Masih ada jadwal kelas yang belum tuntas...` |
| TC-EDGE-005 | PIN expired tepat saat tekan Kirim | Grace 5 detik | `now = expires_at +3s` vs `+6s` | Lihat TC-SIS-PIN-005/004 | — | +3s sukses, +6s `SESSION_EXPIRED` redirect tunggu rekonsiliasi |
| TC-EDGE-006 | Akses luar madrasah | Geofence blok | Jarak >100m | Lihat TC-SIS-GEO-002 / TC-GUR-CHKIN-004 | — | Kartu sesi hidden + ilustrasi + `Anda terdeteksi di luar lingkungan madrasah` |
| TC-EDGE-007 | Token QR expired saat scan | Toleransi 40s (current + previous window 20s) | Token window-1 (dalam 40s) vs window-2 (>40s) | Lihat TC-KIOSK-005/006 | — | Dalam 40s masih sukses; >40s toast `Token QR kedaluwarsa. Arahkan kamera kembali ke kode QR baru...` |
| TC-EDGE-008 | Tablet kiosk freeze/mati listrik | ServiceWorker auto-reload | Kiosk offline | 1. Matikan tablet 2. Cek `GET /kiosk` setelah restart 3. Cek admin dashboard notifikasi offline | — | Kiosk reload otomatis, Admin lihat status offline di dashboard, fallback PIN darurat jika ada |

---

## 7. RBAC Negative — Matriks Hak Akses PRD §4.2

| ID Uji | Modul/Sub-Fitur | Skenario Uji | Prasyarat | Langkah Pengujian | Data Uji | Hasil yang Diharapkan |
|--------|-----------------|--------------|-----------|-------------------|----------|----------------------|
| TC-RBAC-001 | Siswa → Guru scan | Siswa coba scan QR kiosk | Login siswa | 1. `GET /guru/scan` 2. `POST /guru/scan/check-in` | — | `403` / redirect `abort(403)` (middleware `role:guru`) |
| TC-RBAC-002 | Siswa → Admin | Siswa coba akses admin | Login siswa | 1. `GET /admin/siswa` atau `POST /admin/siswa` | — | `403` (middleware `role:admin`) |
| TC-RBAC-003 | Siswa → buka sesi kelas | Siswa coba openSession | Login siswa | 1. `POST /guru/sessions/{schedule}/open` | — | `403` |
| TC-RBAC-004 | Siswa → koreksi & audit | Siswa coba presensi-siswa | Login siswa | 1. `GET /admin/presensi-siswa` 2. `GET /admin/audit` | — | `403` |
| TC-RBAC-005 | Guru → Admin master | Guru coba CRUD siswa | Login guru | 1. `GET /admin/siswa` 2. `POST /admin/lokasi` | — | `403` |
| TC-RBAC-006 | Guru → Siswa verifyPin | Guru coba verify PIN | Login guru | 1. `POST /siswa/verify-pin` | `pin=8492` | `403` (role:siswa) |
| TC-RBAC-007 | Guru → sesi milik guru lain | Horizontal escalation | Guru B akses session guru A | 1. `GET /guru/sessions/{session milik A}` 2. `GET .../reconcile` | — | `403` (Controller:142 `abort(403)`) |
| TC-RBAC-008 | Guru → presensi admin & audit | Guru coba presensi-siswa/audit | Login guru | 1. `GET /admin/presensi-siswa` | — | `403` |
| TC-RBAC-009 | Admin → Siswa/Guru PWA | Admin coba verifyPin/scan | Login admin | 1. `POST /siswa/verify-pin` 2. `GET /guru/scan` | — | `403` (role mismatch) |
| TC-RBAC-010 | Guest → semua protected | Tanpa login | Guest | 1. `GET /siswa` 2. `GET /guru` 3. `GET /admin` | — | `302` redirect `login` |
| TC-RBAC-011 | Locked session — guru tidak bisa edit, admin tetap bisa | Setelah LOCKED guru blok, admin override | Session LOCKED | 1. Guru `POST .../reconcile` → blok 2. Admin `PUT /admin/presensi-siswa/{attendance}` | — | Guru error `Data kehadiran kelas ini telah ditutup...` ; Admin sukses dengan `reason` + audit log |

---

## Appendix A — Dev & QA Tooling (Non-MVP, Tidak Diuji di TC Utama)

| ID Uji | Modul | Skenario | Hasil |
|--------|-------|----------|-------|
| TC-DEV-001 | Time Simulator | `POST /dev/time-simulator` local/testing only, middleware `?simulate_time`, widget presets 07:00/07:30/+3m/14:00, file `framework/simulated_time.json` via `TimeSimulatorService`, `SimulateTime` middleware `Carbon::setTestNow()` | dev-only, excluded dari MVP — tidak dibawa ke production |

> **Catatan:** Fitur Foto Profil adalah MVP (wajib diuji via `TC-COM-AUTH-014/015` & `TC-ADM-SIS-005/006`); **Time Simulator tidak masuk MVP** — hanya alat bantu QA untuk simulasi late 07:15, PIN expiry, token 40s, time gating tanpa menunggu jam riil.

---

## Cara Pakai Dokumen Ini

1. **Manual QA:** Eksekusi baris per baris di browser + `php artisan test` (Pest). Untuk time-sensitive TC, gunakan `POST /dev/time-simulator` (`action=jump|add|custom|reset`) di env `local/testing` atau `Carbon::setTestNow()` di test.
2. **Otomatisasi:** Tiap TC dapat dipetakan 1:1 ke Pest feature test — contoh `TC-SIS-PIN-001` → `tests/Feature/SiswaVerifyPinTest.php::test_siswa_can_verify_pin_inside_geofence_and_creates_daily_attendance`.
3. **Geofence Test:** Override `SchoolLocation` di seeder ke koordinat test lab; gunakan `calculateDistance` untuk assert `distance_meters`.
4. **Kiosk HMAC Test:** Freeze `Carbon::now()` untuk deterministik window, assert `validateToken` untuk window-0, window-1 true, window-2 false.

---

*Total: ~96 baris uji utama + 1 Dev Tooling (16 Common + 14 Siswa Geo/PIN + 14 Guru Kiosk/Sesi + 12 Rekonsiliasi/Check-out + 24 Admin Master/Laporan incl. TC-ADM-JDW-005 + 7 Kiosk + 8 Edge + 11 RBAC) + 1 Appendix A (TC-DEV-001). Selaras PRD v2.1 (2026-09-09) — CSV BOM, radius 30-500+25m, late 07:15, token 40s, double overlap, time gating STRICT, Blade Custom Admin Panel. Dokumen ini adalah living spec — sinkronkan dengan perubahan PRD & Service saat implementasi.*
