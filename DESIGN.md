# Design System & Typography Guidelines — Sistem Absensi MA Ma'arif

> **Single Source of Truth** untuk arsitektur visual, typografi, typescale, weightscale, sistem warna, dan standar komponen pada platform Sistem Absensi Kehadiran Siswa & Guru MA Ma'arif Cilageni Kadungora.

---

## 1. Visi & Filosofi Desain

Sistem ini memadukan estetika **Madrasah Modern, Bersih, dan Terstruktur** dengan pendekatan **PWA Mobile-First & Desktop Dashboard**:
- **Density**: *Daily App Balanced* (tingkat kepadatan seimbang, mudah dibaca cepat saat presensi fisik di kelas maupun di depan terminal kiosk).
- **Nuansa**: Islami, terpercaya, berwibawa, dan mutakhir dengan palet hijau khas Ma'arif (*Ma'arif Emerald & Forest Green*), netral Slate berkelas, serta aksen status fungsional.
- **Hierarki Jelas**: Tipografi tegas memandu mata pengguna dalam 3 detik: Siapa yang login &rarr; Status GPS & Sesi Kelas &rarr; Jadwal & Verifikasi PIN.

---

## 2. Arsitektur Tipografi (Font Stacks)

Sistem menggunakan **single-font system** berbasis **`Inter`** murni di seluruh aplikasi, dengan fitur OpenType Tabular Numbers (`tnum`) untuk penyajian angka dan data tabular:

| Kategori Elemen | Family | Loaded Weights | Utility Class | Peran & Fitur Tipografi |
|---|---|---|---|---|
| **UI, Body & Heading** | `Inter`, system-ui, sans-serif | 400, 500, 600, 700, 800, 900 | `font-sans` (Default), `.heading-font` / `font-heading` | Teks isi, deskripsi, form input, tabel, navigasi, judul halaman, greeting nama siswa/guru, nama mata pelajaran, kartu hero, dan judul modal. Heading menggunakan `Inter` dengan weight 700–900 + `tracking-tight` untuk hierarki tegas. |
| **Data, Angka & Jam (Tabular)** | `Inter`, system-ui, sans-serif | 400, 500, 600, 700, 800 | `.mono-font` / `font-mono` | Jam digital aktif, PIN presensi 4-digit, NISN/NIP, countdown timer, koordinat GPS, dan tag jam pelajaran. Menggunakan `font-feature-settings: 'tnum', 'zero'` agar angka berlebar tetap (*monospaced tabular numbers*) tanpa perlu font monospace terpisah. |

> **Aturan keras**: Hanya 1 font family yang diizinkan di seluruh aplikasi — **`Inter`**. Font eksternal lain (`JetBrains Mono`, `Plus Jakarta Sans`, serif) dilarang.

