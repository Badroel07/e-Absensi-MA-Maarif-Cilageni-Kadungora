# DESIGN.md — SIMADMA (Sistem Informasi Madrasah MA Ma'arif Cilageni)

> Dokumen ini direkayasa balik dari kode frontend yang ada saat ini (Blade views, Tailwind CSS v4, Alpine.js, Lucide). Semua nilai diambil langsung dari `resources/css/app.css`, `resources/views/**`, dan `routes/web.php`.

## 1. Overview

**SIMADMA** adalah sistem informasi presensi (absensi) untuk Madrasah Aliyah **MA Ma'arif Cilageni**, Kadungora–Garut, Jawa Barat. Sistem mencatat kehadiran guru dan siswa menggunakan verifikasi QR dinamis, radius geofence lokasi madrasah, dan PIN sesi kelas.

**Target pengguna (3 peran + 1 terminal):**

| Peran | Perangkat utama | Kebutuhan UI |
|---|---|---|
| **Admin** | Desktop/tablet | Dashboard monitoring, CRUD data pokok madrasah, koreksi presensi, laporan eksekutif |
| **Guru** (Dewan Guru) | Mobile/tablet PWA | Presensi diri via QR, buka sesi kelas, konfirmasi kehadiran siswa |
| **Siswa** | Mobile PWA | Lihat status presensi, jadwal pelajaran, riwayat |
| **Kiosk / Layar Presensi** | Terminal publik (layar ruang guru) | Tampilan QR dinamis + feed aktivitas real-time |

**Stack frontend:** Laravel Blade templates, Tailwind CSS **v4** (konfigurasi CSS-first via `@theme` di `resources/css/app.css`), **Alpine.js 3**, **Lucide icons**, **html5-qrcode** (pemindai QR), PWA (manifest + service worker), runtime navigasi SPA kustom (PJAX-style, `partials/partial-nav.blade.php`).

## 2. Design Principles

1. **Mobile-first untuk pengguna harian; desktop-first untuk admin.** Guru & siswa memakai bottom navigation, target sentuh minimum 48px (`.touch-btn`), dan safe-area insets. Admin mendapat sidebar penuh di desktop.
2. **Identitas institusional hijau.** Skala warna kustom `maarif` (emerald/hijau, seed `#15803D`) dipakai konsisten sebagai warna brand di semua layar; aksen emas/amber untuk peran guru dan peringatan.
3. **Pembedaan peran lewat warna.** Guru = amber (`bg-amber-300 text-slate-900`, avatar `bg-amber-100 text-amber-800`); Admin & Siswa = emerald/maarif. Kiosk berdiri sendiri dengan tema gelap amber.
4. **Umum Bahasa Indonesia penuh.** Semua label, judul, pesan konfirmasi, dan toast berbahasa Indonesia (contoh: "Konfirmasi Tindakan", "Ya, Hapus").
5. **Densitas informasi tinggi.** Teks UI didominasi `text-xs` (12px) dan ukuran mikro kustom (`3xs` 9px, `2xs` 10px, `xs-plus` 11px) — dirancang untuk tabel data dan kartu ringkas.
6. **Progressive enhancement & persepsi kecepatan.** Skeleton shimmer saat loading, crossfade konten SPA 120ms, progress bar navigasi, haptic feedback (`navigator.vibrate`), penghormatan `prefers-reduced-motion`.
7. **Konfirmasi sebelum tindakan berdampak.** Setiap aksi destruktif/final melalui modal konfirmasi terpusat dengan kategori semantik (`danger`, `warning`, `lock`, `logout`, dll.).
8. *(Inferred)* **Permukaan ganda terang/gelap.** Aplikasi utama terang (slate-50 + kartu putih); Kiosk gelap penuh (`#080c14`) untuk layar ruangan.

## 3. Design Tokens

Sumber utama: blok `@theme` di `resources/css/app.css`. ⚠️ Halaman login memuat Tailwind **via CDN** dengan duplikasi konfigurasi token yang sama (lihat §9).

### 3.1 Warna

**Brand — skala `maarif` (hijau, warna utama):**

