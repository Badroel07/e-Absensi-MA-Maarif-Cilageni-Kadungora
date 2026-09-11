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
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 heading-font tracking-tight">Dashboard Siswa</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Terminal presensi ruang kelas dan pemantauan kehadiran harian madrasah</p>
        </div>
    </div>

    <!-- 1. HERO GREETING & PROFILE BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-950 text-white p-5 sm:p-7 shadow-lg border border-emerald-700/50 flex flex-col gap-4 sm:gap-5">
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
                <i data-lucide="clock" class="w-3.5 h-3.5 text-emerald-300 shrink-0"></i>
                <div class="liveClockTicker text-xs font-bold mono-font tracking-wider">
                    {{ now()->format('H:i:s') }} <span class="text-[10px] font-semibold text-emerald-300">WIB</span>
                </div>
            </div>
        </div>

        <!-- MAIN HERO ROW: Profile (Left) & Desktop Clock (Right) -->
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Student Identity (Left) -->
            <div class="flex items-center gap-4">
                <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-white/20 to-white/5 border border-white/25 text-white flex items-center justify-center font-bold text-2xl sm:text-3xl shadow-lg shrink-0 backdrop-blur-md overflow-hidden ring-2 ring-white/15">
                    @if($student->profile_photo_url)
                        <img src="{{ $student->profile_photo_url }}" loading="lazy" decoding="async" alt="{{ $student->name }}" class="w-full h-full object-cover">
                    @else
                        <span class="select-none">{{ substr($student->name, 0, 1) }}</span>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold text-white heading-font tracking-tight truncate">
                        {{ $student->name }}
                    </h2>
                    
                    <div class="flex items-center gap-2 flex-wrap text-xs text-emerald-200/90 font-medium mt-1">
                        <span>Kelas {{ $student->classroom->name ?? '-' }}</span>
                        <span class="text-emerald-400/40 select-none">&bull;</span>
                        <span>TA {{ $student->classroom->academic_year ?? '2026/2027' }}</span>
                        <span class="text-emerald-400/40 select-none">&bull;</span>
                        <span>NISN: <strong class="mono-font font-semibold text-white">{{ $student->identity_number }}</strong></span>
                    </div>

                    <!-- GPS Live Status Indicator (Linear / Clean Editorial) -->
                    <div class="mt-2 flex items-center">
                        <button type="button" onclick="initGeolocation(true)" id="geofenceBadge" title="Ketuk untuk memperbarui lokasi GPS" class="group inline-flex items-center gap-2 text-xs font-semibold text-emerald-200/90 hover:text-white transition-colors cursor-pointer focus:outline-none">
                            <span class="relative flex h-2 w-2">
                                <span id="geofenceBadgePing" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span id="geofenceBadgeDot" class="relative inline-flex rounded-full h-2 w-2 bg-amber-400"></span>
                            </span>
                            <span id="geofenceBadgeText" class="tracking-tight">Mendeteksi Lokasi GPS...</span>
                            <span id="geofenceBadgeDist" class="hidden mono-font text-[11px] opacity-90"></span>
                            <i data-lucide="rotate-cw" id="gpsBadgeIcon" class="w-3.5 h-3.5 text-emerald-300/70 group-hover:text-emerald-200 group-hover:rotate-180 transition-transform duration-300"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- [DESKTOP ONLY] Live Clock & Date (Right) -->
            <div class="hidden md:flex flex-col items-end shrink-0 pl-6 border-l border-white/15 text-right">
                <div id="liveClockDisplay" class="liveClockTicker text-2xl lg:text-3xl font-bold text-white mono-font tracking-tight drop-shadow-xs">
                    {{ now()->format('H:i:s') }} <span class="text-xs font-semibold text-emerald-200">WIB</span>
                </div>
                <p class="text-xs text-emerald-100/90 mt-1 font-medium">
                    {{ $todayDay }}, {{ now()->translatedFormat('d F Y') }}
                </p>
            </div>
        </div>
    </div>

    <!-- WARNING ALERTS (Di Luar Area & Status Rombel) -->
    <!-- Outside Geofence Warning Box -->
    <div id="outsideWarning" class="hidden bg-rose-50 border border-rose-200 rounded-3xl p-6 text-center space-y-4 shadow-xs">
        <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto shadow-xs">
            <i data-lucide="map-pin-off" class="w-7 h-7"></i>
        </div>
        <div>
            <h3 class="text-base font-bold text-rose-950 heading-font">Di Luar Area Madrasah</h3>
            <p id="outsideDistanceText" class="text-xs text-rose-800 mt-1">
                Sistem mendeteksi posisi kamu berada di luar area madrasah (maksimal {{ $location->radius_meters ?? 75 }} meter).
            </p>
            <p class="text-xs text-rose-700 mt-2 font-medium">
                Presensi hanya dapat dilakukan jika kamu berada di dalam area lingkungan madrasah.
            </p>
        </div>
        <button type="button" onclick="initGeolocation(true)" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-rose-100 active:bg-rose-200 border border-rose-300 text-rose-800 font-bold text-xs transition-all duration-150 active:scale-[0.98] shadow-xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
            <i data-lucide="rotate-cw" id="gpsRefreshIcon" class="w-4 h-4 text-rose-600 shrink-0"></i>
            <span>Coba Deteksi Ulang GPS</span>
        </button>
    </div>

    @if(!$student->classroom_id)
        <div class="bg-amber-50/90 border border-amber-200 rounded-3xl p-6 text-center space-y-4 shadow-xs">
            <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto shadow-xs">
                <i data-lucide="alert-triangle" class="w-7 h-7"></i>
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
    <details class="group bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden transition-all duration-200 open:border-emerald-300 open:shadow-xs">
        <summary class="flex items-center justify-between p-4 sm:p-5 cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden hover:bg-slate-50/80 transition-colors">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
                    <i data-lucide="help-circle" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-sm font-semibold text-slate-800 heading-font">
                            Petunjuk Presensi Siswa
                        </h4>
                        <span class="text-[10px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/70">
                            3 Langkah Alur
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-normal mt-0.5 leading-relaxed">
                        Ketuk untuk melihat 3 langkah mudah presensi kehadiran di kelas
                    </p>
                </div>
            </div>
            <div class="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-slate-200 text-slate-500 flex items-center justify-center shrink-0 ml-2 transition-transform duration-200 group-open:rotate-180">
                <i data-lucide="chevron-down" class="w-4 h-4"></i>
            </div>
        </summary>
        <div class="px-5 pb-5 pt-2 border-t border-slate-100 text-xs sm:text-sm bg-slate-50/50">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 pt-2">
                <div class="p-4 bg-white rounded-2xl border border-slate-200/70 shadow-2xs flex items-start gap-3">
                    <span class="w-6 h-6 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-semibold mono-font flex items-center justify-center shrink-0 mt-0.5">1</span>
                    <div class="text-slate-600 text-xs leading-relaxed">
                        <strong class="text-slate-900 block font-semibold mb-0.5">Berada di Madrasah</strong>
                        Presensi hanya dapat diverifikasi saat GPS mendeteksi kamu berada di area madrasah.
                    </div>
                </div>
                <div class="p-4 bg-white rounded-2xl border border-slate-200/70 shadow-2xs flex items-start gap-3">
                    <span class="w-6 h-6 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-semibold mono-font flex items-center justify-center shrink-0 mt-0.5">2</span>
                    <div class="text-slate-600 text-xs leading-relaxed">
                        <strong class="text-slate-900 block font-semibold mb-0.5">Masukkan PIN</strong>
                        Ketik 4 angka PIN yang dibagikan oleh guru pengampu saat jam pelajaran dimulai.
                    </div>
                </div>
                <div class="p-4 bg-white rounded-2xl border border-slate-200/70 shadow-2xs flex items-start gap-3">
                    <span class="w-6 h-6 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-semibold mono-font flex items-center justify-center shrink-0 mt-0.5">3</span>
                    <div class="text-slate-600 text-xs leading-relaxed">
                        <strong class="text-slate-900 block font-semibold mb-0.5">Kehadiran Otomatis</strong>
                        Presensi pada jam pertama otomatis mencatat kehadiran masuk harian madrasah.
                    </div>
                </div>
            </div>
        </div>
    </details>

    <!-- 2. QUICK METRIC & ATTENDANCE SUMMARY CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Metric 1: Total Mapel Hari Ini -->
        <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jadwal Hari Ini</span>
                <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl sm:text-3xl font-bold text-slate-900 mono-font leading-none block">
                    {{ $todaySchedules->count() }}
                </span>
                <p class="text-[11px] text-slate-500 font-medium mt-1">Mata Pelajaran</p>
            </div>
        </div>

        <!-- Metric 2: Hadir Hari Ini -->
        <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-emerald-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Hadir</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200/60 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl sm:text-3xl font-bold text-emerald-700 mono-font leading-none block">
                    {{ $hadirCount }}
                </span>
                <p class="text-[11px] text-slate-500 font-medium mt-1">Sesi Terverifikasi</p>
            </div>
        </div>

        <!-- Metric 3: Izin / Sakit -->
        <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-amber-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Izin / Sakit</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 border border-amber-200/60 flex items-center justify-center shrink-0">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl sm:text-3xl font-bold text-amber-700 mono-font leading-none block">
                    {{ $izinCount + $sakitCount }}
                </span>
                <p class="text-[11px] text-slate-500 font-medium mt-1">Dispensasi</p>
            </div>
        </div>

        <!-- Metric 4: Alpa -->
        <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-rose-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-rose-700 uppercase tracking-wider">Alpa</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-700 border border-rose-200/60 flex items-center justify-center shrink-0">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl sm:text-3xl font-bold text-rose-700 mono-font leading-none block">
                    {{ $alpaCount }}
                </span>
                <p class="text-[11px] text-slate-500 font-medium mt-1">Tanpa Keterangan</p>
            </div>
        </div>
    </div>

    <!-- 3. MAIN DASHBOARD CONTENT (Single Column Flow) -->
    <div class="space-y-6">
        
        <!-- Active Class Session Card (Appears when session is opened by teacher) -->
        <div id="sessionCard" class="hidden bg-white rounded-3xl p-6 border-2 border-maarif-600 shadow-xl shadow-maarif-700/10 space-y-6 transition-all duration-300">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="min-w-0 flex-1">
                    <h3 id="sessionSubject" class="text-xl sm:text-2xl font-bold text-slate-900 heading-font truncate">
                        --
                    </h3>
                    <p id="sessionTeacher" class="text-xs text-slate-500 font-medium mt-0.5 truncate">Bapak/Ibu Guru: --</p>
                </div>
                <div class="text-right pl-4 shrink-0">
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Sisa Waktu</p>
                    <div id="sessionCountdown" class="text-2xl sm:text-3xl font-bold text-amber-600 mono-font">
                        --:--
                    </div>
                </div>
            </div>

            <!-- PIN Input Keypad Section -->
            <div id="pinSection" class="space-y-4">
                <div class="text-center">
                    <label class="text-xs font-semibold text-slate-700">Masukkan 4 Angka PIN dari Bapak/Ibu Guru</label>
                    <p class="text-[11px] text-slate-400 mt-0.5">Ketik angka PIN yang disebutkan oleh Bapak/Ibu Guru</p>
                    
                    <!-- 4-Digit Display Boxes -->
                    <div class="flex items-center justify-center gap-3 sm:gap-4 my-4">
                        <div id="digit0" class="w-14 h-16 sm:w-16 sm:h-20 rounded-2xl border-2 border-maarif-600 bg-white shadow-xs flex items-center justify-center text-2xl sm:text-3xl font-bold text-slate-800 mono-font shrink-0 ring-2 ring-maarif-500/30 transition-all"></div>
                        <div id="digit1" class="w-14 h-16 sm:w-16 sm:h-20 rounded-2xl border-2 border-slate-300 bg-slate-50/50 shadow-xs flex items-center justify-center text-2xl sm:text-3xl font-bold text-slate-800 mono-font shrink-0 transition-all"></div>
                        <div id="digit2" class="w-14 h-16 sm:w-16 sm:h-20 rounded-2xl border-2 border-slate-300 bg-slate-50/50 shadow-xs flex items-center justify-center text-2xl sm:text-3xl font-bold text-slate-800 mono-font shrink-0 transition-all"></div>
                        <div id="digit3" class="w-14 h-16 sm:w-16 sm:h-20 rounded-2xl border-2 border-slate-300 bg-slate-50/50 shadow-xs flex items-center justify-center text-2xl sm:text-3xl font-bold text-slate-800 mono-font shrink-0 transition-all"></div>
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
                    <button type="button" onclick="clearPin()" aria-label="Hapus semua angka PIN" class="keypad-btn bg-slate-100 hover:bg-slate-200 active:bg-slate-300 active:scale-95 text-slate-500 text-sm font-semibold rounded-2xl border border-slate-200 shadow-2xs transition-all duration-150 flex items-center justify-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
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
                    <h4 class="text-lg font-semibold text-slate-900 heading-font">Kehadiran Berhasil Dicatat!</h4>
                    <p class="text-sm text-emerald-700 font-semibold mt-0.5">Kamu tercatat: <strong>HADIR</strong> pada mata pelajaran ini.</p>
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
                <h4 class="text-base font-semibold text-slate-900 heading-font">Belum Ada Presensi Kelas yang Dibuka</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Bapak/Ibu Guru belum membuka presensi untuk kelas <strong>{{ $student->classroom->name ?? 'kamu' }}</strong>. Halaman ini akan otomatis menampilkan kotak PIN ketika presensi dibuka.
                </p>
            </div>
        </div>

        <!-- Today's Class Schedule (Jadwal Pelajaran Hari Ini) -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-semibold text-slate-900 heading-font uppercase tracking-wide">
                            Jadwal Pelajaran Hari Ini ({{ $todayDay }})
                        </h3>
                        <p class="text-xs text-slate-500">Kelas {{ $student->classroom->name ?? '-' }} &bull; MA Ma'arif Cilageni</p>
                    </div>
                </div>
                <a href="{{ route('siswa.schedule') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 hover:text-emerald-800 bg-slate-100 hover:bg-slate-200/80 px-3 py-1.5 rounded-xl transition-all duration-150 active:scale-95 shadow-2xs shrink-0 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                    <span>Lihat Mingguan</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            @if($todaySchedules->isEmpty())
                <div class="py-10 text-center text-slate-400 space-y-2">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                        <i data-lucide="calendar-x-2" class="w-6 h-6"></i>
                    </div>
                    <p class="text-xs font-medium text-slate-500">Tidak ada jadwal pelajaran terjadwal untuk hari {{ $todayDay }}.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($todaySchedules as $sch)
                        @php
                            $matchedAttendance = $todayAttendances->firstWhere('schedule_id', $sch->id);
                        @endphp
                        <div class="p-4 rounded-2xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $matchedAttendance ? ($matchedAttendance->status === 'HADIR' ? 'bg-emerald-50/40 border-emerald-200/80' : 'bg-amber-50/40 border-amber-200/80') : 'bg-slate-50/70 border-slate-200/70 hover:bg-slate-100/80' }}">
                            <div class="flex items-center space-x-3.5 min-w-0">
                                <span class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-800 font-semibold mono-font text-xs shrink-0 shadow-2xs">
                                    {{ substr($sch->start_time, 0, 5) }} – {{ substr($sch->end_time, 0, 5) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-semibold text-slate-900 leading-snug break-words">{{ $sch->subject->name }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5 break-words">{{ $sch->teacher->name }}</p>
                                </div>
                            </div>

                            <div class="self-end sm:self-center shrink-0">
                                @if($matchedAttendance)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $matchedAttendance->status === 'HADIR' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($matchedAttendance->status === 'IZIN' ? 'bg-amber-100 text-amber-800 border border-amber-200' : ($matchedAttendance->status === 'SAKIT' ? 'bg-sky-100 text-sky-800 border border-sky-200' : 'bg-rose-100 text-rose-800 border border-rose-200')) }}">
                                        @if($matchedAttendance->status === 'HADIR')
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600"></i>
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
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
                        <i data-lucide="check-square" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-semibold text-slate-900 uppercase tracking-wide heading-font">
                            Riwayat Kehadiran Hari Ini
                        </h3>
                        <p class="text-xs text-slate-500">Catatan kehadiran pada jam pelajaran hari ini</p>
                    </div>
                </div>
                <span class="text-xs font-semibold text-slate-700 px-3 py-1 rounded-xl bg-slate-100 border border-slate-200/80 mono-font shrink-0">
                    {{ $todayAttendances->count() }} Tercatat
                </span>
            </div>

            @if($todayAttendances->isEmpty())
                <div class="text-center py-10 text-slate-400 space-y-2">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                        <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                    </div>
                    <p class="text-xs font-medium text-slate-500">Belum ada rekaman kehadiran pelajaran hari ini.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($todayAttendances as $att)
                        <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/70 flex items-center justify-between text-xs hover:bg-slate-100/80 transition">
                            <div class="min-w-0 flex-1 pr-3">
                                <p class="font-semibold text-slate-900 text-sm leading-snug break-words">{{ $att->schedule->subject->name ?? 'Mata Pelajaran' }}</p>
                                <p class="text-xs text-slate-500 mt-0.5 break-words">
                                    {{ $att->schedule->teacher->name ?? 'Dewan Guru' }} &bull; 
                                    <span class="mono-font text-slate-600 font-medium">{{ $att->verified_at ? $att->verified_at->format('H:i') . ' WIB' : 'Pukul ' . substr($att->schedule->start_time ?? '', 0, 5) . ' WIB' }}</span>
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold shrink-0 {{ $att->status === 'HADIR' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($att->status === 'IZIN' ? 'bg-amber-100 text-amber-800 border border-amber-200' : ($att->status === 'SAKIT' ? 'bg-sky-100 text-sky-800 border border-sky-200' : 'bg-rose-100 text-rose-800 border border-rose-200')) }}">
                                @if($att->status === 'HADIR')
                                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600"></i>
                                @endif
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

    const geofenceBadge = document.getElementById('geofenceBadge');
    const geofenceBadgeDot = document.getElementById('geofenceBadgeDot');
    const geofenceBadgePing = document.getElementById('geofenceBadgePing');
    const geofenceBadgeText = document.getElementById('geofenceBadgeText');
    const geofenceBadgeDist = document.getElementById('geofenceBadgeDist');
    const gpsBadgeIcon = document.getElementById('gpsBadgeIcon');

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
        const badgeIcon = document.getElementById('gpsBadgeIcon');
        if (isManual) {
            if (refreshIcon) {
                refreshIcon.classList.add('animate-spin');
                setTimeout(() => refreshIcon.classList.remove('animate-spin'), 1500);
            }
            if (badgeIcon) {
                badgeIcon.classList.add('animate-spin');
                setTimeout(() => badgeIcon.classList.remove('animate-spin'), 1500);
            }
        }

        if (!navigator.geolocation) {
            if (geofenceBadge) {
                geofenceBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-rose-200 hover:text-rose-100 transition-colors cursor-pointer focus:outline-none";
            }
            if (geofenceBadgePing) geofenceBadgePing.classList.add('hidden');
            if (geofenceBadgeDot) {
                geofenceBadgeDot.className = "relative inline-flex rounded-full h-2 w-2 bg-rose-400";
            }
            if (geofenceBadgeText) geofenceBadgeText.textContent = "GPS Tidak Didukung";
            if (geofenceBadgeDist) geofenceBadgeDist.classList.add('hidden');
            return;
        }

        const handlePosition = (pos) => {
            currentCoords.lat = pos.coords.latitude;
            currentCoords.lng = pos.coords.longitude;
            checkServerStatus();
        };

        const handleError = (err) => {
            if (err.code === 1) { // PERMISSION_DENIED
                if (geofenceBadge) {
                    geofenceBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-rose-200 hover:text-rose-100 transition-colors cursor-pointer focus:outline-none";
                }
                if (geofenceBadgePing) geofenceBadgePing.classList.add('hidden');
                if (geofenceBadgeDot) {
                    geofenceBadgeDot.className = "relative inline-flex rounded-full h-2 w-2 bg-rose-400";
                }
                if (geofenceBadgeText) geofenceBadgeText.textContent = "Izin GPS Ditolak";
                if (geofenceBadgeDist) geofenceBadgeDist.classList.add('hidden');
            } else if (err.code === 2) { // POSITION_UNAVAILABLE
                if (geofenceBadge) {
                    geofenceBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-amber-200 hover:text-amber-100 transition-colors cursor-pointer focus:outline-none";
                }
                if (geofenceBadgePing) {
                    geofenceBadgePing.className = "animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75";
                    geofenceBadgePing.classList.remove('hidden');
                }
                if (geofenceBadgeDot) {
                    geofenceBadgeDot.className = "relative inline-flex rounded-full h-2 w-2 bg-amber-400";
                }
                if (geofenceBadgeText) geofenceBadgeText.textContent = "Lokasi Belum Terdeteksi";
                if (geofenceBadgeDist) geofenceBadgeDist.classList.add('hidden');
            } else if (err.code === 3) { // TIMEOUT
                if (!currentCoords.lat) {
                    if (geofenceBadge) {
                        geofenceBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-amber-200 hover:text-amber-100 transition-colors cursor-pointer focus:outline-none";
                    }
                    if (geofenceBadgePing) {
                        geofenceBadgePing.className = "animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75";
                        geofenceBadgePing.classList.remove('hidden');
                    }
                    if (geofenceBadgeDot) {
                        geofenceBadgeDot.className = "relative inline-flex rounded-full h-2 w-2 bg-amber-400";
                    }
                    if (geofenceBadgeText) geofenceBadgeText.textContent = "Mencari Lokasi GPS...";
                    if (geofenceBadgeDist) geofenceBadgeDist.classList.add('hidden');
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
        const distStr = Math.round(data.distance) >= 1000 
            ? (data.distance / 1000).toFixed(1) + ' km' 
            : Math.round(data.distance) + 'm';

        if (data.is_within_geofence) {
            if (geofenceBadge) {
                geofenceBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-emerald-200 hover:text-white transition-colors cursor-pointer focus:outline-none";
            }
            if (geofenceBadgePing) geofenceBadgePing.classList.add('hidden');
            if (geofenceBadgeDot) {
                geofenceBadgeDot.className = "relative inline-flex rounded-full h-2 w-2 bg-emerald-400 shadow-xs shadow-emerald-400/50";
            }
            if (geofenceBadgeText) geofenceBadgeText.textContent = "Di Area Madrasah";
            if (geofenceBadgeDist) {
                geofenceBadgeDist.textContent = `(${distStr})`;
                geofenceBadgeDist.classList.remove('hidden');
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
            if (geofenceBadge) {
                geofenceBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-rose-200 hover:text-rose-100 transition-colors cursor-pointer focus:outline-none";
            }
            if (geofenceBadgePing) geofenceBadgePing.classList.add('hidden');
            if (geofenceBadgeDot) {
                geofenceBadgeDot.className = "relative inline-flex rounded-full h-2 w-2 bg-rose-400 shadow-xs shadow-rose-400/50";
            }
            if (geofenceBadgeText) geofenceBadgeText.textContent = "Di Luar Radius";
            if (geofenceBadgeDist) {
                geofenceBadgeDist.textContent = `(${distStr})`;
                geofenceBadgeDist.classList.remove('hidden');
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
                el.className = "w-14 h-16 sm:w-16 sm:h-20 rounded-2xl border-2 border-maarif-600 bg-emerald-50/60 flex items-center justify-center text-2xl sm:text-3xl font-bold text-emerald-950 mono-font shadow-sm shrink-0 ring-2 ring-emerald-500/20 transition-all";
            } else if (i === enteredPin.length) {
                el.textContent = "";
                el.className = "w-14 h-16 sm:w-16 sm:h-20 rounded-2xl border-2 border-maarif-600 bg-white shadow-xs flex items-center justify-center text-2xl sm:text-3xl font-bold text-slate-800 mono-font shrink-0 ring-2 ring-maarif-500/30 transition-all";
            } else {
                el.textContent = "";
                el.className = "w-14 h-16 sm:w-16 sm:h-20 rounded-2xl border-2 border-slate-300 bg-slate-50/50 shadow-xs flex items-center justify-center text-2xl sm:text-3xl font-bold text-slate-800 mono-font shrink-0 transition-all";
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

    window.addEventListener('dev-location-changed', () => {
        initGeolocation(true);
    });
</script>
@endpush
