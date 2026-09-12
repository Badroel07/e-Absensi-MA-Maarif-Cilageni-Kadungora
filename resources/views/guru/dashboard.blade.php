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
</style>
@endpush

@section('content')
<div class="space-y-6 font-sans-card">

    <!-- 1. PROFILE SECTION (Card Design) -->
    <section class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-sm" data-purpose="user-card">
        <div class="flex items-start gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-bold text-lg heading-font shrink-0 overflow-hidden">
                @if($teacher->profile_photo_url)
                    <img src="{{ $teacher->profile_photo_url }}" loading="lazy" decoding="async" alt="{{ $teacher->name }}" class="w-full h-full object-cover">
                @else
                    <span class="select-none">{{ substr($teacher->name, 0, 1) }}</span>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-1">
                    <p class="text-[11px] font-medium text-slate-400 tracking-wide mono-font">NIP {{ $teacher->identity_number }}</p>
                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold text-[11px]">Guru Mapel</span>
                </div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight heading-font truncate mt-0.5">{{ $teacher->name }}</h2>
                <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                    @if($schedules->first())
                        <span class="text-slate-600 font-medium">Kelas {{ $schedules->first()->classroom->name }}</span>
                        <span class="text-slate-300">•</span>
                    @endif
                    <button type="button" onclick="initGeolocation(true)" id="geofenceBadge" title="Ketuk untuk memperbarui lokasi GPS" class="text-emerald-700 font-medium flex items-center gap-1 hover:text-emerald-800 transition-colors cursor-pointer focus:outline-none">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 shrink-0"></i>
                        <span id="geofenceBadgeText">Mendeteksi Lokasi...</span>
                        <span id="geofenceBadgeDist" class="hidden text-[11px] mono-font opacity-85"></span>
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
                <span class="liveClockTicker font-bold heading-font mono-font text-sm">{{ now()->format('H:i:s') }}</span>
                <span class="text-[10px] text-slate-400 font-medium">WIB</span>
            </div>
        </div>
    </section>

    <!-- ALERTS (GPS & Check-in Warnings) -->
    <div id="outsideWarning" class="hidden bg-rose-50/80 border border-rose-200/70 rounded-2xl p-4 text-center space-y-2.5 shadow-sm">
        <div class="flex items-center justify-center gap-2 text-rose-700">
            <i data-lucide="map-pin-off" class="w-4 h-4"></i>
            <h3 class="text-xs font-bold heading-font">Di Luar Area Madrasah</h3>
        </div>
        <p id="outsideDistanceText" class="text-xs text-rose-700 leading-relaxed">Posisi terdeteksi di luar radius madrasah (maksimal {{ $location->radius_meters ?? 75 }}m).</p>
        <button type="button" onclick="initGeolocation(true)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-rose-200 text-rose-700 font-semibold text-xs hover:bg-rose-50 transition shadow-2xs">
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5" id="gpsRefreshIcon"></i>
            <span>Cek Ulang GPS</span>
        </button>
    </div>

    @if(!$hasCheckedIn)
        <div id="checkinWarning" class="hidden bg-amber-50/80 border border-amber-200/70 rounded-2xl p-4 text-center space-y-2.5 shadow-sm">
            <h3 class="text-xs font-bold text-amber-900 heading-font">Presensi Masuk Diperlukan</h3>
            <p class="text-xs text-amber-800 leading-relaxed">Silakan lakukan presensi masuk di Layar Madrasah sebelum membuka sesi presensi siswa.</p>
            <a href="{{ route('guru.scan') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow-xs">
                <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                <span>Pindai QR Presensi Masuk</span>
            </a>
        </div>
    @endif

    <!-- 2. PANDUAN RINGKAS ALUR PRESENSI (Accordion directly below profile) -->
    <section data-purpose="guide-section">
        <details class="group bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden transition-all duration-200">
            <summary class="flex items-center justify-between p-4 cursor-pointer list-none [&::-webkit-details-marker]:hidden hover:bg-slate-50/60 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-50 text-emerald-700 flex items-center justify-center border border-slate-100 shrink-0">
                        <i data-lucide="info" class="w-5 h-5 text-emerald-700"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 heading-font">Panduan Ringkas Alur Presensi</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">Ketuk untuk melihat 4 langkah alur presensi</p>
                    </div>
                </div>
                <div class="w-6 h-6 flex items-center justify-center text-slate-400">
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200 group-open:rotate-180"></i>
                </div>
            </summary>
            <div class="px-4 pb-4 pt-2 border-t border-slate-100 text-xs text-slate-600 grid grid-cols-1 sm:grid-cols-2 gap-2.5 bg-slate-50/40">
                <div class="p-2.5 bg-white rounded-xl border border-slate-100 flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-md bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center shrink-0 mono-font text-[11px]">1</span>
                    <div class="leading-relaxed"><strong class="text-slate-900">Presensi Masuk:</strong> Pindai QR di Layar Presensi Madrasah.</div>
                </div>
                <div class="p-2.5 bg-white rounded-xl border border-slate-100 flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-md bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center shrink-0 mono-font text-[11px]">2</span>
                    <div class="leading-relaxed"><strong class="text-slate-900">Buka Kelas:</strong> Tentukan durasi & bagikan PIN ke siswa.</div>
                </div>
                <div class="p-2.5 bg-white rounded-xl border border-slate-100 flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-md bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center shrink-0 mono-font text-[11px]">3</span>
                    <div class="leading-relaxed"><strong class="text-slate-900">Rekap:</strong> Set status Izin/Sakit/Alpa, lalu simpan.</div>
                </div>
                <div class="p-2.5 bg-white rounded-xl border border-slate-100 flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-md bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center shrink-0 mono-font text-[11px]">4</span>
                    <div class="leading-relaxed"><strong class="text-slate-900">Presensi Pulang:</strong> Pindai QR pulang setelah seluruh kelas selesai.</div>
                </div>
            </div>
        </details>
    </section>

    <!-- 3. RINGKASAN HARI INI (Metrics Overview) -->
    <section data-purpose="metrics-grid">
        <div class="flex items-baseline justify-between mb-3">
            <h3 class="text-xs font-bold uppercase tracking-wider heading-font text-slate-900">Ringkasan Hari Ini</h3>
            <span class="text-xs text-emerald-700 font-semibold">{{ $lockedSessionsCount }} Sesi Selesai</span>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <!-- Total Jadwal -->
            <div class="bg-white rounded-2xl border border-slate-200/70 p-3.5 shadow-sm flex flex-col justify-between h-24">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-xs font-medium text-slate-500">Total Jadwal</span>
                    <i data-lucide="book-open" class="w-4 h-4 text-slate-400"></i>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-slate-900 tracking-tight heading-font mono-font">{{ $schedules->count() }}</span>
                    <span class="text-xs text-slate-500">Kelas</span>
                </div>
            </div>

            <!-- Sesi Aktif -->
            <div class="bg-white rounded-2xl border border-slate-200/70 p-3.5 shadow-sm flex flex-col justify-between h-24">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-xs font-medium text-slate-500">Sesi Aktif</span>
                    <i data-lucide="radio" class="w-4 h-4 text-slate-400"></i>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-slate-900 tracking-tight heading-font mono-font">{{ $activeSessionsCount }}</span>
                    <span class="text-xs text-slate-500">Berlangsung</span>
                </div>
            </div>

            <!-- Tuntas -->
            <div class="bg-white rounded-2xl border border-slate-200/70 p-3.5 shadow-sm flex flex-col justify-between h-24">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-xs font-medium text-slate-500">Tuntas</span>
                    <i data-lucide="check" class="w-4 h-4 text-slate-400"></i>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-emerald-700 tracking-tight heading-font mono-font">{{ $lockedSessionsCount }}</span>
                    <span class="text-xs text-emerald-700 font-medium">Selesai</span>
                </div>
            </div>

            <!-- Sisa Jadwal -->
            <div class="bg-white rounded-2xl border border-slate-200/70 p-3.5 shadow-sm flex flex-col justify-between h-24">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-xs font-medium text-slate-500">Sisa Jadwal</span>
                    <i data-lucide="clock" class="w-4 h-4 text-slate-400"></i>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-slate-900 tracking-tight heading-font mono-font">{{ $pendingSchedules->count() }}</span>
                    <span class="text-xs text-slate-500">{{ $pendingSchedules->count() > 0 ? 'Menunggu' : 'Tuntas' }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. JADWAL & SESI KELAS HARI INI -->
    <section class="space-y-3.5 pt-2" data-purpose="schedule-section">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold text-slate-900 tracking-tight heading-font">Jadwal & Sesi Kelas Hari Ini</h3>
            <span class="text-xs font-semibold text-emerald-700">{{ $schedules->count() }} Jadwal</span>
        </div>

        @if($schedules->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm p-8 text-center space-y-3">
                <i data-lucide="calendar-off" class="w-7 h-7 text-slate-300 mx-auto"></i>
                <p class="text-xs text-slate-500">Tidak ada jadwal mengajar pada hari {{ $todayDay }}.</p>
            </div>
        @else
            <div class="space-y-3.5">
                @foreach($schedules as $index => $sch)
                    @php
                        $session = $sch->todaySession;
                        $nowTime = now()->format('H:i:s');
                        $startTime = strlen($sch->start_time) === 5 ? $sch->start_time . ':00' : $sch->start_time;
                        $endTime = strlen($sch->end_time) === 5 ? $sch->end_time . ':00' : $sch->end_time;
                        $isWithinSchedule = ($nowTime >= $startTime && $nowTime <= $endTime);

                        $hadirCount = $session ? $session->attendances->where('status', 'HADIR')->count() : 0;
                        $izinCount = $session ? $session->attendances->whereIn('status', ['IZIN', 'SAKIT'])->count() : 0;
                        $alpaCount = $session ? $session->attendances->where('status', 'ALPA')->count() : 0;
                    @endphp

                    <article class="bg-white rounded-2xl border border-slate-200/70 shadow-sm p-4 space-y-3.5 hover:border-slate-300/80 transition-colors">
                        <!-- Top Row: Badge Number + Class + Subject + Time Badge -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold text-xs heading-font">#{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-xs font-medium text-slate-500">Kelas {{ $sch->classroom->name }}</span>
                                </div>
                                <h4 class="text-base font-bold text-slate-900 tracking-tight heading-font truncate">{{ $sch->subject->name }}</h4>
                            </div>
                            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-100 text-xs font-medium text-slate-600 shrink-0">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span class="mono-font font-semibold text-slate-800">{{ substr($sch->start_time, 0, 5) }} – {{ substr($sch->end_time, 0, 5) }}</span>
                            </div>
                        </div>

                        <!-- Middle Row: Attendance Counter + Status Badge -->
                        @if($session)
                            <div class="bg-slate-50/80 border border-slate-100 rounded-xl px-3.5 py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-3 text-xs">
                                    <div class="flex items-center gap-1">
                                        <span class="text-emerald-700 font-bold">{{ $hadirCount }}</span>
                                        <span class="text-slate-500">Hadir</span>
                                    </div>
                                    <span class="text-slate-300">|</span>
                                    <div class="flex items-center gap-1">
                                        <span class="text-amber-700 font-bold">{{ $izinCount }}</span>
                                        <span class="text-slate-500">Izin</span>
                                    </div>
                                    <span class="text-slate-300">|</span>
                                    <div class="flex items-center gap-1">
                                        <span class="text-rose-700 font-bold mono-font">{{ $alpaCount }}</span>
                                        <span class="text-rose-700 font-semibold">Alpa</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 text-xs font-semibold heading-font shrink-0 {{ $session->isLocked() ? 'text-emerald-700' : ($session->isActive() ? 'text-amber-700' : 'text-rose-700') }}">
                                    @if($session->isLocked())
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-700"></i>
                                        <span>Selesai</span>
                                    @elseif($session->isActive())
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                        </span>
                                        <span>Sesi Aktif</span>
                                    @else
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        <span>Perlu Rekap</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="bg-slate-50/80 border border-slate-100 rounded-xl px-3.5 py-2 flex items-center gap-2 text-xs {{ $isWithinSchedule ? 'text-emerald-700 font-medium' : 'text-slate-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isWithinSchedule ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                <span>{{ $isWithinSchedule ? 'Jam ajar aktif — siap dibuka' : 'Belum dimulai' }}</span>
                            </div>
                        @endif

                        <!-- Bottom Row: Action / Status / Form -->
                        <div class="pt-0.5 flex items-center justify-between text-xs">
                            @if(!$session)
                                @if(!$hasCheckedIn)
                                    <span class="text-amber-700 text-xs font-medium">Presensi masuk diperlukan</span>
                                    <a href="{{ route('guru.scan') }}" class="font-semibold text-amber-800 hover:underline">Scan Masuk →</a>
                                @elseif(!$isWithinSchedule)
                                    <div class="flex items-center gap-1.5 text-slate-400 text-[11px]">
                                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                        <span>Buka pada {{ substr($sch->start_time,0,5) }}–{{ substr($sch->end_time,0,5) }} WIB</span>
                                    </div>
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase">Standby</span>
                                @else
                                    <form action="{{ route('guru.session.open', $sch) }}" method="POST" data-loading-form class="openSessionForm w-full flex gap-2">
                                        @csrf
                                        <div class="relative w-[130px] shrink-0">
                                            <select name="duration" class="w-full h-9 appearance-none text-xs border border-slate-200 rounded-xl pl-3 pr-7 bg-white text-slate-700 focus:ring-2 focus:ring-emerald-600 font-medium">
                                                <option value="3">Durasi: 3 Menit</option>
                                                <option value="2">Durasi: 2 Menit</option>
                                                <option value="4">Durasi: 4 Menit</option>
                                                <option value="5">Durasi: 5 Menit</option>
                                            </select>
                                            <span class="pointer-events-none absolute inset-y-0 right-2.5 flex items-center text-slate-400">
                                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                                            </span>
                                        </div>
                                        <button type="submit" disabled class="sessionSubmitBtn opacity-50 cursor-not-allowed flex-1 h-9 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-4 rounded-xl text-xs inline-flex items-center justify-center gap-1.5 transition active:scale-[0.98]">
                                            <i data-lucide="play" class="w-3.5 h-3.5"></i>
                                            <span>Buka Sesi Presensi</span>
                                        </button>
                                    </form>
                                @endif
                            @elseif($session->isActive())
                                <a href="{{ route('guru.session.show', $session) }}" class="w-full h-10 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white font-semibold px-4 rounded-xl text-xs inline-flex items-center justify-center gap-2 transition active:scale-[0.98] shadow-xs">
                                    <i data-lucide="qr-code" class="w-4 h-4"></i>
                                    <span>Buka Layar Presensi Kelas (Live Monitor)</span>
                                </a>
                            @elseif($session->isLocked())
                                <div class="flex items-center gap-1.5 text-slate-400 text-[11px]">
                                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                    <span>Presensi terkunci</span>
                                </div>
                                <a href="{{ route('guru.session.reconcile', $session) }}" class="font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1 transition-colors">
                                    <span>Lihat Rekap Kehadiran</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            @else
                                <a href="{{ route('guru.session.reconcile', $session) }}" class="w-full h-10 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-semibold px-4 rounded-xl text-xs inline-flex items-center justify-center gap-1.5 transition active:scale-[0.98] shadow-xs">
                                    <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                                    <span>Catat Keterangan Siswa & Simpan</span>
                                </a>
                            @endif
                        </div>

                        @if($session && $session->isActive())
                            <div class="pt-1 text-[11px] text-amber-800 font-medium flex items-center justify-between border-t border-slate-100">
                                <span>PIN: <strong class="mono-font">{{ $session->pin_code }}</strong></span>
                                <span>Berakhir: <strong class="mono-font">{{ $session->expires_at->format('H:i') }} WIB</strong></span>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <!-- 5. STATUS PRESENSI GURU (Attendance Card) -->
    <section class="space-y-3.5 pt-2" data-purpose="teacher-attendance">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold text-slate-900 tracking-tight heading-font">Status Presensi Guru</h3>
        </div>

        <article class="bg-white rounded-2xl border border-slate-200/70 shadow-sm p-4 space-y-4 hover:border-slate-300/80 transition-colors">
            <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-100">
                <h4 class="text-base font-bold text-slate-900 tracking-tight heading-font">Presensi Masuk & Pulang</h4>
                <div class="flex items-center gap-1 px-2.5 py-1 rounded-full {{ $hasCheckedIn ? ($dailyAttendance?->check_out_time ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-700') : 'bg-slate-100 text-slate-600' }} shrink-0">
                    <i data-lucide="{{ $hasCheckedIn ? ($dailyAttendance?->check_out_time ? 'check-circle-2' : 'clock') : 'clock' }}" class="w-3.5 h-3.5 {{ $hasCheckedIn ? ($dailyAttendance?->check_out_time ? 'text-emerald-700' : 'text-amber-600') : 'text-slate-400' }}"></i>
                    <span class="text-xs font-medium">{{ $hasCheckedIn ? ($dailyAttendance?->check_out_time ? 'Sudah Pulang' : 'Menunggu Pulang') : 'Belum Presensi' }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2.5 items-stretch">
                <!-- Masuk -->
                <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 flex flex-col justify-between min-h-[82px]">
                    <span class="text-xs text-slate-500 font-medium">Presensi Masuk</span>
                    <div class="flex items-end justify-between gap-1.5 mt-1 min-h-[26px]">
                        <div class="flex items-baseline gap-1">
                            <span class="text-base font-bold text-slate-900 heading-font mono-font leading-none">{{ $hasCheckedIn ? substr($dailyAttendance->check_in_time ?? '', 0, 5) : '--:--' }}</span>
                            <span class="text-[10px] font-medium text-slate-400 leading-none">WIB</span>
                        </div>
                        @if($hasCheckedIn)
                            <span class="px-2 py-0.5 rounded-md {{ ($dailyAttendance->check_in_status ?? 'HADIR') === 'TERLAMBAT' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }} font-semibold text-[10px] shrink-0 leading-tight">
                                {{ ($dailyAttendance->check_in_status ?? 'HADIR') === 'TERLAMBAT' ? 'Terlambat' : 'Tepat Waktu' }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 border border-slate-200 font-semibold text-[10px] shrink-0 leading-tight">Menunggu</span>
                        @endif
                    </div>
                </div>

                <!-- Pulang -->
                <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 flex flex-col justify-between min-h-[82px]">
                    <span class="text-xs text-slate-500 font-medium">Presensi Pulang</span>
                    <div class="flex items-end justify-between gap-1.5 mt-1 min-h-[26px]">
                        @if($dailyAttendance?->check_out_time)
                            <div class="flex items-baseline gap-1">
                                <span class="text-base font-bold text-slate-900 heading-font mono-font leading-none">{{ substr($dailyAttendance->check_out_time, 0, 5) }}</span>
                                <span class="text-[10px] font-medium text-slate-400 leading-none">WIB</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200 font-semibold text-[10px] shrink-0 leading-tight">Selesai</span>
                        @else
                            @php
                                $pulangLocked = $pendingSchedules->count() > 0;
                            @endphp
                            <div class="flex items-baseline gap-1">
                                <span class="text-base font-bold {{ $pulangLocked ? 'text-slate-400' : 'text-slate-900' }} heading-font mono-font leading-none">--:--</span>
                                <span class="text-[10px] font-medium {{ $pulangLocked ? 'text-slate-400' : 'text-slate-900' }} leading-none">WIB</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-md {{ $pulangLocked ? 'bg-slate-100 text-slate-400 border border-slate-200' : 'bg-amber-100 text-amber-700 border border-amber-200' }} font-semibold text-[10px] shrink-0 leading-tight">
                                {{ $pulangLocked ? 'Menunggu' : 'Siap Pulang' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            @if($hasCheckedIn && !$dailyAttendance?->check_out_time && $pendingSchedules->count() === 0)
                <div>
                    <a href="{{ route('guru.scan') }}" class="w-full bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] transition duration-150 text-white font-semibold py-3 px-4 rounded-xl shadow-sm flex items-center justify-center gap-2.5 focus:outline-none focus:ring-4 focus:ring-emerald-700/20 text-sm heading-font">
                        <i data-lucide="qr-code" class="w-5 h-5 text-emerald-200"></i>
                        <span>Pindai QR Presensi Pulang</span>
                    </a>
                </div>
            @endif
        </article>
    </section>

</div>

@push('scripts')
<script>
(function() {
    'use strict';

    const GEO_CACHE_KEY = 'maarif_geo_cache';
    const GEO_CACHE_TTL = 120000;

    function getCachedGeofence() {
        try {
            const raw = sessionStorage.getItem(GEO_CACHE_KEY);
            if (!raw) return null;
            const data = JSON.parse(raw);
            if (Date.now() - data.timestamp < GEO_CACHE_TTL) return data;
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
    let currentCoords = { lat: null, lng: null };
    let activeWatchId = null;
    let gpsRetryTimeout = null;
    let lastGeoAttempt = 0;
    let isWithinGeofence = false;
    let isGpsLocked = false;

    function updateLiveClock() {
        const now = (typeof window.getServerNow === 'function') ? window.getServerNow() : new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.querySelectorAll('.liveClockTicker').forEach(el => {
            el.textContent = `${hours}:${minutes}:${seconds}`;
        });
    }

    function initGeolocation(isManual = false) {
        const now = Date.now();
        if (!isManual && (now - lastGeoAttempt < 1500)) return;
        lastGeoAttempt = now;

        const refreshIcon = document.getElementById('gpsRefreshIcon');
        if (isManual && refreshIcon) {
            refreshIcon.classList.add('animate-spin');
            setTimeout(() => refreshIcon.classList.remove('animate-spin'), 1500);
        }

        if (!isGpsLocked || isManual) {
            isGpsLocked = false;
            isWithinGeofence = false;
            document.querySelectorAll('.sessionSubmitBtn').forEach(btn => {
                btn.setAttribute('disabled', 'disabled');
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            });
        }

        const geofenceBadgeText = document.getElementById('geofenceBadgeText');
        const geofenceBadgeDist = document.getElementById('geofenceBadgeDist');
        const geofenceBadgeDot = document.getElementById('geofenceBadgeDot');
        const geofenceBadgePing = document.getElementById('geofenceBadgePing');

        const geofenceBadge = document.getElementById('geofenceBadge');
        if (!navigator.geolocation) {
            if (geofenceBadge) {
                geofenceBadge.className = "text-rose-600 font-medium flex items-center gap-1 hover:text-rose-700 transition-colors cursor-pointer focus:outline-none";
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
                    if (document.visibilityState !== 'hidden') initGeolocation(false);
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
            const currentText = document.getElementById('geofenceBadgeText');
            const currentDist = document.getElementById('geofenceBadgeDist');

            if (err.code === 1) {
                if (currentBadge) currentBadge.className = "text-rose-600 font-medium flex items-center gap-1 hover:text-rose-700 transition-colors cursor-pointer focus:outline-none";
                if (currentText) currentText.textContent = "Izin GPS Ditolak";
                if (currentDist) currentDist.classList.add('hidden');
            } else if (err.code === 2) {
                if (currentBadge) currentBadge.className = "text-amber-600 font-medium flex items-center gap-1 hover:text-amber-700 transition-colors cursor-pointer focus:outline-none";
                if (currentText) currentText.textContent = "Sinyal Lemah";
                if (currentDist) currentDist.classList.add('hidden');
                scheduleRetry(4000);
            } else if (err.code === 3) {
                if (currentBadge) currentBadge.className = "text-amber-600 font-medium flex items-center gap-1 hover:text-amber-700 transition-colors cursor-pointer focus:outline-none";
                if (currentText) currentText.textContent = "Mencari GPS...";
                if (currentDist) currentDist.classList.add('hidden');
                scheduleRetry(2500);
            }
        };

        const geoOptions = {
            enableHighAccuracy: true,
            timeout: isManual ? 10000 : 7000,
            maximumAge: isManual ? 0 : 30000
        };

        navigator.geolocation.getCurrentPosition(handlePosition, handleError, geoOptions);

        try {
            if (activeWatchId !== null) navigator.geolocation.clearWatch(activeWatchId);
            activeWatchId = navigator.geolocation.watchPosition(
                (pos) => {
                    currentCoords.lat = pos.coords.latitude;
                    currentCoords.lng = pos.coords.longitude;
                    checkServerStatus();
                },
                (err) => {},
                { enableHighAccuracy: true, maximumAge: 15000 }
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
                geofenceBadge.className = "text-emerald-700 font-medium flex items-center gap-1 hover:text-emerald-800 transition-colors cursor-pointer focus:outline-none";
            }
            if (geofenceBadgeText) geofenceBadgeText.textContent = "Area Madrasah";
            if (geofenceBadgeDist) {
                geofenceBadgeDist.textContent = `(${distStr})`;
                geofenceBadgeDist.classList.remove('hidden');
            }

            if (outsideWarning) outsideWarning.classList.add('hidden');
            if (checkinWarning) checkinWarning.classList.remove('hidden');

            document.querySelectorAll('.sessionSubmitBtn').forEach(btn => {
                btn.removeAttribute('disabled');
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        } else {
            isWithinGeofence = false;
            isGpsLocked = true;
            if (checkinWarning) checkinWarning.classList.add('hidden');

            if (geofenceBadge) {
                geofenceBadge.className = "text-rose-600 font-medium flex items-center gap-1 hover:text-rose-700 transition-colors cursor-pointer focus:outline-none";
            }
            if (geofenceBadgeText) geofenceBadgeText.textContent = "Di Luar Madrasah";
            if (geofenceBadgeDist) {
                geofenceBadgeDist.textContent = `(${distStr})`;
                geofenceBadgeDist.classList.remove('hidden');
            }

            if (outsideWarning) {
                outsideWarning.classList.remove('hidden');
                if (outsideDistanceText) {
                    const distText = data.distance > 1000 ? (data.distance / 1000).toFixed(1) + " km" : Math.round(data.distance) + " m";
                    outsideDistanceText.textContent = `Posisi terdeteksi ${distText} dari madrasah (maksimal radius: ${data.radius}m).`;
                }
            }

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
        if (document.visibilityState === 'visible') initGeolocation(false);
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
                    if (permissionStatus.state === 'granted') initGeolocation(true);
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
@endsection