| Token | Hex | Penggunaan dominan |
|---|---|---|
| `maarif-50` | `#f0fdf4` | Selection highlight, latar tipis |
| `maarif-100` | `#dcfce7` | Selection text bg, avatar siswa |
| `maarif-200` | `#bbf7d0` | Border avatar siswa |
| `maarif-300` | `#86efac` | — |
| `maarif-500` | `#22c55e` | Aksen QR kiosk |
| `maarif-600` | `#16a34a` | Focus ring utama, badge role siswa/admin |
| `maarif-700` | `#15803D` | **Primary button, nav aktif, brand header, theme-color PWA** |
| `maarif-800` | `#166534` | Hover primary, header desktop/sidebar brand (`emerald-800` dipakai setara) |
| `maarif-900` | `#14532d` | Hero KPI gelap, gradient login |
| `maarif-950` | `#052e16` | Gradient terdalam |
| `maarif-gold` | `#EAB308` | Aksen emas institusi |

**Netral (Tailwind `slate`):** latar halaman `bg-slate-50`; kartu `bg-white`; teks utama `text-slate-900`; sekunder `text-slate-500`; muted `text-slate-400`; border `border-slate-200` / `border-slate-100`.

**State colors:**

| Status | Warna | Contoh kelas |
|---|---|---|
| Success | Emerald/Maarif | `bg-emerald-700 hover:bg-emerald-800`, `bg-emerald-50 text-emerald-800 border-emerald-200/80` |
| Error/Danger | Rose | `bg-rose-600 hover:bg-rose-700`, `bg-rose-50 text-rose-800 border-rose-200/80` |
| Warning | Amber | `bg-amber-600`, `bg-amber-50 text-amber-800 border-amber-200/80` |
| Info | Sky | `bg-sky-50 text-sky-800 border-sky-200/80`, focus ring `sky-600` (dipakai pada sebagian input — inkonsisten, lihat §9) |
| Netral (logout/info) | Slate | `bg-slate-900 hover:bg-slate-800` |

**Kiosk (tema gelap):** body `bg-[#080c14]`; panel `bg-slate-900/90 border-slate-800`; aksen `emerald-400`/`emerald-500`; teks sekunder `slate-400`.

**Gradient khas:** header mobile & bottom nav `bg-gradient-to-r from-emerald-800 via-maarif-700 to-emerald-800`; hero KPI admin `from-maarif-800 to-maarif-900`; panel login `from-maarif-900 via-maarif-800 to-slate-950`; indikator nav aktif `from-emerald-400 to-teal-300` dengan glow `shadow-[0_0_8px_#34d399]`.

### 3.2 Tipografi

| Peran | Font | Catatan |
|---|---|---|
| Body / UI | **DM Sans** | `--font-sans`, dimuat dari Google Fonts (opsz variable) |
| Heading & angka | **Lexend** | `--font-heading` (`.heading-font`); juga berfungsi sebagai "mono" |
| Angka/tabular | Lexend + `font-feature-settings: 'tnum', 'zero'` | Kelas `.mono-font` / `.font-mono` — dipakai untuk jam, NISN, PIN, countdown |

**Ukuran kustom (di luar skala Tailwind):**

| Token | Ukuran / line-height | Penggunaan |
|---|---|---|
| `text-3xs` | 9px / 12px, tracking 0.05em | Label mikro |
| `text-2xs` | 10px / 14px, tracking 0.025em | Label nav mobile, header tabel |
| `text-xs-plus` | 11px / 16px, tracking 0.015em | Eyebrow/kicker (juga ditulis manual `text-[11px]`) |

**Skala yang terlihat di UI:** `text-[10px]` (label nav), `text-[11px]` (eyebrow/uppercase), `text-xs` (body UI dominan), `text-sm` (subjudul), `text-base–lg` (judul header), `text-2xl–3xl` (judul login/kiosk), `text-4xl–6xl font-extrabold` (jam kiosk). Heading memakai `tracking-tight`; eyebrow memakai `uppercase tracking-wider/widest font-semibold`.

### 3.3 Spacing & Grid

- Skala spacing Tailwind default + satu tambahan: `--spacing-13: 3.25rem`.
- Konten utama: `px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8 pb-36 md:pb-12` (padding bawah 36 di mobile untuk memberi ruang bottom nav).
- Header desktop & sidebar brand disinkronkan pada **h-72px**.
- Sidebar: `w-64` (md) → `w-72` (lg), fixed kiri; konten `md:pl-64 lg:pl-72`.
- Kiosk: grid 12 kolom (`lg:grid-cols-12`), dua pilar `lg:col-span-6` + `max-w-7xl mx-auto`.
- Safe area: `.safe-bottom { padding-bottom: max(env(safe-area-inset-bottom, 0px), 8px) }` + padanannya untuk top inset di header mobile; viewport `viewport-fit=cover`.

