@extends('layouts.app')

@section('title', 'Dashboard — MA Ma\'arif Cilageni')

@push('styles')
<style>
    .mono-font {
        font-family: 'JetBrains Mono', monospace;
    }
    .radar-pulse {
        animation: radar-ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
    @keyframes radar-ping {
        75%, 100% {
            transform: scale(2);
            opacity: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 heading-font tracking-tight">Dashboard</h1>
            <p class="text-xs sm:text-sm text-slate-500">Halaman presensi harian Bapak/Ibu Guru, pembukaan sesi kelas, dan pencatatan kehadiran siswa</p>
        </div>
    </div>

    <!-- 1. HERO GREETING & PROFILE BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-maarif-900 via-maarif-800 to-emerald-950 text-white p-5 sm:p-7 shadow-xl border border-emerald-600/30 flex flex-col gap-4 sm:gap-5">
        <!-- Background Ambient Glow -->
        <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-emerald-400/20 blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -bottom-16 w-56 h-56 rounded-full bg-emerald-600/25 blur-2xl pointer-events-none"></div>

        <!-- [MOBILE ONLY] Top Row: Date & Live Digital Clock Capsule (< md) -->
        <div class="md:hidden relative z-10 flex items-center justify-between gap-2 pb-3 border-b border-white/10">
            <!-- Date Indicator -->
            <div class="text-xs font-semibold text-emerald-100/90 whitespace-nowrap tracking-tight">
                {{ $todayDay }}, {{ now()->translatedFormat('d F Y') }}
            </div>

            <!-- Live Clock Pill Capsule -->
            <div class="px-2.5 py-1 rounded-full bg-black/25 backdrop-blur-md border border-white/20 text-white shrink-0 shadow-inner flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="liveClockTicker text-xs font-bold mono-font tracking-wider">
                    {{ now()->format('H:i:s') }} <span class="text-[10px] font-semibold text-emerald-300">WIB</span>
                </div>
            </div>
        </div>

        <!-- MAIN HERO ROW: Profile (Left) & Desktop Clock (Right) -->
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Teacher Identity (Left) -->
            <div class="flex items-center gap-4">
                <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-white/20 to-white/5 border border-white/25 text-white flex items-center justify-center font-bold text-2xl sm:text-3xl shadow-lg shrink-0 backdrop-blur-md overflow-hidden ring-2 ring-white/15">
                    @if($teacher->profile_photo_url)
                        <img src="{{ $teacher->profile_photo_url }}" loading="lazy" decoding="async" alt="{{ $teacher->name }}" class="w-full h-full object-cover">
                    @else
                        <span class="select-none">{{ substr($teacher->name, 0, 1) }}</span>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="px-2 py-0.5 rounded-md bg-emerald-500/30 border border-emerald-400/40 text-[11px] font-semibold text-emerald-200">
                            Dewan Guru
                        </span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-white heading-font tracking-tight truncate">
                        {{ $teacher->name }}
                    </h2>
                    <p class="text-xs text-emerald-100/80 font-medium mt-0.5 flex items-center gap-2 flex-wrap">
                        <span>NIP: <strong class="mono-font text-white">{{ $teacher->identity_number }}</strong></span>
                        <span>&bull;</span>
                        <span class="truncate">{{ $teacher->email }}</span>
                    </p>
                </div>
            </div>

            <!-- [DESKTOP ONLY] Live Clock & Date (Right) -->
            <div class="hidden md:flex flex-col items-end shrink-0 pl-6 border-l border-white/15 text-right">
                <div class="liveClockTicker text-2xl lg:text-3xl font-bold text-white mono-font tracking-tight drop-shadow-xs">
                    {{ now()->format('H:i:s') }} <span class="text-xs font-semibold text-emerald-200">WIB</span>
                </div>
                <p class="text-xs text-emerald-100/90 mt-1 font-medium">
                    {{ $todayDay }}, {{ now()->translatedFormat('d F Y') }}
                </p>
            </div>
        </div>

        <!-- Bottom Row: Dedicated Frosted Glass GPS Geofence Pod -->
        <div id="geofenceCard" class="relative z-10 rounded-2xl bg-black/20 sm:bg-white/10 p-3.5 sm:p-4 border border-white/15 backdrop-blur-md shadow-inner flex flex-col md:flex-row md:items-center md:justify-between gap-3 sm:gap-4 text-xs">
            <div class="flex items-center gap-3 sm:gap-3.5 min-w-0 flex-1">
                <div id="geofenceIconBox" class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-white/15 border border-white/25 text-white flex items-center justify-center shrink-0 shadow-sm transition-all">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span id="geofenceTitle" class="font-semibold text-white uppercase tracking-wider text-xs heading-font">
                            Mendeteksi Lokasi GPS...
                        </span>
                        <span id="geofenceDistance" class="font-mono font-bold text-xs px-2.5 py-0.5 rounded-full bg-white text-emerald-950 shadow-xs border border-white">
                            -- m
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-white/15 border border-white/20 text-[10px] sm:text-[11px] text-emerald-100 font-semibold">
                            Batas Radius: {{ $location->radius_meters ?? 75 }} meter
                        </span>
                    </div>
                    <p id="geofenceDesc" class="text-[11px] sm:text-xs text-emerald-100/90 mt-1 leading-snug break-words">
                        Harap izinkan akses lokasi (GPS) pada peramban HP atau laptop Bapak/Ibu Guru.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-white/10">
                <button type="button" onclick="initGeolocation(true)" class="w-full md:w-auto py-2 sm:py-2.5 px-4 rounded-xl bg-white/15 hover:bg-white/25 active:bg-white/30 text-white text-xs font-semibold backdrop-blur-md transition-all duration-150 inline-flex items-center justify-center gap-2 border border-white/25 hover:border-white/40 shadow-sm active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-white whitespace-nowrap">
                    <svg id="gpsRefreshIcon" class="w-4 h-4 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Perbarui Lokasi GPS</span>
                </button>
            </div>
        </div>
    </div>

    <!-- WARNING ALERTS (Di Luar Area & Presensi Masuk Diperlukan) -->
    <!-- Outside Geofence Warning Box (Appears when teacher is outside school radius) -->
    <div id="outsideWarning" class="hidden bg-red-50/95 border border-red-200 rounded-3xl p-6 text-center space-y-4 shadow-xs">
        <div class="w-14 h-14 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mx-auto shadow-xs">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
        </div>
        <div>
            <h3 class="text-base font-semibold text-red-950 heading-font">Di Luar Area Madrasah</h3>
            <p id="outsideDistanceText" class="text-xs text-red-800 mt-1">
                Sistem mendeteksi posisi Bapak/Ibu Guru berada di luar area lingkungan madrasah (maksimal {{ $location->radius_meters ?? 75 }} meter).
            </p>
            <p class="text-xs text-red-700 mt-2 font-medium">
                Pembukaan sesi presensi siswa hanya dapat dilakukan saat Bapak/Ibu Guru telah berada di area madrasah.
            </p>
        </div>
        <button type="button" onclick="initGeolocation(true)" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-red-50 active:bg-red-100 border border-red-200 text-red-700 font-semibold text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
            <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Coba Deteksi Ulang GPS</span>
        </button>
    </div>

    <!-- Outside Check-in Gating Warning Box (If not checked in today at Kiosk) -->
    @if(!$hasCheckedIn)
        <div class="bg-amber-50/90 border border-amber-200 rounded-3xl p-6 text-center space-y-4 shadow-xs">
            <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto shadow-xs">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-amber-950 heading-font">Presensi Masuk Diperlukan</h3>
                <p class="text-xs text-amber-800 mt-1">
                    Bapak/Ibu Guru belum melakukan presensi masuk di Layar Presensi Madrasah hari ini.
                </p>
                <p class="text-xs text-amber-700 mt-2 font-medium">
                    Pembukaan sesi presensi kelas baru dapat dibuka setelah Bapak/Ibu Guru memindai Kode QR di Layar Presensi Madrasah.
                </p>
            </div>
            <a href="{{ route('guru.scan') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold text-xs shadow-md shadow-amber-600/25 transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Pindai QR Presensi Masuk</span>
            </a>
        </div>
    @endif

    <!-- PANDUAN PRESENSI GURU ACCORDION (Collapsible) -->
    <details class="group bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden transition-all duration-200 open:border-emerald-200 open:shadow-sm">
        <summary class="flex items-center justify-between p-4 sm:p-4.5 cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden hover:bg-slate-50/80 transition-colors">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-xs sm:text-sm font-semibold text-slate-800 heading-font">
                            Panduan Presensi Bapak/Ibu Guru
                        </h4>
                        <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                            Petunjuk Alur
                        </span>
                    </div>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-normal mt-0.5 leading-relaxed">
                        Ketuk untuk melihat 4 langkah alur presensi mandiri & kelas
                    </p>
                </div>
            </div>
            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-slate-100 group-hover:bg-slate-200 text-slate-500 flex items-center justify-center shrink-0 ml-2 transition-transform duration-200 group-open:rotate-180">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </summary>
        <div class="px-4 pb-5 pt-2 border-t border-slate-100 text-xs sm:text-sm bg-slate-50/50">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                <div class="p-3 bg-white rounded-xl border border-slate-100 shadow-2xs flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">1</span>
                    <div class="text-slate-600 text-xs leading-relaxed">
                        <strong class="text-slate-900 block mb-0.5">Presensi Masuk</strong>
                        Pastikan berada di area madrasah, lalu pindai Kode QR di Layar Presensi Madrasah.
                    </div>
                </div>
                <div class="p-3 bg-white rounded-xl border border-slate-100 shadow-2xs flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">2</span>
                    <div class="text-slate-600 text-xs leading-relaxed">
                        <strong class="text-slate-900 block mb-0.5">Buka Presensi Kelas</strong>
                        Tentukan durasi (2–5 menit) dan bagikan 4 angka PIN kepada siswa di kelas.
                    </div>
                </div>
                <div class="p-3 bg-white rounded-xl border border-slate-100 shadow-2xs flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">3</span>
                    <div class="text-slate-600 text-xs leading-relaxed">
                        <strong class="text-slate-900 block mb-0.5">Konfirmasi Siswa & Simpan</strong>
                        Periksa siswa tanpa PIN, beri keterangan Izin/Sakit, lalu simpan kehadiran kelas.
                    </div>
                </div>
                <div class="p-3 bg-white rounded-xl border border-slate-100 shadow-2xs flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">4</span>
                    <div class="text-slate-600 text-xs leading-relaxed">
                        <strong class="text-slate-900 block mb-0.5">Presensi Pulang</strong>
                        Setelah seluruh kelas selesai dicatat, lakukan presensi pulang di Layar Presensi Madrasah.
                    </div>
                </div>
            </div>
        </div>
    </details>

    <!-- 2. QUICK METRIC & ATTENDANCE SUMMARY CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Metric 1: Total Jadwal Mengajar -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-slate-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jadwal Mengajar</span>
                <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-baseline space-x-1.5">
                <span class="text-2xl font-bold text-slate-900 mono-font">{{ $schedules->count() }}</span>
                <span class="text-xs font-semibold text-slate-500">Mata Pelajaran</span>
            </div>
        </div>

        <!-- Metric 2: Sesi Aktif -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-amber-200 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Sesi Aktif</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-baseline space-x-1.5">
                <span class="text-2xl font-bold text-amber-700 mono-font">{{ $activeSessionsCount }}</span>
                <span class="text-xs font-semibold text-slate-500">Sesi Terbuka</span>
            </div>
        </div>

        <!-- Metric 3: Tuntas & Disimpan -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-emerald-200 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Selesai & Disimpan</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-baseline space-x-1.5">
                <span class="text-2xl font-bold text-emerald-700 mono-font">{{ $lockedSessionsCount }}</span>
                <span class="text-xs font-semibold text-slate-500">Kelas Selesai</span>
            </div>
        </div>

        <!-- Metric 4: Sisa Kelas Mengajar -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-slate-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sisa Kelas</span>
                <div class="w-8 h-8 rounded-xl {{ $pendingSchedules->count() > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-baseline space-x-1.5">
                <span class="text-2xl font-bold {{ $pendingSchedules->count() > 0 ? 'text-amber-700' : 'text-emerald-700' }} mono-font">{{ $pendingSchedules->count() }}</span>
                <span class="text-xs font-semibold text-slate-500">{{ $pendingSchedules->count() > 0 ? 'Kelas Menunggu' : 'Semua Tuntas' }}</span>
            </div>
        </div>
    </div>

    <!-- 3. MAIN DASHBOARD CONTENT (Single Column Flow) -->
    <div class="space-y-6">
        
        <!-- Active Class Session Card (Appears when there is an active session running) -->
        @if($activeSession)
            <div class="bg-white rounded-3xl p-6 border-2 border-amber-500 shadow-xl shadow-amber-500/10 space-y-5 transition-all">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping mr-2"></span>
                            Sesi Presensi Siswa Sedang Aktif
                        </span>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 heading-font mt-2">
                            {{ $activeSession->schedule->subject->name ?? 'Mata Pelajaran' }}
                        </h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                            Kelas {{ $activeSession->schedule->classroom->name ?? '-' }} &bull; Durasi: {{ $activeSession->duration_minutes }} Menit
                        </p>
                    </div>
                    <div class="text-right pl-4">
                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">KODE PIN</p>
                        <div class="text-3xl sm:text-4xl font-bold text-amber-600 mono-font tracking-widest">
                            {{ $activeSession->pin_code }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between bg-amber-50/80 rounded-2xl p-4 border border-amber-200/80 text-xs">
                    <div class="flex items-center space-x-2 text-amber-800 font-medium">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Berakhir pada: <strong>{{ $activeSession->expires_at->format('H:i:s') }} WIB</strong></span>
                    </div>
                    <span class="font-semibold text-amber-700">
                        {{ $activeSession->attendances->where('status', 'HADIR')->count() }} Siswa Hadir
                    </span>
                </div>

                <a href="{{ route('guru.session.show', $activeSession) }}" class="w-full bg-amber-500 hover:bg-amber-600 active:bg-amber-700 active:scale-[0.98] text-white font-semibold py-3.5 px-4 rounded-xl text-xs inline-flex items-center justify-center gap-2 transition-all duration-150 shadow-md shadow-amber-500/25 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-amber-500">
                    <span>Buka Layar Presensi Kelas (Lihat Siswa Masuk)</span>
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        @endif


        <!-- Today's Teaching Schedule List (Jadwal Mengajar Hari Ini) -->
        <div id="jadwal-mengajar" class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/80 shadow-xs space-y-4 overflow-hidden scroll-mt-20">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3.5 border-b border-slate-100">
                <div class="flex items-start sm:items-center gap-2.5 min-w-0 flex-1">
                    <svg class="w-5 h-5 text-maarif-700 shrink-0 mt-0.5 sm:mt-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-[13px] sm:text-sm font-semibold text-slate-900 heading-font uppercase tracking-wide leading-snug break-words">
                            Jadwal Mengajar Hari Ini ({{ $todayDay }})
                        </h3>
                        <p class="text-xs text-slate-500 leading-snug break-words">Kelola sesi presensi kelas dan pencatatan kehadiran siswa</p>
                    </div>
                </div>
                <a href="{{ route('guru.history') }}" class="inline-flex items-center justify-center sm:justify-end self-start sm:self-center shrink-0 text-xs font-semibold text-maarif-700 hover:text-maarif-800 active:text-maarif-900 bg-maarif-50 sm:bg-transparent border border-maarif-100 sm:border-0 px-3 py-1.5 sm:px-0 sm:py-0 rounded-full sm:rounded-none hover:bg-maarif-100 sm:hover:bg-transparent transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                    Riwayat Kelas &rarr;
                </a>
            </div>

            @if($schedules->isEmpty())
                <div class="py-10 text-center text-slate-400 space-y-2">
                    <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-xs font-medium">Tidak ada jadwal mengajar terjadwal untuk hari {{ $todayDay }}.</p>
                </div>
            @else
                <div class="space-y-3.5">
                    @foreach($schedules as $sch)
                        @php
                            $session = $sch->todaySession;
                            $nowSch = \Carbon\Carbon::now();
                            $todayNameSch = \App\Services\TeacherAttendanceService::getIndonesianDayName($nowSch);
                            $startSch = \Carbon\Carbon::parse($sch->start_time)->setDate($nowSch->year, $nowSch->month, $nowSch->day);
                            $endSch = \Carbon\Carbon::parse($sch->end_time)->setDate($nowSch->year, $nowSch->month, $nowSch->day);
                            $isWithinSchedule = ($sch->day_of_week === $todayNameSch) && $nowSch->between($startSch, $endSch, true);
                        @endphp
                        <div class="p-4 sm:p-5 rounded-2xl border transition-all flex flex-col gap-3.5 {{ $session && $session->status === 'LOCKED' ? 'bg-emerald-50/50 border-emerald-200/80' : ($session && $session->isActive() ? 'bg-amber-50/50 border-amber-200/80 ring-1 ring-amber-300' : 'bg-slate-50/80 border-slate-200/80 hover:bg-slate-100/70') }} overflow-hidden">
                            <div class="flex flex-col gap-3">
                                <!-- Top Row: time pill + class badge (wrap) + title full width -->
                                <div class="flex flex-col gap-2.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-xl bg-white border border-slate-200/90 text-slate-800 font-bold mono-font text-xs shrink-0 shadow-2xs whitespace-nowrap">
                                            {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-maarif-700 text-white font-semibold text-[10px] shrink-0 tracking-wide">
                                            Kelas {{ $sch->classroom->name }}
                                        </span>
                                    </div>
                                    <div class="min-w-0 w-full">
                                        <h4 class="text-[15px] sm:text-sm font-semibold text-slate-900 leading-snug break-words">{{ $sch->subject->name }}</h4>
                                        <p class="text-[11px] text-slate-500 mt-1 mono-font break-words leading-snug">Kode: {{ $sch->subject->code }}</p>
                                    </div>
                                </div>

                                <!-- Session Status Badges — wrap left-aligned, no absolute -->
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @if(!$session)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold bg-white border border-slate-200 text-slate-600 whitespace-nowrap">
                                            Menunggu Dibuka
                                        </span>
                                        @if(!$isWithinSchedule)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 leading-snug break-words max-w-full">
                                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span class="break-words">Di luar jam pelajaran ({{ substr($sch->start_time,0,5) }}–{{ substr($sch->end_time,0,5) }} WIB)</span>
                                            </span>
                                        @endif
                                    @elseif($session->isActive())
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300 animate-pulse leading-none break-words">
                                            ● Sesi Aktif (PIN: {{ $session->pin_code }})
                                        </span>
                                    @elseif($session->isLocked())
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300 leading-none">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 shrink-0"></i>
                                            <span>Selesai & Disimpan</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300 leading-none">
                                            Perlu Konfirmasi Keterangan Siswa
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Action Buttons Area — stacked on mobile, no overlap -->
                            <div class="pt-3.5 border-t border-slate-200/70 flex flex-col gap-2.5">
                                @if(!$session)
                                    @if($hasCheckedIn)
                                        @if(!$isWithinSchedule)
                                            <div class="w-full flex flex-col gap-2.5">
                                                <div class="px-3 py-3 rounded-xl bg-slate-100 border border-slate-200 text-[11px] leading-relaxed text-slate-600 text-center sm:text-left font-medium break-words">
                                                    Sesi hanya dapat dibuka dalam rentang <strong class="text-slate-800">{{ substr($sch->start_time,0,5) }}–{{ substr($sch->end_time,0,5) }} WIB</strong>. Sekarang {{ \Carbon\Carbon::now()->format('H:i') }} WIB — di luar jam pelajaran.
                                                </div>
                                                <form action="{{ route('guru.session.open', $sch) }}" method="POST" data-loading-form class="openSessionForm w-full flex flex-col sm:flex-row sm:items-center gap-2.5 opacity-60">
                                                    @csrf
                                                    <div class="relative w-full sm:w-[148px] shrink-0">
                                                        <select name="duration" disabled class="w-full h-11 block appearance-none text-xs border border-slate-300 rounded-xl pl-3.5 pr-8 bg-slate-100 text-slate-500 font-medium cursor-not-allowed">
                                                            <option value="3">Durasi: 3 Menit</option>
                                                            <option value="2">Durasi: 2 Menit</option>
                                                            <option value="4">Durasi: 4 Menit</option>
                                                            <option value="5">Durasi: 5 Menit</option>
                                                        </select>
                                                        <div class="pointer-events-none absolute inset-y-0 right-0 pr-3 text-slate-400 flex items-center justify-center">
                                                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                                                        </div>
                                                    </div>
                                                    <button type="submit" disabled class="sessionSubmitBtn w-full sm:flex-1 h-11 bg-slate-300 text-slate-500 font-semibold px-4 rounded-xl text-xs flex items-center justify-center gap-1.5 cursor-not-allowed leading-none">
                                                        <span>Di luar jam pelajaran</span>
                                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <form action="{{ route('guru.session.open', $sch) }}" method="POST" data-loading-form class="openSessionForm w-full flex flex-col sm:flex-row sm:items-center gap-2.5">
                                                @csrf
                                                <div class="relative w-full sm:w-[148px] shrink-0">
                                                    <select name="duration" class="w-full h-11 block appearance-none text-xs border border-slate-300 rounded-xl pl-3.5 pr-8 bg-white text-slate-700 focus:ring-2 focus:ring-maarif-600 font-medium cursor-pointer">
                                                        <option value="3">Durasi: 3 Menit</option>
                                                        <option value="2">Durasi: 2 Menit</option>
                                                        <option value="4">Durasi: 4 Menit</option>
                                                        <option value="5">Durasi: 5 Menit</option>
                                                    </select>
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 pr-3 text-slate-500 flex items-center justify-center">
                                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                                                    </div>
                                                </div>
                                                <button type="submit" class="sessionSubmitBtn w-full sm:flex-1 h-11 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 active:scale-[0.98] text-white font-semibold px-4 rounded-xl text-xs inline-flex items-center justify-center gap-1.5 transition-all duration-150 shadow-md shadow-maarif-700/25 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-maarif-600 leading-none">
                                                    <span>Buka Sesi Presensi</span>
                                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <button disabled class="w-full bg-slate-200 text-slate-400 font-semibold py-3 px-3 rounded-xl text-xs cursor-not-allowed leading-snug break-words text-center">
                                            Belum Bisa Dibuka (Silakan Presensi Masuk Terlebih Dahulu)
                                        </button>
                                    @endif
                                @elseif($session->isActive())
                                    <a href="{{ route('guru.session.show', $session) }}" class="w-full bg-amber-500 hover:bg-amber-600 active:bg-amber-700 active:scale-[0.98] text-white font-semibold py-3 sm:py-2.5 px-4 rounded-xl text-xs inline-flex items-center justify-center gap-2 transition-all duration-150 shadow-md shadow-amber-500/25 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-amber-500 leading-none text-center break-words">
                                        <span>Buka Layar Presensi Kelas (PIN: {{ $session->pin_code }})</span>
                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                @elseif($session->isLocked())
                                    <div class="w-full flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
                                        <span class="text-slate-500 leading-snug break-words">Presensi kelas telah selesai dan tersimpan.</span>
                                        <a href="{{ route('guru.session.reconcile', $session) }}" class="inline-flex items-center justify-center sm:justify-end text-maarif-700 hover:text-maarif-800 active:text-maarif-900 font-semibold whitespace-nowrap bg-maarif-50 hover:bg-maarif-100 sm:bg-transparent border border-maarif-100 sm:border-0 rounded-full sm:rounded-none px-3 py-1.5 sm:px-0 sm:py-0 transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                                            Lihat Daftar Kehadiran &rarr;
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ route('guru.session.reconcile', $session) }}" class="w-full bg-rose-600 hover:bg-rose-700 active:bg-rose-800 active:scale-[0.98] text-white font-semibold py-3 sm:py-2.5 px-4 rounded-xl text-xs inline-flex items-center justify-center gap-1.5 transition-all duration-150 shadow-md shadow-rose-600/25 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-rose-600 leading-snug text-center break-words">
                                        <span>Catat Keterangan Siswa & Simpan</span>
                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Teaching Completion Lock & Kiosk Status Card -->
        <div class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/80 shadow-xs space-y-4 overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3.5 border-b border-slate-100">
                <div class="flex items-start sm:items-center gap-2.5 min-w-0 flex-1">
                    <svg class="w-5 h-5 {{ $pendingSchedules->isEmpty() && $hasCheckedIn ? 'text-emerald-600' : 'text-amber-600' }} shrink-0 mt-0.5 sm:mt-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wide heading-font leading-snug break-words">
                            Status Presensi Pulang
                        </h3>
                        <p class="text-xs text-slate-500 leading-snug break-words">Kelengkapan tugas mengajar sebelum pulang</p>
                    </div>
                </div>
                <span class="text-xs font-semibold px-3 py-1.5 rounded-full {{ $pendingSchedules->isEmpty() && $hasCheckedIn ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }} shrink-0 inline-flex items-center gap-1 self-start sm:self-center whitespace-nowrap">
                    @if($pendingSchedules->isEmpty() && $hasCheckedIn)
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                        <span>SIAP PRESENSI PULANG</span>
                    @else
                        <span>BELUM BISA PULANG</span>
                    @endif
                </span>
            </div>

            <div class="space-y-3 text-xs">
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 font-medium">Presensi Masuk:</span>
                        @if($hasCheckedIn)
                            <span class="font-semibold text-emerald-700 font-mono">{{ substr($dailyAttendance->check_in_time ?? '', 0, 5) }} WIB ({{ $dailyAttendance->check_in_status ?? 'HADIR' }})</span>
                        @else
                            <span class="font-semibold text-rose-600">Belum Datang</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 font-medium">Kelas Belum Disimpan:</span>
                        <span class="font-semibold {{ $pendingSchedules->isEmpty() ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $pendingSchedules->count() }} Kelas
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 font-medium">Presensi Pulang:</span>
                        @if($dailyAttendance?->check_out_time)
                            <span class="font-semibold text-emerald-700 font-mono">{{ substr($dailyAttendance->check_out_time, 0, 5) }} WIB</span>
                        @else
                            <span class="font-semibold text-slate-400">Belum Pulang</span>
                        @endif
                    </div>
                </div>

                <p class="text-[11px] text-slate-500 leading-relaxed">
                    @if($pendingSchedules->isNotEmpty())
                        Ada <strong>{{ $pendingSchedules->count() }}</strong> kelas yang belum selesai disimpan keterangannya. Bapak/Ibu Guru perlu menyelesaikan pencatatan kehadiran kelas terlebih dahulu sebelum dapat presensi pulang di Layar Presensi Madrasah.
                    @else
                        Seluruh kelas mengajar hari ini telah selesai dicatat. Bapak/Ibu Guru sudah dapat melakukan presensi pulang di Layar Presensi Madrasah.
                    @endif
                </p>

                <!-- Quick Action Button to Kiosk Scan -->
                <div class="pt-2">
                    @if(!$hasCheckedIn)
                        <a href="{{ route('guru.scan') }}" class="w-full py-2.5 px-4 rounded-xl bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold text-xs inline-flex items-center justify-center gap-2 shadow-md shadow-amber-600/25 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-amber-500">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            <span>Pindai QR Presensi Masuk</span>
                        </a>
                    @else
                        <a href="{{ route('guru.scan') }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white font-semibold text-xs inline-flex items-center justify-center gap-2 shadow-md shadow-slate-800/25 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-slate-700">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            <span>Pindai QR Presensi Pulang</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Today's Session Log Feed (Aktivitas Sesi Hari Ini) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center space-x-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wide heading-font">
                            Riwayat Kelas Hari Ini
                        </h3>
                        <p class="text-xs text-slate-500">Catatan kehadiran mengajar hari ini</p>
                    </div>
                </div>
                <span class="text-xs font-semibold text-slate-500 px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200 shrink-0">
                    {{ $schedules->filter(fn($s) => $s->todaySession)->count() }} Tercatat
                </span>
            </div>

            @php
                $recordedSessions = $schedules->filter(fn($s) => $s->todaySession);
            @endphp

            @if($recordedSessions->isEmpty())
                <div class="text-center py-8 text-slate-400">
                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-xs font-medium">Belum ada sesi kelas yang dibuka hari ini.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($recordedSessions as $sch)
                        @php
                            $sess = $sch->todaySession;
                            $hadirCount = $sess->attendances->where('status', 'HADIR')->count();
                            $izinCount = $sess->attendances->whereIn('status', ['IZIN', 'SAKIT'])->count();
                            $alpaCount = $sess->attendances->where('status', 'ALPA')->count();
                        @endphp
                        <div class="p-3.5 rounded-2xl border border-slate-100 bg-slate-50/60 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-semibold text-slate-900">{{ $sch->subject->name }}</span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-200 font-semibold text-[10px] text-slate-700">
                                        {{ $sch->classroom->name }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    Mulai: {{ $sess->started_at?->format('H:i') }} WIB &bull; PIN: <span class="font-mono font-semibold text-slate-700">{{ $sess->pin_code }}</span>
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold text-[10px] {{ $sess->isLocked() ? 'bg-emerald-100 text-emerald-800' : ($sess->isActive() ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $sess->status }}
                                </span>
                                <p class="text-[10px] text-slate-500 mt-0.5">
                                    {{ $hadirCount }} Hadir &bull; {{ $izinCount }} Izin &bull; {{ $alpaCount }} Alpa
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Live Digital Clock Ticker
    function updateLiveClock() {
        const now = (typeof window.getServerNow === 'function') ? window.getServerNow() : new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const timeStrDesktop = `${hours}:${minutes}:${seconds} <span class="text-xs font-semibold text-emerald-200">WIB</span>`;
        const timeStrMobile = `${hours}:${minutes}:${seconds} <span class="text-[10px] font-semibold text-emerald-300">WIB</span>`;

        document.querySelectorAll('.liveClockTicker').forEach(el => {
            if (el.closest('.md\\:hidden')) {
                el.innerHTML = timeStrMobile;
            } else {
                el.innerHTML = timeStrDesktop;
            }
        });
    }
    setInterval(updateLiveClock, 1000);
    updateLiveClock();

    // Geolocation Engine
    let currentCoords = { lat: null, lng: null };
    const geofenceIconBox = document.getElementById('geofenceIconBox');
    const geofenceTitle = document.getElementById('geofenceTitle');
    const geofenceDesc = document.getElementById('geofenceDesc');
    const geofenceDistance = document.getElementById('geofenceDistance');
    const outsideWarning = document.getElementById('outsideWarning');
    const outsideDistanceText = document.getElementById('outsideDistanceText');

    function initGeolocation(isManual = false) {
        const refreshIcon = document.getElementById('gpsRefreshIcon');
        if (isManual && refreshIcon) {
            refreshIcon.classList.add('animate-spin');
            setTimeout(() => refreshIcon.classList.remove('animate-spin'), 1500);
        }

        if (!navigator.geolocation) {
            if (geofenceTitle) geofenceTitle.textContent = "GPS Tidak Didukung";
            if (geofenceDesc) geofenceDesc.textContent = "Browser perangkat Anda tidak mendukung geolokasi.";
            return;
        }

        const handlePosition = (pos) => {
            currentCoords.lat = pos.coords.latitude;
            currentCoords.lng = pos.coords.longitude;
            checkServerStatus();
        };

        const handleError = (err) => {
            if (err.code === 1) { // PERMISSION_DENIED
                if (geofenceIconBox) {
                    geofenceIconBox.className = "w-10 h-10 rounded-xl bg-red-600 border border-red-500 text-white flex items-center justify-center shrink-0 shadow-md transition-all";
                }
                if (geofenceTitle) {
                    geofenceTitle.textContent = "Izin Lokasi Ditolak";
                    geofenceTitle.className = "font-semibold text-white uppercase tracking-wider text-xs heading-font";
                }
                if (geofenceDesc) {
                    geofenceDesc.textContent = "Harap izinkan akses lokasi (GPS) pada peramban HP atau laptop Bapak/Ibu Guru.";
                }
            } else if (err.code === 2) { // POSITION_UNAVAILABLE
                if (geofenceIconBox) {
                    geofenceIconBox.className = "w-10 h-10 rounded-xl bg-amber-500 border border-amber-400 text-white flex items-center justify-center shrink-0 shadow-md animate-pulse transition-all";
                }
                if (geofenceTitle) {
                    geofenceTitle.textContent = "Lokasi Tidak Terdeteksi";
                    geofenceTitle.className = "font-semibold text-white uppercase tracking-wider text-xs heading-font";
                }
                if (geofenceDesc) {
                    geofenceDesc.textContent = "Pastikan GPS aktif dan perangkat terhubung ke internet.";
                }
            } else if (err.code === 3) { // TIMEOUT
                if (!currentCoords.lat) {
                    if (geofenceIconBox) {
                        geofenceIconBox.className = "w-10 h-10 rounded-xl bg-amber-500 border border-amber-400 text-white flex items-center justify-center shrink-0 shadow-md animate-pulse transition-all";
                    }
                    if (geofenceTitle) {
                        geofenceTitle.textContent = "Mencari Lokasi GPS...";
                        geofenceTitle.className = "font-semibold text-white uppercase tracking-wider text-xs heading-font";
                    }
                    if (geofenceDesc) {
                        geofenceDesc.textContent = "Sedang memeriksa posisi madrasah. Harap tunggu beberapa saat...";
                    }
                }
            }
        };

        // Tier 1: Fast initial fix
        navigator.geolocation.getCurrentPosition(
            handlePosition,
            () => {},
            { enableHighAccuracy: false, timeout: 6000, maximumAge: 60000 }
        );

        // Tier 2: Refine with high accuracy
        navigator.geolocation.watchPosition(
            handlePosition,
            handleError,
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 }
        );
    }

    async function checkServerStatus() {
        if (!currentCoords.lat || !currentCoords.lng) return;

        try {
            const res = await fetch("{{ route('guru.check-status', [], false) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    latitude: currentCoords.lat,
                    longitude: currentCoords.lng
                })
            });

            if (res.ok) {
                const data = await res.json();
                updateUIState(data);
            }
        } catch(e) {
            console.warn("Gagal mengecek status lokasi ke server:", e);
        }
    }

    function updateUIState(data) {
        if (data.is_within_geofence) {
            if (geofenceIconBox) {
                geofenceIconBox.className = "w-10 h-10 rounded-xl bg-emerald-500 border border-emerald-400 text-white flex items-center justify-center shrink-0 shadow-md transition-all";
            }
            if (geofenceTitle) {
                geofenceTitle.textContent = "Di Lingkungan Madrasah";
                geofenceTitle.className = "font-semibold text-white uppercase tracking-wider text-xs heading-font";
            }
            if (geofenceDesc) {
                geofenceDesc.textContent = `Posisi Bapak/Ibu Guru terverifikasi berada di dalam area ${data.school_name || "MA Ma'arif Cilageni"}.`;
            }
            if (geofenceDistance) {
                geofenceDistance.textContent = Math.round(data.distance) + "m";
                geofenceDistance.className = "font-mono font-bold text-xs px-2.5 py-0.5 rounded-full bg-white text-emerald-900 shadow-xs border border-white";
            }

            if (outsideWarning) outsideWarning.classList.add('hidden');

            // Enable open session buttons
            document.querySelectorAll('.sessionSubmitBtn').forEach(btn => {
                btn.removeAttribute('disabled');
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        } else {
            if (geofenceIconBox) {
                geofenceIconBox.className = "w-10 h-10 rounded-xl bg-red-600 border border-red-500 text-white flex items-center justify-center shrink-0 shadow-md transition-all";
            }
            if (geofenceTitle) {
                geofenceTitle.textContent = "Di Luar Area Madrasah";
                geofenceTitle.className = "font-semibold text-white uppercase tracking-wider text-xs heading-font";
            }
            if (geofenceDesc) {
                geofenceDesc.textContent = `Posisi Bapak/Ibu Guru berada di luar area madrasah (maksimal ${data.radius} meter).`;
            }
            if (geofenceDistance) {
                const distText = data.distance > 1000 ? (data.distance / 1000).toFixed(1) + "km" : Math.round(data.distance) + "m";
                geofenceDistance.textContent = distText;
                geofenceDistance.className = "font-mono font-bold text-xs px-2.5 py-0.5 rounded-full bg-red-600 text-white shadow-xs border border-red-500";
            }

            if (outsideWarning) {
                outsideWarning.classList.remove('hidden');
                if (outsideDistanceText) {
                    const distText = data.distance > 1000 ? (data.distance / 1000).toFixed(1) + " kilometer" : Math.round(data.distance) + " meter";
                    outsideDistanceText.textContent = `Posisi Bapak/Ibu Guru berjarak ${distText} dari ${data.school_name || "MA Ma'arif Cilageni"} (Jarak maksimal yang diizinkan: ${data.radius} meter).`;
                }
            }

            // Disable open session buttons if outside geofence
            document.querySelectorAll('.sessionSubmitBtn').forEach(btn => {
                btn.setAttribute('disabled', 'disabled');
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            });
        }
    }

    // Auto-initiate Geolocation on page load
    document.addEventListener('DOMContentLoaded', () => {
        initGeolocation(false);
    });

    window.addEventListener('dev-location-changed', () => {
        initGeolocation(true);
    });
</script>
@endpush

<!-- Spacer to prevent floating simulator + bottom nav overlap on mobile -->
<div class="h-6 md:hidden" aria-hidden="true"></div>
@endsection
