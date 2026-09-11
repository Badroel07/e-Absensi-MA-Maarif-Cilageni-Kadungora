@extends('layouts.app')

@section('title', 'Riwayat Kelas Mengajar — MA Ma\'arif Cilageni')
@section('page-title', 'Riwayat Kelas Mengajar')

@push('styles')
<style>
    .mono-font {
        font-family: 'JetBrains Mono', monospace;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- ── 1. HEADER ──────────────────────────────────────────────────── --}}
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 heading-font tracking-tight">
            Riwayat Kelas Mengajar
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Rekaman seluruh sesi kelas yang telah Bapak/Ibu Guru laksanakan beserta status penyelesaiannya.
        </p>
    </div>

    {{-- ── 2. STATS BOARD ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">

        {{-- Hero: Persentase Selesai --}}
        <div class="sm:col-span-2 lg:col-span-5 bg-gradient-to-br from-emerald-800 via-emerald-800 to-emerald-900 rounded-3xl p-5 sm:p-6 text-white shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 text-emerald-200 text-xs font-semibold uppercase tracking-wider">
                        <i data-lucide="book-open-check" class="w-4 h-4 text-emerald-300"></i>
                        <span>Sesi Diselesaikan</span>
                    </div>
                    @if($persenSelesai >= 90)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/25 border border-emerald-400/40 text-emerald-100">
                            Sangat Konsisten
                        </span>
                    @elseif($persenSelesai >= 70)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/25 border border-amber-400/40 text-amber-100">
                            Cukup Baik
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-500/25 border border-rose-400/40 text-rose-100">
                            Perlu Evaluasi
                        </span>
                    @endif
                </div>

                <div class="mt-4 flex items-baseline gap-2.5">
                    <span class="text-4xl sm:text-5xl font-bold mono-font tracking-tight leading-none text-white">
                        {{ $persenSelesai }}%
                    </span>
                    <span class="text-xs font-semibold text-emerald-200/90">
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
                    <span class="mono-font">{{ $totalActive }} Aktif</span>
                </div>
            </div>
        </div>

        {{-- Secondary: Selesai, Aktif, Durasi --}}
        <div class="sm:col-span-2 lg:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-4">

            {{-- Selesai --}}
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-emerald-300/80 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Selesai</span>
                    <span class="w-9 h-9 rounded-2xl bg-emerald-50 border border-emerald-200/70 text-emerald-600 flex items-center justify-center shrink-0">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-emerald-600 mono-font leading-none block">
                        {{ $totalLocked }}
                    </span>
                    <p class="text-[11px] text-slate-500 font-medium mt-1.5">
                        Sesi terkunci
                    </p>
                </div>
            </div>

            {{-- Aktif --}}
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-amber-300/80 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Aktif</span>
                    <span class="w-9 h-9 rounded-2xl bg-amber-50 border border-amber-200/70 text-amber-600 flex items-center justify-center shrink-0">
                        <i data-lucide="loader-2" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-amber-600 mono-font leading-none block">
                        {{ $totalActive }}
                    </span>
                    <p class="text-[11px] text-slate-500 font-medium mt-1.5">
                        Sesi belum ditutup
                    </p>
                </div>
            </div>

            {{-- Total Durasi --}}
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-indigo-300/80 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Durasi</span>
                    <span class="w-9 h-9 rounded-2xl bg-indigo-50 border border-indigo-200/70 text-indigo-600 flex items-center justify-center shrink-0">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-indigo-600 mono-font leading-none block">
                        {{ $totalDuration >= 60 ? floor($totalDuration / 60).'j' : $totalDuration.'m' }}
                    </span>
                    <p class="text-[11px] text-slate-500 font-medium mt-1.5">
                        Total waktu mengajar
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. FILTER BAR ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-5 shadow-xs space-y-4">

        {{-- Status Tabs --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-medium scrollbar-none">
            @php
                $statusTabs = [
                    ''       => ['label' => 'Semua',   'count' => $totalSessions, 'icon' => 'list-filter'],
                    'LOCKED' => ['label' => 'Selesai', 'count' => $totalLocked,   'icon' => 'check-circle-2'],
                    'ACTIVE' => ['label' => 'Aktif',   'count' => $totalActive,   'icon' => 'loader-2'],
                ];
            @endphp

            @foreach($statusTabs as $val => $tab)
                @php
                    $isActive = ($selectedStatus === $val) || (empty($selectedStatus) && $val === '');
                    $tabUrl   = route('guru.history', array_merge(request()->except('page'), ['status' => $val ?: null]));
                @endphp
                <a href="{{ $tabUrl }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl transition-all duration-150 active:scale-95 shrink-0 select-none cursor-pointer {{ $isActive ? 'bg-slate-900 text-white shadow-2xs font-semibold' : 'bg-slate-100/80 hover:bg-slate-200/70 text-slate-600 font-medium' }}">
                    <i data-lucide="{{ $tab['icon'] }}" class="w-3.5 h-3.5 {{ $isActive ? 'text-white' : 'text-slate-500' }}"></i>
                    <span>{{ $tab['label'] }}</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] mono-font font-semibold {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </div>

        {{-- Search & Date Filter --}}
        <form method="GET" action="{{ route('guru.history') }}" data-loading-form class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2 border-t border-slate-100 text-xs">
            @if($selectedStatus)
                <input type="hidden" name="status" value="{{ $selectedStatus }}">
            @endif

            {{-- Search --}}
            <div class="sm:col-span-5 relative">
                <label for="search-input" class="sr-only">Cari Kelas atau Mata Pelajaran</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" id="search-input" name="search" value="{{ $search }}"
                           placeholder="Cari kelas atau mata pelajaran..."
                           class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-slate-900 font-medium placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 transition">
                </div>
            </div>

            {{-- Start Date --}}
            <div class="sm:col-span-3">
                <input type="date" name="start_date" value="{{ $startDate }}"
                       title="Dari Tanggal"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-slate-800 mono-font font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-600 transition text-xs">
            </div>

            {{-- End Date --}}
            <div class="sm:col-span-3">
                <input type="date" name="end_date" value="{{ $endDate }}"
                       title="Sampai Tanggal"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white text-slate-800 mono-font font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-600 transition text-xs">
            </div>

            {{-- Actions --}}
            <div class="sm:col-span-1 flex items-center gap-1.5">
                <button type="submit"
                        class="w-full py-2.5 px-3 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white font-semibold rounded-xl transition-all duration-150 active:scale-95 flex items-center justify-center gap-1 shadow-2xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700"
                        title="Terapkan Filter">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span class="sm:hidden">Terapkan</span>
                </button>

                @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
                    <a href="{{ route('guru.history') }}"
                       class="p-2.5 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-600 rounded-xl transition-all duration-150 active:scale-95 flex items-center justify-center shrink-0 cursor-pointer"
                       title="Reset Seluruh Filter">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>
        </form>

        {{-- Active Filter Pills --}}
        @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
            <div class="flex flex-wrap items-center gap-2 pt-2 text-[11px] text-slate-500 font-medium">
                <span class="text-slate-400">Filter Aktif:</span>
                @if(!empty($selectedStatus))
                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200/70 font-semibold">
                        Status: {{ $selectedStatus === 'LOCKED' ? 'Selesai' : 'Aktif' }}
                    </span>
                @endif
                @if(!empty($search))
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 font-semibold">
                        Kata Kunci: "{{ $search }}"
                    </span>
                @endif
                @if(!empty($startDate) || !empty($endDate))
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 mono-font font-semibold">
                        Rentang: {{ $startDate ?: 'Awal' }} s/d {{ $endDate ?: 'Hari Ini' }}
                    </span>
                @endif
                <a href="{{ route('guru.history') }}" class="text-emerald-700 hover:text-emerald-800 underline font-semibold cursor-pointer ml-1">
                    Hapus Filter
                </a>
            </div>
        @endif
    </div>

    {{-- ── 4. RECORDS TABLE ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden"
         x-data="{ ready: false }"
         x-init="$nextTick(() => { setTimeout(() => { ready = true; }, window.__isLiveSearching ? 0 : 450); })">
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 heading-font text-base sm:text-lg tracking-tight">
                    Rekam Jejak Sesi Mengajar
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Riwayat kelas yang telah dilaksanakan, beserta PIN sesi dan status penyelesaian.
                </p>
            </div>
            <div class="self-start sm:self-auto">
                <span class="text-xs text-slate-500 mono-font">Total {{ $sessions->total() }} sesi</span>
            </div>
        </div>

        {{-- Skeleton placeholder — visible immediately on page load (NO x-cloak) --}}
        <div x-show="!ready" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" class="p-4 sm:p-6 space-y-2.5">
            <x-skeleton :count="6" :columns="6" :avatar="false" />
        </div>

        {{-- Real Content Container (Desktop Table + Mobile Cards + Pagination) --}}
        <div x-show="ready" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            {{-- 4A. DESKTOP TABLE (Hidden on Mobile) --}}
            <div class="hidden md:block overflow-x-auto p-4 sm:p-6">
                <table class="w-full text-left border-separate border-spacing-y-3.5 text-xs">
                <thead>
                    <tr class="bg-slate-900 text-slate-200 font-semibold text-[11px] tracking-wider uppercase shadow-xs">
                        <th class="py-4 px-6 rounded-l-2xl min-w-[155px] text-slate-300">Tanggal</th>
                        <th class="py-4 px-6 min-w-[100px] text-slate-300">Kelas</th>
                        <th class="py-4 px-6 min-w-[220px] text-slate-300">Mata Pelajaran</th>
                        <th class="py-4 px-6 min-w-[110px] text-slate-300">PIN Sesi</th>
                        <th class="py-4 px-6 min-w-[100px] text-slate-300">Durasi</th>
                        <th class="py-4 px-6 min-w-[110px] text-center text-slate-300">Status</th>
                        <th class="py-4 px-6 rounded-r-2xl min-w-[140px] text-right text-slate-300">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $ses)
                        <tr class="bg-slate-50/70 hover:bg-slate-100/80 transition-colors shadow-2xs group">
                            {{-- Tanggal --}}
                            <td class="py-5 px-6 align-middle whitespace-nowrap rounded-l-2xl border-y border-l border-slate-200/70">
                                <div class="font-semibold text-slate-900 text-xs">
                                    {{ $ses->created_at->translatedFormat('l') }}
                                </div>
                                <div class="text-[11px] text-slate-400 mono-font mt-1">
                                    {{ $ses->created_at->translatedFormat('d M Y') }}
                                </div>
                                <div class="text-[11px] text-slate-400 mono-font">
                                    {{ $ses->created_at->format('H:i') }} WIB
                                </div>
                            </td>

                            {{-- Kelas --}}
                            <td class="py-5 px-6 align-middle border-y border-slate-200/70">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-maarif-700 text-white">
                                    {{ $ses->schedule->classroom->name ?? '-' }}
                                </span>
                            </td>

                            {{-- Mata Pelajaran --}}
                            <td class="py-5 px-6 align-middle border-y border-slate-200/70">
                                <div class="font-semibold text-slate-900 text-sm leading-normal">
                                    {{ $ses->schedule->subject->name ?? '-' }}
                                </div>
                            </td>

                            {{-- PIN Sesi --}}
                            <td class="py-5 px-6 align-middle border-y border-slate-200/70">
                                <span class="mono-font font-semibold text-slate-800 text-sm tracking-widest">
                                    {{ $ses->pin_code }}
                                </span>
                            </td>

                            {{-- Durasi --}}
                            <td class="py-5 px-6 align-middle border-y border-slate-200/70">
                                <span class="text-xs text-slate-500 mono-font">{{ $ses->duration_minutes }} menit</span>
                            </td>

                            {{-- Status --}}
                            <td class="py-5 px-6 align-middle text-center whitespace-nowrap border-y border-slate-200/70">
                                @if($ses->status === 'LOCKED')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100/70 text-emerald-800">
                                        Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100/70 text-amber-800">
                                        
                                        Aktif
                                    </span>
                                @endif
                            </td>

                            {{-- Tindakan --}}
                            <td class="py-5 px-6 align-middle text-right rounded-r-2xl border-y border-r border-slate-200/70">
                                <a href="{{ route('guru.session.reconcile', $ses) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-maarif-50 active:bg-maarif-100 text-slate-700 hover:text-maarif-800 border border-slate-200/80 font-semibold transition-all duration-150 active:scale-95 text-xs shadow-2xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                                    Lihat Kehadiran
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-16 px-4">
                                <div class="max-w-sm mx-auto flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                        <i data-lucide="calendar-x-2" class="w-6 h-6"></i>
                                    </div>
                                    @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
                                        <h4 class="text-sm font-semibold text-slate-800">Tidak Ada Sesi yang Cocok</h4>
                                        <p class="text-xs text-slate-400">
                                            Tidak ditemukan data yang sesuai dengan filter yang diterapkan.
                                        </p>
                                        <a href="{{ route('guru.history') }}"
                                           class="mt-1 inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white font-semibold rounded-xl text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                            <span>Reset Filter</span>
                                        </a>
                                    @else
                                        <h4 class="text-sm font-semibold text-slate-800">Belum Ada Riwayat Sesi</h4>
                                        <p class="text-xs text-slate-400">
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

        {{-- 4B. MOBILE CARDS (Hidden on Desktop) --}}
        <div class="md:hidden p-4 space-y-3.5">
            @forelse($sessions as $ses)
                @php
                    $borderClass = $ses->status === 'LOCKED'
                        ? 'border-l-4 border-l-emerald-500'
                        : 'border-l-4 border-l-amber-500';
                @endphp
                <div class="p-4 rounded-2xl border border-slate-200/80 bg-white {{ $borderClass }} shadow-xs space-y-2 transition">

                    {{-- Top: Tanggal + Status --}}
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-semibold text-slate-900 text-xs">
                                {{ $ses->created_at->translatedFormat('l, d M Y') }}
                            </span>
                            <span class="block text-[11px] text-slate-400 mono-font mt-0.5">
                                {{ $ses->created_at->format('H:i') }} WIB
                            </span>
                        </div>
                        @if($ses->status === 'LOCKED')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 shrink-0">
                                Selesai
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 shrink-0">
                                Aktif
                            </span>
                        @endif
                    </div>

                    {{-- Middle: Kelas + Mapel --}}
                    <div class="pt-0.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-maarif-700 text-white">
                                {{ $ses->schedule->classroom->name ?? '-' }}
                            </span>
                            <h4 class="font-semibold text-slate-900 text-sm leading-snug">
                                {{ $ses->schedule->subject->name ?? '-' }}
                            </h4>
                        </div>
                    </div>

                    {{-- Bottom: PIN + Durasi + Aksi --}}
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>PIN: <strong class="mono-font font-semibold text-slate-700 tracking-widest">{{ $ses->pin_code }}</strong> &middot; {{ $ses->duration_minutes }} menit</span>
                        <a href="{{ route('guru.session.reconcile', $ses) }}"
                           class="inline-flex items-center gap-1 font-semibold text-maarif-700 hover:text-maarif-800 active:text-maarif-900 bg-white border border-slate-200 px-2.5 py-1 rounded-lg transition-all duration-150 active:scale-95 shadow-2xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                            <span>Lihat Kehadiran</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 px-4 space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                        <i data-lucide="calendar-x-2" class="w-6 h-6"></i>
                    </div>
                    @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($selectedStatus))
                        <p class="text-xs font-semibold text-slate-700">Tidak ada sesi sesuai filter</p>
                        <a href="{{ route('guru.history') }}" class="text-xs text-emerald-700 font-semibold underline cursor-pointer">
                            Hapus Filter
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

</div>
@endsection