### Konfigurasi Google Fonts (Single Family):
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
```
Bunny Fonts (vite.config.js) juga hanya memuat family `Inter` via `laravel-vite-plugin/fonts`.

---

## 3. Typescale (Skala Ukuran Font & Spasi Baris)

Skala tipografi terstandarisasi untuk menjamin konsistensi ritme vertikal dan hierarki informasi:

| Token Tailwind | Ukuran (px / rem) | Line Height | Letter Spacing | Peran / Elemen Sasaran |
|---|---|---|---|---|
| `text-3xs` | `9px` / `0.5625rem` | `12px` (`0.75rem`) | `+0.05em` | Micro-pill badges (misal: badge "CILAGENI" pada logo navbar). |
| `text-2xs` | `10px` / `0.625rem` | `14px` (`0.875rem`) | `+0.025em` | Sub-label huruf kapital kecil, WIB time indicators, counter badges. |
| `text-xs+` | `11px` / `0.6875rem` | `16px` (`1rem`) | `+0.015em` | Secondary metadata (TA 2026/2027, nama guru pengampu, status deskripsi). |
| `text-xs` | `12px` / `0.75rem` | `16px` (`1rem`) | `normal` | Caption standar, tombol kecil, deskripsi status GPS, info petunjuk. |
| `text-sm` | `14px` / `0.875rem` | `20px` (`1.25rem`) | `normal` | Label form, item jadwal mata pelajaran, tombol aksi primer, teks menu sidebar. |
| `text-base` | `16px` / `1rem` | `24px` (`1.5rem`) | `normal` | Paragraf reguler, teks instruksi modal, konten pengumuman. |
| `text-lg` | `18px` / `1.125rem` | `28px` (`1.75rem`) | `-0.01em` | Judul kartu sesi kelas, sub-judul modul, judul alert sukses verifikasi. |
| `text-xl` | `20px` / `1.25rem` | `28px` (`1.75rem`) | `-0.02em` | Nama siswa/guru pada kartu profil mobile, judul modal popup utama. |
| `text-2xl` | `24px` / `1.5rem` | `32px` (`2rem`) | `-0.025em` | Judul utama halaman dashboard (`h1`), angka statistik ringkasan kehadiran. |
| `text-3xl` | `30px` / `1.875rem` | `36px` (`2.25rem`) | `-0.03em` | Jam digital aktif desktop (`liveClockDisplay`), digit keypad PIN input. |
| `text-4xl` | `36px` / `2.25rem` | `40px` (`2.5rem`) | `-0.035em` | Countdown timer sesi aktif, layar Kiosk display QR code terminal. |

---

## 4. Weightscale (Skala Ketebalan Font)

Seluruh bobot font yang digunakan **wajib terdaftar dan di-load** untuk mencegah *faux-bold* (peniruan ketebalan artifisial oleh browser yang membuat teks buram). Sistem mengadopsi prinsip ketebalan modern berimbang:

| Utility Tailwind | Numeric Weight | Font Family Terkait | Kasus Penggunaan Ideal |
|---|---|---|---|
| `font-normal` | **400** | `Inter` | Teks paragraf panjang, catatan bantuan, deskripsi umum, cell metadata tabel. |
| `font-medium` | **500** | `Inter` | Label form input, navigasi sidebar & bottom nav non-aktif, hari & tanggal, keterangan status non-kritis. |
| `font-semibold` | **600** | `Inter` | **Standar Komponen UI Utama**: Item menu aktif, seluruh badge status presensi, table headers (`th`), nama entitas di baris tabel, tombol aksi reguler/tabel, sub-heading kartu modul. |
| `font-bold` | **700** | `Inter` | Judul utama halaman (`h1`), judul besar modal dialog, angka statistik/KPI, jam digital aktif. |
| `font-extrabold` | **800** | `Inter` | *Khusus Display Terbatas*: Angka hitungan mundur (countdown timer) besar, layar Kiosk terminal. Dilarang pada badge, teks 10px-12px, atau baris tabel. |
| `font-black` | **900** | `Inter` | *Strictly Restricted*: Hanya untuk digit display PIN raksasa layar penuh (jika diperlukan). Dilarang total pada seluruh teks UI, badge, tombol, heading biasa, dan tabel. |

---

## 5. Sistem Warna & Token Kontras

### A. Palet Brand Ma'arif (Hijau Utama)
- `maarif-50`: `#f0fdf4` (Background badge lembut / aksen terpilih)
- `maarif-100`: `#dcfce7` (Pill status kehadiran HADIR)
- `maarif-200`: `#bbf7d0` (Border kartu aktif & highlight teks sekunder)
- `maarif-500`: `#22c55e` (Aksen dinamis, ping indicator aktif)
- `maarif-600`: `#16a34a` (Border & aksen hover interaktif)
- `maarif-700`: `#15803D` (**Warna Primer Utama** — Navbar, active nav, primary CTA)
- `maarif-800`: `#166534` (Gradien tengah hero banner)
- `maarif-900`: `#14532d` (Gradien dasar hero banner & sidebar admin)

### B. Palet Fungsional Status Presensi
- **HADIR**: Emerald (`bg-emerald-100 text-emerald-800 border-emerald-200`)
- **IZIN**: Amber (`bg-amber-100 text-amber-800 border-amber-200`)
- **SAKIT**: Sky Blue (`bg-sky-100 text-sky-800 border-sky-200`)
- **ALPA**: Rose (`bg-rose-100 text-rose-800 border-rose-200`)

### C. Palet Netral Surface
- `bg-slate-50`: Background dasar seluruh halaman aplikasi.
- `bg-white`: Surface kartu data, modal, dan sidebar desktop.
- `text-slate-900`: Warna teks utama berbobot tinggi.
- `text-slate-500` & `text-slate-400`: Warna teks pendukung / keterangan waktu.