### 3.4 Border Radius

| Token | Penggunaan |
|---|---|
| `rounded-lg` | Ikon kontainer kecil, tombol sekunder kecil |
| `rounded-xl` | **Baku untuk tombol, input, select, badge ikon, avatar** |
| `rounded-2xl` | **Baku untuk kartu, modal, toast, KPI card** |
| `rounded-3xl` | Panel QR kiosk |
| `rounded-full` | Badge/pill, progress bar, skeleton circle |

### 3.5 Shadows

| Token | Nilai | Penggunaan |
|---|---|---|
| `shadow-2xs` | `0 1px 2px 0 rgba(0,0,0,0.03)` | Elemen halus |
| `shadow-xs` | `0 1px 2px 0 rgba(0,0,0,0.05)` | Nav aktif, tombol |
| `shadow-soft` | `0 2px 10px -2px rgba(0,0,0,0.04), 0 1px 3px -1px rgba(0,0,0,0.02)` | Kartu halus |
| `shadow-card` | `0 4px 20px -2px rgba(0,0,0,0.05)` | Kartu |
| Kustom | modal `shadow-[0_20px_25px_-5px_rgba(15,23,42,0.08),...]`; bottom nav `shadow-[0_-4px_24px_rgba(0,0,0,0.25)]`; glow nav aktif `drop-shadow-[0_0_6px_rgba(52,211,153,0.6)]` | Konteks khusus |

### 3.6 Breakpoints

Breakpoint Tailwind default; **`md` (768px) adalah titik switch utama** (sidebar+header desktop vs header+bottom nav mobile), `lg` (1024px) memperlebar sidebar dan grid kiosk.

## 4. UI Components

### 4.1 Tombol

**Komponen Blade: `<x-loading-button>`** (`components/loading-button.blade.php`) — props: `variant` (`primary`/`secondary`/`danger`), `type`, `icon`, `label`, `loadingLabel`. State loading mengganti isi dengan spinner (kelas `.btn-loading`, teks jadi transparan, `pointer-events: none`), disabled `opacity-70`.

| Varian | Kelas inti |
|---|---|
| Primary | `bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white` |
| Secondary | `bg-slate-800 hover:bg-slate-900 text-white` |
| Danger | `bg-rose-600 hover:bg-rose-700 text-white` |

**Pola tombol inline yang berulang** (dipakai langsung di banyak halaman, `py-2.5 px-6 rounded-xl text-xs font-semibold ... active:scale-[0.98] focus-visible:ring-2`): primary `bg-maarif-700…`, small `py-1.5 px-3 rounded-lg`, ikon-saja `p-2 rounded-xl bg-slate-100 hover:bg-rose-600 hover:text-white` (logout).

**State baku:** hover = shade lebih gelap; active = `active:scale-[0.98]` (atau `active:scale-95`); focus = `focus:outline-none focus-visible:ring-2 focus-visible:ring-<warna varian>`; disabled = `disabled:opacity-70 disabled:cursor-not-allowed`; loading = `.btn-loading`.

### 4.2 Input & Select

Pola berulang (grep: 40+ kemunculan): `w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-xs font-medium bg-slate-50/70 focus:bg-white transition` dengan focus ring `focus:ring-2` — warna ring **bermacam**: `maarif-600` (+`focus:border-maarif-600`) mayoritas, `sky-600` pada sebagian form (lihat §9). Field numerik/NISN/PIN memakai `.mono-font`. Checkbox/radio memakai `accent`/`text-` sesuai semantik (`text-maarif-700` aktif, `text-rose-600` nonaktif).

### 4.3 Kartu & KPI

