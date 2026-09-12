# Implementation Plan: Revamp UI Mobile Dashboard Guru

## Overview
Revamp antarmuka Dashboard Guru (`resources/views/guru/dashboard.blade.php` dan komponen terkait) agar mobile-first, bersih dari redundansi visual, menghapus konflik navigasi/trigger, serta merapikan hierarki tipografi dan ruang sesuai hasil kritik UX.

## Architecture & Design Decisions
1. **Unifikasi Jadwal & Riwayat Kelas (Single Source of Truth Card Flow)**
   - Gabungkan blok *Jadwal Mengajar Hari Ini* dan *Riwayat Kelas Hari Ini* menjadi satu timeline kartu kelas terpadu.
   - Setiap kartu merepresentasikan 1 sesi ajar dengan status dinamis: *Belum Dibuka*, *Sesi Aktif (PIN)*, *Perlu Konfirmasi Rekap*, atau *Tuntas (Rekap Disimpan)*.
2. **Kompak Metrik Bar (Horizontal Micro-Pills / Inline Badges)**
   - Ganti grid kartu stat 2x2 / 4-card yang memakan ruang layar vertikal dengan ringkasan horizontal ringkas (inline pill metrics / compact status bar).
3. **Pembersihan Header & GPS Status Integration**
   - Rampingkan header profil guru: nama, NIP, status GPS live menyatu rapi tanpa tumpukan teks rapat.
   - Singkirkan teks narasi bertele-tele di warning/status gating; gunakan microcopy aksi langsung.
4. **Pemberesan Floating Pill & Eliminasi Duplikasi CTA Scan**
   - Pindahkan info dev simulator / live status agar tidak melayang menabrak tab bar bawah (`bottom-nav`).
   - Jadikan tombol utama bottom-nav sebagai pusat akses scan QR madrasah, atau sesuaikan tombol pada status harian agar kontekstual tanpa bersaing visual dengan bottom floating button.
5. **Standardisasi Ikonografi & Komponen UI**
   - Hapus karakter checklist unicode (`☐`, `☑`) dan ganti dengan ikon Lucide (`check-circle-2`, `clock`, `alert-circle`, `play-circle`) dan badge status bersistem warna konsisten.

## Task List

### Phase 1: Information Architecture & Structural Cleanup
- [ ] Task 1: Desain ulang Header & Compact Status Bar Guru
- [ ] Task 2: Ganti Stat Card 2x2 dengan Micro-Metric Bar Horizontal

### Checkpoint 1: Header & Metric Section
- [ ] Header tampak ramping di mobile (<390px) & desktop
- [ ] GPS badge terintegrasi alami tanpa tabrakan layout

### Phase 2: Card Flow Unification & Micro-Interactions
- [ ] Task 3: Gabungkan Kartu Jadwal dan Riwayat Sesi Kelas
- [ ] Task 4: Perbaiki Ikonografi & Aksi State (Hilangkan unicode checklist, integrasikan countdown & direct action)

### Checkpoint 2: Class Session Timeline
- [ ] Tidak ada duplikasi baris kelas antara jadwal dan riwayat
- [ ] Transisi status kelas (buka presensi -> sesi live -> rekap -> tuntas) bekerja mulus

### Phase 3: Conflict Resolution (Floating Elements & CTA Scan)
- [ ] Task 5: Relokasi/Repositioning Floating Pill Dev Simulator di Mobile Viewport
- [ ] Task 6: Harmonisasi CTA Presensi Masuk/Pulang dengan Bottom Navigation Bar

### Checkpoint 3: Final Mobile Usability & Polish
- [ ] Tidak ada elemen mengambang yang menutupi navigasi bawah
- [ ] Sintaks Blade valid dan lulus verifikasi `pest` / `pint`

## Risks and Mitigations
| Risk | Impact | Mitigation |
|------|--------|------------|
| Script geofence realtime / timer clock rusak saat restrukturisasi DOM | High | Pertahankan ID elemen DOM kunci (`geofenceBadge`, `liveClockTicker`, form open session) atau sesuaikan handler JS dengan presisi. |
| Tombol open session terganggu saat GPS gating | High | Pastikan class `.sessionSubmitBtn` dan interaksi `disabled` tetap sinkron dengan script lokasi. |
| Tampilan desktop regresi saat mobile dioptimalkan | Medium | Gunakan teknik Tailwind responsive standard (`flex-col md:flex-row`, `grid-cols-1 md:grid-cols-2`). |

## Open Questions
- Tidak ada blocker fungsional; seluruh flow data backend sudah tersedia via `TeacherAttendanceService`.