---

## 6. Layout & Container Rules

1. **Fullwidth Responsive**:
   - Kontainer utama `<main>` pada layout menggunakan `w-full px-4 sm:px-6 lg:px-8` tanpa pembatasan kaku `max-w-7xl mx-auto`. Konten mengisi lebar monitor secara proporsional.
2. **Mobile Ergonomics**:
   - Area sentuh interaktif (tombol presensi, keypad PIN, bottom-nav) memiliki tinggi minimum `44px` hingga `48px` (`touch-btn`).
   - Kartu status GPS di mobile membentang *full-width* dengan aksi tombol satu ketukan jempol.
3. **Penyelarasan Kolom**:
   - Menggunakan CSS Grid `grid-cols-1 lg:grid-cols-12 gap-6 items-start` untuk menjaga keseimbangan antara kolom aksi (kiri) dan kolom riwayat (kanan).

---

## 7. Anti-Patterns (Larangan Mutlak)

1. **Dilarang Menggunakan Faux-Bold**: Jangan gunakan bobot font yang tidak diload di Google Fonts (misal font-light 300 pada Inter).
2. **Dilarang Arbitrary Font Sizes Tanpa Standar**: Hindari penggunaan sembarangan seperti `text-[13px]` atau `text-[15px]`. Selalu gunakan skala token terdaftar (`text-2xs`, `text-xs+`, `text-xs`, `text-sm`, `text-base`).
3. **Dilarang Font Gado-Gado**: Jangan memasukkan font di luar font resmi tunggal (**`Inter`**). Seluruh varian font lain seperti `JetBrains Mono`, `Plus Jakarta Sans`, atau serif generik dilarang keras di antarmuka sistem absensi ini. Untuk angka dan jam, gunakan kelas `.mono-font` / `font-mono` yang telah dikonfigurasi dengan OpenType tabular numbers `tnum`.
4. **Dilarang Memotong Tanggal & Data**: Gunakan `whitespace-nowrap` pada string tanggal resmi dan pastikan tidak terpotong elipsis (`...`).

---

## 8. Standar Desain Komponen Button (Modal Button System as Global Standard)

> **Filosofi**: Standar tombol mengadopsi gaya tombol aksi interaktif pada **Modal Konfirmasi & Form Modal (`confirm-dialog`)**. Desain ini menonjolkan sudut melengkung modern (*smooth rounded corners*), elevasi bayangan berkarakter (*colored ambient glow*), umpan balik sentuhan fisik (*tactile spring compression* `active:scale-[0.98]`), serta keterbacaan tipografi berbobot profesional dan bersih (`font-semibold`).

### A. Anatomi & Formula Dasar (Core Button Tokens)

Setiap tombol di platform Sistem Absensi Ma'arif **wajib** memenuhi formula berikut:

```
[Layout & Alignment]  inline-flex items-center justify-center gap-2 select-none cursor-pointer
[Tipografi & Berat]    font-semibold tracking-tight
[Radius Sudut]        rounded-xl sm:rounded-2xl (Standar/Large) | rounded-lg (Small)
[Transisi & Taktil]   transition-all duration-150 active:scale-[0.98]
[Aksesibilitas]       focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2
[Target Sentuh]       min-h-[42px] sm:min-h-[44px] (Mobile Touch Friendly)
```

---

### B. Matriks Ukuran (Size Scale)

| Ukuran | Utility Classes | Line-height & Teks | Tinggi Min | Penggunaan Utama |
|---|---|---|---|---|
| **Large (`lg`)** | `py-3 px-5 sm:px-6 text-sm sm:text-base rounded-xl sm:rounded-2xl gap-2.5 min-h-[48px]` | `text-sm sm:text-base font-semibold` | `48px` | Tombol CTA Hero, Modal Submit/Confirm, Halaman Login, Tombol Presensi Utama Mobile. |
| **Medium / Standar (`md`)** | `py-2.5 px-4 text-xs sm:text-sm rounded-xl gap-2 min-h-[42px]` | `text-xs sm:text-sm font-semibold` | `42px` | Header Action ("+ Tambah Siswa"), Form Submit Halaman Admin, Modal Cancel, Filter Trigger. |
| **Small / Kompak (`sm`)** | `py-1.5 px-3 text-xs rounded-lg sm:rounded-xl gap-1.5 min-h-[34px]` | `text-xs font-semibold` | `34px` | Aksi Baris Tabel (Edit/Detail), Tag Filter Cepat, Pill Action pada card ringkas. |
| **Icon Only (`icon`)** | `p-2 sm:p-2.5 rounded-xl sm:rounded-2xl flex items-center justify-center min-w-[38px] min-h-[38px]` | N/A | `38px` | Close Button Modal (`&times;` / `x`), Refresh GPS, Delete Icon Baris Tabel. |