- Kartu standar: `bg-white rounded-2xl border border-slate-200/80` (dashboard admin) atau `overflow-hidden` untuk kartu tabel.
- KPI card: `bg-white rounded-2xl p-5 flex flex-col justify-between` + ikon kontainer `w-12 h-12 rounded-2xl bg-slate-100`.
- Hero KPI gelap: `bg-gradient-to-br from-maarif-800 to-maarif-900 rounded-2xl p-6 text-white relative overflow-hidden` dengan skeleton varian `.skeleton-dark`.
- Pill bar "frosted glass" (dashboard GPS/jam): container pill blur dengan border tipis (lihat `siswa/dashboard`, `guru/dashboard`).

### 4.4 Modal — Global Confirm/Alert (`partials/confirm-dialog.blade.php`)

Satu modal terpusat dengan API `window.confirmAction({type, title, message, confirmText, icon})` → `Promise<boolean>`, dan `window.showAlertDialog()` (tanpa tombol batal). dipicu deklaratif via atribut `data-confirm*` pada form/tombol (tipe semantik bisa ter-deteksi otomatis dari kata "hapus"/"keluar"/"reset sandi"/"kunci"/"simpan").

| Tipe | Eyebrow | Ikon default | Tombol submit |
|---|---|---|---|
| `danger`/`delete` | "Tindakan Permanen" | `trash-2` | `bg-rose-600 hover:bg-rose-700` |
| `warning`/`key` | "Perhatian Khusus" | `alert-triangle` / `key-round` | `bg-amber-600…` |
| `primary`/`save`/`lock` | "Alur Final Presensi" | `save` / `lock` | `bg-emerald-700…` |
| `logout` | "Sesi Akun" | `log-out` | `bg-slate-900…` |
| `success` (centered) | "Sinkronisasi Selesai" | `check-circle-2` | `bg-slate-900…`, full-width |
| `info` (centered) | "Petunjuk Teknis" | `info` | `bg-slate-900…`, "Saya Mengerti" |

Struktur: kartu `max-w-md bg-white rounded-2xl p-7 sm:p-8`, backdrop `bg-slate-950/60` (tanpa blur), animasi scale-95→100 + fade, `role="dialog" aria-modal="true"`, Escape & klik backdrop menutup, fokus awal ke tombol Batal, `overflow-hidden` pada body saat terbuka, haptic `[25]`.

### 4.5 Toast (`partials/toast-notification.blade.php`)

`window.toast.success/error/warning/info(message, duration)`; container fixed `top-4 right-4`, `aria-live="polite"`. Kartu: `bg-slate-900/95 backdrop-blur-md text-white border-slate-700/80 rounded-2xl p-3.5`, badge ikon 8×8 tinted per tipe (`emerald`/`rose`/`amber`/`sky`), progress bar 2.5px di bawah yang menyusut linear, auto-dismiss 2000–2500ms, close button dengan `aria-label="Tutup notifikasi"`, haptic `[15]`. Terhubung ke flash session Laravel via `#page-flash-messages`.

### 4.6 Badge / Pill

`inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold` dengan pasangan `bg-{tone}-50 text-{tone}-800 border-{tone}-200/80` (emerald=hadiah/aktif, sky=info, rose=bolos/inaktif, amber=terlambat/perhatian). Badge peran di header mobile: `GURU` amber, `ADMIN`/`SISWA` emerald. Badge status gelap di hero: `bg-[#064e3b]/80 text-emerald-200/90 border-emerald-500/30`.

### 4.7 Tabel (pola admin)

`<thead>`: `bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-semibold text-[10px] tracking-widest`; sel `py-3 px-5`. Baris dan aksi baris (edit/hapus) memakai tombol ikon kecil rounded-lg.

### 4.8 Skeleton Loading (`components/skeleton/*`)

Komponen Blade: `card`, `circle`, `kpi`, `rect`, `row`, `text` + varian CSS `.skeleton`, `.skeleton-dark`, `.skeleton-auto-hide` (fade-out otomatis 1.2s), shimmer 1.6s, `@media (prefers-reduced-motion: reduce)` menonaktifkan animasi.

### 4.9 Navigasi

- **Sidebar desktop** (`partials/sidebar.blade.php`): brand emerald-800 72px, grup label `text-[11px] uppercase text-slate-400`, item `px-3.5 py-2.5 rounded-xl` — aktif `bg-emerald-700 text-white shadow-xs`, idle `text-slate-600 hover:bg-slate-100/80`; ikon Lucide `w-4 h-4`. Footer profil + tombol logout. Item kiosk diberi gaya amber khusus + ikon external-link.
- **Bottom nav mobile** (`partials/bottom-nav.blade.php`): fixed z-50, gradient emerald, 4–5 item per peran, indikator aktif = garis glow di atas ikon + teks putih bold; `aria-current="page"`.
- **Ikon:** Lucide, di-render via `data-lucide` + `lucide.createIcons()`.

