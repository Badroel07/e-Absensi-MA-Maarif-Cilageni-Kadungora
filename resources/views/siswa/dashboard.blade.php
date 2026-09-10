@extends('layouts.app')

@section('title', 'Dashboard — MA Ma\'arif Cilageni')

@push('styles')
<style>
    .keypad-btn {
        height: 52px;
        font-size: 1.25rem;
        font-weight: 700;
        font-family: 'JetBrains Mono', monospace;
    }
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
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Dashboard</h1>
            <p class="text-xs sm:text-sm text-slate-500">Presensi kelas dan informasi kehadiran harian siswa</p>
        </div>
    </div>

    <!-- 1. HERO GREETING & PROFILE BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-maarif-900 via-maarif-800 to-emerald-950 text-white p-5 sm:p-7 shadow-xl border border-emerald-600/30 flex flex-col gap-4 sm:gap-5">
        <!-- Background Ambient Glow -->
        <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-emerald-400/20 blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -bottom-16 w-56 h-56 rounded-full bg-emerald-600/25 blur-2xl pointer-events-none"></div>        <!-- [MOBILE ONLY] Top Row: Date & Live Digital Clock Capsule (< md) -->
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
                <div class="liveClockTicker text-xs font-black mono-font tracking-wider">
                    {{ now()->format('H:i:s') }} <span class="text-[10px] font-bold text-emerald-300">WIB</span>
                </div>
            </div>
        </div>

        <!-- MAIN HERO ROW: Profile (Left) & Desktop Clock (Right) -->
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Student Identity (Left) -->
            <div class="flex items-center gap-4">
                <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-white/20 to-white/5 border border-white/25 text-white flex items-center justify-center font-black text-2xl sm:text-3xl shadow-lg shrink-0 backdrop-blur-md overflow-hidden ring-2 ring-white/15">
                    @if($student->profile_photo_url)
                        <img src="{{ $student->profile_photo_url }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                    @else
                        <span class="select-none">{{ substr($student->name, 0, 1) }}</span>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="px-2 py-0.5 rounded-md bg-emerald-500/30 border border-emerald-400/40 text-[11px] font-bold text-emerald-200">
                            Kelas {{ $student->classroom->name ?? '-' }}
                        </span>
                        <span class="text-[11px] text-emerald-200/80 font-medium">
                            TA {{ $student->classroom->academic_year ?? '2026/2027' }}
                        </span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-white heading-font tracking-tight truncate">
                        {{ $student->name }}
                    </h2>
                    <p class="text-xs text-emerald-100/80 font-medium mt-0.5 flex items-center gap-1.5">
                        <span>NISN:</span>
                        <span class="mono-font font-bold text-white tracking-wider">{{ $student->identity_number }}</span>
                    </p>
                </div>
            </div>

            <!-- [DESKTOP ONLY] Live Clock & Date (Right) -->
            <div class="hidden md:flex flex-col items-end shrink-0 pl-6 border-l border-white/15 text-right">
                <div id="liveClockDisplay" class="liveClockTicker text-2xl lg:text-3xl font-black text-white mono-font tracking-tight drop-shadow-xs">
                    {{ now()->format('H:i:s') }} <span class="text-xs font-bold text-emerald-200">WIB</span>
                </div>
                <p class="text-xs text-emerald-100/90 mt-1 font-medium">
                    {{ $todayDay }}, {{ now()->translatedFormat('d F Y') }}
                </p>
            </div>
        </div>

        <!-- Bottom Row: Dedicated Frosted Glass GPS Pod -->
        <div id="geofenceCard" class="relative z-10 rounded-2xl bg-black/20 sm:bg-white/10 p-3.5 sm:p-4 border border-white/15 backdrop-blur-md shadow-inner flex flex-col md:flex-row md:items-center md:justify-between gap-3 sm:gap-4 text-xs">
            <div class="flex items-center gap-3 sm:gap-3.5 min-w-0 flex-1">
                <div id="geofenceIconBox" class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-white/15 border border-white/25 text-white flex items-center justify-center shrink-0 shadow-sm transition-all">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span id="geofenceTitle" class="font-bold text-white uppercase tracking-wider text-xs heading-font">
                            Mendeteksi Lokasi GPS...
                        </span>
                        <span id="geofenceDistance" class="font-mono font-black text-xs px-2.5 py-0.5 rounded-full bg-white text-emerald-950 shadow-xs border border-white">
                            -- m
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-white/15 border border-white/20 text-[10px] sm:text-[11px] text-emerald-100 font-semibold">
                            Batas Radius: {{ $location->radius_meters ?? 75 }} meter
                        </span>
                    </div>
                    <p id="geofenceDesc" class="text-[11px] sm:text-xs text-emerald-100/90 mt-1 leading-snug break-words">
                        Harap izinkan akses lokasi (GPS) pada peramban HP Anda.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-white/10">
                <button type="button" onclick="initGeolocation(true)" class="w-full md:w-auto py-2 sm:py-2.5 px-4 rounded-xl bg-white/15 hover:bg-white/25 active:bg-white/30 text-white text-xs font-bold backdrop-blur-md transition-all duration-150 inline-flex items-center justify-center gap-2 border border-white/25 hover:border-white/40 shadow-sm active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-white whitespace-nowrap">
                    <svg id="gpsRefreshIcon" class="w-4 h-4 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Perbarui Lokasi GPS</span>
                </button>
            </div>
        </div>
    </div>

    <!-- WARNING ALERTS (Di Luar Area & Status Rombel) -->
    <!-- Outside Geofence Warning Box -->
    <div id="outsideWarning" class="hidden bg-red-50/95 border border-red-200 rounded-3xl p-6 text-center space-y-4 shadow-xs">
        <div class="w-14 h-14 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mx-auto shadow-xs">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
        </div>
        <div>
            <h3 class="text-base font-bold text-red-950 heading-font">Di Luar Area Madrasah</h3>
            <p id="outsideDistanceText" class="text-xs text-red-800 mt-1">
                Sistem mendeteksi posisi kamu berada di luar area madrasah (maksimal {{ $location->radius_meters ?? 75 }} meter).
            </p>
            <p class="text-xs text-red-700 mt-2 font-medium">
                Presensi hanya dapat dilakukan jika kamu berada di dalam area lingkungan madrasah.
            </p>
        </div>
        <button type="button" onclick="initGeolocation(true)" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-red-50 active:bg-red-100 border border-red-200 text-red-700 font-bold text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
            <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Coba Deteksi Ulang GPS</span>
        </button>
    </div>

    @if(!$student->classroom_id)
        <div class="bg-amber-50/90 border border-amber-200 rounded-3xl p-6 text-center space-y-4 shadow-xs">
            <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto shadow-xs">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-amber-950 heading-font">Belum Terdaftar di Kelas</h3>
                <p class="text-xs text-amber-800 mt-1">
                    Akun kamu belum didaftarkan ke dalam rombel/kelas aktif madrasah.
                </p>
                <p class="text-xs text-amber-700 mt-2 font-medium">
                    Silakan hubungi wali kelas atau staf Tata Usaha (TU) madrasah untuk penempatan rombel kelas.
                </p>
            </div>
        </div>
    @endif

    <!-- PETUNJUK PRESENSI SISWA ACCORDION (Collapsible) -->
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
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 heading-font">
                            Petunjuk Presensi Siswa
                        </h4>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                            Petunjuk Alur
                        </span>
                    </div>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-normal truncate mt-0.5">
                        Ketuk untuk melihat 3 langkah mudah presensi kehadiran di kelas
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2">
                <div class="p-3 bg-white rounded-xl border border-slate-100 shadow-2xs flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold flex items-center justify-center shrink-0 mt-0.5">1</span>
                    <div class="text-slate-600 text-xs leading-relaxed">
                        <strong class="text-slate-900 block mb-0.5">Berada di Madrasah</strong>
                        Presensi hanya dapat dilakukan saat kamu sudah berada di dalam lingkungan madrasah.
                    </div>
                </div>
                <div class="p-3 bg-white rounded-xl border border-slate-100 shadow-2xs flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold flex items-center justify-center shrink-0 mt-0.5">2</span>
                    <div class="text-slate-600 text-xs leading-relaxed">
                        <strong class="text-slate-900 block mb-0.5">Masukkan PIN</strong>
                        Ketik 4 angka PIN yang dibagikan oleh Bapak/Ibu Guru saat jam pelajaran dimulai.
                    </div>
                </div>
                <div class="p-3 bg-white rounded-xl border border-slate-100 shadow-2xs flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold flex items-center justify-center shrink-0 mt-0.5">3</span>
                    <div class="text-slate-600 text-xs leading-relaxed">
                        <strong class="text-slate-900 block mb-0.5">Kehadiran Otomatis</strong>
                        Presensi pada jam pertama otomatis mencatat kehadiran masuk madrasah hari ini.
                    </div>
                </div>
            </div>
        </div>
    </details>

    <!-- 2. QUICK METRIC & ATTENDANCE SUMMARY CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Metric 1: Total Mapel Hari Ini -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-slate-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Jadwal Hari Ini</span>
                <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-baseline space-x-1.5">
                <span class="text-2xl font-black text-slate-900 mono-font">{{ $todaySchedules->count() }}</span>
                <span class="text-xs font-semibold text-slate-500">Mata Pelajaran</span>
            </div>
        </div>

        <!-- Metric 2: Hadir Hari Ini -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-emerald-200 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Hadir</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-baseline space-x-1.5">
                <span class="text-2xl font-black text-emerald-700 mono-font">{{ $hadirCount }}</span>
                <span class="text-xs font-semibold text-slate-500">Mata Pelajaran</span>
            </div>
        </div>

        <!-- Metric 3: Izin / Sakit -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-amber-200 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Izin / Sakit</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-baseline space-x-1.5">
                <span class="text-2xl font-black text-amber-700 mono-font">{{ $izinCount + $sakitCount }}</span>
                <span class="text-xs font-semibold text-slate-500">Keterangan Izin</span>
            </div>
        </div>

        <!-- Metric 4: Alpa -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-rose-200 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-rose-700 uppercase tracking-wider">Alpa</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-baseline space-x-1.5">
                <span class="text-2xl font-black text-rose-700 mono-font">{{ $alpaCount }}</span>
                <span class="text-xs font-semibold text-slate-500">Tanpa Keterangan</span>
            </div>
        </div>
    </div>

    <!-- 3. MAIN DASHBOARD CONTENT (Single Column Flow) -->
    <div class="space-y-6">
        
        <!-- Active Class Session Card (Appears when session is opened by teacher) -->
        <div id="sessionCard" class="hidden bg-white rounded-3xl p-6 border-2 border-maarif-600 shadow-xl shadow-maarif-700/10 space-y-6 transition-all duration-300">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="min-w-0 flex-1">
                    <h3 id="sessionSubject" class="text-xl sm:text-2xl font-black text-slate-900 heading-font truncate">
                        --
                    </h3>
                    <p id="sessionTeacher" class="text-xs text-slate-500 font-medium mt-0.5 truncate">Bapak/Ibu Guru: --</p>
                </div>
                <div class="text-right pl-4 shrink-0">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Sisa Waktu</p>
                    <div id="sessionCountdown" class="text-2xl sm:text-3xl font-black text-amber-600 mono-font">
                        --:--
                    </div>
                </div>
            </div>

            <!-- PIN Input Keypad Section -->
            <div id="pinSection" class="space-y-4">
                <div class="text-center">
                    <label class="text-xs font-bold text-slate-700">Masukkan 4 Angka PIN dari Bapak/Ibu Guru</label>
                    <p class="text-[11px] text-slate-400 mt-0.5">Ketik angka PIN yang disebutkan oleh Bapak/Ibu Guru</p>
                    
                    <!-- 4-Digit Display Boxes -->
                    <div class="flex items-center justify-center gap-3 sm:gap-4 my-4">
                        <div id="digit0" class="w-14 h-16 sm:w-16 sm:h-20 rounded-2xl border-2 border-maarif-600 bg-white shadow-xs flex items-center justify-center text-2xl sm:text-3xl font-extrabold text-slate-800 mono-font shrink-0 ring-2 ring-maarif-500/30 transition-all"></div>
                        <div id="digit1" class="w-14 h-16 sm:w-16 sm:h-20 rounded-2xl border-2 border-slate-300 bg-slate-50/50 shadow-xs flex items-center justify-center text-2xl sm:text-3xl font-extrabold text-slate-800 mono-font shrink-0 transition-all"></div>
                        <div id="digit2" class="w-14 h-16 sm:w-16 sm:h-20 rounded-2xl border-2 border-slate-300 bg-slate-50/50 shadow-xs flex items-center justify-center text-2xl sm:text-3xl font-extrabold text-slate-800 mono-font shrink-0 transition-all"></div>
                        <div id="digit3" class="w-14 h-16 sm:w-16 sm:h-20 rounded-2xl border-2 border-slate-300 bg-slate-50/50 shadow-xs flex items-center justify-center text-2xl sm:text-3xl font-extrabold text-slate-800 mono-font shrink-0 transition-all"></div>
                    </div>
                    <p id="pinFeedback" class="text-xs font-semibold text-rose-600 h-5"></p>
                </div>

                <!-- Ergonomic Touch Keypad (3x4 Grid) -->
                <div class="grid grid-cols-3 gap-2.5 pt-1 max-w-xs mx-auto">
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9] as $n)
                        <button type="button" onclick="pressKey('{{ $n }}')" class="keypad-btn bg-slate-100 hover:bg-slate-200 active:bg-slate-300 active:scale-95 text-slate-800 rounded-2xl border border-slate-200 shadow-2xs transition-all duration-150 flex items-center justify-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                            {{ $n }}
                        </button>
                    @endforeach
                    <button type="button" onclick="clearPin()" aria-label="Hapus semua angka PIN" class="keypad-btn bg-slate-100 hover:bg-slate-200 active:bg-slate-300 active:scale-95 text-slate-500 text-sm font-bold rounded-2xl border border-slate-200 shadow-2xs transition-all duration-150 flex items-center justify-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        C
                    </button>
                    <button type="button" onclick="pressKey('0')" class="keypad-btn bg-slate-100 hover:bg-slate-200 active:bg-slate-300 active:scale-95 text-slate-800 rounded-2xl border border-slate-200 shadow-2xs transition-all duration-150 flex items-center justify-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                        0
                    </button>
                    <button type="button" onclick="backspacePin()" aria-label="Hapus satu angka" class="keypad-btn bg-slate-100 hover:bg-slate-200 active:bg-slate-300 active:scale-95 text-slate-700 rounded-2xl border border-slate-200 shadow-2xs transition-all duration-150 flex items-center justify-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-7.172a2 2 0 00-1.414.586L3 12z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Success Verification Display -->
            <div id="successSection" class="hidden py-6 text-center space-y-3">
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-md">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-lg font-extrabold text-slate-900 heading-font">Kehadiran Berhasil Dicatat!</h4>
                    <p class="text-sm text-emerald-700 font-bold mt-0.5">Kamu tercatat: <strong>HADIR</strong> pada mata pelajaran ini.</p>
                    <p class="text-xs text-slate-500 mt-1">Presensi pada jam pertama otomatis mencatat kehadiran harian kamu.</p>
                </div>
            </div>
        </div>


        <!-- Standby / No Active Session Notice (Refined Hub State) -->
        <div id="noSessionNotice" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs text-center space-y-3 relative overflow-hidden">
            <div class="flex justify-center text-maarif-700">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <div class="max-w-md mx-auto space-y-1">
                <h4 class="text-base font-bold text-slate-900 heading-font">Belum Ada Presensi Kelas yang Dibuka</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Bapak/Ibu Guru belum membuka presensi untuk kelas <strong>{{ $student->classroom->name ?? 'kamu' }}</strong>. Halaman ini akan otomatis menampilkan kotak PIN ketika presensi dibuka.
                </p>
            </div>
        </div>

        <!-- Today's Class Schedule (Jadwal Pelajaran Hari Ini) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center space-x-2.5">
                    <svg class="w-5 h-5 text-maarif-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 heading-font uppercase tracking-wide">
                            Jadwal Pelajaran Hari Ini ({{ $todayDay }})
                        </h3>
                        <p class="text-xs text-slate-500">Kelas {{ $student->classroom->name ?? '-' }} &bull; MA Ma'arif Cilageni</p>
                    </div>
                </div>
                <a href="{{ route('siswa.schedule') }}" class="inline-flex items-center gap-1 text-xs font-bold text-maarif-700 hover:text-maarif-800 active:text-maarif-900 bg-white border border-slate-200 px-2.5 py-1 rounded-lg transition-all duration-150 active:scale-95 shadow-2xs shrink-0 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                    <span>Lihat Mingguan</span>
                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </a>
            </div>

            @if($todaySchedules->isEmpty())
                <div class="py-8 text-center text-slate-400 space-y-2">
                    <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-xs font-medium">Tidak ada jadwal pelajaran terjadwal untuk hari {{ $todayDay }}.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($todaySchedules as $sch)
                        @php
                            $matchedAttendance = $todayAttendances->firstWhere('schedule_id', $sch->id);
                        @endphp
                        <div class="p-4 rounded-2xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $matchedAttendance ? ($matchedAttendance->status === 'HADIR' ? 'bg-emerald-50/50 border-emerald-200/80' : 'bg-amber-50/50 border-amber-200/80') : 'bg-slate-50/80 border-slate-200/80 hover:bg-slate-100/70' }}">
                            <div class="flex items-center space-x-3.5">
                                <span class="px-2.5 py-1.5 rounded-xl bg-white border border-slate-200/90 text-slate-800 font-extrabold mono-font text-xs shrink-0 shadow-2xs">
                                    {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}
                                </span>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 leading-snug">{{ $sch->subject->name }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $sch->teacher->name }}</p>
                                </div>
                            </div>

                            <div class="self-end sm:self-center shrink-0">
                                @if($matchedAttendance)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold {{ $matchedAttendance->status === 'HADIR' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($matchedAttendance->status === 'IZIN' ? 'bg-amber-100 text-amber-800 border border-amber-200' : ($matchedAttendance->status === 'SAKIT' ? 'bg-sky-100 text-sky-800 border border-sky-200' : 'bg-rose-100 text-rose-800 border border-rose-200')) }}">
                                        @if($matchedAttendance->status === 'HADIR')
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @endif
                                        {{ $matchedAttendance->status }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold bg-white border border-slate-200 text-slate-600">
                                        Menunggu Sesi
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Today's Attendance Feed (Riwayat Kehadiran Hari Ini) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center space-x-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide heading-font">
                            Riwayat Kehadiran Hari Ini
                        </h3>
                        <p class="text-xs text-slate-500">Catatan kehadiran pada jam pelajaran hari ini</p>
                    </div>
                </div>
                <span class="text-xs font-semibold text-slate-500 px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200 shrink-0">
                    {{ $todayAttendances->count() }} Tercatat
                </span>
            </div>

            @if($todayAttendances->isEmpty())
                <div class="text-center py-8 text-slate-400">
                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-xs font-medium">Belum ada rekaman kehadiran pelajaran hari ini.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($todayAttendances as $att)
                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80 flex items-center justify-between text-xs hover:bg-slate-100/70 transition">
                            <div>
                                <p class="font-bold text-slate-900 text-sm leading-snug">{{ $att->schedule->subject->name ?? 'Mata Pelajaran' }}</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $att->schedule->teacher->name ?? 'Guru' }} &bull; {{ $att->verified_at ? $att->verified_at->format('H:i') . ' WIB' : 'Pukul ' . substr($att->schedule->start_time ?? '', 0, 5) }}
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold shrink-0 ml-2 {{ $att->status === 'HADIR' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($att->status === 'IZIN' ? 'bg-amber-100 text-amber-800 border border-amber-200' : ($att->status === 'SAKIT' ? 'bg-sky-100 text-sky-800 border border-sky-200' : 'bg-rose-100 text-rose-800 border border-rose-200')) }}">
                                {{ $att->status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let currentCoords = { lat: null, lng: null };
    let enteredPin = "";
    let activeSession = null;
    let countdownTimer = null;
    let remainingSec = 0;

    const geofenceCard = document.getElementById('geofenceCard');
    const geofenceIconBox = document.getElementById('geofenceIconBox');
    const geofenceDot = document.getElementById('geofenceDot');
    const geofenceTitle = document.getElementById('geofenceTitle');
    const geofenceDesc = document.getElementById('geofenceDesc');
    const geofenceDistance = document.getElementById('geofenceDistance');

    const sessionCard = document.getElementById('sessionCard');
    const outsideWarning = document.getElementById('outsideWarning');
    const outsideDistanceText = document.getElementById('outsideDistanceText');
    const noSessionNotice = document.getElementById('noSessionNotice');

    const sessionSubject = document.getElementById('sessionSubject');
    const sessionTeacher = document.getElementById('sessionTeacher');
    const sessionCountdown = document.getElementById('sessionCountdown');
    const pinSection = document.getElementById('pinSection');
    const successSection = document.getElementById('successSection');
    const pinFeedback = document.getElementById('pinFeedback');

    // Live Clock Ticker
    function startLiveClock() {
        setInterval(() => {
            const now = (typeof window.getServerNow === 'function') ? window.getServerNow() : new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const html = `${h}:${m}:${s} <span class="text-xs font-semibold text-emerald-300">WIB</span>`;
            document.querySelectorAll('.liveClockTicker').forEach(el => {
                el.innerHTML = html;
            });
        }, 1000);
    }

    // Geolocation with manual refresh animation support
    function initGeolocation(isManual = false) {
        const refreshIcon = document.getElementById('gpsRefreshIcon');
        if (isManual && refreshIcon) {
            refreshIcon.classList.add('animate-spin');
            setTimeout(() => refreshIcon.classList.remove('animate-spin'), 1500);
        }

        if (!navigator.geolocation) {
            if (geofenceTitle) geofenceTitle.textContent = "GPS Tidak Didukung";
            if (geofenceDesc) geofenceDesc.textContent = "Browser perangkat Anda tidak mendukung geolokasi.";
            if (heroGpsText) heroGpsText.textContent = "GPS Error";
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
                    geofenceIconBox.className = "w-11 h-11 rounded-2xl bg-red-600 border border-red-500 text-white flex items-center justify-center shrink-0 shadow-md transition-all";
                }
                if (geofenceTitle) {
                    geofenceTitle.textContent = "Izin Lokasi Ditolak";
                    geofenceTitle.className = "font-black text-white uppercase tracking-wider text-xs heading-font";
                }
                if (geofenceDesc) geofenceDesc.textContent = "Harap izinkan akses lokasi (GPS) pada browser HP Anda.";
            } else if (err.code === 2) { // POSITION_UNAVAILABLE
                if (geofenceIconBox) {
                    geofenceIconBox.className = "w-11 h-11 rounded-2xl bg-amber-500 border border-amber-400 text-white flex items-center justify-center shrink-0 shadow-md animate-pulse transition-all";
                }
                if (geofenceTitle) {
                    geofenceTitle.textContent = "Lokasi Tidak Terdeteksi";
                    geofenceTitle.className = "font-black text-white uppercase tracking-wider text-xs heading-font";
                }
                if (geofenceDesc) geofenceDesc.textContent = "Pastikan GPS aktif dan HP terhubung ke internet.";
            } else if (err.code === 3) { // TIMEOUT
                if (!currentCoords.lat) {
                    if (geofenceIconBox) {
                        geofenceIconBox.className = "w-11 h-11 rounded-2xl bg-amber-500 border border-amber-400 text-white flex items-center justify-center shrink-0 shadow-md animate-pulse transition-all";
                    }
                    if (geofenceTitle) {
                        geofenceTitle.textContent = "Mencari Lokasi GPS...";
                        geofenceTitle.className = "font-black text-white uppercase tracking-wider text-xs heading-font";
                    }
                    if (geofenceDesc) geofenceDesc.textContent = "Sedang memeriksa posisi Anda. Harap tunggu beberapa saat...";
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
            const res = await fetch("{{ route('siswa.check-status', [], false) }}", {
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
                geofenceIconBox.className = "w-11 h-11 rounded-2xl bg-emerald-500 border border-emerald-400 text-white flex items-center justify-center shrink-0 shadow-md transition-all";
            }
            if (geofenceTitle) {
                geofenceTitle.textContent = "Di Lingkungan Madrasah";
                geofenceTitle.className = "font-black text-white uppercase tracking-wider text-xs heading-font";
            }
            if (geofenceDesc) geofenceDesc.textContent = "Posisi GPS valid di dalam area madrasah.";
            if (geofenceDistance) {
                geofenceDistance.textContent = Math.round(data.distance) + "m";
                geofenceDistance.className = "font-mono font-black text-xs px-2.5 py-0.5 rounded-full bg-white text-emerald-900 shadow-xs border border-white";
            }

            if (outsideWarning) outsideWarning.classList.add('hidden');

            if (data.has_session && data.session) {
                activeSession = data.session;
                if (noSessionNotice) noSessionNotice.classList.add('hidden');
                if (sessionCard) sessionCard.classList.remove('hidden');
                if (sessionSubject) sessionSubject.textContent = data.session.subject_name;
                if (sessionTeacher) sessionTeacher.textContent = "Bapak/Ibu Guru: " + data.session.teacher_name;

                if (data.has_verified) {
                    if (pinSection) pinSection.classList.add('hidden');
                    if (successSection) successSection.classList.remove('hidden');
                } else {
                    if (pinSection) pinSection.classList.remove('hidden');
                    if (successSection) successSection.classList.add('hidden');
                }

                remainingSec = data.session.remaining_seconds || 0;
                startCountdownUI();
            } else {
                if (sessionCard) sessionCard.classList.add('hidden');
                if (noSessionNotice) noSessionNotice.classList.remove('hidden');
            }
        } else {
            if (geofenceIconBox) {
                geofenceIconBox.className = "w-11 h-11 rounded-2xl bg-red-600 border border-red-500 text-white flex items-center justify-center shrink-0 shadow-md transition-all";
            }
            if (geofenceTitle) {
                geofenceTitle.textContent = "Di Luar Area Madrasah";
                geofenceTitle.className = "font-black text-white uppercase tracking-wider text-xs heading-font";
            }
            if (geofenceDesc) geofenceDesc.textContent = "Posisi Anda berada di luar area lingkungan madrasah (maksimal 75 meter).";
            if (geofenceDistance) {
                geofenceDistance.textContent = Math.round(data.distance) + "m";
                geofenceDistance.className = "font-mono font-black text-xs px-2.5 py-0.5 rounded-full bg-red-600 text-white shadow-xs border border-red-500";
            }

            if (sessionCard) sessionCard.classList.add('hidden');
            if (noSessionNotice) noSessionNotice.classList.add('hidden');
            if (outsideWarning) {
                outsideWarning.classList.remove('hidden');
                if (outsideDistanceText) {
                    outsideDistanceText.textContent = `Sistem mendeteksi posisi Anda berjarak ${Math.round(data.distance)} meter dari madrasah (maksimal: ${data.radius} meter).`;
                }
            }
        }
    }

    function startCountdownUI() {
        if (countdownTimer) clearInterval(countdownTimer);
        updateTimerDisplay();

        countdownTimer = setInterval(() => {
            if (remainingSec > 0) {
                remainingSec--;
                updateTimerDisplay();
            } else {
                if (sessionCountdown) sessionCountdown.textContent = "00:00 (Waktu Habis)";
                clearInterval(countdownTimer);
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        if (!sessionCountdown) return;
        const m = Math.floor(remainingSec / 60);
        const s = remainingSec % 60;
        sessionCountdown.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    // Keypad Logic
    function pressKey(num) {
        if (enteredPin.length < 4) {
            if (window.triggerHaptic) window.triggerHaptic(40);
            enteredPin += num;
            updatePinBoxes();
            if (enteredPin.length === 4) {
                submitPin();
            }
        }
    }

    function backspacePin() {
        if (enteredPin.length > 0) {
            if (window.triggerHaptic) window.triggerHaptic(40);
            enteredPin = enteredPin.slice(0, -1);
            updatePinBoxes();
            if (pinFeedback) pinFeedback.textContent = "";
        }
    }

    function clearPin() {
        if (window.triggerHaptic) window.triggerHaptic(40);
        enteredPin = "";
        updatePinBoxes();
        if (pinFeedback) pinFeedback.textContent = "";
    }

    function updatePinBoxes() {
        for (let i = 0; i < 4; i++) {
            const el = document.getElementById('digit' + i);
            if (!el) continue;
            if (i < enteredPin.length) {
                el.textContent = enteredPin[i];
                el.className = "w-14 h-16 sm:w-16 sm:h-20 rounded-2xl border-2 border-maarif-600 bg-emerald-50/60 flex items-center justify-center text-2xl sm:text-3xl font-extrabold text-emerald-950 mono-font shadow-sm shrink-0 ring-2 ring-emerald-500/20 transition-all";
            } else if (i === enteredPin.length) {
                el.textContent = "";
                el.className = "w-14 h-16 sm:w-16 sm:h-20 rounded-2xl border-2 border-maarif-600 bg-white shadow-xs flex items-center justify-center text-2xl sm:text-3xl font-extrabold text-slate-800 mono-font shrink-0 ring-2 ring-maarif-500/30 transition-all";
            } else {
                el.textContent = "";
                el.className = "w-14 h-16 sm:w-16 sm:h-20 rounded-2xl border-2 border-slate-300 bg-slate-50/50 shadow-xs flex items-center justify-center text-2xl sm:text-3xl font-extrabold text-slate-800 mono-font shrink-0 transition-all";
            }
        }
    }

    async function submitPin() {
        if (pinFeedback) {
            pinFeedback.textContent = "Memeriksa PIN...";
            pinFeedback.className = "text-xs font-semibold text-slate-500 h-5";
        }

        try {
            const res = await fetch("{{ route('siswa.verify-pin', [], false) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    pin: enteredPin,
                    latitude: currentCoords.lat,
                    longitude: currentCoords.lng
                })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                if (window.triggerHaptic) window.triggerHaptic([100, 50, 100]);
                if (pinSection) pinSection.classList.add('hidden');
                if (successSection) successSection.classList.remove('hidden');
                if (pinFeedback) pinFeedback.textContent = "";
                // Refresh status after 2 seconds
                setTimeout(checkServerStatus, 2000);
            } else {
                if (window.triggerHaptic) window.triggerHaptic([200]);
                if (pinFeedback) {
                    pinFeedback.textContent = data.message || "PIN salah. Silakan coba lagi.";
                    pinFeedback.className = "text-xs font-bold text-rose-600 h-5";
                }
                clearPin();
            }
        } catch(e) {
            if (pinFeedback) {
                pinFeedback.textContent = "Terjadi gangguan koneksi internet.";
                pinFeedback.className = "text-xs font-bold text-rose-600 h-5";
            }
            clearPin();
        }
    }

    // Physical keyboard input support
    document.addEventListener('keydown', (e) => {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
        if (sessionCard && !sessionCard.classList.contains('hidden') && pinSection && !pinSection.classList.contains('hidden')) {
            if (e.key >= '0' && e.key <= '9') {
                pressKey(e.key);
            } else if (e.key === 'Backspace') {
                backspacePin();
            } else if (e.key === 'Escape' || e.key === 'c' || e.key === 'C') {
                clearPin();
            }
        }
    });

    // Initialize on load
    window.addEventListener('DOMContentLoaded', () => {
        startLiveClock();
        initGeolocation();
        updatePinBoxes();
        setInterval(checkServerStatus, 4000);
    });
</script>
@endpush