### C. Perbedaan Mendasar Button vs Badge (Aturan Solid vs Semi-Transparan)

> **ATURAN MUTLAK SISTEM DESAIN**:
> 1. **BUTTON (Aksi Interaktif)**: **WAJIB SOLID** dengan teks kontras tinggi (umumnya `text-white`), bayangan fungsional, dan feedback taktil (`active:scale-95` / `active:scale-[0.98]`). Dilarang menggunakan warna pastel/soft pudar pada tombol karena membingungkan pengguna antara tombol aksi dengan badge status.
> 2. **BADGE (Status Informatif Non-Klik)**: **WAJIB SEMI-TRANSPARAN / TINTED** (`bg-emerald-50 text-emerald-800 border border-emerald-200/80`, `bg-amber-50 text-amber-800`, `bg-rose-50 text-rose-800`, `bg-sky-50 text-sky-800`, `bg-slate-100 text-slate-700`). Badge murni untuk label informasi dan tidak boleh meniru tampilan tombol solid.

---

### D. Matriks Variasi Warna Tombol (Seluruh Varian Wajib SOLID)

Tinggal sesuaikan variasi warna solid sesuai fungsi dan konteks aksinya:

#### 1. Primary (Brand Ma'arif Emerald)
- **Fungsi**: Aksi utama sistem, simpan data formulir, login, konfirmasi presensi masuk, tombol utama modal.
- **Solid**:
  ```html
  bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white shadow-xs focus-visible:ring-emerald-600
  ```

#### 2. Detail / Riwayat / Buka Rekap (Emerald Solid)
- **Fungsi**: Melihat riwayat presensi siswa/guru, membuka rekap sesi mengajar.
- **Solid**:
  ```html
  bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white shadow-xs focus-visible:ring-emerald-600
  ```

#### 3. Edit Data (Sky Blue Solid)
- **Fungsi**: Mengubah data master siswa, guru, rombel, mapel, jadwal pelajaran.
- **Solid**:
  ```html
  bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white shadow-xs focus-visible:ring-sky-500
  ```

#### 4. Koreksi Data TU (Indigo Solid)
- **Fungsi**: Form koreksi kehadiran siswa atau guru oleh staf Tata Usaha.
- **Solid**:
  ```html
  bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white shadow-xs focus-visible:ring-indigo-500
  ```

#### 5. Warning / Reset Sandi & PIN (Amber Solid)
- **Fungsi**: 1-klik reset kata sandi ke tanggal lahir, reset PIN siswa/guru.
- **Solid**:
  ```html
  bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white shadow-xs focus-visible:ring-amber-500
  ```

#### 6. Danger / Destructive / Hapus (Rose Red Solid)
- **Fungsi**: Hapus data guru/siswa/jadwal/kelas, batalkan sesi, logout akun.
- **Solid**:
  ```html
  bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-xs focus-visible:ring-rose-500
  ```

#### 7. Filter & Utilitas (Dark Slate Solid)
- **Fungsi**: Tombol submit filter pencarian, tombol navigasi kembali, tombol aksi netral.
- **Solid**:
  ```html
  bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white shadow-xs focus-visible:ring-slate-700
  ```

#### 8. Secondary Batal / Dismiss (Slate Neutral)
- **Fungsi**: Tombol "Batal" pada modal form, dismiss dialog.
- **Solid**:
  ```html
  bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-bold border border-slate-200/80 focus-visible:ring-slate-400
  ```
- **Kelas Tailwind**:
  ```html
  bg-emerald-500/25 hover:bg-emerald-500/35 active:bg-emerald-500/45 text-white backdrop-blur-md border border-emerald-400/40 shadow-sm focus-visible:ring-white
  ```

---

### D. Matriks Status Interaktif (Interactive States & Loading)