## 5. Layout & Navigation

**Dua layout Blade:**

1. `layouts/app.blade.php` — dipakai guru & siswa (dan admin via header dinamis): sidebar desktop + header desktop emerald-800 72px (eyebrow peran: "Portal Dewan Guru"/"Portal Siswa"/"Panel Administrator") + header mobile gradient dengan logo & badge peran + main `#main-content` + bottom nav mobile.
2. `layouts/admin.blade.php` — varian khusus admin: identik secara struktur, eyebrow tetap "Panel Administrator", memuat Lucide dari CDN, menambah kunci `.preload-transitions` anti-flicker.

**Kedua layout membawa:** `confirm-dialog`, `toast-notification`, `partial-nav` (mesin SPA), `time-simulator` (khusus environment local), jembatan flash `#page-flash-messages`, sinkronisasi waktu server (`window.getServerNow()`), registrasi service worker + `window.triggerHaptic`.

**Navigasi antar halaman — SPA runtime (`partials/partial-nav.blade.php`):** interceptasi klik tautan & submit form → fetch dengan progress bar gradien atas (`#pjax-progress-bar`, emerald→amber, glow) → crossfade konten `#main-content` (`.spa-content-enter`, 120ms) → restorasi posisi scroll untuk back/forward, pembersihan otomatis interval/listener/media (mis. stop kamera QR) per halaman, dan replai flash message dari dokumen hasil fetch. Kiosk **di luar** SPA (halaman mandiri, tanpa layout).

**Routing (`routes/web.php`):** `/` me-redirect sesi peran; prefix `siswa.*`, `guru.*`, `admin.*` dengan middleware `role:`; `kiosk.*` publik; `profile` & `profile.password/photo` untuk semua peran terautentikasi; `/dev/time-simulator` hanya local/testing.

## 6. Core User Flows

1. **Login → distribusi peran.** `/login` (layar split: panel branding hijau gelap kiri 45% dengan kartu fitur "Kode QR Kiosk" & "Radius Lokasi"; form kanan putih) → redirect otomatis ke dashboard sesuai peran (`admin`/`guru`/`siswa`).
2. **Presensi guru (alur utama harian).** Dashboard guru (kartu status + jam + pill radius geofence) → **Pindai QR** (`guru.scan`, kamera html5-qrcode; verifikasi radius lokasi) → check-in tercatat → dari dashboard buka **Sesi Kelas** (`guru.session.open` → `session.show` = `session-live`, tampil live, dibuka/kunci dengan PIN) → **Reconcile** (`session.reconcile` — konfirmasi keterangan siswa, modal tipe `primary`/`lock` "Simpan & Kunci") → **Riwayat** (`guru.history`).
3. **Presensi siswa (PWA).** Dashboard siswa (jam server-sinkron, status presensi hari ini, pill radius GPS) → verifikasi PIN (`siswa.verify-pin`) → **Jadwal** (`siswa.schedule`) → **Riwayat** (`siswa.history`).
4. **Admin — CRUD Data Pokok Madrasah.** Sidebar grup "Data Pokok Madrasah" → halaman index (Siswa/Guru/Kelas/Mapel/Jadwal): tabel + tombol tambah → form modal → simpan (toast sukses) → edit (modal terisi) → hapus (modal `danger` "Ya, Hapus") → reset sandi siswa/guru (modal `key`) → riwayat per siswa (`siswa.riwayat`).
5. **Admin — Monitoring & Koreksi Presensi.** "Kehadiran Dewan Guru" (`presensi-guru.index`, memantau kiosk; koreksi per guru & per sesi jadwal) dan "Kehadiran Siswa" (`presensi-siswa.index`, koreksi `lessonAttendance` via PUT).
6. **Admin — Laporan.** `laporan.index` (filter) → **cetak PDF** (`laporan.pdf`, layout cetak terpisah `admin/laporan/pdf.blade.php`) atau **ekspor Excel/CSV** (`laporan.excel`).
7. **Admin — Konfigurasi & Audit.** Pengaturan lokasi geofence (`lokasi.index`) dan Riwayat Perubahan Data (`audit.index`).
8. **Kiosk Layar Presensi.** Layar penuh gelap: jam digital besar + QR dinamis (canvas putih, refresh dengan countdown bar) + feed "Aktivitas Presensi Hari Ini" (polling `kiosk.poll-event`); guru memindai QR dari ponsel.
9. **Profil (semua peran).** `profile` — ubah sandi (modal konfirmasi), unggah/hapus foto profil.

