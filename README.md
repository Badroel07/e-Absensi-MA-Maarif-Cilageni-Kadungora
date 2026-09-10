# Sistem Absensi Kehadiran MA Ma'arif Cilageni Kadungora

Sistem Absensi Kehadiran Siswa & Dewan Guru MA Ma'arif Cilageni Kadungora berbasis arsitektur **Zero-Queue Classroom Attendance**, **Dynamic Kiosk QR (HMAC-SHA256)**, dan **Geofencing GPS Server-side** sesuai spesifikasi PRD v2.0.0.

---

## 🌟 Fitur Utama & Core Engines

1. **Engine 1: Kiosk Dynamic QR & Teacher Attendance Engine**
   - Layar Kiosk tablet Ruang Guru/TU (`/kiosk`) dengan Dynamic QR HMAC-SHA256 yang berganti setiap 20 detik secara otomatis.
   - Pindai QR kedatangan guru via kamera HP (`/guru/scan`).
   - **Check-in Gating**: Guru wajib absen kedatangan di Kiosk sebelum dapat mengaktifkan sesi kelas.
   - **Teaching Completion Lock**: Presensi pulang di Kiosk menolak check-out jika masih terdapat jadwal mengajar hari ini yang belum direkonsiliasi dan dikunci (*Completed & Locked*).

2. **Engine 2: Geofence-Gated Discovery & PIN 4-Digit Classroom Engine**
   - Siswa memasukkan PIN 4-digit langsung dari bangku kelas via HP (`/siswa`).
   - Server-side Haversine Formula: Sesi dan form input PIN hanya terbuka jika siswa berada di dalam radius resmi madrasah (50–100m). Di luar radius, sesi terkunci.
   - Brute-force rate limiting: Batas maksimal 3 kali salah PIN per sesi sebelum akun dibekukan 5 menit.
   - Getaran responsif (*Haptic Feedback API*) ganda saat verifikasi sukses.
   - **Otomatisasi Kehadiran Harian**: Kehadiran siswa pada sesi mapel jam pertama otomatis diakui sebagai kehadiran harian madrasah.

3. **Engine 3: Rekonsiliasi Ketidakhadiran & Penguncian Sesi Pasca-Kelas**
   - Setelah sesi PIN berakhir atau guru menekan selesaikan sesi, muncul daftar siswa yang belum hadir.
   - Tombol pill satu sentuhan: 🟡 Izin, 🔵 Sakit, 🔴 Alpa.
   - Guru mengunci sesi menjadi status `LOCKED` permanen, memenuhi syarat Teaching Completion Lock.

4. **Engine 4: Admin Back-Office & Master Data Management**
   - CRUD Master Siswa (NISN, Nama, Tanggal Lahir, Kelas, No HP).
   - CRUD Master Guru (NIP, Nama, Tanggal Lahir, Email, No HP).
   - CRUD Master Kelas / Rombel (7A, 7B, 8A, 8B, 9A, 9B).
   - CRUD Master Mata Pelajaran (Fikih, Al-Qur'an Hadits, Akidah Akhlak, MTK, dll).
   - CRUD Master Jadwal Mingguan dengan validasi **Sanitasi Bentrok Jadwal (*Schedule Overlap Prevention*)**.
   - **1-Klik Reset Password**: Memulihkan kata sandi siswa/guru langsung ke default tanggal lahir (`DDMMYYYY`).
   - Konfigurasi titik koordinat geofence latitude, longitude, dan radius toleransi.

5. **Engine 5: Monitoring Real-Time & Pusat Dokumen Laporan Eksekutif**
   - Dashboard monitoring statistik live (Total Siswa, Hadir, Izin, Sakit, Alpa, Guru Hadir, Sesi Aktif).
   - Dokumen Cetak Resmi PDF ber-Kop Madrasah MA Ma'arif Cilageni Kadungora lengkap dengan kolom tanda tangan Admin dan pengesahan bertanda tangan Kepala Madrasah.
   - Ekspor data laporan ke spreadsheet Excel / CSV.
   - Audit trail log perubahan manual status kehadiran.

6. **Desain Mobile-First & PWA Native-Like**
   - Identitas warna resmi Hijau Ma'arif NU (`#15803D`) dan Deep Forest (`#166534`).
   - Ergonomi jempol (*fixed bottom navigation bar* 64px, touch target &ge; 48px, keypad numerik PIN 56px).
   - Service worker offline shell & manifest PWA.

---

## 🔑 Kredensial Default Login untuk Uji Coba

| Peran              | Identitas Login (NISN / NIP / Email)              | Kata Sandi Default    | Akses Dashboard |
| :----------------- | :------------------------------------------------ | :-------------------- | :-------------- |
| **Admin**          | `admin@maarif.sch.id` (atau `198501012010011001`) | `password`            | `/admin`        |
| **Guru 1**         | `197505122000031002`                              | `12051975` (DDMMYYYY) | `/guru`         |
| **Guru 2**         | `198208152005012003`                              | `15081982` (DDMMYYYY) | `/guru`         |
| **Siswa 1 (7A)**   | `0091234501`                                      | `10052011` (DDMMYYYY) | `/siswa`        |
| **Siswa 2 (7A)**   | `0091234502`                                      | `15032011` (DDMMYYYY) | `/siswa`        |
| **Siswa 3 (8A)**   | `0081234501`                                      | `12042010` (DDMMYYYY) | `/siswa`        |
| **Terminal Kiosk** | Layar Publik / Tablet Ruang Guru                  | *Tanpa Login*         | `/kiosk`        |

---

## 🗄️ Basis Data (Database Engine)

Sistem menggunakan **MariaDB (v12.3.2) / MySQL 8.0+** lokal via Laragon:
- **Database Utama**: `absensi_maarif` (port `3306`, user `root`, password empty)
- **Database Testing (PEST)**: `absensi_maarif_test`

---

## 🚀 Cara Menjalankan Aplikasi

### 1. Menjalankan Web Server
```bash
php artisan serve
```
Akses aplikasi melalui peramban: `http://localhost:8000`

### 2. Menjalankan Database Seeder (Reset & Seed Data di MariaDB/MySQL)
```bash
php artisan migrate:fresh --seed
```

### 3. Menjalankan Pengujian Otomatis (PEST Testing di MariaDB/MySQL)
```bash
./vendor/bin/pest
```
Seluruh 36 skenario pengujian Pest Feature & Unit menguji seluruh Core Engines langsung terhadap basis data MariaDB/MySQL dan lulus 100% (36 passed, 131 assertions).

---

## 🌐 Lingkungan Produksi & CI/CD Deployment

- **Production Domain**: `https://maarif.chkl.my.id`
- **Server Stack**: CloudPanel, Nginx, PHP 8.4, MariaDB
- **Automated Pipeline**: GitHub Actions (`.github/workflows/deploy.yml`) otomatis melakukan build frontend dan deployment ke VPS setiap kali terdapat commit push ke branch `main`.