Setiap varian tombol harus menangani seluruh siklus status pengguna secara konsisten:

| Status | Behavior & Utility Classes | Visual Output |
|---|---|---|
| **Hover** | `hover:brightness-95` atau hover shade yang lebih gelap (`hover:bg-{color}-800`) | Warna mendalam, visual interaktif siap diklik. |
| **Active / Press** | `active:scale-[0.98]` (atau `active:scale-95` pada icon/small) | Efek kompresi taktil elastis seperti tombol fisik. |
| **Focus-Visible** | `focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-{color}-500` | Ring kontras untuk navigasi keyboard (aksesibilitas a11y). |
| **Disabled** | `disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none disabled:active:scale-100` | Tampilan redup, interaksi terkunci, tidak memicu animasi skala. |
| **Loading / Busy** | `<svg class="animate-spin w-4 h-4 mr-2" ...></svg>` + `cursor-wait opacity-80 pointer-events-none` | Indikator proses berputar saat submit AJAX/Form. |

---

### E. Standar Ikonografi & Penyelarasan (Icon Alignment & Sizing)

Untuk tombol yang memiliki icon, ikuti standar proporsi berikut:

| Ukuran Tombol | Ukuran Icon (`w-h`) | Gap / Jarak | Penempatan |
|---|---|---|---|
| **Large (`lg`)** | `w-5 h-5` (20px) | `gap-2.5` | Leading Icon (sebelum teks) |
| **Medium (`md`)** | `w-4 h-4` (16px) | `gap-2` | Leading Icon (sebelum teks) |
| **Small (`sm`)** | `w-3.5 h-3.5` (14px) | `gap-1.5` | Leading Icon (sebelum teks) |
| **Icon Only (`icon`)** | `w-4 h-4` sm:`w-5 h-5` | `p-2 sm:p-2.5` | Sentris penuh (tanpa teks) |

---

### F. Snippet Contoh Implementasi

#### 1. Modal Footer Action Pair (Standar Modal)
```html
<div class="pt-3 flex items-center gap-2.5 sm:gap-3">
    <button type="button" onclick="closeModal()" 
        class="flex-1 py-3 px-4 rounded-xl sm:rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-bold text-xs sm:text-sm transition-all duration-150 active:scale-[0.98] cursor-pointer">
        Batal
    </button>
    <button type="submit" 
        class="flex-1 py-3 px-4 rounded-xl sm:rounded-2xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-bold text-xs sm:text-sm shadow-md shadow-maarif-700/25 transition-all duration-150 active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:pointer-events-none">
        <i data-lucide="check" class="w-4 h-4"></i>
        <span>Simpan Data</span>
    </button>
</div>
```

#### 2. Header "+ Tambah Data" Action Button
```html
<button type="button" onclick="openAddModal()" 
    class="inline-flex items-center justify-center gap-2 py-2.5 px-4 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md shadow-maarif-700/25 transition-all duration-150 active:scale-[0.98] cursor-pointer">
    <i data-lucide="plus" class="w-4 h-4"></i>
    <span>Tambah Siswa</span>
</button>
```

#### 3. Row Table Action Buttons (Solid Variant)
```html
<div class="flex items-center justify-end gap-1.5">
    <a href="..." 
        class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 shadow-xs cursor-pointer" title="Riwayat">
        <i data-lucide="history" class="w-3.5 h-3.5"></i>
        <span>Riwayat</span>
    </a>
    <button type="button" onclick="openEditModal(...)" 
        class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 shadow-xs cursor-pointer" title="Edit">
        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
        <span>Edit</span>
    </button>
    <button type="button" data-confirm="Hapus data siswa ini?" data-confirm-type="danger" 
        class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 shadow-xs cursor-pointer" title="Hapus">
        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
        <span>Hapus</span>
    </button>
</div>
```

---