## 7. Responsive & Accessibility

**Responsif:**
- Mobile-first; switch utama di `md` (768px). Di bawah `md`: header gradient ringkas + bottom nav fixed (item 4–5 tergantung peran). Di atas: sidebar fixed `w-64`/`w-72` + header 72px.
- Padding konten bertingkat `px-4 sm:px-6 lg:px-8`; `pb-36` mobile memberi ruang bottom nav.
- Kiosk: kolom tunggal di mobile → dua pilar 12-col di `lg`; ukuran jam menaik `text-4xl→6xl`.
- Safe-area: `viewport-fit=cover` + `env(safe-area-inset-*)` pada header mobile dan bottom nav (`.safe-bottom`).
- PWA: manifest + service worker (`public/manifest.json`, `service-worker.js`), `theme-color #15803D`, `apple-mobile-web-app-capable`.

**Aksesibilitas (pola yang terlihat di kode):**
- `focus-visible` ring di semua tombol/modal/input; outline mouse-click dinonaktifkan sambil menjaga navigasi keyboard (`:focus:not(:focus-visible)`).
- Modal: `role="dialog"`, `aria-modal`, `aria-labelledby`, fokus awal ke tombol Batal, tutup via Escape.
- Nav: `aria-current="page"`; ikon-saja punya `aria-label`/`title` (contoh: "Keluar dari sistem", "Tutup modal").
- Toast container `aria-live="polite"`; logo memakai `alt` deskriptif; `<html lang="id">`.
- Animasi menghormati `prefers-reduced-motion` (skeleton); `[x-cloak]` mencegah kedipan konten Alpine; fallback `<noscript>`.
- Haptic feedback pada aksi penting (`navigator.vibrate`).
- ⚠️ Gap: viewport memakai `maximum-scale=1.0, user-scalable=no` — memblokir zoom (masalah aksesibilitas WCAG; lihat §9).

## 8. Page & Screen Inventory

| Halaman | Route | Fungsi |
|---|---|---|
| `auth/login` | `login` | Layar masuk split-screen (branding + form) |
| `siswa/dashboard` | `siswa.dashboard` | Status presensi hari ini, jam, radius GPS, verifikasi PIN |
| `siswa/schedule` | `siswa.schedule` | Jadwal pelajaran siswa |
| `siswa/history` | `siswa.history` | Riwayat kehadiran siswa |
| `guru/dashboard` | `guru.dashboard` | Status presensi guru, jam, pill radius, tombol buka sesi |
| `guru/scan` | `guru.scan` | Pemindai QR presensi (kamera) |
| `guru/session-live` | `guru.session.show` | Sesi kelas live — presensi siswa per pertemuan |
| `guru/reconcile` | `guru.session.reconcile` | Konfirmasi keterangan siswa sebelum sesi dikunci |
| `guru/schedule` | `guru.schedule` | Jadwal mengajar guru |
| `guru/history` | `guru.history` | Riwayat kelas mengajar / presensi guru |
| `admin/dashboard` | `admin.dashboard` | KPI madrasah (hero gelap + kartu KPI) |
| `admin/siswa/index` | `admin.siswa.index` | CRUD Data Siswa (tabel + modal form) |
| `admin/siswa/riwayat` | `admin.siswa.riwayat` | Riwayat presensi per siswa |
| `admin/guru/index` | `admin.guru.index` | CRUD Data Guru |
| `admin/guru/riwayat` | `admin.guru.riwayat` | Riwayat presensi per guru |
| `admin/kelas/index` | `admin.kelas.index` | CRUD Rombel/Kelas |
| `admin/mapel/index` | `admin.mapel.index` | CRUD Mata Pelajaran |
| `admin/jadwal/index` | `admin.jadwal.index` | CRUD Jadwal Mingguan |
| `admin/lokasi/index` | `admin.lokasi.index` | Pengaturan lokasi & radius geofence |
| `admin/laporan/index` | `admin.laporan.index` | Pusat laporan & rekapitulasi (filter) |
| `admin/laporan/pdf` | `admin.laporan.pdf` | Layout cetak PDF laporan |
| `admin/presensi-guru/index` | `admin.presensi-guru.index` | Monitoring kiosk & koreksi presensi guru |
| `admin/presensi-siswa/index` | `admin.presensi-siswa.index` | Koreksi kehadiran siswa |
| `admin/audit/index` | `admin.audit.index` | Riwayat perubahan data (audit trail) |
| `profile/index` | `profile` | Profil pengguna: sandi & foto |
| `kiosk/index` | `kiosk.index` | **Layar terminal publik**: QR dinamis + feed real-time |
| `welcome.blade.php` | — | Default Laravel, tidak dirutekan (sisa scaffold) |

