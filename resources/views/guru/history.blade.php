@extends('layouts.app')

@section('title', 'Riwayat Kelas Mengajar — MA Ma\'arif Cilageni')
@section('page-title', 'Riwayat Kelas Mengajar')

@section('content')
<div class="space-y-6">

    {{-- ── 1. HEADER HALAMAN (Unboxed, Pure Typography) ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/70">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 heading-font tracking-tight">
                Riwayat Kelas Mengajar
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Rekaman seluruh sesi kelas yang telah dilaksanakan beserta status penyelesaiannya
            </p>
        </div>
        <div class="flex items-center gap-3 text-xs self-start sm:self-auto">
            <span class="text-slate-500 font-medium">Total Terdata:</span>
            <span class="mono-font font-bold text-slate-900 text-sm">{{ $totalSessions }} Sesi</span>
        </div>
    </div>

    {{-- ── 2. STATS BOARD (Human Crafted, Anti-Pill) ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">

        {{-- Hero: Persentase Selesai --}}
        <div class="sm:col-span-2 lg:col-span-5 bg-gradient-to-br from-emerald-800 via-emerald-800 to-emerald-900 rounded-3xl p-5 sm:p-6 text-white shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 text-emerald-200 text-xs font-semibold uppercase tracking-wider">
                        <i data-lucide="book-open-check" class="w-4 h-4 text-emerald-300"></i>
                        <span>Sesi Diselesaikan</span>
                    </div>
                    @if($persenSelesai >= 90)
                        <span class="text-[11px] font-semibold text-emerald-200">
                            Sangat Konsisten
                        </span>
                    @elseif($persenSelesai >= 70)
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
                        {{ $persenSelesai }}%
                    </span>
                    <span class="text-xs font-semibold text-emerald-200/90 mono-font">
                        ({{ $totalLocked }} Selesai)
                    </span>
                </div>
            </div>

            <div class="mt-6 relative z-10">
                <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
                    <div class="bg-emerald-400 h-2 rounded-full transition-all duration-500" style="width: {{ min($persenSelesai, 100) }}%"></div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-emerald-100/90 mt-2 font-medium">
                    <span>{{ $totalLocked }} dari {{ $totalSessions }} total sesi</span>
                    <span class="mono-font">{{ $totalActive }} Sesi Aktif</span>
                </div>
            </div>
        </div>

        {{-- Secondary: Selesai, Aktif, Durasi --}}
        <div class="sm:col-span-2 lg:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-4">

            {{-- Selesai --}}
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-emerald-300/80 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Selesai</span>
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-emerald-600 mono-font leading-none block">
                        {{ $totalLocked }}
                    </span>
                    <p class="text-[11px] text-slate-500 font-medium mt-1.5">
                        Sesi ditutup & tersimpan
                    </p>
                </div>
            </div>

            {{-- Aktif --}}
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-amber-300/80 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Aktif</span>
                    <i data-lucide="loader-2" class="w-5 h-5 text-amber-600 shrink-0"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-amber-600 mono-font leading-none block">
                        {{ $totalActive }}
                    </span>
                    <p class="text-[11px] text-slate-500 font-medium mt-1.5">
                        Sesi kelas belum ditutup
                    </p>
                </div>
            </div>

            {{-- Total Durasi --}}
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-indigo-300/80 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Durasi</span>
                    <i data-lucide="clock" class="w-5 h-5 text-indigo-600 shrink-0"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-indigo-600 mono-font leading-none block">
                        {{ $totalDuration >= 60 ? floor($totalDuration / 60).'j' : $totalDuration.'m' }}
                    </span>
                    <p class="text-[11px] text-slate-500 font-medium mt-1.5">
                        Total durasi mengajar
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. FILTER BAR (Clean Controls, Anti-Pill) ── --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-5 shadow-xs space-y-4">

        {{-- Status Tabs --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-medium scrollbar-none">
            @php
                $statusTabs = [
                    ''       => ['label' => 'Semua Sesi', 'count' => $totalSessions, 'icon' => 'list-filter'],
                    'LOCKED' => ['label' => 'Selesai',    'count' => $totalLocked,   'icon' => 'check-circle-2'],
                    'ACTIVE' => ['label' => 'Sesi Aktif', 'count' => $totalActive,   'icon' => 'loader-2'],
                ];
            @endphp

            @foreach($statusTabs as $val => $tab)
                @php
                    $isActive = ($selectedStatus === $val) || (empty($selectedStatus) && $val === '');
                    $tabUrl   = route('guru.history', array_merge(request()->except('page'), ['status' => $val ?: null]));
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

        {{-- Search & Date Filter --}}
        <form method="GET" action="{{ route('guru.history') }}" data-loading-form class="grid grid-cols-1 sm:grid-cols-12 sm:items-end gap-3 pt-2 border-t border-slate-100 text-xs">
            @if($selectedStatus)
                <input type="hidden" name="status" value="{{ $selectedStatus }}">
            @endif

            {{-- Search Input --}}
            <div class="sm:col-span-5 relative">
                <label for="search-input" class="sr-only">Cari Kelas atau Mata Pelajaran</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" id="search-input" name="search" value="{{ $search }}"
                           placeholder="Cari kelas atau mata pelajaran..."
                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-slate-900 font-medium placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-maarif-600 transition">
                </div>
            </div>

            {{-- Dari Tanggal (facade: native input transparent full-cover + custom calendar icon) --}}
            <div class="sm:col-span-3 flex flex-col gap-1">
                <label class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider pl-0.5">Dari Tanggal</label>
                <div class="relative w-full pl-4 pr-10 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-xs transition focus-within:bg-white focus-within:ring-2 focus-within:ring-maarif-600 flex items-center min-h-[42px] cursor-pointer" data-date-wrap>
                    <span data-date-label class="mono-font font-semibold {{ $startDate ? 'text-slate-800' : 'text-slate-400' }}">
                        {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'dd/mm/yyyy' }}
                    </span>
                    <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </span>
                    <input type="date" name="start_date" value="{{ $startDate }}" aria-label="Dari Tanggal"
                           onchange="updateDateLabel(this)"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                </div>
            </div>

            {{-- Sampai Tanggal (facade: native input transparent full-cover + custom calendar icon) --}}
            <div class="sm:col-span-3 flex flex-col gap-1">
                <label class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider pl-0.5">Sampai Tanggal</label>
                <div class="relative w-full pl-4 pr-10 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-xs transition focus-within:bg-white focus-within:ring-2 focus-within:ring-maarif-600 flex items-center min-h-[42px] cursor-pointer" data-date-wrap>
                    <span data-date-label class="mono-font font-semibold {{ $endDate ? 'text-slate-800' : 'text-slate-400' }}">
                        {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'dd/mm/yyyy' }}
                    </span>
                    <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </span>
                    <input type="date" name="end_date" value="{{ $endDate }}" aria-label="Sampai Tanggal"
                           onchange="updateDateLabel(this)"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                </div>
            </div>

            {{-- Actions --}}
            <div class="sm:col-span-1 flex items-center gap-1.5">
                <button type="submit"
                        class="w-full py-2.5 px-3 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold rounded-xl transition-all duration-150 active:scale-95 flex items-center justify-center gap-1 shadow-2xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700 min-h-[42px]"
                        title="Terapkan Filter">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span class="sm:hidden text-xs">Terapkan</span>
                </button>

                @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
                    <a href="{{ route('guru.history') }}"
                       class="p-2.5 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-600 rounded-xl transition-all duration-150 active:scale-95 flex items-center justify-center shrink-0 cursor-pointer min-h-[42px] min-w-[42px]"
                       title="Reset Seluruh Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>

        {{-- Active Filter Text Indicators (Clean, Anti-Pill) --}}
        @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
            <div class="flex flex-wrap items-center gap-3 pt-2 text-xs text-slate-500 font-medium border-t border-slate-100">
                <span class="text-slate-400 font-semibold">Filter Diterapkan:</span>
                @if(!empty($selectedStatus))
                    <span class="text-emerald-700 font-semibold">
                        Status: {{ $selectedStatus === 'LOCKED' ? 'Selesai' : 'Aktif' }}
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
                <a href="{{ route('guru.history') }}" class="text-rose-600 hover:text-rose-700 font-semibold underline cursor-pointer ml-auto text-xs">
                    Reset Filter
                </a>
            </div>
        @endif
    </div>

    {{-- ── 4. DAFTAR REKAM JEJAK SESI MENGAJAR ── --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 heading-font text-base sm:text-lg tracking-tight">
                    Rekam Jejak Sesi Mengajar
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Riwayat kelas yang telah dilaksanakan beserta PIN sesi dan status penyelesaian
                </p>
            </div>
            <div class="self-start sm:self-auto">
                <span class="text-xs text-slate-500 mono-font font-medium">Menampilkan {{ $sessions->count() }} dari {{ $sessions->total() }} sesi</span>
            </div>
        </div>

        {{-- 4A. DESKTOP TABLE VIEW (Hidden on Mobile) --}}
        <div class="hidden md:block overflow-x-auto p-4 sm:p-6">
            <table class="w-full text-left border-separate border-spacing-y-3 text-xs">
                <thead>
                    <tr class="bg-slate-900 text-slate-200 font-semibold text-[11px] tracking-wider uppercase shadow-xs">
                        <th class="py-4 px-6 rounded-l-2xl min-w-[155px] text-slate-300">Waktu & Tanggal</th>
                        <th class="py-4 px-6 min-w-[110px] text-slate-300">Kelas</th>
                        <th class="py-4 px-6 min-w-[220px] text-slate-300">Mata Pelajaran</th>
                        <th class="py-4 px-6 min-w-[110px] text-slate-300">PIN Sesi</th>
                        <th class="py-4 px-6 min-w-[100px] text-slate-300">Durasi</th>
                        <th class="py-4 px-6 min-w-[120px] text-center text-slate-300">Status</th>
                        <th class="py-4 px-6 rounded-r-2xl min-w-[150px] text-right text-slate-300">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $ses)
                        <tr class="bg-slate-50/70 hover:bg-slate-100/80 transition-colors shadow-2xs group">
                            {{-- Tanggal & Waktu --}}
                            <td class="py-4 px-6 align-middle whitespace-nowrap rounded-l-2xl border-y border-l border-slate-200/70">
                                <div class="font-semibold text-slate-900 text-xs">
                                    {{ $ses->created_at->translatedFormat('l') }}
                                </div>
                                <div class="text-[11px] text-slate-500 mono-font mt-0.5">
                                    {{ $ses->created_at->translatedFormat('d M Y') }}
                                </div>
                                <div class="text-[11px] text-slate-400 mono-font">
                                    {{ $ses->created_at->format('H:i') }} WIB
                                </div>
                            </td>

                            {{-- Kelas (Clean Typography + Door Icon, Anti-Pill) --}}
                            <td class="py-4 px-6 align-middle border-y border-slate-200/70">
                                <span class="inline-flex items-center gap-1.5 font-bold text-slate-800 text-xs whitespace-nowrap">
                                    <i data-lucide="door-closed" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                    <span>Kelas {{ $ses->schedule->classroom->name ?? '-' }}</span>
                                </span>
                            </td>

                            {{-- Mata Pelajaran --}}
                            <td class="py-4 px-6 align-middle border-y border-slate-200/70">
                                <div class="font-bold text-slate-900 text-sm leading-normal">
                                    {{ $ses->schedule->subject->name ?? '-' }}
                                </div>
                                @if(!empty($ses->schedule->subject->code))
                                    <div class="text-[11px] text-slate-400 mono-font mt-0.5">
                                        Kode: {{ $ses->schedule->subject->code }}
                                    </div>
                                @endif
                            </td>

                            {{-- PIN Sesi --}}
                            <td class="py-4 px-6 align-middle border-y border-slate-200/70">
                                <span class="mono-font font-bold text-slate-900 text-sm tracking-wider">
                                    {{ $ses->pin_code }}
                                </span>
                            </td>

                            {{-- Durasi --}}
                            <td class="py-4 px-6 align-middle border-y border-slate-200/70">
                                <span class="text-xs text-slate-600 mono-font font-medium">{{ $ses->duration_minutes }} menit</span>
                            </td>

                            {{-- Status (Anti-Pill, Pure Typography) --}}
                            <td class="py-4 px-6 align-middle text-center whitespace-nowrap border-y border-slate-200/70">
                                @if($ses->status === 'LOCKED')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 justify-center">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span>Selesai</span>
                                    </span>
                                @elseif($ses->isActive())
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 justify-center">
                                        <span class="relative flex h-2 w-2 shrink-0">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                        </span>
                                        <span>Sesi Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-700 justify-center">
                                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                                        <span>Perlu Konfirmasi</span>
                                    </span>
                                @endif
                            </td>

                            {{-- Tindakan (Solid Button Standard) --}}
                            <td class="py-4 px-6 align-middle text-right rounded-r-2xl border-y border-r border-slate-200/70">
                                <a href="{{ route('guru.session.reconcile', $ses) }}"
                                   class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold transition-all duration-150 active:scale-[0.98] text-xs shadow-2xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700 min-h-[38px]">
                                    <span>Lihat Kehadiran</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-16 px-4">
                                <div class="max-w-sm mx-auto flex flex-col items-center gap-2">
                                    <i data-lucide="calendar-x-2" class="w-8 h-8 text-slate-300"></i>
                                    @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
                                        <h4 class="text-sm font-semibold text-slate-800">Tidak Ada Sesi yang Cocok</h4>
                                        <p class="text-xs text-slate-500">
                                            Tidak ditemukan data yang sesuai dengan filter yang diterapkan.
                                        </p>
                                        <a href="{{ route('guru.history') }}"
                                           class="mt-1 inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold rounded-xl text-xs transition-all duration-150 active:scale-[0.98] shadow-xs cursor-pointer">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                            <span>Reset Filter</span>
                                        </a>
                                    @else
                                        <h4 class="text-sm font-semibold text-slate-800">Belum Ada Riwayat Sesi</h4>
                                        <p class="text-xs text-slate-500">
                                            Sesi mengajar yang telah selesai akan otomatis tercatat di sini.
                                        </p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 4B. MOBILE CARDS VIEW (Clean Structured, Anti-Pill, Pure Border) --}}
        <div class="md:hidden p-4 space-y-3.5">
            @forelse($sessions as $ses)
                <div class="p-4 rounded-2xl border border-slate-200/80 bg-white hover:border-slate-300 shadow-xs space-y-3 transition">

                    {{-- Top Row: Tanggal & Waktu + Status --}}
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-bold text-slate-900 text-xs">
                                {{ $ses->created_at->translatedFormat('l, d M Y') }}
                            </span>
                            <span class="block text-[11px] text-slate-500 mono-font mt-0.5">
                                {{ $ses->created_at->format('H:i') }} WIB
                            </span>
                        </div>

                        {{-- Status Mobile (Pure Typography, Anti-Pill) --}}
                        @if($ses->status === 'LOCKED')
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 shrink-0">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span>Selesai</span>
                            </span>
                        @elseif($ses->isActive())
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 shrink-0">
                                <span class="relative flex h-2 w-2 shrink-0">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                </span>
                                <span>Sesi Aktif</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-rose-700 shrink-0">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-rose-600"></i>
                                <span>Perlu Konfirmasi</span>
                            </span>
                        @endif
                    </div>

                    {{-- Middle Row: Mapel & Kelas --}}
                    <div class="space-y-1">
                        <h4 class="font-bold text-slate-900 text-sm leading-snug">
                            {{ $ses->schedule->subject->name ?? '-' }}
                        </h4>
                        <div class="flex items-center gap-3 text-xs text-slate-500 flex-wrap">
                            <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                                <i data-lucide="door-closed" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                <span>Kelas {{ $ses->schedule->classroom->name ?? '-' }}</span>
                            </span>
                            @if(!empty($ses->schedule->subject->code))
                                <span class="mono-font text-[11px] text-slate-400">
                                    Kode: {{ $ses->schedule->subject->code }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Bottom Row: PIN, Durasi & Tombol Aksi --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3 text-xs">
                        <div class="space-y-0.5">
                            <span class="text-[11px] text-slate-400 block font-medium">PIN Sesi:</span>
                            <span class="mono-font font-bold text-slate-900 text-sm tracking-wider">
                                {{ $ses->pin_code }}
                            </span>
                            <span class="text-[11px] text-slate-400 block mono-font">Durasi: {{ $ses->duration_minutes }}m</span>
                        </div>
                        <a href="{{ route('guru.session.reconcile', $ses) }}"
                           class="inline-flex items-center justify-center gap-1.5 font-semibold text-white bg-slate-900 hover:bg-slate-800 active:bg-slate-950 px-4 py-2 rounded-xl transition-all duration-150 active:scale-[0.98] shadow-2xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700 min-h-[40px] text-xs shrink-0">
                            <span>Lihat Kehadiran</span>
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 px-4 space-y-2">
                    <i data-lucide="calendar-x-2" class="w-8 h-8 text-slate-300 mx-auto"></i>
                    @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
                        <p class="text-xs font-semibold text-slate-700">Tidak ada sesi sesuai filter</p>
                        <a href="{{ route('guru.history') }}" class="text-xs text-rose-600 font-semibold underline cursor-pointer">
                            Reset Filter
                        </a>
                    @else
                        <p class="text-xs text-slate-500">Belum ada riwayat sesi mengajar.</p>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($sessions->hasPages())
            <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/50">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

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
            label.classList.add('text-slate-800');
        } else {
            label.textContent = 'dd/mm/yyyy';
            label.classList.remove('text-slate-800');
            label.classList.add('text-slate-400');
        }
    }
</script>
@endpush