### G. Aturan Larangan Tombol (Button Anti-Patterns)
1. **Dilarang Tombol Datar Tanpa Feedback Taktil**: Setiap tombol harus memiliki `active:scale-[0.98]` atau `active:scale-95` dan status hover yang kentara.
2. **Dilarang Sudut Kaku (`rounded-none` / `rounded-sm`)**: Gunakan minimal `rounded-lg` untuk tombol tabel kecil dan `rounded-xl`/`rounded-2xl` untuk tombol reguler/modal.
3. **Dilarang Mengabaikan Cursor Pointer**: Selalu sertakan `cursor-pointer` untuk konsistensi di browser desktop (dan `disabled:cursor-not-allowed` saat terkunci).
4. **Dilarang Shadow Hitam Pekat Sembarangan**: Gunakan colored ambient shadow dengan saturasi warna tombol (`shadow-maarif-700/25`, `shadow-rose-600/25`, `shadow-amber-600/25`), bukan `shadow-2xl` hitam kusam.
5. **Dilarang Menghilangkan Label Teks Tanpa `aria-label` / `title`**: Pada icon-only button, wajib sertakan `title="..."` dan `aria-label="..."` untuk pembaca layar dan tooltip mouse.

---

## 9. Aturan Khusus Desain Mobile, Dashboard, Profil & Jadwal (Clean UI Guidelines)

> **Prinsip Utama**: *"Less Clutter, Pure Typography, High Intent."*
> Mengeliminasi elemen dekoratif semu (pill lonjong tak berguna, border bertumpuk, ring tebal, teks helper klise, pod status bengkak) agar antarmuka fokus pada data esensial, hierarki visual bersih, dan kecepatan aksi pengguna.

### A. Filosofi Anti-Pill & Anti-Bullet Dot
1. **Bukan Segalanya Harus Jadi Pill**:
   - Dilarang membungkus setiap label, peran, atau status non-kritis dengan bentuk pill lonjong (`rounded-full px-2.5 py-0.5` dengan background tajam/berwarna).
   - Untuk data pelengkap (seperti peran: `Peran: Guru`, status akun: `Aktif Terverifikasi`, atau label kelas: `Kelas X`), gunakan teks biasa dengan bobot proporsional (`text-xs text-slate-500` atau `text-slate-600 font-medium`) didampingi icon netral berukuran pas (`text-slate-400 w-3.5 h-3.5`).
2. **Eliminasi Pemisah Dot (`&bull;` / `•`)**:
   - Dilarang menyelipkan bullet dot sebagai pemisah antarteks jika tidak memiliki fungsi semantik yang krusial. Gunakan spasi natural atau pemisah vertikal halus agar tipografi tetap bersih dan tidak terpotong-potong.

---

### B. Kartu Profil & Pengelolaan Avatar (Profile & Identity Container)
1. **Avatar Bersih Tanpa Ring & Border Tebal**:
   - Avatar dilarang menggunakan ring tebal maupun border tajam (`no ring-4 ring-slate-100`, `no border-2 border-white`). Avatar harus menyatu harmonis dengan latar kartu profil tanpa ilusi stiker timbul.
2. **Single Trigger untuk Foto Profil**:
   - Tombol penggantian foto terpusat pada tombol kamera di sudut avatar.
   - Dilarang membuat tombol duplikat terpisah seperti "Unggah Foto / Ganti Foto" di bawah avatar. Satu aksi, satu trigger.
3. **Tanpa Teks Helper Format yang Klise**:
   - Hapus teks keterangan format statis seperti `"Format JPG, PNG, WEBP — Maks. 2 MB"` dari UI publik profil. Seluruh validasi ukuran dan format berkas ditangani oleh notifikasi client-side dan server-side secara interaktif.
4. **Pemisah Baris Data Samar & Rapi**:
   - Gunakan `divide-y divide-slate-200/30` untuk memisahkan daftar rincian identitas akun. Dilarang menggunakan baris bergantian warna (*zebra striping*) atau border tebal yang memecah konsentrasi.
5. **Anti-Redundansi Data Identitas**:
   - Jika suatu data (misal alamat email) sudah ditampilkan di area header kartu utama, jangan tampilkan kembali baris email tersebut pada tabel rincian identitas.
   - Jangan menyertakan baris identitas ganda seperti "Tipe Pengguna" jika "Peran: Guru" sudah dicantumkan.
6. **Icon Header Kartu Bersih & Unboxed (Bare Icons)**:
   - Icon header modul profil (seperti `key-round` pada Perbarui Kata Sandi dan `log-out` pada Keluar dari Sesi) dilarang dibungkus wadah kotak/pod (`no w-11 h-11 rounded-2xl bg-* border`). Tampilkan murni sebagai icon tipografi yang menyatu dengan judul (`w-5 h-5 text-emerald-700` atau `w-5 h-5 text-rose-600`).

