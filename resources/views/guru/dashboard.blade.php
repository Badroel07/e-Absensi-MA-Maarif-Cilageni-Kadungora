@extends('layouts.app')

@section('title', 'Dashboard — MA Ma\'arif Cilageni')

@push('styles')
<style>
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
                @if($teacher->profile_photo_url)
                    <img src="{{ $teacher->profile_photo_url }}" loading="lazy" decoding="async" alt="{{ $teacher->name }}" class="w-full h-full object-cover">
                @else
                    <span class="select-none">{{ substr($teacher->name, 0, 1) }}</span>
                @endif
            </div>

            <!-- Identity Info -->
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Dashboard Guru</span>
                </div>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 heading-font tracking-tight truncate mt-0.5">
                    {{ $teacher->name }}
                </h1>
                <div class="mt-1 space-y-0.5 text-xs">
                    <div class="mono-font font-semibold text-slate-700 leading-tight">{{ $teacher->identity_number }}</div>
                    <div class="text-slate-500 font-medium truncate leading-tight">{{ $teacher->email }}</div>
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
            <div class="liveClockTicker text-xl sm:text-2xl md:text-3xl font-bold text-slate-900 mono-font tracking-tight">
                {{ now()->format('H:i:s') }} <span class="text-xs font-semibold text-slate-500">WIB</span>
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

    <!-- Outside Check-in Gating Warning Box (If not checked in today at Kiosk) — JS hides when outside geofence -->
    @if(!$hasCheckedIn)
        <div id="checkinWarning" class="hidden bg-amber-50/90 border border-amber-200 rounded-3xl p-6 text-center space-y-4 shadow-xs">
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

    <!-- 2. QUICK METRIC & ATTENDANCE SUMMARY SECTION -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 heading-font uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="bar-chart-2" class="w-4 h-4 text-emerald-700"></i>
                <span>Ringkasan Mengajar Hari Ini</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Metric 1: Total Jadwal Mengajar -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Jadwal Mengajar</span>
                    <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="mt-3">
                    <span class="text-2xl sm:text-3xl font-bold text-slate-900 mono-font leading-none block">
                        {{ $schedules->count() }}
                    </span>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-1 truncate">Mata Pelajaran</p>
                </div>
            </div>

            <!-- Metric 2: Sesi Aktif -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-amber-200 transition">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider truncate">Sesi Aktif</span>
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="mt-3">
                    <span class="text-2xl sm:text-3xl font-bold text-amber-700 mono-font leading-none block">
                        {{ $activeSessionsCount }}
                    </span>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-1 truncate">Sesi Terbuka</p>
                </div>
            </div>

            <!-- Metric 3: Tuntas & Disimpan -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-emerald-200 transition">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider truncate">Selesai & Disimpan</span>
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="mt-3">
                    <span class="text-2xl sm:text-3xl font-bold text-emerald-700 mono-font leading-none block">
                        {{ $lockedSessionsCount }}
                    </span>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-1 truncate">Kelas Selesai</p>
                </div>
            </div>

            <!-- Metric 4: Sisa Kelas Mengajar -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Sisa Kelas</span>
                    <svg class="w-5 h-5 {{ $pendingSchedules->count() > 0 ? 'text-amber-600' : 'text-emerald-600' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="mt-3">
                    <span class="text-2xl sm:text-3xl font-bold {{ $pendingSchedules->count() > 0 ? 'text-amber-700' : 'text-emerald-700' }} mono-font leading-none block">
                        {{ $pendingSchedules->count() }}
                    </span>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-1 truncate">{{ $pendingSchedules->count() > 0 ? 'Kelas Menunggu' : 'Semua Tuntas' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. MAIN DASHBOARD CONTENT (Single Column Flow) -->
    <div class="space-y-8 sm:space-y-10">
        
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
        <div id="jadwal-mengajar" class="space-y-3 scroll-mt-20">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900 heading-font uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="book-open" class="w-4 h-4 text-emerald-700"></i>
                    <span>Jadwal Mengajar Hari Ini</span>
                </h2>
                <a href="{{ route('guru.history') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-maarif-700 hover:text-maarif-800 active:text-maarif-900 transition-colors cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                    <span>Riwayat Kelas</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-3.5 overflow-hidden">
                @if($schedules->isEmpty())
                    <div class="py-10 text-center text-slate-400 space-y-2">
                        <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-xs font-medium text-slate-500">Tidak ada jadwal mengajar terjadwal untuk hari {{ $todayDay }}.</p>
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
                                $cardStateClass = match(true) {
                                    (bool)($session && $session->isActive()) => 'bg-amber-50/30 border-amber-300 ring-1 ring-amber-400/30 shadow-xs',
                                    (bool)($session && $session->isLocked()) => 'bg-slate-50/40 border-slate-200/80 hover:bg-slate-50/70',
                                    (bool)($session && !$session->isLocked() && !$session->isActive()) => 'bg-rose-50/30 border-rose-300 ring-1 ring-rose-400/20 shadow-xs',
                                    (bool)$isWithinSchedule => 'bg-emerald-50/30 border-emerald-500/80 ring-1 ring-emerald-500/20 shadow-xs',
                                    default => 'bg-slate-50/60 hover:bg-white border-slate-200/80 hover:border-slate-300/90 shadow-2xs hover:shadow-xs',
                                };
                            @endphp
                            <div class="p-4 sm:p-5 rounded-2xl border transition-all duration-150 flex flex-col gap-3.5 {{ $cardStateClass }} overflow-hidden">
                                
                                {{-- Top Row: Sesi (Kiri) + Mapel & Info Kelas & Waktu Pelajaran (Rata Kiri Sejajar) --}}
                                <div class="flex items-start space-x-3.5 min-w-0">
                                    {{-- Nomor Sesi Murni (Monospace, tanpa kotak) --}}
                                    <span class="mono-font font-bold text-base sm:text-lg {{ $isWithinSchedule ? 'text-emerald-700 font-extrabold' : 'text-slate-400' }} shrink-0 w-7 text-center select-none pt-0.5 transition-colors">
                                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>

                                    {{-- Detail Mata Pelajaran, Info Kelas, dan Rentang Waktu (Rata Kiri Sejajar) --}}
                                    <div class="min-w-0 flex-1 space-y-1.5">
                                        <h4 class="text-base sm:text-lg font-bold text-slate-900 heading-font leading-snug tracking-tight break-words">
                                            {{ $sch->subject->name }}
                                        </h4>
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-slate-700">
                                                <i data-lucide="door-closed" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                                <span>Kelas {{ $sch->classroom->name }}</span>
                                            </span>
                                            @if(!empty($sch->subject->code))
                                                <span class="mono-font text-[11px] text-slate-500 font-medium">
                                                    Kode: {{ $sch->subject->code }}
                                                </span>
                                            @endif
                                            <div class="font-semibold mono-font text-xs flex items-center gap-1.5 tracking-wide {{ $isWithinSchedule ? 'text-emerald-700 font-bold' : 'text-slate-600' }}">
                                                <i data-lucide="clock" class="w-3.5 h-3.5 {{ $isWithinSchedule ? 'text-emerald-600' : 'text-slate-400' }} shrink-0"></i>
                                                <span class="leading-none">{{ substr($sch->start_time, 0, 5) }} – {{ substr($sch->end_time, 0, 5) }}</span>
                                                <span class="text-[11px] {{ $isWithinSchedule ? 'text-emerald-600 font-bold' : 'text-slate-400 font-medium' }} uppercase tracking-wider leading-none">WIB</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Session Status Bar (Clean, Non-Pill, Human Crafted) --}}
                                @if($session)
                                    @if($session->isActive())
                                        <div class="flex items-center justify-between gap-3 px-3 py-2 rounded-xl bg-white/80 border border-amber-200/90 shadow-2xs">
                                            <div class="flex items-center gap-2 text-xs font-semibold text-amber-900">
                                                <span class="relative flex h-2 w-2 shrink-0">
                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                                </span>
                                                <span>Sesi Presensi Aktif</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[11px] font-medium text-slate-500">PIN:</span>
                                                <span class="mono-font font-bold text-sm text-slate-900 tracking-wider bg-amber-50 px-2 py-0.5 rounded border border-amber-200">{{ $session->pin_code }}</span>
                                            </div>
                                        </div>
                                    @elseif(!$session->isLocked())
                                        <div class="flex items-center gap-1.5 text-xs font-semibold text-rose-700">
                                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-rose-600 shrink-0"></i>
                                            <span>Perlu Konfirmasi Keterangan Kehadiran Siswa</span>
                                        </div>
                                    @endif
                                @endif

                                {{-- Action Buttons Area (Solid, Tactile, No Pill, Strict Button Standard) --}}
                                <div class="pt-3 border-t border-slate-200/60 flex flex-col gap-2.5">
                                    @if(!$session)
                                        @if($hasCheckedIn)
                                            @if(!$isWithinSchedule)
                                                <div class="w-full flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl bg-slate-100/70 border border-slate-200/60 text-xs text-slate-500 font-medium">
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                                        <span>Sesi dapat dibuka pukul {{ substr($sch->start_time,0,5) }}–{{ substr($sch->end_time,0,5) }} WIB</span>
                                                    </span>
                                                    <span class="text-[11px] font-mono text-slate-400 uppercase tracking-wider">Standby</span>
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
                                                    <button type="submit" disabled class="sessionSubmitBtn opacity-50 cursor-not-allowed w-full sm:flex-1 h-11 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 active:scale-[0.98] text-white font-semibold px-4 rounded-xl text-xs inline-flex items-center justify-center gap-2 transition-all duration-150 shadow-xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-maarif-600 leading-none">
                                                        <span>Buka Sesi Presensi</span>
                                                        <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <div class="w-full py-2.5 px-3.5 rounded-xl bg-slate-100/70 border border-slate-200/60 text-xs text-slate-500 font-medium text-center leading-snug">
                                                Silakan lakukan presensi masuk terlebih dahulu untuk membuka sesi
                                            </div>
                                        @endif
                                    @elseif($session->isActive())
                                        <a href="{{ route('guru.session.show', $session) }}" class="w-full min-h-[44px] bg-amber-600 hover:bg-amber-700 active:bg-amber-800 active:scale-[0.98] text-white font-semibold py-2.5 px-4 rounded-xl text-xs sm:text-sm inline-flex items-center justify-center gap-2 transition-all duration-150 shadow-xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-amber-500 leading-none text-center">
                                            <i data-lucide="qr-code" class="w-4 h-4 shrink-0"></i>
                                            <span>Buka Layar Presensi Kelas</span>
                                        </a>
                                    @elseif($session->isLocked())
                                        <div class="w-full flex flex-col gap-2 py-1">
                                            {{-- Status: icon + text sama-sama mulai di indent nama mapel (pl-[42px] = w-7 + space-x-3.5) --}}
                                            <div class="flex items-center gap-2 pl-[42px] min-w-0">
                                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                                <span class="text-xs font-medium text-slate-600 leading-snug">Selesai & Tersimpan</span>
                                            </div>
                                            {{-- Button: baris sendiri rata kanan --}}
                                            <div class="flex justify-end">
                                                <a href="{{ route('guru.session.reconcile', $session) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-maarif-700 hover:text-maarif-800 active:text-maarif-900 transition-colors cursor-pointer py-1 shrink-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                                                    <span>Lihat Rekap Kehadiran</span>
                                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                                </a>
                                            </div>
                                        </div>
                                @else
                                    <a href="{{ route('guru.session.reconcile', $session) }}" class="w-full min-h-[44px] bg-rose-600 hover:bg-rose-700 active:bg-rose-800 active:scale-[0.98] text-white font-semibold py-2.5 px-4 rounded-xl text-xs sm:text-sm inline-flex items-center justify-center gap-2 transition-all duration-150 shadow-xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-rose-600 leading-snug text-center">
                                        <i data-lucide="clipboard-check" class="w-4 h-4 shrink-0"></i>
                                        <span>Catat Keterangan Siswa & Simpan</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

        <!-- Teaching Completion Lock & Kiosk Status Card -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900 heading-font uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-700"></i>
                    <span>Status Presensi Harian</span>
                </h2>
            </div>

            <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4 overflow-hidden">
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
                                <span class="font-semibold text-emerald-700 font-mono">{{ substr($dailyAttendance->check_out_time, 0, 5) }} WIB (SELESAI)</span>
                            @else
                                <span class="font-semibold text-slate-400">Belum Pulang</span>
                            @endif
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        @if($dailyAttendance?->check_out_time)
                            Alhamdulillah, Bapak/Ibu Guru telah menyelesaikan seluruh tugas mengajar dan sudah melakukan presensi pulang. Anda sudah boleh pulang dan beristirahat. Terima kasih atas dedikasi mengajar hari ini!
                        @elseif($pendingSchedules->isNotEmpty())
                            Ada <strong>{{ $pendingSchedules->count() }}</strong> kelas yang belum selesai disimpan keterangannya. Bapak/Ibu Guru perlu menyelesaikan pencatatan kehadiran kelas terlebih dahulu sebelum dapat presensi pulang di Layar Presensi Madrasah.
                        @else
                            Seluruh kelas mengajar hari ini telah selesai dicatat. Bapak/Ibu Guru sudah dapat melakukan presensi pulang di Layar Presensi Madrasah.
                        @endif
                    </p>

                    <!-- Quick Action Button to Kiosk Scan -->
                    <div class="pt-2">
                        @if($dailyAttendance?->check_out_time)
                            <div class="w-full py-2.5 px-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold inline-flex items-center justify-center gap-2">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                <span>Presensi Pulang Selesai — Selamat Beristirahat</span>
                            </div>
                        @elseif(!$hasCheckedIn)
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
        </div>

        <!-- Today's Session Log Feed (Riwayat Kelas Hari Ini) -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900 heading-font uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-emerald-700"></i>
                    <span>Riwayat Kelas Hari Ini</span>
                </h2>
                <span class="mono-font text-xs font-semibold text-slate-500">
                    {{ $schedules->filter(fn($s) => $s->todaySession)->count() }} Tercatat
                </span>
            </div>

            <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
                @php
                    $recordedSessions = $schedules->filter(fn($s) => $s->todaySession);
                @endphp

                @if($recordedSessions->isEmpty())
                    <div class="text-center py-8 text-slate-400 space-y-2">
                        <i data-lucide="clipboard-x" class="w-10 h-10 mx-auto text-slate-300"></i>
                        <p class="text-xs font-medium text-slate-500">Belum ada sesi kelas yang dibuka hari ini.</p>
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
                            <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/50 hover:bg-white hover:border-slate-300 transition-all duration-150 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs shadow-2xs">
                                <div class="min-w-0 flex-1 space-y-1">
                                    <div class="flex items-center gap-2.5 flex-wrap">
                                        <h4 class="text-sm font-bold text-slate-900 heading-font leading-snug">
                                            {{ $sch->subject->name }}
                                        </h4>
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600">
                                            <i data-lucide="door-closed" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                            <span>Kelas {{ $sch->classroom->name }}</span>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 text-[11px] text-slate-500 flex-wrap">
                                        <span class="inline-flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                            <span>Mulai: <strong class="mono-font font-semibold text-slate-700">{{ $sess->started_at?->format('H:i') }} WIB</strong></span>
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <i data-lucide="key-round" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                            <span>PIN: <strong class="mono-font font-bold text-slate-800 tracking-wider">{{ $sess->pin_code }}</strong></span>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center sm:items-end justify-between sm:justify-center flex-row sm:flex-col gap-1.5 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200/60">
                                    @if($sess->isLocked())
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">
                                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                            <span>Selesai</span>
                                        </span>
                                    @elseif($sess->isActive())
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700">
                                            <span class="relative flex h-2 w-2 shrink-0">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                            </span>
                                            <span>Sesi Aktif</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-rose-700">
                                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-rose-600 shrink-0"></i>
                                            <span>Perlu Konfirmasi</span>
                                        </span>
                                    @endif
                                    <div class="mono-font text-[11px] text-slate-600 flex items-center gap-2 font-medium">
                                        <span class="text-emerald-700 font-semibold">{{ $hadirCount }} Hadir</span>
                                        <span class="text-slate-300">/</span>
                                        <span class="text-amber-700 font-semibold">{{ $izinCount }} Izin</span>
                                        <span class="text-slate-300">/</span>
                                        <span class="text-rose-700 font-semibold">{{ $alpaCount }} Alpa</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

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

    let liveClockInterval = null;
    let statusInterval = null;
    let currentCoords = { lat: null, lng: null };
    let activeWatchId = null;
    let gpsRetryTimeout = null;
    let lastGeoAttempt = 0;
    let isWithinGeofence = false;
    let isGpsLocked = false;

    // Live Digital Clock Ticker
    function updateLiveClock() {
        const now = (typeof window.getServerNow === 'function') ? window.getServerNow() : new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const timeStr = `${hours}:${minutes}:${seconds} <span class="text-xs font-semibold text-slate-500">WIB</span>`;

        document.querySelectorAll('.liveClockTicker').forEach(el => {
            el.innerHTML = timeStr;
        });
    }

    function initGeolocation(isManual = false) {
        const now = Date.now();
        if (!isManual && (now - lastGeoAttempt < 1500)) {
            return;
        }
        lastGeoAttempt = now;

        const refreshIcon = document.getElementById('gpsRefreshIcon');
        if (isManual && refreshIcon) {
            refreshIcon.classList.add('animate-spin');
            setTimeout(() => refreshIcon.classList.remove('animate-spin'), 1500);
        }

        // Lock session buttons while GPS is searching / re-acquiring if not locked
        if (!isGpsLocked || isManual) {
            isGpsLocked = false;
            isWithinGeofence = false;
            document.querySelectorAll('.sessionSubmitBtn').forEach(btn => {
                btn.setAttribute('disabled', 'disabled');
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            });
        }

        const geofenceBadge = document.getElementById('geofenceBadge');
        const geofenceBadgeDot = document.getElementById('geofenceBadgeDot');
        const geofenceBadgePing = document.getElementById('geofenceBadgePing');
        const geofenceBadgeText = document.getElementById('geofenceBadgeText');
        const geofenceBadgeDist = document.getElementById('geofenceBadgeDist');

        if (!navigator.geolocation) {
            if (geofenceBadge) {
                geofenceBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-rose-600 hover:text-rose-700 transition-colors cursor-pointer focus:outline-none";
            }
            if (geofenceBadgePing) geofenceBadgePing.classList.add('hidden');
            if (geofenceBadgeDot) {
                geofenceBadgeDot.className = "relative inline-flex rounded-full h-2 w-2 bg-rose-500";
            }
            if (geofenceBadgeText) geofenceBadgeText.textContent = "GPS Tidak Didukung";
            if (geofenceBadgeDist) geofenceBadgeDist.classList.add('hidden');

            const cw = document.getElementById('checkinWarning');
            if (cw) cw.classList.add('hidden');
            document.querySelectorAll('.sessionSubmitBtn').forEach(btn => {
                btn.setAttribute('disabled', 'disabled');
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            });
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
            const cwErr = document.getElementById('checkinWarning');
            if (cwErr) cwErr.classList.add('hidden');
            document.querySelectorAll('.sessionSubmitBtn').forEach(btn => {
                btn.setAttribute('disabled', 'disabled');
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            });

            const currentBadge = document.getElementById('geofenceBadge');
            const currentPing = document.getElementById('geofenceBadgePing');
            const currentDot = document.getElementById('geofenceBadgeDot');
            const currentText = document.getElementById('geofenceBadgeText');
            const currentDist = document.getElementById('geofenceBadgeDist');

            if (err.code === 1) { // PERMISSION_DENIED
                if (currentBadge) {
                    currentBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-rose-600 hover:text-rose-700 transition-colors cursor-pointer focus:outline-none";
                }
                if (currentPing) currentPing.classList.add('hidden');
                if (currentDot) {
                    currentDot.className = "relative inline-flex rounded-full h-2 w-2 bg-rose-500";
                }
                if (currentText) currentText.textContent = "Izin Lokasi Ditolak";
                if (currentDist) currentDist.classList.add('hidden');
            } else if (err.code === 2) { // POSITION_UNAVAILABLE
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
        const outsideWarning = document.getElementById('outsideWarning');
        const outsideDistanceText = document.getElementById('outsideDistanceText');
        const checkinWarning = document.getElementById('checkinWarning');

        const distStr = Math.round(data.distance) >= 1000 
            ? (data.distance / 1000).toFixed(1) + ' km' 
            : Math.round(data.distance) + 'm';

        if (data.is_within_geofence) {
            isWithinGeofence = true;
            isGpsLocked = true;

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
            if (checkinWarning) checkinWarning.classList.remove('hidden');

            // Enable open session buttons
            document.querySelectorAll('.sessionSubmitBtn').forEach(btn => {
                btn.removeAttribute('disabled');
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        } else {
            isWithinGeofence = false;
            isGpsLocked = true;
            if (checkinWarning) checkinWarning.classList.add('hidden');

            if (geofenceBadge) {
                geofenceBadge.className = "group inline-flex items-center gap-2 text-xs font-semibold text-rose-600 hover:text-rose-700 transition-colors cursor-pointer focus:outline-none";
            }
            if (geofenceBadgePing) {
                geofenceBadgePing.className = "animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75";
                geofenceBadgePing.classList.remove('hidden');
            }
            if (geofenceBadgeDot) {
                geofenceBadgeDot.className = "relative inline-flex rounded-full h-2 w-2 bg-rose-500";
            }
            if (geofenceBadgeText) geofenceBadgeText.textContent = "Di Luar Area Madrasah";
            if (geofenceBadgeDist) {
                geofenceBadgeDist.textContent = `(${distStr})`;
                geofenceBadgeDist.classList.remove('hidden');
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

    function handleFormSubmit(e) {
        if (!isWithinGeofence || !currentCoords.lat) {
            e.preventDefault();
            alert('Presensi terkunci: Posisi GPS belum terverifikasi berada di dalam lingkungan madrasah.');
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

    window.initGeolocation = initGeolocation;
    window.checkServerStatus = checkServerStatus;

    function cleanup() {
        if (activeWatchId !== null) {
            navigator.geolocation.clearWatch(activeWatchId);
            activeWatchId = null;
        }
        if (gpsRetryTimeout) {
            clearTimeout(gpsRetryTimeout);
            gpsRetryTimeout = null;
        }
        if (liveClockInterval) {
            clearInterval(liveClockInterval);
            liveClockInterval = null;
        }
        if (statusInterval) {
            clearInterval(statusInterval);
            statusInterval = null;
        }
        document.querySelectorAll('.openSessionForm').forEach(form => {
            form.removeEventListener('submit', handleFormSubmit);
        });
        document.removeEventListener('visibilitychange', handleVisibilityChange);
        window.removeEventListener('focus', handleFocus);
        window.removeEventListener('dev-location-changed', handleDevLocationChanged);
    }

    if (window.MaarifSPA && typeof window.MaarifSPA.onPageUnload === 'function') {
        window.MaarifSPA.onPageUnload(cleanup);
    }

    function init() {
        if (liveClockInterval) clearInterval(liveClockInterval);
        liveClockInterval = setInterval(updateLiveClock, 1000);
        updateLiveClock();

        // Restore verified GPS state immediately if available from recent check in this session
        const cached = getCachedGeofence();
        if (cached && cached.coords && cached.coords.lat && cached.serverData) {
            currentCoords.lat = cached.coords.lat;
            currentCoords.lng = cached.coords.lng;
            updateUIState(cached.serverData);
        }

        initGeolocation(false);

        document.querySelectorAll('.openSessionForm').forEach(form => {
            form.addEventListener('submit', handleFormSubmit);
        });

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

<!-- Spacer to prevent floating simulator + bottom nav overlap on mobile -->
<div class="h-6 md:hidden" aria-hidden="true"></div>
@endsection
