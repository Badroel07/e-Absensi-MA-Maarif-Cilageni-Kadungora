@extends('layouts.app')

@section('title', 'Konfirmasi Kehadiran Siswa — MA Ma\'arif Cilageni')
@section('page-title', 'Konfirmasi Kehadiran Siswa')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{
    activeTab: 'all',
    searchQuery: '',
    bulkSet(status) {
        document.querySelectorAll('input[type=radio][value=' + status + ']:not(:disabled)').forEach(el => {
            el.checked = true;
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });
        if (window.triggerHaptic) window.triggerHaptic([30]);
    }
}">

    @php
        $hadirCount = $existingAttendances->where('status', 'HADIR')->count();
        $totalCount = $students->count();
        $unverifiedCount = max(0, $totalCount - $hadirCount);
    @endphp

    {{-- ── 1. HEADER HALAMAN & METADATA SESI (Unboxed, Bare Back Button, Pure Typography) ── --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-2 border-b border-slate-200/70">
        <div class="space-y-2 min-w-0">
            <div class="flex items-center gap-3">
                <a href="{{ route('guru.dashboard') }}" aria-label="Kembali ke Dashboard" class="text-slate-400 hover:text-slate-700 active:text-slate-900 transition-colors p-1 -ml-1 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600 rounded-lg inline-flex items-center justify-center shrink-0">
                    <i data-lucide="arrow-left" class="w-6 h-6"></i>
                </a>
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 heading-font tracking-tight leading-tight truncate">
                        Konfirmasi Kehadiran Siswa
                    </h1>
                    <div class="flex items-center gap-3 text-xs sm:text-sm text-slate-500 mt-1 flex-wrap">
                        <span class="font-bold text-slate-800">{{ $session->schedule->subject->name }}</span>
                        <span class="inline-flex items-center gap-1.5 font-medium text-slate-600">
                            <i data-lucide="door-closed" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                            <span>Kelas {{ $session->schedule->classroom->name }}</span>
                        </span>
                        @if(!empty($session->schedule->subject->code))
                            <span class="mono-font text-xs text-slate-400 font-medium">
                                Kode: {{ $session->schedule->subject->code }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1 mono-font text-xs text-slate-500 font-medium">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                            <span>{{ substr($session->schedule->start_time, 0, 5) }}–{{ substr($session->schedule->end_time, 0, 5) }} WIB</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Metrics Indicators (Pure Monospace / Clean Structured Layout) --}}
        <div class="flex items-center gap-2 self-start md:self-end shrink-0 flex-wrap pl-9 md:pl-0">
            <div class="px-3.5 py-2 rounded-xl bg-white border border-slate-200/80 shadow-2xs text-xs flex items-center gap-2">
                <span class="text-slate-500 font-medium text-[11px]">Total Siswa:</span>
                <span class="mono-font font-bold text-slate-900 text-sm">{{ $totalCount }}</span>
            </div>
            <div class="px-3.5 py-2 rounded-xl bg-white border border-emerald-200/80 shadow-2xs text-xs flex items-center gap-2">
                <span class="text-emerald-700 font-medium text-[11px]">Verifikasi PIN:</span>
                <span class="mono-font font-bold text-emerald-800 text-sm">{{ $hadirCount }}</span>
            </div>
            @if($unverifiedCount > 0)
                <div class="px-3.5 py-2 rounded-xl bg-white border border-rose-200/80 shadow-2xs text-xs flex items-center gap-2">
                    <span class="text-rose-700 font-medium text-[11px]">Perlu Konfirmasi:</span>
                    <span class="mono-font font-bold text-rose-800 text-sm">{{ $unverifiedCount }}</span>
                </div>
            @else
                <div class="px-3.5 py-2 rounded-xl bg-white border border-emerald-200/80 shadow-2xs text-xs font-semibold text-emerald-800 flex items-center gap-1.5">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span>Semua Hadir</span>
                </div>
            @endif
        </div>
    </div>

    {{-- ── 2. STATUS & INSTRUCTION CALLOUT ── --}}
    @if($isLocked ?? false)
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-rose-200 shadow-xs flex items-start gap-3.5 text-xs text-rose-950">
            <div class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5">
                <i data-lucide="lock" class="w-4 h-4"></i>
            </div>
            <div class="min-w-0 flex-1 space-y-1">
                <h3 class="font-bold text-sm text-rose-950 heading-font">
                    Presensi Kelas Selesai & Disimpan Permanen
                </h3>
                <p class="text-rose-800 leading-relaxed">
                    Data kehadiran kelas ini sudah berstatus final dan terkunci. Jika terdapat penyesuaian surat dokter atau izin resmi susulan, silakan hubungi <strong>Admin Madrasah</strong>.
                </p>
            </div>
        </div>
    @else
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-start gap-3.5 text-xs text-slate-600">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200/70 flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="info" class="w-4 h-4"></i>
            </div>
            <div class="min-w-0 flex-1 leading-relaxed space-y-1">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <span class="font-semibold text-slate-900">Alur Penyelesaian Presensi Kelas:</span>
                    <span class="mono-font text-[11px] text-slate-400">PIN Sesi: <strong class="text-slate-800 font-bold tracking-wider">{{ $session->pin_code }}</strong></span>
                </div>
                <p class="text-slate-600">
                    Siswa yang hadir mandiri telah otomatis terverifikasi via PIN. Untuk siswa yang belum memasukkan PIN, silakan tentukan statusnya (<strong class="text-emerald-700">Hadir</strong>, <strong class="text-amber-700">Izin</strong>, <strong class="text-sky-700">Sakit</strong>, atau <strong class="text-rose-700">Alpa</strong>) serta sertakan keterangan jika diperlukan.
                </p>
            </div>
        </div>
    @endif

    {{-- ── 3. FORM REKONSILIASI ── --}}
    <form action="{{ ($isLocked ?? false) ? '#' : route('guru.session.reconcile.save', $session) }}" method="POST" data-loading-form class="space-y-6">
        @csrf

        <div class="bg-white rounded-3xl p-5 sm:p-7 border border-slate-200/80 shadow-xs space-y-5">
            
            {{-- Toolbar: Search, Filter Tabs & Quick Actions --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                {{-- Filter Tabs (All / Belum Terverifikasi / Terverifikasi PIN) --}}
                <div class="inline-flex p-1 rounded-xl bg-slate-100/90 border border-slate-200/70 text-xs font-medium self-start">
                    <button type="button" @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-white text-slate-900 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition-all duration-150 cursor-pointer">
                        Semua ({{ $totalCount }})
                    </button>
                    <button type="button" @click="activeTab = 'unverified'" :class="activeTab === 'unverified' ? 'bg-white text-rose-900 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition-all duration-150 cursor-pointer">
                        Perlu Diisi ({{ $unverifiedCount }})
                    </button>
                    <button type="button" @click="activeTab = 'verified'" :class="activeTab === 'verified' ? 'bg-white text-emerald-900 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition-all duration-150 cursor-pointer">
                        Hadir PIN ({{ $hadirCount }})
                    </button>
                </div>

                {{-- Search & Bulk Helpers --}}
                <div class="flex items-center gap-2.5 flex-1 md:justify-end">
                    <div class="relative w-full md:w-60">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        </div>
                        <input type="text" x-model="searchQuery" placeholder="Cari nama atau NISN..." class="w-full pl-8.5 pr-3 py-1.5 text-xs bg-slate-50 hover:bg-slate-100/70 focus:bg-white border border-slate-200/90 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-maarif-600 transition-all">
                    </div>

                    @if(!($isLocked ?? false) && $unverifiedCount > 0)
                        {{-- Bulk Quick Action --}}
                        <div class="relative shrink-0" x-data="{ openBulk: false }">
                            <button type="button" @click="openBulk = !openBulk" @click.outside="openBulk = false" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs inline-flex items-center gap-1.5 border border-slate-200/80 transition-all cursor-pointer">
                                <span>Set Cepat</span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </button>
                            <div x-show="openBulk" x-cloak class="absolute right-0 mt-1.5 w-44 bg-white rounded-xl shadow-lg border border-slate-200 py-1 z-20 text-xs">
                                <button type="button" @click="bulkSet('ALPA'); openBulk = false;" class="w-full text-left px-3.5 py-2 hover:bg-rose-50 text-rose-700 font-medium flex items-center justify-between cursor-pointer">
                                    <span>Set Semua Alpa</span>
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                </button>
                                <button type="button" @click="bulkSet('HADIR'); openBulk = false;" class="w-full text-left px-3.5 py-2 hover:bg-emerald-50 text-emerald-700 font-medium flex items-center justify-between cursor-pointer">
                                    <span>Set Semua Hadir</span>
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                </button>
                                <button type="button" @click="bulkSet('IZIN'); openBulk = false;" class="w-full text-left px-3.5 py-2 hover:bg-amber-50 text-amber-700 font-medium flex items-center justify-between cursor-pointer">
                                    <span>Set Semua Izin</span>
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Students List Cards --}}
            <div class="space-y-3">
                @foreach($students as $st)
                    @php
                        $att = $existingAttendances[$st->id] ?? null;
                        $isVerifiedHadir = ($att && $att->status === 'HADIR');
                        $currentStatus = $att ? $att->status : 'ALPA';
                        $initials = collect(explode(' ', $st->name))
                            ->filter()
                            ->map(fn($w) => mb_substr($w, 0, 1))
                            ->take(2)
                            ->join('');
                    @endphp

                    @if($isVerifiedHadir)
                        {{-- Row Siswa Terverifikasi Hadir via PIN --}}
                        <div x-show="(activeTab === 'all' || activeTab === 'verified') && ('{{ strtolower($st->name) }} {{ $st->identity_number }}'.includes(searchQuery.toLowerCase().trim()))"
                             class="p-4 sm:p-4.5 rounded-2xl border border-emerald-200/80 bg-emerald-50/20 hover:bg-emerald-50/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 transition-all duration-150">
                            <div class="flex items-center gap-3.5 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-xl bg-emerald-700 text-white font-bold text-xs sm:text-sm flex items-center justify-center shrink-0 shadow-xs select-none">
                                    {{ $initials ?: 'S' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-900 leading-snug truncate" title="{{ $st->name }}">
                                        {{ $st->name }}
                                    </p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        NISN: <span class="mono-font text-slate-600">{{ $st->identity_number }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-4 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-emerald-100/70">
                                <span class="text-xs text-emerald-800 font-medium inline-flex items-center gap-1.5">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                                    <span>PIN: <strong class="mono-font font-semibold">{{ $att->verified_at ? $att->verified_at->format('H:i:s') . ' WIB' : 'Tercatat' }}</strong></span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-800">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                    <span>Hadir (PIN)</span>
                                </span>
                            </div>
                        </div>
                    @else
                        {{-- Row Siswa Belum Hadir (Manual Reconcile) --}}
                        <div x-show="(activeTab === 'all' || activeTab === 'unverified') && ('{{ strtolower($st->name) }} {{ $st->identity_number }}'.includes(searchQuery.toLowerCase().trim()))"
                             class="p-4 sm:p-5 rounded-2xl border border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs transition-all duration-150 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            
                            {{-- Left: Identitas Siswa --}}
                            <div class="flex items-center gap-3.5 min-w-0 lg:w-1/3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs sm:text-sm flex items-center justify-center shrink-0 border border-slate-200/70 select-none">
                                    {{ $initials ?: 'S' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-semibold text-slate-900 leading-snug truncate" title="{{ $st->name }}">
                                            {{ $st->name }}
                                        </p>
                                        <span class="text-[11px] font-medium text-slate-400">
                                            (Belum Hadir)
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        NISN: <span class="mono-font text-slate-500">{{ $st->identity_number }}</span>
                                    </p>
                                </div>
                            </div>

                            {{-- Middle: Catatan / Keterangan Surat Izin --}}
                            <div class="w-full lg:flex-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                </div>
                                <input type="text" name="notes[{{ $st->id }}]" value="{{ $att->notes ?? '' }}" placeholder="Keterangan surat izin / alasan..."
                                    {{ ($isLocked ?? false) ? 'disabled readonly' : '' }}
                                    class="w-full pl-9 pr-3.5 py-2.5 text-xs bg-slate-50 hover:bg-slate-100/70 focus:bg-white border border-slate-200/90 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-maarif-600 focus:border-transparent transition-all duration-150 {{ ($isLocked ?? false) ? 'bg-slate-100/70 cursor-not-allowed' : '' }}">
                            </div>

                            {{-- Right: Tactile Segmented Status Switcher --}}
                            <div class="bg-slate-100/90 p-1 rounded-xl grid grid-cols-4 gap-1 border border-slate-200/70 shrink-0 w-full lg:w-auto min-w-[280px]">
                                <label class="{{ ($isLocked ?? false) ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }} select-none">
                                    <input type="radio" name="statuses[{{ $st->id }}]" value="HADIR" class="peer hidden" {{ $currentStatus === 'HADIR' ? 'checked' : '' }} {{ ($isLocked ?? false) ? 'disabled' : '' }}>
                                    <span class="block py-2 px-2 text-center text-xs font-medium rounded-lg text-slate-600 hover:text-slate-900 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:font-semibold peer-checked:shadow-xs transition-all duration-150">
                                        <span class="inline-flex items-center justify-center gap-1">
                                            <i data-lucide="check" class="w-3.5 h-3.5 shrink-0"></i>
                                            <span>Hadir</span>
                                        </span>
                                    </span>
                                </label>

                                <label class="{{ ($isLocked ?? false) ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }} select-none">
                                    <input type="radio" name="statuses[{{ $st->id }}]" value="IZIN" class="peer hidden" {{ $currentStatus === 'IZIN' ? 'checked' : '' }} {{ ($isLocked ?? false) ? 'disabled' : '' }}>
                                    <span class="block py-2 px-2 text-center text-xs font-medium rounded-lg text-slate-600 hover:text-slate-900 peer-checked:bg-amber-500 peer-checked:text-white peer-checked:font-semibold peer-checked:shadow-xs transition-all duration-150">
                                        <span class="inline-flex items-center justify-center gap-1">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5 shrink-0"></i>
                                            <span>Izin</span>
                                        </span>
                                    </span>
                                </label>

                                <label class="{{ ($isLocked ?? false) ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }} select-none">
                                    <input type="radio" name="statuses[{{ $st->id }}]" value="SAKIT" class="peer hidden" {{ $currentStatus === 'SAKIT' ? 'checked' : '' }} {{ ($isLocked ?? false) ? 'disabled' : '' }}>
                                    <span class="block py-2 px-2 text-center text-xs font-medium rounded-lg text-slate-600 hover:text-slate-900 peer-checked:bg-sky-600 peer-checked:text-white peer-checked:font-semibold peer-checked:shadow-xs transition-all duration-150">
                                        <span class="inline-flex items-center justify-center gap-1">
                                            <i data-lucide="activity" class="w-3.5 h-3.5 shrink-0"></i>
                                            <span>Sakit</span>
                                        </span>
                                    </span>
                                </label>

                                <label class="{{ ($isLocked ?? false) ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }} select-none">
                                    <input type="radio" name="statuses[{{ $st->id }}]" value="ALPA" class="peer hidden" {{ $currentStatus === 'ALPA' ? 'checked' : '' }} {{ ($isLocked ?? false) ? 'disabled' : '' }}>
                                    <span class="block py-2 px-2 text-center text-xs font-medium rounded-lg text-slate-600 hover:text-slate-900 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:font-semibold peer-checked:shadow-xs transition-all duration-150">
                                        <span class="inline-flex items-center justify-center gap-1">
                                            <i data-lucide="x" class="w-3.5 h-3.5 shrink-0"></i>
                                            <span>Alpa</span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- ── 4. ACTION BUTTONS AREA ── --}}
        @if(!($isLocked ?? false))
            <div class="pt-2 max-w-xl mx-auto">
                <button type="submit"
                    data-confirm="Simpan dan kunci kehadiran kelas ini sekarang? Data yang telah disimpan tidak dapat diubah kembali oleh Bapak/Ibu Guru."
                    data-confirm-title="Simpan Kehadiran Kelas"
                    data-confirm-type="primary"
                    data-confirm-btn="Ya, Simpan Sekarang"
                    data-confirm-icon="lock"
                    class="w-full min-h-[48px] bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold py-3.5 sm:py-4 px-6 rounded-2xl text-sm inline-flex items-center justify-center gap-2 shadow-lg shadow-maarif-700/25 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-maarif-600">
                    <i data-lucide="lock" class="w-4 h-4 shrink-0"></i>
                    <span>Simpan Kehadiran Kelas</span>
                </button>
            </div>
        @endif
    </form>
</div>
@endsection