---

### C. Dashboard Guru & Siswa (Mobile Hero & Indicators)
1. **Hapus Badge Role di Profile Card**:
   - Pada kartu profil hero mobile dashboard (baik Guru maupun Siswa), jangan menambahkan badge role dekoratif seperti `"Dewan Guru"`. Nama lengkap dan NIP/NISN sudah mendefinisikan identitas pengguna secara jelas.
2. **Indikator Geofence GPS Seragam & Linear**:
   - Dilarang menggunakan modul/pod geofence berukuran besar dengan background tebal yang memakan ruang vertikal mobile secara berlebihan.
   - Gunakan indikator linear ringkas dan interaktif tepat di bawah baris NIP/NISN (`geofenceBadge` dengan ping dot berkedip + jarak meter + icon refresh berputar). Format indikator GPS guru dan siswa wajib identik dan seragam.
3. **Accordion Panduan / Petunjuk Bersih**:
   - Hapus badge dekoratif tak penting pada accordion petunjuk (seperti badge `"Petunjuk Alur"` pada guru atau `"3 Langkah Alur"` pada siswa).
   - Selaraskan ikon panduan menggunakan ikon netral yang konsisten: wajib gunakan ikon `info` (`data-lucide="info"`), bukan ikon tanda tanya `help-circle`.
4. **Hero Profil & Greeting Unboxed (Anti-Boxed Hero Profile)**:
   - Dilarang membungkus section greeting / profil dashboard ke dalam card/container boks (`no bg-gradient rounded-3xl p-5 border shadow-xl`).
   - Tampilkan identitas profil secara *unboxed* langsung pada surface halaman dengan garis bawah pemisah halus (`pb-6 border-b border-slate-200/80`), mengintegrasikan avatar bersih, nama pengguna, metadata NIP/NISN (tanpa bullet dot), indikator GPS linear interaktif, dan jam digital aktif (`.liveClockTicker`) dengan tipografi tabular Inter.

---

### D. Halaman Jadwal Pelajaran (Schedule Interface)
1. **Header Hari Bersih**:
   - Dilarang memasang icon kalender (`calendar-days`) di samping nama hari.
   - Dilarang memasang badge pill `"Hari Ini"` pada header hari. Indikator aktif harus berfokus langsung pada slot jam sesi mengajar yang sedang berlangsung.
2. **Nomor Sesi Mapel Tanpa Kotak**:
   - Nomor urut jam pelajaran tampil sebagai angka murni dengan font monospace (`font-mono text-sm font-semibold`), tanpa dibungkus kotak background (`bg-slate-100`) maupun border bujur sangkar.
3. **Keterangan Kelas Tanpa Background**:
   - Tampilkan informasi kelas sebagai teks biasa (contoh: `Kelas X-A`) didampingi icon pintu/kelas netral (`door-closed text-slate-400`), tanpa badge background berwarna hijau atau abu-abu.
4. **Rentang Waktu Rata Kanan & Tanpa Background Gelap**:
   - Teks rentang jam mengajar diletakkan di sisi kanan (`justify-end`).
   - Dilarang memberi background hitam atau kotak pill gelap pada teks jam. Waktu ditampilkan sebagai teks murni.
5. **Realtime Emerald Highlight untuk Sesi Aktif**:
   - Jika hari ini dan waktu sekarang masuk ke dalam range jam jadwal mengajar (`$isCurrentSlot`), angka nomor sesi dan teks rentang jam otomatis aktif menyala dengan warna hijau emerald (`text-emerald-700` dan `text-emerald-600 font-bold`).
   - Ketika berada di luar jam pelajaran aktif, teks jam kembali menjadi warna netral (`text-slate-400 font-medium`).
6. **Penyelarasan Baseline Label Waktu (WIB)**:
   - Teks keterangan zona waktu `WIB` dilarang mengambang (*floating*) atau memiliki offset vertikal yang tidak wajar dari angka jam.
   - Gunakan `leading-none text-[11px]` dengan perataan baseline/center yang sejajar persis dengan angka jam (`text-xs font-mono`).
