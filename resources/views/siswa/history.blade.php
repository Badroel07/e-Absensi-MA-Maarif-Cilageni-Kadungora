@extends('layouts.app')

@section('title', 'Riwayat Presensi — MA Ma\'arif Cilageni')
@section('page-title', 'Riwayat Presensi Siswa')

@section('content')
<div class="space-y-6">

    <!-- BEGIN: TitleHeadingSection -->
    <section class="space-y-1.5" data-purpose="page-title-overview">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight heading-font">Riwayat Presensi Siswa</h2>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
            Rekam jejak kehadiran per jam pelajaran, dispensasi, dan ketepatan belajar
        </p>
        <div class="pt-0.5 flex items-center space-x-2">
            <span class="text-xs font-medium text-slate-500">Total Terdata: <span class="font-bold text-emerald-700 mono-font heading-font">{{ $totalSessions }} Sesi</span></span>
        </div>
    </section>
    <!-- END: TitleHeadingSection -->

    <!-- BEGIN: HeroMetricBanner -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4" id="stats-grid-wrapper">
        <!-- Left Hero Card (Tingkat Kehadiran) -->
        <div class="lg:col-span-5 flex flex-col">
            @php
                $heroLabel = 'Disiplin Prima';
                if ($persenHadir < 75) { $heroLabel = 'Perlu Evaluasi'; }
                elseif ($persenHadir < 90) { $heroLabel = 'Cukup Baik'; }
            @endphp
            <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#064e3b] via-[#065f46] to-[#022c22] text-white p-5 lg:p-6 shadow-sm border border-emerald-600/30 flex-1 flex flex-col justify-between" data-purpose="hero-progress-card">
                <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-emerald-400/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative z-10 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2.5">
                            <span class="p-1.5 bg-white/10 rounded-lg text-emerald-300 backdrop-blur-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-100 heading-font">TINGKAT KEHADIRAN</span>
                        </div>
                        <span class="text-xs font-medium text-emerald-200/90 bg-[#064e3b]/80 px-2.5 py-1 rounded-full border border-emerald-500/30">{{ $heroLabel }}</span>
                    </div>
                    <div class="flex items-baseline space-x-2.5">
                        <span class="text-4xl lg:text-5xl font-extrabold tracking-tight text-white heading-font mono-font">{{ $persenHadir }}%</span>
                        <span class="text-sm font-medium text-emerald-200/90">({{ $totalHadir }} Hadir)</span>
                    </div>
                    <div class="w-full bg-[#022c22]/80 h-2.5 rounded-full overflow-hidden p-0.5 border border-emerald-600/40">
                        <div class="bg-gradient-to-r from-emerald-400 to-teal-300 h-full rounded-full shadow-sm" style="width: {{ min($persenHadir, 100) }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-emerald-100/90 pt-1 border-t border-white/10 font-medium">
                        <span>{{ $totalHadir }} dari {{ $totalSessions }} total sesi</span>
                        <span class="text-emerald-300 font-semibold mono-font">{{ $totalIzin + $totalSakit }} Dispensasi / {{ $totalAlpa }} Alpa</span>
                    </div>
                </div>
            </section>
        </div>
        <!-- Right Quick Metric Cards (3 Columns) -->
        <div class="lg:col-span-7">
            <section class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 h-full" data-purpose="summary-metric-cards">
                <!-- Hadir Card -->
                <div class="bg-white p-4 lg:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between hover:border-emerald-300 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 heading-font">HADIR</span>
                        <div class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl font-extrabold text-slate-850 heading-font mono-font">{{ $totalHadir }}</p>
                        <p class="text-[11px] leading-tight text-slate-400 mt-1 font-medium">Sesi terverifikasi</p>
                    </div>
                </div>
                <!-- Dispensasi Card -->
                <div class="bg-white p-4 lg:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between hover:border-amber-300 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 heading-font">DISPENSASI</span>
                        <div class="w-7 h-7 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl font-extrabold text-amber-600 heading-font mono-font">{{ $totalIzin + $totalSakit }}</p>
                        <p class="text-[11px] leading-tight text-slate-400 mt-1 font-medium">{{ $totalIzin }} Izin / {{ $totalSakit }} Sakit</p>
                    </div>
                </div>
                <!-- Alpa Card -->
                <div class="bg-white p-4 lg:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between hover:border-rose-300 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 heading-font">ALPA</span>
                        <div class="w-7 h-7 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl font-extrabold text-rose-600 heading-font mono-font">{{ $totalAlpa }}</p>
                        <p class="text-[11px] leading-tight text-slate-400 mt-1 font-medium">Tanpa keterangan</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <!-- END: HeroMetricBanner -->

    <!-- BEGIN: FilterAndSearchSection (Full-Width Card) -->
    <section class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm space-y-4" data-purpose="filter-and-search-box">
        <!-- Filter Tabs / Chips -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3.5">
            <div class="flex items-center space-x-2 overflow-x-auto no-scrollbar pb-0.5">
                @php
                    $filterChips = [
                        ''      => ['label' => 'Semua', 'count' => $totalSessions, 'icon' => 'all'],
                        'HADIR' => ['label' => 'Hadir', 'count' => $totalHadir,    'icon' => 'done'],
                        'IZIN'  => ['label' => 'Izin',  'count' => $totalIzin,     'icon' => 'izin'],
                        'SAKIT' => ['label' => 'Sakit', 'count' => $totalSakit,    'icon' => 'sakit'],
                        'ALPA'  => ['label' => 'Alpa',  'count' => $totalAlpa,     'icon' => 'alpa'],
                    ];
                @endphp
                @foreach($filterChips as $val => $chip)
                    @php
                        $isActive = ($selectedStatus === $val) || (empty($selectedStatus) && $val === '');
                        $chipUrl  = route('siswa.history', array_merge(request()->except('page'), ['status' => $val ?: null]));
                    @endphp
                    @if($chip['icon'] === 'all')
                        <a href="{{ $chipUrl }}" class="px-4 py-2 rounded-xl text-xs flex items-center space-x-1.5 shrink-0 shadow-sm transition-colors {{ $isActive ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold' }}">
                            <svg class="w-3.5 h-3.5 {{ $isActive ? 'text-white' : 'text-slate-500' }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            <span>{{ $chip['label'] }} ({{ $chip['count'] }})</span>
                        </a>
                    @elseif($chip['icon'] === 'done')
                        <a href="{{ $chipUrl }}" class="px-4 py-2 rounded-xl text-xs flex items-center space-x-1.5 shrink-0 transition-colors {{ $isActive ? 'bg-slate-900 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold' }}">
                            <svg class="w-3.5 h-3.5 {{ $isActive ? 'text-emerald-400' : 'text-emerald-600' }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            <span>{{ $chip['label'] }} ({{ $chip['count'] }})</span>
                        </a>
                    @elseif($chip['icon'] === 'izin')
                        <a href="{{ $chipUrl }}" class="px-4 py-2 rounded-xl text-xs flex items-center space-x-1.5 shrink-0 transition-colors {{ $isActive ? 'bg-slate-900 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold' }}">
                            <svg class="w-3.5 h-3.5 {{ $isActive ? 'text-amber-400' : 'text-amber-500' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            <span>{{ $chip['label'] }} ({{ $chip['count'] }})</span>
                        </a>
                    @elseif($chip['icon'] === 'sakit')
                        <a href="{{ $chipUrl }}" class="px-4 py-2 rounded-xl text-xs flex items-center space-x-1.5 shrink-0 transition-colors {{ $isActive ? 'bg-slate-900 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold' }}">
                            <svg class="w-3.5 h-3.5 {{ $isActive ? 'text-sky-400' : 'text-sky-500' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            <span>{{ $chip['label'] }} ({{ $chip['count'] }})</span>
                        </a>
                    @else
                        <a href="{{ $chipUrl }}" class="px-4 py-2 rounded-xl text-xs flex items-center space-x-1.5 shrink-0 transition-colors {{ $isActive ? 'bg-slate-900 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold' }}">
                            <svg class="w-3.5 h-3.5 {{ $isActive ? 'text-rose-400' : 'text-rose-500' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            <span>{{ $chip['label'] }} ({{ $chip['count'] }})</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Search & Date Filter Form -->
        <form method="GET" action="{{ route('siswa.history') }}" data-loading-form class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-end">
            @if($selectedStatus)
                <input type="hidden" name="status" value="{{ $selectedStatus }}">
            @endif

            <!-- Search Field (5 cols on md:) -->
            <div class="md:col-span-5 relative">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">PENCARIAN</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </div>
                    <input class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 transition" placeholder="Cari mata pelajaran atau dewan guru..." type="text" name="search" value="{{ $search }}"/>
                </div>
            </div>

            <!-- Date Inputs (5 cols on md:) -->
            <div class="md:col-span-5 grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">DARI TANGGAL</label>
                    <div class="relative w-full pl-3.5 pr-9 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-700 flex items-center min-h-[38px] cursor-pointer" data-date-wrap>
                        <span data-date-label class="mono-font font-medium {{ $startDate ? 'text-slate-700' : 'text-slate-400' }}">
                            {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'dd/mm/yyyy' }}
                        </span>
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                        <input type="date" name="start_date" value="{{ $startDate }}" aria-label="Dari Tanggal" onchange="updateDateLabel(this)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">SAMPAI TANGGAL</label>
                    <div class="relative w-full pl-3.5 pr-9 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-700 flex items-center min-h-[38px] cursor-pointer" data-date-wrap>
                        <span data-date-label class="mono-font font-medium {{ $endDate ? 'text-slate-700' : 'text-slate-400' }}">
                            {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'dd/mm/yyyy' }}
                        </span>
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                        <input type="date" name="end_date" value="{{ $endDate }}" aria-label="Sampai Tanggal" onchange="updateDateLabel(this)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>
                </div>
            </div>

            <!-- Submit Button & Reset Button (2 cols on md:) -->
            <div class="md:col-span-2 flex items-center gap-2">
                <button class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold flex items-center justify-center space-x-2 active:scale-[0.99] transition-all shadow-sm cursor-pointer" type="submit">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span>Terapkan</span>
                </button>
                @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
                    <a href="{{ route('siswa.history') }}" title="Reset Filter" class="py-2.5 px-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-semibold flex items-center justify-center transition cursor-pointer shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </section>
    <!-- END: FilterAndSearchSection -->

    <!-- BEGIN: AttendanceRecordsList (Single-Column Full-Width Stack for Desktop & Mobile) -->
    <section class="space-y-3.5" data-purpose="attendance-records-timeline">
        <div class="flex items-baseline justify-between px-1">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight heading-font">Rekam Jejak Presensi Pelajaran</h3>
                <p class="text-xs text-slate-500 mt-0.5">Riwayat kehadiran yang telah diverifikasi oleh guru pengampu</p>
            </div>
            <span class="text-xs font-semibold text-slate-500 shrink-0 mono-font">{{ $attendances->count() }} dari {{ $attendances->total() }} sesi</span>
        </div>

        <!-- Single Column Vertical Stack -->
        <div class="grid grid-cols-1 gap-3.5 w-full">
            @forelse($attendances as $att)
                <article class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:border-emerald-300 hover:shadow transition-all w-full">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <!-- Col 1: Waktu & Tanggal -->
                        <div class="md:w-48 shrink-0">
                            <div class="flex items-center space-x-2">
                                <span class="text-xs font-bold text-slate-800 heading-font mono-font">{{ $att->attendance_date->translatedFormat('l, d M Y') }}</span>
                            </div>
                            <p class="text-[11px] text-slate-500 font-medium mt-0.5 mono-font">
                                @if($att->schedule)
                                    {{ substr($att->schedule->start_time, 0, 5) }} – {{ substr($att->schedule->end_time, 0, 5) }} WIB
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <!-- Col 2: Mata Pelajaran & Guru Pengampu -->
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-extrabold text-slate-900 leading-snug heading-font">{{ $att->schedule->subject->name ?? '-' }}</h4>
                            <div class="flex items-center space-x-2 mt-1 flex-wrap text-xs">
                                <span class="inline-flex items-center font-medium text-slate-600">
                                    <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    {{ $att->schedule->teacher->name ?? 'Dewan Guru' }}
                                </span>
                                @if(!empty($att->schedule->subject->code))
                                    <span class="text-slate-300">•</span>
                                    <span class="text-slate-400 font-medium mono-font">Kode: {{ $att->schedule->subject->code }}</span>
                                @endif
                            </div>
                        </div>
                        <!-- Col 3: Waktu Verifikasi -->
                        <div class="flex md:flex-col justify-between items-center md:items-start md:w-36 shrink-0 py-2 md:py-0 border-y md:border-y-0 border-slate-100">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider heading-font">WAKTU VERIFIKASI</p>
                                @if($att->verified_at)
                                    <p class="text-sm font-extrabold text-slate-800 tracking-wider mono-font heading-font">{{ $att->verified_at->format('H:i') }} WIB</p>
                                @else
                                    <p class="text-xs font-semibold text-slate-500">Tercatat Sistem</p>
                                @endif
                            </div>
                        </div>
                        <!-- Col 4: Status -->
                        <div class="flex items-center justify-between md:justify-end space-x-4 md:w-36 shrink-0">
                            @if($att->status === 'HADIR')
                                <span class="inline-flex items-center space-x-1 text-xs font-semibold text-emerald-600 heading-font">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <span>Hadir</span>
                                </span>
                            @elseif($att->status === 'IZIN')
                                <span class="inline-flex items-center space-x-1 text-xs font-semibold text-amber-600 heading-font">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <span>Izin</span>
                                </span>
                            @elseif($att->status === 'SAKIT')
                                <span class="inline-flex items-center space-x-1 text-xs font-semibold text-sky-600 heading-font">
                                    <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <span>Sakit</span>
                                </span>
                            @else
                                <span class="inline-flex items-center space-x-1 text-xs font-semibold text-rose-600 heading-font">
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <span>Alpa</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="bg-white rounded-2xl p-8 text-center border border-slate-200/80 shadow-sm space-y-3">
                    <svg class="w-10 h-10 text-slate-300 mx-auto" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
                        <div class="space-y-2">
                            <h4 class="text-sm font-bold text-slate-800 heading-font">Tidak Ada Presensi yang Cocok</h4>
                            <p class="text-xs text-slate-500">Tidak ditemukan data presensi yang sesuai dengan filter yang diterapkan.</p>
                            <a href="{{ route('siswa.history') }}" class="mt-1 inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl text-xs transition-all shadow-sm cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <span>Reset Filter</span>
                            </a>
                        </div>
                    @else
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-slate-800 heading-font">Belum Ada Riwayat Presensi</h4>
                            <p class="text-xs text-slate-500">Kehadiran pelajaran yang telah diverifikasi guru akan otomatis tercatat di sini.</p>
                        </div>
                    @endif
                </div>
            @endforelse
        </div>

        @if($attendances->hasPages())
            <div class="pt-2 flex justify-center">
                {{ $attendances->links() }}
            </div>
        @endif
    </section>
    <!-- END: AttendanceRecordsList -->

</div>

@push('scripts')
<script>
    function updateDateLabel(input) {
        var wrap = input.closest('div.relative');
        var label = wrap ? wrap.querySelector('[data-date-label]') : null;
        if (!label) return;
        if (input.value) {
            var parts = input.value.split('-');
            label.textContent = parts[2] + '/' + parts[1] + '/' + parts[0];
            label.classList.remove('text-slate-400');
            label.classList.add('text-slate-700');
        } else {
            label.textContent = 'dd/mm/yyyy';
            label.classList.remove('text-slate-700');
            label.classList.add('text-slate-400');
        }
    }
</script>
@endpush
@endsection
