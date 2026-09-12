# Todo List: Revamp Dashboard Guru (Mobile-First UX)

## Phase 1: Header & Metric Restructuring

### Task 1: Desain Ulang Header Profil & Status Lokasi
- [x] Profil mobile menampilkan Nama, NIP/Role, dan status GPS dalam 1 flow vertikal/horizontal ringkas.
- [x] Jam & tanggal berada di posisi editorial yang bersih.
- [x] Warning gating dibuat ringkas tanpa narasi panjang.

### Task 2: Ganti Stat Card 2x2 Menjadi Horizontal Micro-Pill Bar
- [x] Tinggi vertikal section ringkasan terpangkas 70% dibanding grid card sebelumnya.
- [x] Angka dan label tetap jelas terbaca dengan kontras warna status.

## Checkpoint 1: Header & Metrics Done
- [x] Header & metric ringkas, bersih, dan hemat ruang viewport mobile.
- [x] GPS live pinging tetap jalan.

---

## Phase 2: Flow Kelas Terpadu & Standar UI

### Task 3: Gabungkan Bagian Jadwal & Riwayat Menjadi Satu Timeline Kelas
- [x] Tidak ada duplikasi baris kelas antara jadwal dan riwayat.
- [x] Setiap kartu menyajikan status eksplisit dinamis (*Belum Dimulai*, *Sesi Terbuka*, *Perlu Rekap*, *Selesai Disimpan*).
- [x] Menghilangkan redundansi teks keterangan panjang di dalam kartu.

### Task 4: Standarisasi Ikonografi & Micro-Interactions
- [x] Seluruh simbol unicode mentah (`☐`, `☑`) diganti icon Lucide (`check-circle-2`, `radio`, `clock`, `alert-circle`).
- [x] Indikator status kelas memakai warna semantik konsisten.

## Checkpoint 2: Unified Class Timeline Done
- [x] Tampilan kelas rapi dalam satu flow berurutan.
- [x] Seluruh aksi tombol berfungsi (Buka sesi, monitoring, reconcile).

---

## Phase 3: Navigasi, Floating Pill & Gating Scan

### Task 5: Rapikan Posisi Floating Dev Controls Pill
- [x] Floating dev pill diposisikan di sudut aman mobile header (`top: 0.75rem; right: 0.75rem`), tidak menutupi atau bertabrakan dengan tombol tengah bottom bar.

### Task 6: Harmonisasi CTA Presensi Masuk & Pulang
- [x] Teks penjelasan status presensi harian diringkas padat.
- [x] Tombol aksi terintegrasi jelas dan tidak berebut fokus dengan bottom bar.

## Checkpoint 3: Usability & Code Quality Verification
- [x] `vendor/bin/pint --format agent` lolos.
- [x] Seluruh 134 test suite lolos (28 tests khusus fitur Guru).