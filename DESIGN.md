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

Sistem menggunakan tri-font system yang saling melengkapi:

| Kategori Font | Family | Loaded Weights | Utility Class | Peran & Tujuan |
|---|---|---|---|---|
| **Primary Sans (UI & Body)** | `Inter`, system-ui, sans-serif | 400, 500, 600, 700 | `font-sans` (Default) | Teks isi, deskripsi, form input, tabel, navigasi, dan copy umum dengan keterbacaan tinggi pada layar kecil. |
| **Heading & Display** | `Plus Jakarta Sans`, sans-serif | 600, 700, 800, 900 | `.heading-font` / `font-heading` | Judul halaman, greeting nama siswa/guru, nama mata pelajaran, kartu hero, dan judul modal. |
| **Monospace (Data & Angka)** | `JetBrains Mono`, monospace | 500, 600, 700, 800 | `.mono-font` / `font-mono` | Jam digital aktif, PIN presensi 4-digit, NISN/NIP, countdown timer, koordinat GPS, dan tag jam pelajaran. |

### Konfigurasi Google Fonts:
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800;900&display=swap" rel="stylesheet">
```

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

Seluruh bobot font yang digunakan **wajib terdaftar dan di-load** untuk mencegah *faux-bold* (peniruan ketebalan artifisial oleh browser yang membuat teks buram):

| Utility Tailwind | Numeric Weight | Font Family Terkait | Kasus Penggunaan Ideal |
|---|---|---|---|
| `font-normal` | **400** | `Inter` | Teks paragraf panjang, catatan bantuan, deskripsi umum. |
| `font-medium` | **500** | `Inter`, `JetBrains Mono` | Label sekunder, hari & tanggal, keterangan status non-kritis. |
| `font-semibold` | **600** | `Inter`, `Plus Jakarta Sans`, `JetBrains Mono` | Item jadwal pelajaran, badge status, filter tab, sub-heading kartu. |
| `font-bold` | **700** | `Inter`, `Plus Jakarta Sans`, `JetBrains Mono` | Tombol CTA, judul kartu modul, angka statistik, jam digital. |
| `font-extrabold` | **800** | `Plus Jakarta Sans`, `JetBrains Mono` | Judul halaman utama, badge prioritas tinggi, PIN display box. |
| `font-black` | **900** | `Plus Jakarta Sans` | Hero display greeting (nama siswa/guru pada hero card utama). |

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

1. **Dilarang Menggunakan Faux-Bold**: Jangan gunakan bobot font yang tidak diload di Google Fonts (misal font-light 300 pada Plus Jakarta Sans atau font-thin).
2. **Dilarang Arbitrary Font Sizes Tanpa Standar**: Hindari penggunaan sembarangan seperti `text-[13px]` atau `text-[15px]`. Selalu gunakan skala token terdaftar (`text-2xs`, `text-xs+`, `text-xs`, `text-sm`, `text-base`).
3. **Dilarang Font Gado-Gado**: Jangan memasukkan font di luar ketiga font resmi (`Inter`, `Plus Jakarta Sans`, `JetBrains Mono`). Font serif generik dilarang keras di antarmuka sistem absensi ini.
4. **Dilarang Memotong Tanggal & Data**: Gunakan `whitespace-nowrap` pada string tanggal resmi dan pastikan tidak terpotong elipsis (`...`).

---

## 8. Standar Desain Komponen Button (Modal Button System as Global Standard)

> **Filosofi**: Standar tombol mengadopsi gaya tombol aksi interaktif pada **Modal Konfirmasi & Form Modal (`confirm-dialog`)**. Desain ini menonjolkan sudut melengkung modern (*smooth rounded corners*), elevasi bayangan berkarakter (*colored ambient glow*), umpan balik sentuhan fisik (*tactile spring compression* `active:scale-[0.98]`), serta keterbacaan tipografi berbobot tegas (`font-bold`).

### A. Anatomi & Formula Dasar (Core Button Tokens)

Setiap tombol di platform Sistem Absensi Ma'arif **wajib** memenuhi formula berikut:

```
[Layout & Alignment]  inline-flex items-center justify-center gap-2 select-none cursor-pointer
[Tipografi & Berat]    font-bold tracking-tight
[Radius Sudut]        rounded-xl sm:rounded-2xl (Standar/Large) | rounded-lg (Small)
[Transisi & Taktil]   transition-all duration-150 active:scale-[0.98]
[Aksesibilitas]       focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2
[Target Sentuh]       min-h-[42px] sm:min-h-[44px] (Mobile Touch Friendly)
```

---

### B. Matriks Ukuran (Size Scale)

| Ukuran | Utility Classes | Line-height & Teks | Tinggi Min | Penggunaan Utama |
|---|---|---|---|---|
| **Large (`lg`)** | `py-3 px-5 sm:px-6 text-sm sm:text-base rounded-xl sm:rounded-2xl gap-2.5 min-h-[48px]` | `text-sm sm:text-base font-bold` | `48px` | Tombol CTA Hero, Modal Submit/Confirm, Halaman Login, Tombol Presensi Utama Mobile. |
| **Medium / Standar (`md`)** | `py-2.5 px-4 text-xs sm:text-sm rounded-xl gap-2 min-h-[42px]` | `text-xs sm:text-sm font-bold` | `42px` | Header Action ("+ Tambah Siswa"), Form Submit Halaman Admin, Modal Cancel, Filter Trigger. |
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