**Partial & komponen:** `layouts/app`, `layouts/admin`, `partials/sidebar`, `partials/bottom-nav`, `partials/confirm-dialog`, `partials/toast-notification`, `partials/partial-nav` (SPA runtime), `partials/time-simulator` (dev), `components/loading-button`, `components/skeleton/{card,circle,index,kpi,rect,row,text}`. Catatan: `partials/admin-sidebar.blade.php` ada di filesystem tetapi layout memuat `partials/sidebar` — TODO: needs confirmation apakah masih dipakai.

## 9. Gaps & Recommendations

1. **Duplikasi token di halaman login.** `auth/login` memuat Tailwind dari CDN (`cdn.tailwindcss.com`) dan mendefinisikan ulang palet `maarif` + font — rawan drift dari `app.css` dan tidak untuk produksi. Rekomendasi: pindahkan login ke layout Vite yang ada.
2. **Inkonsistensi focus ring input.** Mayoritas input memakai `focus:ring-maarif-600`, sebagian `focus:ring-sky-600` (terdeteksi pada form admin). Rekomendasi: bakukan satu warna fokus (maarif-600) — idealnya jadikan komponen Blade `<x-input>` seperti `loading-button`.
3. **CSS duplikat antara `app.css` dan inline `<style>` layout.** `.custom-sidebar-scroll`, `.no-scrollbar`, `.mono-font`, `.touch-btn`, `.safe-bottom` didefinisikan baik di `app.css` maupun inline di kedua layout (dan kiosk mendefinisikan `.mono-font` memakai *Inter*, bukan Lexend). Rekomendasi: satukan di `app.css`, hapus inline.
4. **Komentar/kode tidak sinkron.** (a) Komentar `app.css` menyebut "Single font Inter" padahal `.mono-font` memakai Lexend; (b) komentar kiosk "20-Second Refresh Cycle" vs teks UI "berganti setiap 10 detik" — TODO: needs confirmation durasi refresh QR yang benar.
5. **Peta judul header merujuk rute yang tampaknya tidak ada.** `layouts/app` mengecek `request()->routeIs('siswa.leaves.*')` dan `guru.session` — tidak ada di `routes/web.php` saat ini (yang ada `guru.session.show`, dst.). Tidak fatal, tapi misleading. TODO: needs confirmation apakah fitur "Pengajuan Izin" direncanakan.
6. **Tombol & input tidak terkomponisasi.** Kelas tombol/input yang sama disalin-paste puluhan kali (variasi kecil antar salinan). Rekomendasi: perluas `components/` dengan `x-button`, `x-input`, `x-badge`, `x-card`.
7. **`user-scalable=no` + `maximum-scale=1.0`** memblokir zoom browser — pelanggaran aksesibilitas umum (WCAG 1.4.4). Rekomendasi: izinkan zoom.
8. **`welcome.blade.php`** masih berisi scaffold Laravel default dan tidak dirutekan — hapus.
9. **Dua layout hampir identik.** `layouts/admin` vs `layouts/app` berbeda hanya pada eyebrow dinamis, pemuatan Lucide, dan anti-flicker. Rekomendasi: satukan menjadi satu layout dengan flag per peran.
10. **Skeleton "hero" KPI & pill bar** hanya ada di halaman tertentu; halaman tabel admin lain tidak memakai skeleton konsisten — standarkan pola loading.
