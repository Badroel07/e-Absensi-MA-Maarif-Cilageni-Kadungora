@extends('layouts.app')

@section('title', 'Dashboard — MA Ma\'arif Cilageni')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Lexend:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    .heading-font, .font-heading { font-family: 'Lexend', 'Inter', system-ui, sans-serif; }
    .font-sans-card { font-family: 'DM Sans', 'Inter', system-ui, sans-serif; }
    .mono-font, .font-num { font-feature-settings: 'tnum', 'zero'; }
    .radar-pulse { animation: radar-ping 2s cubic-bezier(0, 0, 0.2, 1) infinite; }
    @keyframes radar-ping { 75%, 100% { transform: scale(2); opacity: 0; } }
    .keypad-btn {
        height: 48px;
        font-size: 1.125rem;
        font-weight: 700;
        font-family: 'DM Sans', 'Inter', system-ui, sans-serif;
        font-feature-settings: 'tnum', 'zero';
    }
</style>
@endpush

@section('content')
<div class="space-y-6 font-sans-card">

    <!-- 1. PROFILE SECTION (Card Design) -->
    <section class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-sm" data-purpose="user-card">
        <div class="flex items-start gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-bold text-lg heading-font shrink-0 overflow-hidden">
                @if($student->profile_photo_url)
                    <img src="{{ $student->profile_photo_url }}" loading="lazy" decoding="async" alt="{{ $student->name }}" class="w-full h-full object-cover">
                @else
                    <span class="select-none">{{ substr($student->name, 0, 1) }}</span>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-bold text-slate-900 tracking-tight heading-font truncate">{{ $student->name }}</h2>
                <div class="flex items-center gap-2 mt-1 text-xs text-slate-500 flex-wrap">
                    <span class="text-slate-600 font-medium">Kelas {{ $student->classroom->name ?? '-' }}</span>
                    <span class="text-slate-300">•</span>
                    <span class="text-slate-500 mono-font font-medium">T.A. {{ $student->classroom->academic_year ?? '-' }}</span>
                    <span class="text-slate-300">•</span>
                    <button type="button" onclick="initGeolocation(true)" id="geofenceBadge" title="Ketuk untuk memperbarui lokasi GPS" class="text-emerald-700 font-medium flex items-center gap-1 hover:text-emerald-800 transition-colors cursor-pointer focus:outline-none">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 shrink-0" id="gpsBadgeIcon"></i>
                        <span id="geofenceBadgeText">Mendeteksi Lokasi...</span>
                        <span id="geofenceBadgeDist" class="hidden text-[11px] mono-font opacity-85"></span>
                        <span class="hidden">
                            <span id="geofenceBadgeDot"></span>
                            <span id="geofenceBadgePing"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <div class="flex items-center gap-1.5">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                <span class="font-medium text-slate-600">{{ $todayDay }}, {{ now()->translatedFormat('d M Y') }}</span>
            </div>
            <div class="flex items-center gap-1 text-slate-900">
                <span id="liveClockDisplay" class="liveClockTicker font-bold heading-font mono-font text-sm">{{ now()->format('H:i:s') }}</span>
                <span class="text-[10px] text-slate-400 font-medium">WIB</span>
            </div>
        </div>
    </section>

    <!-- ALERTS (GPS & Status Rombel Warnings) -->
    <div id="outsideWarning" class="hidden bg-rose-50/80 border border-rose-200/70 rounded-2xl p-4 text-center space-y-2.5 shadow-sm">
        <div class="flex items-center justify-center gap-2 text-rose-700">
            <i data-lucide="map-pin-off" class="w-4 h-4"></i>
            <h3 class="text-xs font-bold heading-font">Di Luar Area Madrasah</h3>
        </div>
        <p id="outsideDistanceText" class="text-xs text-rose-700 leading-relaxed">Posisi terdeteksi di luar radius madrasah (maksimal {{ $location->radius_meters ?? 75 }}m).</p>
        <button type="button" onclick="initGeolocation(true)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-rose-200 text-rose-700 font-semibold text-xs hover:bg-rose-50 transition shadow-2xs cursor-pointer">
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5" id="gpsRefreshIcon"></i>
            <span>Cek Ulang GPS</span>
        </button>
    </div>

    <!-- GPS Searching Notice -->
    <div id="gpsSearchingNotice" class="bg-emerald-50/70 border border-emerald-200/70 rounded-2xl p-3.5 text-center space-y-2 shadow-sm transition-all duration-300">
        <div class="flex items-center justify-center gap-2 text-emerald-800">
            <i data-lucide="satellite" class="w-4 h-4 animate-pulse"></i>
            <h4 class="text-xs font-bold heading-font">Memeriksa Lokasi GPS...</h4>
        </div>
        <p class="text-[11px] text-emerald-700 leading-relaxed">Sistem sedang memverifikasi posisi kamu di lingkungan madrasah.</p>
    </div>

    @if(!$student->classroom_id)
        <div class="bg-amber-50/80 border border-amber-200/70 rounded-2xl p-4 text-center space-y-2 shadow-sm">
            <div class="flex items-center justify-center gap-2 text-amber-900">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-700"></i>
                <h3 class="text-xs font-bold heading-font">Belum Terdaftar di Kelas</h3>
            </div>
            <p class="text-xs text-amber-800 leading-relaxed">Akun kamu belum ditempatkan pada rombel kelas aktif. Silakan hubungi wali kelas atau staf Tata Usaha.</p>
        </div>
    @endif

    <!-- ACTIVE CLASS SESSION & PIN INPUT CARD -->
    <div id="sessionCard" class="hidden bg-white rounded-2xl p-4 sm:p-5 border-2 border-emerald-600 shadow-md shadow-emerald-700/10 space-y-4 transition-all duration-300">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 text-xs text-emerald-700 font-bold heading-font">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span>Sesi Presensi Terbuka</span>
                </div>
                <h3 id="sessionSubject" class="text-base sm:text-lg font-bold text-slate-900 heading-font leading-snug mt-0.5">--</h3>
                <p id="sessionTeacher" class="text-xs text-slate-500 font-medium truncate">Bapak/Ibu Guru: --</p>
            </div>
            <div class="text-right pl-3 shrink-0">
                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Sisa Waktu</p>
                <div id="sessionCountdown" class="text-xl sm:text-2xl font-bold text-amber-600 mono-font">--:--</div>
            </div>
        </div>

        <!-- PIN Input Keypad Section -->
        <div id="pinSection" class="space-y-3.5">
            <div class="text-center">
                <label class="text-xs font-semibold text-slate-700">Masukkan 4 Angka PIN Presensi</label>
                <p class="text-[11px] text-slate-400 mt-0.5">Ketik kode PIN yang diinstruksikan oleh guru di kelas</p>
                
                <!-- 4-Digit Display Boxes -->
                <div class="flex items-center justify-center gap-2.5 my-3">
                    <div id="digit0" class="w-12 h-14 sm:w-14 sm:h-16 rounded-xl border-2 border-emerald-600 bg-white shadow-xs flex items-center justify-center text-xl sm:text-2xl font-bold text-slate-800 mono-font shrink-0 ring-2 ring-emerald-500/20 transition-all"></div>
                    <div id="digit1" class="w-12 h-14 sm:w-14 sm:h-16 rounded-xl border-2 border-slate-200 bg-slate-50/50 shadow-xs flex items-center justify-center text-xl sm:text-2xl font-bold text-slate-800 mono-font shrink-0 transition-all"></div>
                    <div id="digit2" class="w-12 h-14 sm:w-14 sm:h-16 rounded-xl border-2 border-slate-200 bg-slate-50/50 shadow-xs flex items-center justify-center text-xl sm:text-2xl font-bold text-slate-800 mono-font shrink-0 transition-all"></div>
                    <div id="digit3" class="w-12 h-14 sm:w-14 sm:h-16 rounded-xl border-2 border-slate-200 bg-slate-50/50 shadow-xs flex items-center justify-center text-xl sm:text-2xl font-bold text-slate-800 mono-font shrink-0 transition-all"></div>
                </div>
                <p id="pinFeedback" class="text-xs font-semibold text-rose-600 h-5"></p>
            </div>

            <!-- Ergonomic Touch Keypad (3x4 Grid) -->
            <div class="grid grid-cols-3 gap-2 pt-0.5 max-w-[280px] mx-auto">
                @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9] as $n)
                    <button type="button" onclick="pressKey('{{ $n }}')" class="keypad-btn bg-slate-100 hover:bg-slate-200 active:bg-slate-300 active:scale-95 text-slate-800 rounded-xl border border-slate-200 shadow-2xs transition-all duration-150 flex items-center justify-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                        {{ $n }}
                    </button>
                @endforeach
                <button type="button" onclick="clearPin()" aria-label="Hapus semua angka PIN" class="keypad-btn bg-slate-100 hover:bg-slate-200 active:bg-slate-300 active:scale-95 text-slate-500 text-xs font-bold rounded-xl border border-slate-200 shadow-2xs transition-all duration-150 flex items-center justify-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                    C
                </button>
                <button type="button" onclick="pressKey('0')" class="keypad-btn bg-slate-100 hover:bg-slate-200 active:bg-slate-300 active:scale-95 text-slate-800 rounded-xl border border-slate-200 shadow-2xs transition-all duration-150 flex items-center justify-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                    0
                </button>
                <button type="button" onclick="backspacePin()" aria-label="Hapus satu angka" class="keypad-btn bg-slate-100 hover:bg-slate-200 active:bg-slate-300 active:scale-95 text-slate-700 rounded-xl border border-slate-200 shadow-2xs transition-all duration-150 flex items-center justify-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-7.172a2 2 0 00-1.414.586L3 12z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Success Verification Display -->
        <div id="successSection" class="hidden py-4 text-center space-y-2">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-sm">
                <i data-lucide="check" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="text-base font-bold text-slate-900 heading-font">Kehadiran Berhasil Dicatat!</h4>
                <p class="text-xs text-emerald-700 font-semibold mt-0.5">Status kehadiran kamu: <strong>HADIR</strong> pada pelajaran ini.</p>
                <p class="text-[11px] text-slate-400 mt-1">Presensi jam pertama otomatis mencatat kehadiran harian kamu.</p>
            </div>
        </div>
    </div>

    <!-- Standby / No Active Session Notice -->
    <div id="noSessionNotice" class="hidden bg-white rounded-2xl py-6 px-6 sm:px-8 border border-slate-200/80 shadow-sm text-center space-y-2.5">
        <div class="w-10 h-10 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center mx-auto border border-slate-100">
            <i data-lucide="clock" class="w-5 h-5 text-slate-400"></i>
        </div>
        <div class="space-y-1">
            <h4 class="text-sm font-bold text-slate-800 heading-font">Belum Ada Presensi Kelas yang Dibuka</h4>
            <p class="text-xs text-slate-500 leading-relaxed max-w-md mx-auto">
                Bapak/Ibu Guru belum membuka presensi kelas. Kotak PIN akan otomatis muncul ketika presensi dibuka.
            </p>
        </div>
    </div>

    <!-- 2. PANDUAN RINGKAS ALUR PRESENSI -->
    <section data-purpose="guide-section">
        <details class="group bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden transition-all duration-200">
            <summary class="flex items-center justify-between p-4 cursor-pointer list-none [&::-webkit-details-marker]:hidden hover:bg-slate-50/60 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-50 text-emerald-700 flex items-center justify-center border border-slate-100 shrink-0">
                        <i data-lucide="info" class="w-5 h-5 text-emerald-700"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 heading-font">Panduan Presensi Siswa</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">Ketuk untuk melihat 3 langkah mudah presensi</p>
                    </div>
                </div>
                <div class="w-6 h-6 flex items-center justify-center text-slate-400">
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200 group-open:rotate-180"></i>
                </div>
            </summary>
            <div class="px-4 pb-4 pt-2 border-t border-slate-100 text-xs text-slate-600 grid grid-cols-1 sm:grid-cols-3 gap-2.5 bg-slate-50/40">
                <div class="p-2.5 bg-white rounded-xl border border-slate-100 flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-md bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center shrink-0 mono-font text-[11px]">1</span>
                    <div class="leading-relaxed"><strong class="text-slate-900">Di Madrasah:</strong> Pastikan GPS aktif dan berada di area madrasah.</div>
                </div>
                <div class="p-2.5 bg-white rounded-xl border border-slate-100 flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-md bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center shrink-0 mono-font text-[11px]">2</span>
                    <div class="leading-relaxed"><strong class="text-slate-900">Masukkan PIN:</strong> Ketik 4 angka PIN yang dibagikan oleh guru di kelas.</div>
                </div>
                <div class="p-2.5 bg-white rounded-xl border border-slate-100 flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-md bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center shrink-0 mono-font text-[11px]">3</span>
                    <div class="leading-relaxed"><strong class="text-slate-900">Otomatis Hadir:</strong> Presensi mapel jam pertama otomatis mencatat kehadiran harian.</div>
                </div>
            </div>
        </details>
    </section>

    <!-- 3. RINGKASAN HARI INI (Metrics Overview) -->
    <section data-purpose="metrics-grid">
        <div class="flex items-baseline justify-between mb-3">
            <h3 class="text-xs font-bold uppercase tracking-wider heading-font text-slate-900">Ringkasan Hari Ini</h3>
            <span class="text-xs text-emerald-700 font-semibold">{{ $hadirCount }} Sesi Hadir</span>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <!-- Total Jadwal -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 shadow-sm flex flex-col justify-between h-24 hover:border-slate-300 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-700">Jadwal Hari Ini</span>
                    <i data-lucide="book-open" class="w-4 h-4 text-slate-500"></i>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-slate-900 tracking-tight heading-font mono-font">{{ $todaySchedules->count() }}</span>
                    <span class="text-xs text-slate-600 font-medium">Mapel</span>
                </div>
            </div>

            <!-- Hadir -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 shadow-sm flex flex-col justify-between h-24 hover:border-emerald-200 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-700">Hadir</span>
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-emerald-700 tracking-tight heading-font mono-font">{{ $hadirCount }}</span>
                    <span class="text-xs text-emerald-700 font-semibold">Sesi</span>
                </div>
            </div>

            <!-- Izin / Sakit -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 shadow-sm flex flex-col justify-between h-24 hover:border-amber-200 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-700">Izin / Sakit</span>
                    <i data-lucide="file-text" class="w-4 h-4 text-amber-600"></i>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-amber-700 tracking-tight heading-font mono-font">{{ $izinCount + $sakitCount }}</span>
                    <span class="text-xs text-slate-600 font-medium">Dispensasi</span>
                </div>
            </div>

            <!-- Alpa -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 shadow-sm flex flex-col justify-between h-24 hover:border-rose-200 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-700">Alpa</span>
                    <i data-lucide="x-circle" class="w-4 h-4 text-rose-600"></i>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-rose-700 tracking-tight heading-font mono-font">{{ $alpaCount }}</span>
                    <span class="text-xs text-slate-600 font-medium">Tanpa Ket.</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. JADWAL & PRESENSI KELAS HARI INI (Unified Timeline Card Flow) -->
    <section class="space-y-3.5 pt-2" data-purpose="schedule-section">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold text-slate-900 tracking-tight heading-font">Jadwal & Presensi Kelas Hari Ini</h3>
            <a href="{{ route('siswa.schedule') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 hover:text-emerald-800 transition-colors">
                <span>Jadwal Mingguan</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        @if($todaySchedules->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm p-8 text-center space-y-3">
                <i data-lucide="calendar-off" class="w-7 h-7 text-slate-300 mx-auto"></i>
                <p class="text-xs text-slate-500">Tidak ada jadwal pelajaran pada hari {{ $todayDay }}.</p>
            </div>
        @else
            <div class="space-y-3.5">
                @php $nowTime = now()->format('H:i:s'); @endphp
                @foreach($todaySchedules as $index => $sch)
                    @php
                        $matchedAttendance = $todayAttendances->firstWhere('schedule_id', $sch->id);
                        $session = $sch->todaySession;
                        $startTime = strlen($sch->start_time) === 5 ? $sch->start_time . ':00' : $sch->start_time;
                        $endTime = strlen($sch->end_time) === 5 ? $sch->end_time . ':00' : $sch->end_time;
                        $isWithinSchedule = ($nowTime >= $startTime && $nowTime <= $endTime);
                        $isCurrentSlot = ($session && $session->isActive()) || $isWithinSchedule;
                    @endphp

                    <article class="bg-white rounded-2xl border {{ $isCurrentSlot ? 'border-emerald-300 ring-1 ring-emerald-400/20' : 'border-slate-200/70' }} shadow-sm p-4 space-y-3.5 hover:border-slate-300/80 transition-colors">
                        <!-- Top Row: Badge Number + Teacher + Subject + Time Badge -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1 min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded {{ $isCurrentSlot ? 'bg-emerald-50 text-emerald-700 font-bold' : 'bg-slate-100 text-slate-700 border border-slate-200 font-semibold' }} text-xs heading-font mono-font">#{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-xs font-medium text-slate-500 truncate">{{ $sch->teacher->name }}</span>
                                </div>
                                <h4 class="text-base font-bold text-slate-900 tracking-tight heading-font leading-snug">{{ $sch->subject->name }}</h4>
                            </div>
                            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-100 text-xs font-medium text-slate-600 shrink-0">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span class="mono-font font-semibold text-slate-800">{{ substr($sch->start_time, 0, 5) }} – {{ substr($sch->end_time, 0, 5) }}</span>
                            </div>
                        </div>

                        <!-- Status & Action Bar -->
                        @if($matchedAttendance)
                            <div class="bg-slate-50/80 border border-slate-100 rounded-xl px-3.5 py-2.5 flex items-center justify-between">
                                @if($matchedAttendance->status === 'HADIR')
                                    <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span>Hadir — Tercatat {{ $matchedAttendance->verified_at ? $matchedAttendance->verified_at->format('H:i') . ' WIB' : substr($sch->start_time, 0, 5) . ' WIB' }}</span>
                                    </div>
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/60 px-2 py-0.5 rounded-md uppercase tracking-wider">Tuntas</span>
                                @elseif($matchedAttendance->status === 'IZIN')
                                    <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-700">
                                        <i data-lucide="file-text" class="w-4 h-4 text-amber-600 shrink-0"></i>
                                        <span>Izin</span>
                                    </div>
                                    <span class="text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200/60 px-2 py-0.5 rounded-md uppercase tracking-wider">Dispensasi</span>
                                @elseif($matchedAttendance->status === 'SAKIT')
                                    <div class="flex items-center gap-1.5 text-xs font-semibold text-sky-700">
                                        <i data-lucide="activity" class="w-4 h-4 text-sky-600 shrink-0"></i>
                                        <span>Sakit</span>
                                    </div>
                                    <span class="text-[10px] font-bold text-sky-700 bg-sky-50 border border-sky-200/60 px-2 py-0.5 rounded-md uppercase tracking-wider">Dispensasi</span>
                                @else
                                    <div class="flex items-center gap-1.5 text-xs font-semibold text-rose-700">
                                        <i data-lucide="x-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                                        <span>Alpa</span>
                                    </div>
                                    <span class="text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-200/60 px-2 py-0.5 rounded-md uppercase tracking-wider">Tanpa Ket.</span>
                                @endif
                            </div>
                        @elseif($session && $session->isActive())
                            <div class="bg-amber-50/70 border border-amber-200/70 rounded-xl px-3.5 py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs font-bold text-amber-800 heading-font">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                    </span>
                                    <span>Sesi Kelas Aktif</span>
                                </div>
                                <a href="#sessionCard" class="text-xs font-bold text-amber-900 bg-amber-200 hover:bg-amber-300 px-3 py-1 rounded-lg transition active:scale-95">
                                    Ketik PIN &rarr;
                                </a>
                            </div>
                        @else
                            <div class="bg-slate-50/80 border border-slate-100 rounded-xl px-3.5 py-2 flex items-center gap-2 text-xs {{ $isWithinSchedule ? 'text-emerald-700 font-medium' : 'text-slate-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isWithinSchedule ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                <span>{{ $isWithinSchedule ? 'Jam pelajaran berlangsung — menunggu guru membuka presensi' : 'Belum dimulai' }}</span>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>

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
