@extends('layouts.app')

@section('title', 'Riwayat Presensi — MA Ma\'arif Cilageni')
@section('page-title', 'Riwayat Presensi Siswa')

@section('content')
<div class="space-y-6">

    {{-- ── 1. HEADER HALAMAN (Unboxed, Pure Typography — match guru/history) ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/70">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 heading-font tracking-tight">
                Riwayat Presensi Siswa
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Catatan komprehensif rekam kehadiran per sesi pelajaran, dispensasi, dan ketepatan waktu belajar
            </p>
        </div>
        <div class="flex items-center gap-3 text-xs self-start sm:self-auto">
            <span class="text-slate-500 font-medium">Total Terdata:</span>
            <span class="mono-font font-bold text-slate-900 text-sm">{{ $totalSessions }} Sesi</span>
        </div>
    </div>

    {{-- ── 2. ATTENDANCE METRICS BOARD (match guru/history stats board) ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">

        {{-- Primary Anchor: Tingkat Kehadiran Hero Card --}}
        <div class="sm:col-span-2 lg:col-span-5 bg-gradient-to-br from-emerald-800 via-emerald-800 to-emerald-900 rounded-3xl p-5 sm:p-6 text-white shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 text-emerald-200 text-xs font-semibold uppercase tracking-wider">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-300"></i>
                        <span>Tingkat Kehadiran</span>
                    </div>

                    @if($persenHadir >= 90)
                        <span class="text-[11px] font-semibold text-emerald-200">
                            Disiplin Prima
                        </span>
                    @elseif($persenHadir >= 75)
                        <span class="text-[11px] font-semibold text-amber-200">
                            Cukup Baik
                        </span>
                    @else
                        <span class="text-[11px] font-semibold text-rose-200">
                            Perlu Evaluasi
                        </span>
                    @endif
                </div>

                <div class="mt-4 flex items-baseline gap-2.5">
                    <span class="text-4xl sm:text-5xl font-bold mono-font tracking-tight leading-none text-white">
                        {{ $persenHadir }}%
                    </span>
                    <span class="text-xs font-semibold text-emerald-200/90 mono-font">
                        ({{ $totalHadir }} Hadir)
                    </span>
                </div>
            </div>

            <div class="mt-6 relative z-10">
                <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
                    <div class="bg-emerald-400 h-2 rounded-full transition-all duration-500" style="width: {{ min($persenHadir, 100) }}%"></div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-emerald-100/90 mt-2 font-medium">
                    <span>{{ $totalHadir }} dari {{ $totalSessions }} sesi pelajaran</span>
                    <span class="mono-font">{{ $totalSessions > 0 ? round((($totalSessions - $totalHadir) / $totalSessions) * 100, 1) : 0 }}% Absen</span>
                </div>
            </div>
        </div>

        {{-- Secondary Cards: Izin, Sakit, Alpa --}}
        <div class="sm:col-span-2 lg:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Izin --}}
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-amber-300/80 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Izin</span>
                    <i data-lucide="file-text" class="w-5 h-5 text-amber-600 shrink-0"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-amber-600 mono-font leading-none block">
                        {{ $totalIzin }}
                    </span>
                    <p class="text-[11px] text-slate-500 font-medium mt-1.5">
                        Dispensasi tercatat
                    </p>
                </div>
            </div>

            {{-- Sakit --}}
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-sky-300/80 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sakit</span>
                    <i data-lucide="activity" class="w-5 h-5 text-sky-600 shrink-0"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-sky-600 mono-font leading-none block">
                        {{ $totalSakit }}
                    </span>
                    <p class="text-[11px] text-slate-500 font-medium mt-1.5">
                        Istirahat medis
                    </p>
                </div>
            </div>

            {{-- Alpa / Tanpa Keterangan --}}
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-rose-300/80 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Alpa</span>
                    <i data-lucide="x-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-rose-600 mono-font leading-none block">
                        {{ $totalAlpa }}
                    </span>
                    <p class="text-[11px] text-slate-500 font-medium mt-1.5">
                        Tanpa keterangan
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. FILTER BAR (Clean Controls, Anti-Pill — match guru/history) ── --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-5 shadow-xs space-y-4">

        {{-- Status Filter Tabs --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-medium scrollbar-none">
            @php
                $statusTabs = [
                    '' => ['label' => 'Semua', 'count' => $totalSessions, 'icon' => 'list-filter'],
                    'HADIR' => ['label' => 'Hadir', 'count' => $totalHadir, 'icon' => 'check-circle-2'],
                    'IZIN' => ['label' => 'Izin', 'count' => $totalIzin, 'icon' => 'file-text'],
                    'SAKIT' => ['label' => 'Sakit', 'count' => $totalSakit, 'icon' => 'activity'],
                    'ALPA' => ['label' => 'Alpa', 'count' => $totalAlpa, 'icon' => 'x-circle'],
                ];
            @endphp

            @foreach($statusTabs as $val => $tab)
                @php
                    $isActive = ($selectedStatus === $val) || (empty($selectedStatus) && $val === '');
                    $tabUrl = route('siswa.history', array_merge(request()->except('page'), ['status' => $val ?: null]));
                @endphp
                <a href="{{ $tabUrl }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl transition-all duration-150 active:scale-95 shrink-0 select-none cursor-pointer {{ $isActive ? 'bg-slate-900 text-white shadow-2xs font-semibold' : 'bg-slate-100/80 hover:bg-slate-200/70 text-slate-600 font-medium' }}">
                    <i data-lucide="{{ $tab['icon'] }}" class="w-3.5 h-3.5 {{ $isActive ? 'text-white' : 'text-slate-500' }}"></i>
                    <span>{{ $tab['label'] }}</span>
                    <span class="mono-font text-[11px] font-bold {{ $isActive ? 'text-emerald-300' : 'text-slate-500' }}">
                        ({{ $tab['count'] }})
                    </span>
                </a>
            @endforeach
        </div>

        {{-- Search & Date Filters Form --}}
        <form method="GET" action="{{ route('siswa.history') }}" data-loading-form class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2 border-t border-slate-100 text-xs">
            @if($selectedStatus)
                <input type="hidden" name="status" value="{{ $selectedStatus }}">
            @endif

            {{-- Search Input --}}
            <div class="sm:col-span-5 relative">
                <label for="search-input" class="sr-only">Cari Mata Pelajaran atau Guru</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" id="search-input" name="search" value="{{ $search }}"
                           placeholder="Cari mata pelajaran, guru, atau catatan..."
                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-slate-900 font-medium placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 transition">
                </div>
            </div>

            {{-- Date Range: Dari Tanggal --}}
            <div class="sm:col-span-3 flex flex-col gap-1">
                <label class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider pl-0.5">Dari Tanggal</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </span>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-slate-800 mono-font font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-600 transition text-xs [&::-webkit-calendar-picker-indicator]:opacity-50 [&::-webkit-calendar-picker-indicator]:cursor-pointer [&::-webkit-calendar-picker-indicator]:mr-1">
                </div>
            </div>

            {{-- Date Range: Sampai Tanggal --}}
            <div class="sm:col-span-3 flex flex-col gap-1">
                <label class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider pl-0.5">Sampai Tanggal</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </span>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-slate-800 mono-font font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-600 transition text-xs [&::-webkit-calendar-picker-indicator]:opacity-50 [&::-webkit-calendar-picker-indicator]:cursor-pointer [&::-webkit-calendar-picker-indicator]:mr-1">
                </div>
            </div>

            {{-- Actions --}}
            <div class="sm:col-span-1 flex items-end gap-1.5">
                <button type="submit"
                        class="w-full py-2.5 px-3 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold rounded-xl transition-all duration-150 active:scale-95 flex items-center justify-center gap-1 shadow-2xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700 min-h-[42px]"
                        title="Terapkan Filter">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span class="sm:hidden text-xs">Terapkan</span>
                </button>

                @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
                    <a href="{{ route('siswa.history') }}"
                       class="p-2.5 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-600 rounded-xl transition-all duration-150 active:scale-95 flex items-center justify-center shrink-0 cursor-pointer min-h-[42px] min-w-[42px]"
                       title="Reset Seluruh Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>

        {{-- Active Filter Text Indicators (Clean, Anti-Pill — match guru/history) --}}
        @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
            <div class="flex flex-wrap items-center gap-3 pt-2 text-xs text-slate-500 font-medium border-t border-slate-100">
                <span class="text-slate-400 font-semibold">Filter Diterapkan:</span>
                @if(!empty($selectedStatus))
                    <span class="text-emerald-700 font-semibold">
                        Status: {{ $selectedStatus }}
                    </span>
                @endif
                @if(!empty($search))
                    <span class="text-slate-700 font-medium">
                        Pencarian: "{{ $search }}"
                    </span>
                @endif
                @if(!empty($startDate) || !empty($endDate))
                    <span class="text-slate-700 mono-font font-medium">
                        Rentang: {{ $startDate ?: 'Awal' }} s/d {{ $endDate ?: 'Hari Ini' }}
                    </span>
                @endif
                <a href="{{ route('siswa.history') }}" class="text-rose-600 hover:text-rose-700 font-semibold underline cursor-pointer ml-auto text-xs">
                    Reset Filter
                </a>
            </div>
        @endif
    </div>

    {{-- ── 4. DETAILED RECORDS CONTAINER ────────────────────────────── --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 heading-font text-base sm:text-lg tracking-tight">
                    Rekam Jejak Presensi Pelajaran
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Data historis presensi diverifikasi oleh dewan guru pengampu dan sistem terminal madrasah
                </p>
            </div>
            <div class="self-start sm:self-auto">
                <span class="text-xs text-slate-500 mono-font font-medium">Menampilkan {{ $attendances->count() }} dari {{ $attendances->total() }} sesi</span>
            </div>
        </div>

        {{-- 4A. DESKTOP VIEW: Floating Card Rows with Styled Header Bar --}}
        <div class="hidden md:block overflow-x-auto p-4 sm:p-6">
            <table class="w-full text-left border-separate border-spacing-y-3.5 text-xs">
                <thead>
                    <tr class="bg-slate-900 text-slate-200 font-semibold text-[11px] tracking-wider uppercase shadow-xs">
                        <th class="py-4 px-6 rounded-l-2xl min-w-[140px] text-slate-300">Tanggal</th>
                        <th class="py-4 px-6 min-w-[260px] text-slate-300">Mata Pelajaran</th>
                        <th class="py-4 px-6 min-w-[190px] text-slate-300">Guru Pengampu</th>
                        <th class="py-4 px-6 min-w-[140px] whitespace-nowrap text-slate-300">Jam Presensi</th>
                        <th class="py-4 px-6 min-w-[110px] text-center text-slate-300">Status</th>
                        <th class="py-4 px-6 rounded-r-2xl min-w-[180px] text-slate-300">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $att)
                        <tr class="bg-slate-50/70 hover:bg-slate-100/80 transition-colors shadow-2xs group">
                            {{-- Tanggal --}}
                            <td class="py-5 px-6 align-middle whitespace-nowrap rounded-l-2xl border-y border-l border-slate-200/70">
                                <div class="font-semibold text-slate-900 text-xs">
                                    {{ $att->attendance_date->translatedFormat('l') }}
                                </div>
                                <div class="text-[11px] text-slate-400 mono-font mt-1">
                                    {{ $att->attendance_date->translatedFormat('d M Y') }}
                                </div>
                            </td>

                            {{-- Mata Pelajaran --}}
                            <td class="py-5 px-6 align-middle border-y border-slate-200/70">
                                <div class="font-semibold text-slate-900 text-sm leading-normal">
                                    {{ $att->schedule->subject->name ?? 'Mata Pelajaran' }}
                                </div>
                                @if($att->schedule)
                                    <div class="text-[11px] text-slate-400 mono-font mt-1 whitespace-nowrap">
                                        {{ substr($att->schedule->start_time, 0, 5) }} – {{ substr($att->schedule->end_time, 0, 5) }} WIB
                                    </div>
                                @endif
                            </td>

                            {{-- Guru Pengampu --}}
                            <td class="py-5 px-6 align-middle whitespace-nowrap border-y border-slate-200/70">
                                <span class="text-xs text-slate-700 font-medium">
                                    {{ $att->schedule->teacher->name ?? '—' }}
                                </span>
                            </td>

                            {{-- Waktu Verifikasi --}}
                            <td class="py-5 px-6 align-middle whitespace-nowrap border-y border-slate-200/70">
                                @if($att->verified_at)
                                    <span class="text-xs font-medium text-slate-600 mono-font">
                                        {{ $att->verified_at->format('H:i') }} WIB
                                    </span>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Status Kehadiran — Anti-Pill Pure Typography (match guru/history) --}}
                            <td class="py-5 px-6 align-middle text-center whitespace-nowrap border-y border-slate-200/70">
                                @if($att->status === 'HADIR')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 justify-center">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span>Hadir</span>
                                    </span>
                                @elseif($att->status === 'IZIN')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 justify-center">
                                        <i data-lucide="file-text" class="w-4 h-4 text-amber-600 shrink-0"></i>
                                        <span>Izin</span>
                                    </span>
                                @elseif($att->status === 'SAKIT')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-sky-700 justify-center">
                                        <i data-lucide="activity" class="w-4 h-4 text-sky-600 shrink-0"></i>
                                        <span>Sakit</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-700 justify-center">
                                        <i data-lucide="x-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                                        <span>Alpa</span>
                                    </span>
                                @endif
                            </td>

                            {{-- Keterangan --}}
                            <td class="py-5 px-6 align-middle text-slate-600 rounded-r-2xl border-y border-r border-slate-200/70">
                                @if($att->notes)
                                    <span class="text-xs text-slate-600 leading-relaxed block">
                                        {{ $att->notes }}
                                    </span>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-16 px-4">
                                <div class="max-w-sm mx-auto flex flex-col items-center gap-2">
                                    <i data-lucide="calendar-x-2" class="w-8 h-8 text-slate-300"></i>
                                    @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
                                        <h4 class="text-sm font-semibold text-slate-800">Tidak Ada Presensi yang Cocok</h4>
                                        <p class="text-xs text-slate-400">
                                             Tidak ditemukan data yang sesuai dengan filter pencarian yang diterapkan.
                                        </p>
                                        <a href="{{ route('siswa.history') }}"
                                           class="mt-1 inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold rounded-xl text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                            <span>Reset Filter</span>
                                        </a>
                                    @else
                                        <h4 class="text-sm font-semibold text-slate-800">Belum Ada Rekam Presensi</h4>
                                        <p class="text-xs text-slate-400">
                                            Catatan presensi jam pelajaran yang kamu ikuti akan otomatis tercatat di sini setelah diverifikasi.
                                        </p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 4B. MOBILE VIEW: Chronological Cards — Pure Typography Status (Anti-Pill) --}}
        <div class="md:hidden p-4 space-y-3.5">
            @forelse($attendances as $att)
                <div class="p-4 rounded-2xl border border-slate-200/80 bg-white hover:border-slate-300 shadow-xs space-y-3 transition">

                    {{-- Top Row: Date & Status (bare typography) --}}
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-semibold text-slate-900 text-xs">
                                {{ $att->attendance_date->translatedFormat('l, d M Y') }}
                            </span>
                            @if($att->schedule)
                                <span class="block text-[11px] text-slate-400 mono-font mt-0.5">
                                    {{ substr($att->schedule->start_time, 0, 5) }} – {{ substr($att->schedule->end_time, 0, 5) }} WIB
                                </span>
                            @endif
                        </div>

                        @if($att->status === 'HADIR')
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 shrink-0">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span>Hadir</span>
                            </span>
                        @elseif($att->status === 'IZIN')
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 shrink-0">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 text-amber-600"></i>
                                <span>Izin</span>
                            </span>
                        @elseif($att->status === 'SAKIT')
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-sky-700 shrink-0">
                                <i data-lucide="activity" class="w-3.5 h-3.5 text-sky-600"></i>
                                <span>Sakit</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-rose-700 shrink-0">
                                <i data-lucide="x-circle" class="w-3.5 h-3.5 text-rose-600"></i>
                                <span>Alpa</span>
                            </span>
                        @endif
                    </div>

                    {{-- Middle: Subject & Teacher --}}
                    <div class="pt-0.5">
                        <h4 class="font-semibold text-slate-900 text-sm leading-snug">
                            {{ $att->schedule->subject->name ?? 'Mata Pelajaran' }}
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $att->schedule->teacher->name ?? '—' }}
                        </p>
                    </div>

                    {{-- Bottom: Verification Timestamp & Notes --}}
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span class="mono-font">Waktu: {{ $att->verified_at ? $att->verified_at->format('H:i') . ' WIB' : '—' }}</span>
                        @if($att->notes)
                            <span class="text-slate-600 truncate max-w-[180px]">{{ $att->notes }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12 px-4 space-y-2">
                    <i data-lucide="calendar-x-2" class="w-8 h-8 text-slate-300 mx-auto"></i>
                    @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
                        <p class="text-xs font-semibold text-slate-700">Tidak ada presensi sesuai filter</p>
                        <a href="{{ route('siswa.history') }}" class="text-xs text-rose-600 font-semibold underline cursor-pointer">
                            Hapus Filter
                        </a>
                    @else
                        <p class="text-xs text-slate-500">Belum ada rekaman riwayat presensi.</p>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination Container --}}
        @if($attendances->hasPages())
            <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/50">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
