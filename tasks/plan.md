# Implementation Plan: Penyeragaman UI Halaman Siswa dengan UI Halaman Guru

## Overview
Harmonisasi antarmuka portal siswa (`resources/views/siswa/dashboard.blade.php`, `resources/views/siswa/schedule.blade.php`, dan `resources/views/siswa/history.blade.php`) agar mengadopsi standar visual, ergonomi mobile-first, dan hierarki informasi yang sudah diterapkan pada portal guru (`resources/views/guru/*`) serta panduan `DESIGN.md`.

## Architecture Decisions
1. **Tipografi & Token Font**: Gunakan `DM Sans` + `Lexend`, `.heading-font`, `.font-sans-card`, dan `.mono-font`.
2. **Kartu Profil & Status GPS Kompak**: Mengganti unboxed hero lebar dengan kartu profil 12x12 avatar, tombol GPS inline, dan tanggal/jam ticker WIB.
3. **Metrik 2x2 Kompak**: Mengubah 4-kolom lebar menjadi 2x2 micro-card `h-24` dengan bare icon.
4. **Unifikasi Jadwal & Riwayat di Dashboard**: Menyatukan jadwal dan kehadiran hari ini menjadi single timeline card flow ("Jadwal & Presensi Kelas Hari Ini").
5. **Quick Day Selector pada Jadwal Mingguan**: Menambahkan bilah chip hari horizontal (`Semua`, `Senin`, `Selasa`, dst.).
6. **Kartu Riwayat Presensi Terpadu**: Mengganti tabel desktop vs mobile card dengan kartu kronologis terpadu, hero progress banner emerald gradien, dan quick stats 3 kolom.

## Task List

### Phase 1: Dashboard Siswa (`siswa/dashboard.blade.php`)
- [ ] Task 1: Harmonisasi Profil, GPS, Alert & Accordion Panduan Siswa
- [ ] Task 2: Re-layout Metrik 2x2 & Desain Ulang Modal/Card PIN Presensi
- [ ] Task 3: Unifikasi Jadwal & Riwayat Hari Ini Menjadi Single Class Timeline

### Checkpoint 1: Dashboard Siswa Selesai
- [ ] Profil, GPS badge, dan jam WIB kompak dan selaras dengan Guru
- [ ] Ringkasan 2x2 rapi dan hemat ruang layar
- [ ] Timeline kelas satu pintu berjalan
- [ ] Input PIN dan verifikasi kehadiran berfungsi normal

### Phase 2: Jadwal Mingguan Siswa (`siswa/schedule.blade.php`)
- [ ] Task 4: Tambahkan Quick Day Selector Chips & Harmonisasi Kartu Jadwal

### Phase 3: Riwayat Presensi Siswa (`siswa/history.blade.php`)
- [ ] Task 5: Redesain Hero Banner, Quick Stats 3 Kolom & Filter Bar
- [ ] Task 6: Unifikasi List Rekam Jejak Menjadi Kartu Sesi Responsif

### Checkpoint 2: Jadwal & Riwayat Siswa Selesai
- [ ] Bilah chip filter hari pada Jadwal berfungsi mulus
- [ ] Kartu riwayat presensi memiliki tampilan seragam antara mobile & desktop
- [ ] Filter status, pencarian, dan rentang tanggal riwayat berfungsi akurat

### Phase 4: Verifikasi Kualitas, Formatting & Regresi
- [ ] Task 7: Linting, Formatting Pint & Verifikasi Komprehensif

### Checkpoint 3: Verifikasi Final
- [ ] `vendor/bin/pint --format agent` bersih
- [ ] 100% Pest test suite lulus hijau

## Risks and Mitigations
| Risk | Impact | Mitigation |
|---|---|---|
| Hilangnya DOM ID kunci yang dipakai script JS siswa | High | Pertahankan seluruh ID dan class fungsional (`#geofenceBadge`, `#sessionCard`, `#digit0`-`#digit3`, `#outsideWarning`, dsb.). |
| Test suite gagal karena selector Blade | Medium | Jalankan `rtk vendor/bin/pest --filter=Student` di tiap tahapan. |
| Kerusakan layout responsif pada layar kecil | Medium | Gunakan token Tailwind responsive standar (`min-w-0`, `truncate`, `flex-wrap`). |

## Open Questions
- Tidak ada pertanyaan penghambat. Seluruh komponen dan data backend telah tersedia.
