@extends('layouts.app')

@section('title', 'Dashboard — MA Ma\'arif Cilageni')

@push('styles')
<style>
    .keypad-btn {
        height: 52px;
        font-size: 1.25rem;
        font-weight: 700;
        font-family: 'Inter', system-ui, sans-serif;
        font-feature-settings: 'tnum', 'zero';
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
<div class="space-y-8 sm:space-y-10">

    <!-- 1. UNBOXED EDITORIAL PROFILE & HERO SECTION -->
    <div class="pb-6 border-b border-slate-200/80 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
        <!-- Left: Avatar + Identity + GPS -->
        <div class="flex items-start sm:items-center gap-4 min-w-0">
            <!-- Avatar (Clean, unboxed, no thick ring/nested borders) -->
            <div class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-emerald-700 text-white flex items-center justify-center font-bold text-xl sm:text-2xl shrink-0 overflow-hidden shadow-xs">
                @if($student->profile_photo_url)
                    <img src="{{ $student->profile_photo_url }}" loading="lazy" decoding="async" alt="{{ $student->name }}" class="w-full h-full object-cover">
                @else
                    <span class="select-none">{{ substr($student->name, 0, 1) }}</span>
                @endif
            </div>

            <!-- Identity Info -->
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Dashboard Siswa</span>
                </div>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 heading-font tracking-tight truncate mt-0.5">
                    {{ $student->name }}
                </h1>
                <div class="mt-1 space-y-0.5 text-xs">
                    <div class="font-semibold text-slate-700 leading-tight">Kelas {{ $student->classroom->name ?? '-' }} &middot; TA {{ $student->classroom->academic_year ?? '2026/2027' }}</div>
                    <div class="mono-font font-semibold text-slate-600 leading-tight">{{ $student->identity_number }}</div>
                </div>

                <!-- GPS Live Status Indicator (Linear / Clean Editorial) -->
                <div class="mt-2.5 flex items-center">
                    <button type="button" onclick="initGeolocation(true)" id="geofenceBadge" title="Ketuk untuk memperbarui lokasi GPS" class="group inline-flex items-center gap-2 text-xs font-semibold text-emerald-700 hover:text-emerald-800 transition-colors cursor-pointer focus:outline-none">
                        <span class="relative flex h-2 w-2">
                            <span id="geofenceBadgePing" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span id="geofenceBadgeDot" class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        <span id="geofenceBadgeText" class="tracking-tight">Mendeteksi Lokasi GPS...</span>
                        <span id="geofenceBadgeDist" class="hidden mono-font text-[11px] text-slate-500"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: Time & Date Indicator -->
        <div class="flex items-center justify-between md:flex-col md:items-end md:justify-center shrink-0 pt-3 md:pt-0 border-t md:border-t-0 md:border-l border-slate-200/60 md:pl-6 text-left md:text-right">
            <div>
                <p class="text-xs font-medium text-slate-500">
                    {{ $todayDay }}, {{ now()->translatedFormat('d F Y') }}
                </p>
            </div>
            <div id="liveClockDisplay" class="liveClockTicker text-xl sm:text-2xl md:text-3xl font-bold text-slate-900 mono-font tracking-tight">
                {{ now()->format('H:i:s') }} <span class="text-xs font-semibold text-slate-500">WIB</span>
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

    <!-- 2. ACTIVE CLASS SESSION & GPS STATE (Appears prominently above guide) -->
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

    <!-- GPS Searching Notice (Shown while GPS is locking/verifying position) -->
    <div id="gpsSearchingNotice" class="bg-emerald-50/70 border border-emerald-200/80 rounded-3xl p-6 text-center space-y-3.5 shadow-xs transition-all duration-300">
        <div class="w-14 h-14 rounded-2xl bg-emerald-100/80 text-emerald-700 flex items-center justify-center mx-auto shadow-2xs">
            <i data-lucide="satellite" class="w-7 h-7 animate-pulse"></i>
        </div>
        <div class="max-w-md mx-auto space-y-1">
            <h4 class="text-base font-bold text-emerald-950 heading-font">Sedang Memeriksa Lokasi GPS...</h4>
            <p class="text-xs text-emerald-800/90 leading-relaxed font-medium">
                Sistem sedang memverifikasi posisi kamu di lingkungan madrasah. Menu presensi kelas akan terbuka secara otomatis setelah lokasi kamu terkonfirmasi.
            </p>
        </div>
        <div class="pt-1 flex items-center justify-center gap-2 text-[11px] font-semibold text-emerald-700 bg-emerald-100/60 py-1.5 px-3.5 rounded-full w-fit mx-auto border border-emerald-200/60">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-600"></span>
            </span>
            <span>Pastikan GPS HP aktif & izinkan akses lokasi</span>
        </div>
    </div>

    <!-- Standby / No Active Session Notice (Refined Hub State) -->
    <div id="noSessionNotice" class="hidden bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs text-center space-y-3 relative overflow-hidden">
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

    <!-- PETUNJUK PRESENSI SISWA ACCORDION (Collapsible) -->
    <details class="group bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden transition-all duration-200 open:border-emerald-300 open:shadow-xs">
        <summary class="flex items-center justify-between p-4 sm:p-5 cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden hover:bg-slate-50/80 transition-colors">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
                    <i data-lucide="info" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-sm font-semibold text-slate-800 heading-font">
                            Petunjuk Presensi Siswa
                        </h4>
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

    <!-- 3. QUICK METRIC & ATTENDANCE SUMMARY SECTION -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 heading-font uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="bar-chart-2" class="w-4 h-4 text-emerald-700"></i>
                <span>Ringkasan Kehadiran Hari Ini</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Metric 1: Total Mapel Hari Ini -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Jadwal Hari Ini</span>
                    <i data-lucide="calendar" class="w-5 h-5 text-slate-400 shrink-0"></i>
                </div>
                <div class="mt-3">
                    <span class="text-2xl sm:text-3xl font-bold text-slate-900 mono-font leading-none block">{{ $todaySchedules->count() }}</span>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-1 truncate">Mata Pelajaran</p>
                </div>
            </div>

            <!-- Metric 2: Hadir Hari Ini -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-emerald-200 transition">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider truncate">Hadir</span>
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                </div>
                <div class="mt-3">
                    <span class="text-2xl sm:text-3xl font-bold text-emerald-700 mono-font leading-none block">{{ $hadirCount }}</span>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-1 truncate">Sesi Hadir</p>
                </div>
            </div>

            <!-- Metric 3: Izin / Sakit -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-amber-200 transition">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider truncate">Izin / Sakit</span>
                    <i data-lucide="file-text" class="w-5 h-5 text-amber-600 shrink-0"></i>
                </div>
                <div class="mt-3">
                    <span class="text-2xl sm:text-3xl font-bold text-amber-700 mono-font leading-none block">{{ $izinCount + $sakitCount }}</span>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-1 truncate">Dispensasi</p>
                </div>
            </div>

            <!-- Metric 4: Alpa -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-rose-200 transition">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-rose-700 uppercase tracking-wider truncate">Alpa</span>
                    <i data-lucide="x-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                </div>
                <div class="mt-3">
                    <span class="text-2xl sm:text-3xl font-bold text-rose-700 mono-font leading-none block">{{ $alpaCount }}</span>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-1 truncate">Tanpa Keterangan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. SCHEDULE & ATTENDANCE FEED -->
    <div class="space-y-8 sm:space-y-10">

        <!-- Today's Class Schedule (Jadwal Pelajaran Hari Ini) -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900 heading-font uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="book-open" class="w-4 h-4 text-emerald-700"></i>
                    <span>Jadwal Pelajaran Hari Ini</span>
                </h2>
                <a href="{{ route('siswa.schedule') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-maarif-700 hover:text-maarif-800 active:text-maarif-900 transition-colors cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                    <span>Lihat Mingguan</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-3.5 overflow-hidden">
                @if($todaySchedules->isEmpty())
                    <div class="py-10 text-center text-slate-400 space-y-2">
                        <i data-lucide="calendar-x-2" class="w-10 h-10 mx-auto text-slate-300"></i>
                        <p class="text-xs font-medium text-slate-500">Tidak ada jadwal pelajaran terjadwal untuk hari {{ $todayDay }}.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @php $currentTime = now()->format('H:i:s'); @endphp
                        @foreach($todaySchedules as $sch)
                            @php
                                $matchedAttendance = $todayAttendances->firstWhere('schedule_id', $sch->id);
                                $startTime = strlen($sch->start_time) === 5 ? $sch->start_time . ':00' : $sch->start_time;
                                $endTime = strlen($sch->end_time) === 5 ? $sch->end_time . ':00' : $sch->end_time;
                                $isCurrentSlot = $currentTime >= $startTime && $currentTime <= $endTime;
                                $cardStateClass = $matchedAttendance
                                    ? ($matchedAttendance->status === 'HADIR' ? 'bg-emerald-50/30 border-emerald-200/80' : 'bg-amber-50/30 border-amber-200/80')
                                    : ($isCurrentSlot ? 'bg-emerald-50/30 border-emerald-500/80 ring-1 ring-emerald-500/20 shadow-xs' : 'bg-slate-50/60 hover:bg-white border-slate-200/80 hover:border-slate-300/90 shadow-2xs hover:shadow-xs');
                            @endphp
                            <div class="p-4 sm:p-5 rounded-2xl border transition-all duration-150 flex flex-col gap-3.5 {{ $cardStateClass }} overflow-hidden">

                                {{-- Top Row: Sesi (bare mono) + Mapel & Guru + Waktu plain (rata kanan sejajar) --}}
                                <div class="flex items-start space-x-3.5 min-w-0">
                                    <span class="mono-font font-bold text-base sm:text-lg {{ $isCurrentSlot ? 'text-emerald-700 font-extrabold' : 'text-slate-400' }} shrink-0 w-7 text-center select-none pt-0.5 transition-colors">
                                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <div class="min-w-0 flex-1 space-y-1.5">
                                        <h4 class="text-base sm:text-lg font-bold text-slate-900 heading-font leading-snug tracking-tight break-words">
                                            {{ $sch->subject->name }}
                                        </h4>
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-slate-700">
                                                <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                                <span>{{ $sch->teacher->name }}</span>
                                            </span>
                                            <div class="font-semibold mono-font text-xs flex items-center gap-1.5 tracking-wide {{ $isCurrentSlot ? 'text-emerald-700 font-bold' : 'text-slate-600' }}">
                                                <i data-lucide="clock" class="w-3.5 h-3.5 {{ $isCurrentSlot ? 'text-emerald-600' : 'text-slate-400' }} shrink-0"></i>
                                                <span class="leading-none">{{ substr($sch->start_time, 0, 5) }} – {{ substr($sch->end_time, 0, 5) }}</span>
                                                <span class="text-[11px] {{ $isCurrentSlot ? 'text-emerald-600 font-bold' : 'text-slate-400 font-medium' }} uppercase tracking-wider leading-none">WIB</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Status Bar — Anti-Pill Pure Typography (match guru/history) --}}
                                @if($matchedAttendance)
                                    @if($matchedAttendance->status === 'HADIR')
                                        <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-700 pl-[42px]">
                                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                            <span>Hadir — Tercatat pada {{ $matchedAttendance->verified_at ? $matchedAttendance->verified_at->format('H:i') . ' WIB' : substr($sch->start_time, 0, 5) . ' WIB' }}</span>
                                        </div>
                                    @elseif($matchedAttendance->status === 'IZIN')
                                        <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-700 pl-[42px]">
                                            <i data-lucide="file-text" class="w-4 h-4 text-amber-600 shrink-0"></i>
                                            <span>Izin</span>
                                        </div>
                                    @elseif($matchedAttendance->status === 'SAKIT')
                                        <div class="flex items-center gap-1.5 text-xs font-semibold text-sky-700 pl-[42px]">
                                            <i data-lucide="activity" class="w-4 h-4 text-sky-600 shrink-0"></i>
                                            <span>Sakit</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1.5 text-xs font-semibold text-rose-700 pl-[42px]">
                                            <i data-lucide="x-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                                            <span>Alpa</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="flex items-center gap-1.5 text-xs font-medium text-slate-500 pl-[42px]">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                        <span>Menunggu Sesi — Presensi dibuka saat jam pelajaran dimulai</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Today's Attendance Feed (Riwayat Kehadiran Hari Ini) -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900 heading-font uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-emerald-700"></i>
                    <span>Riwayat Kehadiran Hari Ini</span>
                </h2>
                <span class="text-xs font-semibold text-slate-500 mono-font">
                    {{ $todayAttendances->count() }} Tercatat
                </span>
            </div>

            <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
                @if($todayAttendances->isEmpty())
                    <div class="text-center py-8 text-slate-400 space-y-2">
                        <i data-lucide="clipboard-x" class="w-10 h-10 mx-auto text-slate-300"></i>
                        <p class="text-xs font-medium text-slate-500">Belum ada sesi kelas yang tercatat hari ini.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($todayAttendances as $att)
                            @php
                                $hadirCount = $att->status === 'HADIR' ? 1 : 0;
                            @endphp
                            <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/50 hover:bg-white hover:border-slate-300 transition-all duration-150 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs shadow-2xs">
                                <div class="min-w-0 flex-1 space-y-1">
                                    <div class="flex items-center gap-2.5 flex-wrap">
                                        <h4 class="text-sm font-bold text-slate-900 heading-font leading-snug">
                                            {{ $att->schedule->subject->name ?? 'Mata Pelajaran' }}
                                        </h4>
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600">
                                            <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                            <span>{{ $att->schedule->teacher->name ?? 'Dewan Guru' }}</span>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 text-[11px] text-slate-500 flex-wrap">
                                        <span class="inline-flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                            <span>Tercatat: <strong class="mono-font font-semibold text-slate-700">{{ $att->verified_at ? $att->verified_at->format('H:i') . ' WIB' : substr($att->schedule->start_time ?? '', 0, 5) . ' WIB' }}</strong></span>
                                        </span>
                                        @if($att->schedule)
                                            <span class="mono-font text-[11px] text-slate-400 font-medium">
                                                {{ substr($att->schedule->start_time, 0, 5) }} – {{ substr($att->schedule->end_time, 0, 5) }} WIB
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center sm:items-end justify-between sm:justify-center flex-row sm:flex-col gap-1.5 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200/60">
                                    @if($att->status === 'HADIR')
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">
                                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                            <span>Hadir</span>
                                        </span>
                                    @elseif($att->status === 'IZIN')
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700">
                                            <i data-lucide="file-text" class="w-4 h-4 text-amber-600 shrink-0"></i>
                                            <span>Izin</span>
                                        </span>
                                    @elseif($att->status === 'SAKIT')
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-sky-700">
                                            <i data-lucide="activity" class="w-4 h-4 text-sky-600 shrink-0"></i>
                                            <span>Sakit</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-rose-700">
                                            <i data-lucide="x-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                                            <span>Alpa</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    // Session Geolocation Cache (TTL: 2 minutes)
    const GEO_CACHE_KEY = 'maarif_geo_cache';
    const GEO_CACHE_TTL = 120000;

    function getCachedGeofence() {
        try {
            const raw = sessionStorage.getItem(GEO_CACHE_KEY);
            if (!raw) return null;
            const data = JSON.parse(raw);
            if (Date.now() - data.timestamp < GEO_CACHE_TTL) {
                return data;
            }
            sessionStorage.removeItem(GEO_CACHE_KEY);
        } catch(e) {}
        return null;
    }

    function setCachedGeofence(coords, serverData) {
        try {
            sessionStorage.setItem(GEO_CACHE_KEY, JSON.stringify({
                coords: { lat: coords.lat, lng: coords.lng },
                serverData: serverData,
                timestamp: Date.now()
            }));
        } catch(e) {}
    }

    let currentCoords = { lat: null, lng: null };
    let enteredPin = "";
    let activeSession = null;
    let countdownTimer = null;
    let remainingSec = 0;
    let liveClockInterval = null;
    let statusInterval = null;

    let isWithinGeofence = false;
    let isGpsLocked = false;
    let activeWatchId = null;
    let gpsRetryTimeout = null;
    let lastGeoAttempt = 0;

    // Live Clock Ticker
    function startLiveClock() {
        if (liveClockInterval) clearInterval(liveClockInterval);
        const update = () => {
            const now = (typeof window.getServerNow === 'function') ? window.getServerNow() : new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const html = `${h}:${m}:${s} <span class="text-xs font-semibold text-slate-500">WIB</span>`;
            document.querySelectorAll('.liveClockTicker').forEach(el => {
                el.innerHTML = html;
            });
        };
        update();
        liveClockInterval = setInterval(update, 1000);
    }

    // Geolocation with manual refresh animation support & lifecycle auto-recovery
    function initGeolocation(isManual = false) {
        const now = Date.now();
        // Throttle auto-triggers to avoid rapid spam within 1.5s, but always allow manual triggers
        if (!isManual && (now - lastGeoAttempt < 1500)) {
            return;
        }
        lastGeoAttempt = now;

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

        const gpsSearchingNotice = document.getElementById('gpsSearchingNotice');
        const sessionCard = document.getElementById('sessionCard');
        const noSessionNotice = document.getElementById('noSessionNotice');
        const outsideWarning = document.getElementById('outsideWarning');
        const outsideDistanceText = document.getElementById('outsideDistanceText');
        const geofenceBadge = document.getElementById('geofenceBadge');
        const geofenceBadgeDot = document.getElementById('geofenceBadgeDot');
        const geofenceBadgePing = document.getElementById('geofenceBadgePing');
        const geofenceBadgeText = document.getElementById('geofenceBadgeText');
        const geofenceBadgeDist = document.getElementById('geofenceBadgeDist');

        // Lock attendance menu while GPS is searching / re-acquiring if no valid position is yet locked
        if (!isGpsLocked || isManual) {
            isGpsLocked = false;
            isWithinGeofence = false;
            if (gpsSearchingNotice) gpsSearchingNotice.classList.remove('hidden');
            if (sessionCard) sessionCard.classList.add('hidden');
            if (noSessionNotice) noSessionNotice.classList.add('hidden');
            if (outsideWarning) outsideWarning.classList.add('hidden');
        }

        if (!navigator.geolocation) {
            if (gpsSearchingNotice) gpsSearchingNotice.classList.add('hidden');
            if (sessionCard) sessionCard.classList.add('hidden');
            if (geofenceBadge) {
                geofenceBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-rose-600 hover:text-rose-700 transition-colors cursor-pointer focus:outline-none";
            }
            if (geofenceBadgePing) geofenceBadgePing.classList.add('hidden');
            if (geofenceBadgeDot) {
                geofenceBadgeDot.className = "relative inline-flex rounded-full h-2 w-2 bg-rose-500";
            }
            if (geofenceBadgeText) geofenceBadgeText.textContent = "GPS Tidak Didukung";
            if (geofenceBadgeDist) geofenceBadgeDist.classList.add('hidden');
            return;
        }

        if (gpsRetryTimeout) {
            clearTimeout(gpsRetryTimeout);
            gpsRetryTimeout = null;
        }

        const handlePosition = (pos) => {
            if (gpsRetryTimeout) {
                clearTimeout(gpsRetryTimeout);
                gpsRetryTimeout = null;
            }
            currentCoords.lat = pos.coords.latitude;
            currentCoords.lng = pos.coords.longitude;
            checkServerStatus();
        };

        const scheduleRetry = (delay = 3500) => {
            if (!gpsRetryTimeout && (!currentCoords.lat || !currentCoords.lng)) {
                gpsRetryTimeout = setTimeout(() => {
                    gpsRetryTimeout = null;
                    if (document.visibilityState !== 'hidden') {
                        initGeolocation(false);
                    }
                }, delay);
            }
        };

        const handleError = (err) => {
            isGpsLocked = false;
            isWithinGeofence = false;
            const currentSessionCard = document.getElementById('sessionCard');
            if (currentSessionCard) currentSessionCard.classList.add('hidden');

            const currentNotice = document.getElementById('gpsSearchingNotice');
            const currentNoSession = document.getElementById('noSessionNotice');
            const currentBadge = document.getElementById('geofenceBadge');
            const currentPing = document.getElementById('geofenceBadgePing');
            const currentDot = document.getElementById('geofenceBadgeDot');
            const currentText = document.getElementById('geofenceBadgeText');
            const currentDist = document.getElementById('geofenceBadgeDist');
            const currentWarning = document.getElementById('outsideWarning');
            const currentDistText = document.getElementById('outsideDistanceText');

            if (err.code === 1) { // PERMISSION_DENIED
                if (currentNotice) currentNotice.classList.add('hidden');
                if (currentNoSession) currentNoSession.classList.add('hidden');
                if (currentBadge) {
                    currentBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-rose-600 hover:text-rose-700 transition-colors cursor-pointer focus:outline-none";
                }
                if (currentPing) currentPing.classList.add('hidden');
                if (currentDot) {
                    currentDot.className = "relative inline-flex rounded-full h-2 w-2 bg-rose-500";
                }
                if (currentText) currentText.textContent = "Izin GPS Ditolak";
                if (currentDist) currentDist.classList.add('hidden');
                if (currentWarning) {
                    currentWarning.classList.remove('hidden');
                    if (currentDistText) currentDistText.textContent = "Akses lokasi ditolak. Harap izinkan GPS pada browser kamu untuk melakukan presensi.";
                }
            } else if (err.code === 2) { // POSITION_UNAVAILABLE
                if (currentNotice) currentNotice.classList.remove('hidden');
                if (currentNoSession) currentNoSession.classList.add('hidden');
                if (currentWarning) currentWarning.classList.add('hidden');
                if (currentBadge) {
                    currentBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-amber-600 hover:text-amber-700 transition-colors cursor-pointer focus:outline-none";
                }
                if (currentPing) {
                    currentPing.className = "animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75";
                    currentPing.classList.remove('hidden');
                }
                if (currentDot) {
                    currentDot.className = "relative inline-flex rounded-full h-2 w-2 bg-amber-500";
                }
                if (currentText) currentText.textContent = "Mencari Sinyal GPS...";
                if (currentDist) currentDist.classList.add('hidden');
                scheduleRetry(3500);
            } else if (err.code === 3) { // TIMEOUT
                if (!currentCoords.lat) {
                    if (currentNotice) currentNotice.classList.remove('hidden');
                    if (currentNoSession) currentNoSession.classList.add('hidden');
                    if (currentWarning) currentWarning.classList.add('hidden');
                    if (currentBadge) {
                        currentBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-amber-600 hover:text-amber-700 transition-colors cursor-pointer focus:outline-none";
                    }
                    if (currentPing) {
                        currentPing.className = "animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75";
                        currentPing.classList.remove('hidden');
                    }
                    if (currentDot) {
                        currentDot.className = "relative inline-flex rounded-full h-2 w-2 bg-amber-500";
                    }
                    if (currentText) currentText.textContent = "Mencari Lokasi GPS...";
                    if (currentDist) currentDist.classList.add('hidden');
                    scheduleRetry(3000);
                }
            }
        };

        // Clear previous watch to prevent watcher stacking/leaks
        if (activeWatchId !== null) {
            navigator.geolocation.clearWatch(activeWatchId);
            activeWatchId = null;
        }

        // Tier 1: Fast initial fix (allow cached coordinates up to 60s for immediate UI responsiveness)
        navigator.geolocation.getCurrentPosition(
            handlePosition,
            () => {},
            { enableHighAccuracy: false, timeout: 6000, maximumAge: 60000 }
        );

        // Tier 2: Refine with high accuracy
        try {
            activeWatchId = navigator.geolocation.watchPosition(
                handlePosition,
                handleError,
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 }
            );
        } catch(e) {
            console.warn("Gagal memulai watchPosition:", e);
        }
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
                setCachedGeofence(currentCoords, data);
                updateUIState(data);
            }
        } catch(e) {
            console.warn("Gagal mengecek status lokasi ke server:", e);
        }
    }

    function updateUIState(data) {
        const geofenceBadge = document.getElementById('geofenceBadge');
        const geofenceBadgeDot = document.getElementById('geofenceBadgeDot');
        const geofenceBadgePing = document.getElementById('geofenceBadgePing');
        const geofenceBadgeText = document.getElementById('geofenceBadgeText');
        const geofenceBadgeDist = document.getElementById('geofenceBadgeDist');
        const gpsSearchingNotice = document.getElementById('gpsSearchingNotice');
        const sessionCard = document.getElementById('sessionCard');
        const outsideWarning = document.getElementById('outsideWarning');
        const outsideDistanceText = document.getElementById('outsideDistanceText');
        const noSessionNotice = document.getElementById('noSessionNotice');
        const sessionSubject = document.getElementById('sessionSubject');
        const sessionTeacher = document.getElementById('sessionTeacher');
        const pinSection = document.getElementById('pinSection');
        const successSection = document.getElementById('successSection');

        const distStr = Math.round(data.distance) >= 1000 
            ? (data.distance / 1000).toFixed(1) + ' km' 
            : Math.round(data.distance) + 'm';

        // Mark GPS as resolved and hide searching notice
        if (gpsSearchingNotice) gpsSearchingNotice.classList.add('hidden');
        isGpsLocked = true;

        if (data.is_within_geofence) {
            isWithinGeofence = true;
            if (geofenceBadge) {
                geofenceBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-emerald-700 hover:text-emerald-800 transition-colors cursor-pointer focus:outline-none";
            }
            if (geofenceBadgePing) geofenceBadgePing.classList.add('hidden');
            if (geofenceBadgeDot) {
                geofenceBadgeDot.className = "relative inline-flex rounded-full h-2 w-2 bg-emerald-500 shadow-xs shadow-emerald-500/50";
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
            isWithinGeofence = false;
            if (geofenceBadge) {
                geofenceBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-rose-600 hover:text-rose-700 transition-colors cursor-pointer focus:outline-none";
            }
            if (geofenceBadgePing) geofenceBadgePing.classList.add('hidden');
            if (geofenceBadgeDot) {
                geofenceBadgeDot.className = "relative inline-flex rounded-full h-2 w-2 bg-rose-500 shadow-xs shadow-rose-500/50";
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
                const sessionCountdown = document.getElementById('sessionCountdown');
                if (sessionCountdown) sessionCountdown.textContent = "00:00 (Waktu Habis)";
                clearInterval(countdownTimer);
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        const sessionCountdown = document.getElementById('sessionCountdown');
        if (!sessionCountdown) return;
        const m = Math.floor(remainingSec / 60);
        const s = remainingSec % 60;
        sessionCountdown.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    // Keypad Logic
    function pressKey(num) {
        const pinFeedback = document.getElementById('pinFeedback');
        if (!isGpsLocked || !isWithinGeofence || !currentCoords.lat) {
            if (pinFeedback) {
                pinFeedback.textContent = "Presensi terkunci: Menunggu lokasi GPS madrasah terverifikasi.";
                pinFeedback.className = "text-xs font-semibold text-amber-600 h-5";
            }
            if (window.triggerHaptic) window.triggerHaptic(100);
            return;
        }
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
            const pinFeedback = document.getElementById('pinFeedback');
            if (pinFeedback) pinFeedback.textContent = "";
        }
    }

    function clearPin() {
        if (window.triggerHaptic) window.triggerHaptic(40);
        enteredPin = "";
        updatePinBoxes();
        const pinFeedback = document.getElementById('pinFeedback');
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
        const pinFeedback = document.getElementById('pinFeedback');
        const pinSection = document.getElementById('pinSection');
        const successSection = document.getElementById('successSection');

        if (!isGpsLocked || !isWithinGeofence || !currentCoords.lat || !currentCoords.lng) {
            if (pinFeedback) {
                pinFeedback.textContent = "Gagal: Lokasi GPS belum terverifikasi di madrasah.";
                pinFeedback.className = "text-xs font-semibold text-rose-600 h-5";
            }
            clearPin();
            return;
        }

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

    // Keyboard navigation
    function handleKeydown(e) {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
        if (!isGpsLocked || !isWithinGeofence || !currentCoords.lat) return;
        const sessionCard = document.getElementById('sessionCard');
        const pinSection = document.getElementById('pinSection');
        if (sessionCard && !sessionCard.classList.contains('hidden') && pinSection && !pinSection.classList.contains('hidden')) {
            if (e.key >= '0' && e.key <= '9') {
                pressKey(e.key);
            } else if (e.key === 'Backspace') {
                backspacePin();
            } else if (e.key === 'Escape' || e.key === 'c' || e.key === 'C') {
                clearPin();
            }
        }
    }

    function handleVisibilityChange() {
        if (document.visibilityState === 'visible') {
            initGeolocation(false);
        }
    }

    function handleFocus() {
        initGeolocation(false);
    }

    function handleDevLocationChanged() {
        initGeolocation(true);
    }

    // Export functions to window for onclick handlers & external hooks
    window.initGeolocation = initGeolocation;
    window.pressKey = pressKey;
    window.backspacePin = backspacePin;
    window.clearPin = clearPin;
    window.submitPin = submitPin;
    window.checkServerStatus = checkServerStatus;

    // Cleanup on SPA page unload
    function cleanup() {
        if (activeWatchId !== null) {
            navigator.geolocation.clearWatch(activeWatchId);
            activeWatchId = null;
        }
        if (gpsRetryTimeout) {
            clearTimeout(gpsRetryTimeout);
            gpsRetryTimeout = null;
        }
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
        if (statusInterval) {
            clearInterval(statusInterval);
            statusInterval = null;
        }
        if (liveClockInterval) {
            clearInterval(liveClockInterval);
            liveClockInterval = null;
        }
        document.removeEventListener('keydown', handleKeydown);
        document.removeEventListener('visibilitychange', handleVisibilityChange);
        window.removeEventListener('focus', handleFocus);
        window.removeEventListener('dev-location-changed', handleDevLocationChanged);
    }

    if (window.MaarifSPA && typeof window.MaarifSPA.onPageUnload === 'function') {
        window.MaarifSPA.onPageUnload(cleanup);
    }

    function init() {
        startLiveClock();
        updatePinBoxes();

        // Check for recent verified location in session
        const cached = getCachedGeofence();
        if (cached && cached.coords && cached.coords.lat && cached.serverData) {
            currentCoords.lat = cached.coords.lat;
            currentCoords.lng = cached.coords.lng;
            updateUIState(cached.serverData);
        }

        // Trigger fresh geolocation lock (in background if cached, or primary if not)
        initGeolocation(false);

        if (statusInterval) clearInterval(statusInterval);
        statusInterval = setInterval(checkServerStatus, 4000);

        document.addEventListener('keydown', handleKeydown);
        document.addEventListener('visibilitychange', handleVisibilityChange);
        window.addEventListener('focus', handleFocus);
        window.addEventListener('dev-location-changed', handleDevLocationChanged);

        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'geolocation' }).then((permissionStatus) => {
                permissionStatus.onchange = () => {
                    if (permissionStatus.state === 'granted') {
                        initGeolocation(true);
                    }
                };
            }).catch(() => {});
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
</script>
@endpush
