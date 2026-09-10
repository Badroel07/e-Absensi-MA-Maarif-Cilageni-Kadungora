@extends('layouts.app')

@section('title', 'Riwayat Presensi — MA Ma\'arif Cilageni')
@section('page-title', 'Riwayat Presensi Siswa')

@push('styles')
<style>
    .mono-font {
        font-family: 'JetBrains Mono', monospace;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- ── 1. HEADER & IDENTITY ─────────────────────────────────────── --}}
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 heading-font tracking-tight">
            Riwayat Presensi Siswa
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Catatan komprehensif rekam kehadiran per sesi pelajaran, dispensasi, dan ketepatan waktu belajar.
        </p>
    </div>

    {{-- ── 2. ATTENDANCE METRICS BOARD (REAL DATA ONLY) ─────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
        
        {{-- Primary Anchor: Tingkat Kehadiran Hero Card --}}
        <div class="sm:col-span-2 lg:col-span-5 bg-gradient-to-br from-emerald-800 via-emerald-800 to-emerald-900 rounded-3xl p-5 sm:p-6 text-white shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 text-emerald-200 text-xs font-extrabold uppercase tracking-wider">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-300"></i>
                        <span>Tingkat Kehadiran</span>
                    </div>

                    @if($persenHadir >= 90)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/25 border border-emerald-400/40 text-emerald-100">
                            Disiplin Prima
                        </span>
                    @elseif($persenHadir >= 75)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500/25 border border-amber-400/40 text-amber-100">
                            Cukup Baik
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500/25 border border-rose-400/40 text-rose-100">
                            Perlu Evaluasi
                        </span>
                    @endif
                </div>

                <div class="mt-4 flex items-baseline gap-2.5">
                    <span class="text-4xl sm:text-5xl font-black mono-font tracking-tight leading-none text-white">
                        {{ $persenHadir }}%
                    </span>
                    <span class="text-xs font-semibold text-emerald-200/90">
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
                    <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">Izin</span>
                    <span class="w-9 h-9 rounded-2xl bg-amber-50 border border-amber-200/70 text-amber-600 flex items-center justify-center shrink-0">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-black text-amber-600 mono-font leading-none block">
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
                    <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">Sakit</span>
                    <span class="w-9 h-9 rounded-2xl bg-sky-50 border border-sky-200/70 text-sky-600 flex items-center justify-center shrink-0">
                        <i data-lucide="activity" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-black text-sky-600 mono-font leading-none block">
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
                    <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">Alpa</span>
                    <span class="w-9 h-9 rounded-2xl bg-rose-50 border border-rose-200/70 text-rose-600 flex items-center justify-center shrink-0">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-black text-rose-600 mono-font leading-none block">
                        {{ $totalAlpa }}
                    </span>
                    <p class="text-[11px] text-slate-500 font-medium mt-1.5">
                        Tanpa keterangan
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. INTERACTIVE FILTER & SEARCH BAR ───────────────────────── --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-5 shadow-xs space-y-4">
        
        {{-- Status Filter Tabs --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-bold scrollbar-none">
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
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl transition-all duration-150 active:scale-95 shrink-0 select-none cursor-pointer {{ $isActive ? 'bg-slate-900 text-white shadow-2xs font-extrabold' : 'bg-slate-100/80 hover:bg-slate-200/70 text-slate-600 font-bold' }}">
                    <i data-lucide="{{ $tab['icon'] }}" class="w-3.5 h-3.5 {{ $isActive ? 'text-white' : 'text-slate-500' }}"></i>
                    <span>{{ $tab['label'] }}</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] mono-font font-bold {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </div>

        {{-- Search & Date Filters Form --}}
        <form method="GET" action="{{ route('siswa.history') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2 border-t border-slate-100 text-xs">
            {{-- Keep active status filter if set --}}
            @if($selectedStatus)
                <input type="hidden" name="status" value="{{ $selectedStatus }}">
            @endif

            {{-- Search Input --}}
            <div class="sm:col-span-5 relative">
                <label for="search-input" class="sr-only">Cari Mata Pelajaran atau Guru</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" id="search-input" name="search" value="{{ $search }}"
                           placeholder="Cari mata pelajaran, guru, atau catatan..."
                           class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-slate-900 font-medium placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 transition">
                </div>
            </div>

            {{-- Date Range: Start Date --}}
            <div class="sm:col-span-3">
                <input type="date" name="start_date" value="{{ $startDate }}"
                       title="Dari Tanggal"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-slate-800 mono-font font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-600 transition text-xs">
            </div>

            {{-- Date Range: End Date --}}
            <div class="sm:col-span-3">
                <input type="date" name="end_date" value="{{ $endDate }}"
                       title="Sampai Tanggal"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-slate-800 mono-font font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-600 transition text-xs">
            </div>

            {{-- Actions --}}
            <div class="sm:col-span-1 flex items-center gap-1.5">
                <button type="submit"
                        class="w-full py-2.5 px-3 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white font-bold rounded-xl transition-all duration-150 active:scale-95 flex items-center justify-center gap-1 shadow-2xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700"
                        title="Terapkan Filter">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span class="sm:hidden">Terapkan</span>
                </button>

                @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
                    <a href="{{ route('siswa.history') }}"
                       class="p-2.5 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-600 rounded-xl transition-all duration-150 active:scale-95 flex items-center justify-center shrink-0 cursor-pointer"
                       title="Reset Seluruh Filter">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>
        </form>

        {{-- Active Filter Notification Pill --}}
        @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
            <div class="flex flex-wrap items-center gap-2 pt-2 text-[11px] text-slate-500 font-medium">
                <span class="text-slate-400">Filter Aktif:</span>
                @if(!empty($selectedStatus))
                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200/70 font-bold">
                        Status: {{ $selectedStatus }}
                    </span>
                @endif
                @if(!empty($search))
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 font-bold">
                        Kata Kunci: "{{ $search }}"
                    </span>
                @endif
                @if(!empty($startDate) || !empty($endDate))
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 mono-font font-bold">
                        Rentang: {{ $startDate ?: 'Awal' }} s/d {{ $endDate ?: 'Hari Ini' }}
                    </span>
                @endif
                <a href="{{ route('siswa.history') }}" class="text-emerald-700 hover:text-emerald-800 underline font-bold cursor-pointer ml-1">
                    Hapus Filter
                </a>
            </div>
        @endif
    </div>

    {{-- ── 4. DETAILED RECORDS CONTAINER ────────────────────────────── --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-black text-slate-900 heading-font text-base sm:text-lg tracking-tight">
                    Rekam Jejak Presensi Pelajaran
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Data historis presensi diverifikasi oleh dewan guru pengampu dan sistem terminal madrasah.
                </p>
            </div>
            <div class="self-start sm:self-auto">
                <span class="text-xs text-slate-500 mono-font">Total {{ $attendances->total() }} sesi</span>
            </div>
        </div>
        
        {{-- 4A. DESKTOP VIEW: Floating Card Rows with Styled Header Bar --}}
        <div class="hidden md:block overflow-x-auto p-4 sm:p-6">
            <table class="w-full text-left border-separate border-spacing-y-3.5 text-xs">
                <thead>
                    <tr class="bg-slate-900 text-slate-200 font-bold text-[11px] tracking-wider uppercase shadow-xs">
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
                                <div class="font-bold text-slate-900 text-xs">
                                    {{ $att->attendance_date->translatedFormat('l') }}
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono mt-1">
                                    {{ $att->attendance_date->translatedFormat('d M Y') }}
                                </div>
                            </td>

                            {{-- Mata Pelajaran --}}
                            <td class="py-5 px-6 align-middle border-y border-slate-200/70">
                                <div class="font-bold text-slate-900 text-sm leading-normal">
                                    {{ $att->schedule->subject->name ?? 'Mata Pelajaran' }}
                                </div>
                                @if($att->schedule)
                                    <div class="text-[11px] text-slate-400 font-mono mt-1 whitespace-nowrap">
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

                            {{-- Status Kehadiran --}}
                            <td class="py-5 px-6 align-middle text-center whitespace-nowrap border-y border-slate-200/70">
                                @if($att->status === 'HADIR')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100/70 text-emerald-800">
                                        Hadir
                                    </span>
                                @elseif($att->status === 'IZIN')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100/70 text-amber-800">
                                        Izin
                                    </span>
                                @elseif($att->status === 'SAKIT')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-sky-100/70 text-sky-800">
                                        Sakit
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-100/70 text-rose-800">
                                        Alpa
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
                                <div class="max-w-sm mx-auto flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                        <i data-lucide="calendar-x-2" class="w-6 h-6"></i>
                                    </div>
                                    @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
                                        <h4 class="text-sm font-bold text-slate-800">Tidak Ada Presensi yang Cocok</h4>
                                        <p class="text-xs text-slate-400">
                                            Tidak ditemukan data yang sesuai dengan filter pencarian yang diterapkan.
                                        </p>
                                        <a href="{{ route('siswa.history') }}"
                                           class="mt-1 inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white font-bold rounded-xl text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                            <span>Reset Filter</span>
                                        </a>
                                    @else
                                        <h4 class="text-sm font-bold text-slate-800">Belum Ada Rekam Presensi</h4>
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

        {{-- 4B. MOBILE VIEW: Chronological Cards (Hidden on Desktop) --}}
        <div class="md:hidden p-4 space-y-3.5">
            @forelse($attendances as $att)
                @php
                    $borderClass = match($att->status) {
                        'HADIR' => 'border-l-4 border-l-emerald-500',
                        'IZIN' => 'border-l-4 border-l-amber-500',
                        'SAKIT' => 'border-l-4 border-l-sky-500',
                        default => 'border-l-4 border-l-rose-500',
                    };
                @endphp
                <div class="p-4 rounded-2xl border border-slate-200/80 bg-white {{ $borderClass }} shadow-xs space-y-2 transition">
                    
                    {{-- Top Row: Date & Status Badge --}}
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-bold text-slate-900 text-xs">
                                {{ $att->attendance_date->translatedFormat('l, d M Y') }}
                            </span>
                            @if($att->schedule)
                                <span class="block text-[11px] text-slate-400 font-mono mt-0.5">
                                    {{ substr($att->schedule->start_time, 0, 5) }} – {{ substr($att->schedule->end_time, 0, 5) }} WIB
                                </span>
                            @endif
                        </div>

                        {{-- Status Pill --}}
                        @if($att->status === 'HADIR')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 shrink-0">
                                Hadir
                            </span>
                        @elseif($att->status === 'IZIN')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 shrink-0">
                                Izin
                            </span>
                        @elseif($att->status === 'SAKIT')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-800 shrink-0">
                                Sakit
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 shrink-0">
                                Alpa
                            </span>
                        @endif
                    </div>

                    {{-- Middle: Subject & Teacher --}}
                    <div class="pt-0.5">
                        <h4 class="font-bold text-slate-900 text-sm leading-snug">
                            {{ $att->schedule->subject->name ?? 'Mata Pelajaran' }}
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $att->schedule->teacher->name ?? '—' }}
                        </p>
                    </div>

                    {{-- Bottom: Verification Timestamp & Notes --}}
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Waktu: {{ $att->verified_at ? $att->verified_at->format('H:i') . ' WIB' : '—' }}</span>
                        @if($att->notes)
                            <span class="text-slate-600 truncate max-w-[180px]">{{ $att->notes }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12 px-4 space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                        <i data-lucide="calendar-x-2" class="w-6 h-6"></i>
                    </div>
                    @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
                        <p class="text-xs font-bold text-slate-700">Tidak ada presensi sesuai filter</p>
                        <a href="{{ route('siswa.history') }}" class="text-xs text-emerald-700 font-bold underline cursor-pointer">
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
