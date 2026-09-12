# Todo List: Penyeragaman UI Halaman Siswa dengan UI Halaman Guru

## Phase 1: Dashboard Siswa (`resources/views/siswa/dashboard.blade.php`)

### Task 1: Harmonisasi Profil, GPS, Alert & Accordion Panduan Siswa
- [x] Muat font stack `Lexend` & `DM Sans`, terapkan class `.font-sans-card`.
- [x] Profil siswa kompak `rounded-2xl border border-slate-200/70 p-4 shadow-sm` dengan avatar 12x12, kelas + NISN, tombol GPS inline (`#geofenceBadge`), dan ticker jam WIB.
- [x] Rampingkan alert `#outsideWarning` dan `#gpsSearchingNotice`.
- [x] Accordion petunjuk presensi rapi dengan icon `info` (`data-lucide="info"`).

### Task 2: Re-layout Metrik 2x2 & Desain Ulang Modal/Card PIN Presensi
- [x] Metrik ringkasan kehadiran diubah menjadi `grid grid-cols-2 gap-3` (`h-24` micro-cards).
- [x] Desain ulang `#sessionCard` aktif dan keypad sentuh 3x4 menjadi modern dan ergonomis.
- [x] Pertahankan seluruh DOM ID input PIN dan verifikasi.

### Task 3: Unifikasi Jadwal & Riwayat Hari Ini Menjadi Single Class Timeline
- [x] Gabungkan "Jadwal Pelajaran Hari Ini" dan "Riwayat Kehadiran Hari Ini" menjadi satu timeline "Jadwal & Presensi Kelas Hari Ini".
- [x] Setiap kartu menampilkan badge `#01`, mapel, guru pengampu, waktu plain mono, dan status dinamis.
- [x] Highlight emerald pada sesi aktif saat jam pelajaran berlangsung.

## Checkpoint 1: Dashboard Siswa Selesai
- [x] Profil, GPS badge, dan jam WIB kompak dan selaras dengan Guru.
- [x] Ringkasan 2x2 rapi dan hemat ruang layar.
- [x] Timeline kelas satu pintu berjalan tanpa duplikasi.
- [x] Input PIN dan verifikasi kehadiran berfungsi normal (`rtk vendor/bin/pest --filter=StudentAttendanceTest`).

---

## Phase 2: Jadwal Mingguan Siswa (`resources/views/siswa/schedule.blade.php`)

### Task 4: Tambahkan Quick Day Selector Chips & Harmonisasi Kartu Jadwal
- [x] Tambahkan bilah horizontal chip filter hari (`Semua`, `Senin`, `Selasa`, dst.).
- [x] Selaraskan kartu jadwal dengan `guru/schedule.blade.php` (badge `#01`, icon `door-closed`, waktu plain, highlight emerald aktif).

---

## Phase 3: Riwayat Presensi Siswa (`resources/views/siswa/history.blade.php`)

### Task 5: Redesain Hero Banner, Quick Stats 3 Kolom & Filter Bar
- [x] Hero Progress Banner emerald gradien (`bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-950`).
- [x] 3 kartu ringkasan cepat (`grid grid-cols-3 gap-3`).
- [x] Bilah chip filter status horizontal dan input tanggal native dengan format `d/m/Y`.

### Task 6: Unifikasi List Rekam Jejak Menjadi Kartu Sesi Responsif
- [x] Ganti tabel desktop kaku & mobile card terpisah dengan kartu rekam jejak presensi responsif terpadu.
- [x] Status kehadiran anti-pill pure typography (`check-circle-2`, `file-text`, `x-circle`).
- [x] Empty state bersih dan paginasi rapi.

## Checkpoint 2: Jadwal & Riwayat Siswa Selesai
- [x] Bilah chip filter hari pada Jadwal berfungsi mulus.
- [x] Kartu riwayat presensi memiliki tampilan seragam antara mobile & desktop.
- [x] Filter status, pencarian, dan rentang tanggal riwayat berfungsi akurat.

---

## Phase 4: Verifikasi Kualitas & Regresi

### Task 7: Linting, Formatting Pint & Verifikasi Komprehensif
- [x] Jalankan `vendor/bin/pint --format agent`.
- [x] Jalankan seluruh test suite `rtk vendor/bin/pest`.
- [x] Pastikan 100% test lulus hijau.

## Checkpoint 3: Verifikasi Final
- [x] `vendor/bin/pint --format agent` bersih tanpa error.
- [x] Seluruh test suite (134+ tests) lulus hijau.