7. **Section Hari Tanpa Wrapper Card (Anti-Nested Cards)**:
   - Dilarang membungkus section per hari ke dalam card kontainer besar (`bg-white rounded-3xl p-5 border shadow`) yang mengakibatkan kartu item jadwal bersarang di dalam kartu (*nested card-in-card anti-pattern*).
   - Section hari cukup ditandai dengan header nama hari yang bersih dan garis pemisah halus (`border-b border-slate-200/70`), dengan card item pelajaran langsung mengalir bebas di bawahnya.

---

### E. Navigasi & Mobile Headerbar
1. **Larangan Tombol Logout di Mobile Headerbar**:
   - Dilarang menempatkan tombol logout pada bar navigasi atas mobile (`mobile headerbar`).
   - Tombol logout hanya diperkenankan berada di dalam halaman Pengaturan Akun / Profil dan di dalam Drawer Menu Sidebar Navigasi.
2. **Tombol Kembali (Back Button) Telanjang Tanpa Bungkus**:
   - Dilarang membungkus tombol kembali (`arrow-left`) dengan card, kotak border, maupun latar belakang bujur sangkar abu-abu (`no bg-slate-50 border border-slate-200 rounded-2xl`).
   - Tombol kembali harus tampil murni sebagai tautan icon telanjang (`text-slate-400 hover:text-slate-700 active:text-slate-900 transition-colors p-1 -ml-1 cursor-pointer`) yang mengalir harmonis tepat di samping teks judul halaman.

---

### F. Riwayat Sesi & Status Kehadiran (Session History & Status Rules)
1. **Dilarang Membungkus Status 'Selesai' dan 'Tercatat' (Anti-Boxed Status/Counter)**:
   - Dilarang membungkus indikator counter header seperti `"X Tercatat"` dengan border atau latar belakang abu-abu/badge (`no px-3 py-1 rounded-xl bg-slate-50 border`). Tampilkan murni sebagai teks monospace bersih (`mono-font text-xs font-semibold text-slate-500`).
   - Dilarang membungkus status `"Selesai"` pada baris riwayat kelas dengan capsule pill, border, atau badge latar hijau (`no px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200`). Status selesai ditampilkan murni sebagai teks tipografi berbobot tegas (`text-xs font-semibold text-emerald-700`) didampingi ikon centang hijau (`check-circle-2 text-emerald-600 w-4 h-4`).
   - Status fungsional lain (seperti *Sesi Aktif* dan *Perlu Konfirmasi*) juga wajib tampil bersih tanpa pembungkus pill tebal berlebihan.
   - Angka rekapitulasi kehadiran (Hadir / Izin / Alpa) ditampilkan dalam font tabular Inter (`.mono-font` / `font-mono`) terpisah dengan karakter garis miring netral (`/`), tanpa menggunakan bullet dot (`&bull;`).
2. **Dilarang Menggunakan Garis Vertikal Berwarna pada Card (Anti-Vertical Accent Stripe)**:
   - Dilarang memberikan garis vertikal berwarna di sisi kartu riwayat sesi, log kehadiran, maupun kartu list lainnya (`no border-l-4 border-l-emerald-500`, `no border-l-4 border-l-amber-500`, `no border-l-4 border-l-rose-500`).
   - Kartu harus bersih dengan border netral seragam di seluruh sisinya (`border border-slate-200/80 bg-white hover:border-slate-300`).
   - Pembedaan status harus dikomunikasikan secara natural lewat tipografi status, ikon semantik (`check-circle-2`, `loader-2`, `alert-circle`), dan warna teks status yang jelas—bukan melalui garis border tebal samping yang membuat visual tampak berantakan dan klise.
3. **Dilarang Membungkus Icon dengan Kotak/Container Semu (Anti-Boxed Icon Containers)**:
   - Dilarang membungkus icon dekoratif atau icon metriks dalam wadah bujur sangkar / rounded box buatan (`no w-8 h-8 rounded-xl bg-slate-100`, `no w-9 h-9 rounded-2xl bg-emerald-50 border`, `no w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center`).
   - Tampilkan icon secara murni (*bare icon*) dengan proporsi ukuran dan warna yang tepat (`w-5 h-5 text-slate-400`, `w-5 h-5 text-emerald-600`, `w-8 h-8 text-slate-300`).
   - Icon menyatu natural dengan tipografi di sekitarnya tanpa kesan stiker tempel atau pod berlapis-lapis.
