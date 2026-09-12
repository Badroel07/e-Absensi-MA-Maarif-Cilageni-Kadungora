@extends('layouts.app')

@section('title', 'Konfirmasi Kehadiran Siswa — MA Ma\'arif Cilageni')
@section('page-title', 'Konfirmasi Kehadiran Siswa')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: 'all',
    searchQuery: '',
    selectedStatuses: {},
    init() {
        @foreach($students as $st)
            @php
                $att = $existingAttendances[$st->id] ?? null;
                $stStatus = $att ? $att->status : 'ALPA';
            @endphp
            this.selectedStatuses['{{ $st->id }}'] = '{{ $stStatus }}';
        @endforeach
    },
    setStatus(studentId, status) {
        if ({{ ($isLocked ?? false) ? 'true' : 'false' }}) return;
        this.selectedStatuses[studentId] = status;
        const radio = document.querySelector(`input[name='statuses[${studentId}]'][value='${status}']`);
        if (radio) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change', { bubbles: true }));
        }
        if (window.triggerHaptic) window.triggerHaptic([20]);
    }
}">

    @php
        $hadirCount = $existingAttendances->where('status', 'HADIR')->count();
        $totalCount = $students->count();
        $unverifiedCount = max(0, $totalCount - $hadirCount);
    @endphp

    <!-- BEGIN: HeaderSection -->
    <div class="pt-1 pb-0.5 flex items-center gap-3">
        <a href="{{ route('guru.dashboard') }}" aria-label="Kembali" class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 flex items-center justify-center transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
            </svg>
        </a>
        <div class="flex-1 min-w-0">
            <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight leading-tight heading-font">Konfirmasi Kehadiran Siswa</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola dan finalisasi status kehadiran presensi kelas</p>
        </div>
    </div>
    <!-- END: HeaderSection -->

    <!-- BEGIN: SessionMetaCard -->
    <section class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-soft" data-purpose="session-meta">
        <div class="flex items-start space-x-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center flex-wrap gap-1.5 mb-1.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-100 shadow-sm">
                        <svg class="w-3.5 h-3.5 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                        </svg>
                        Kelas {{ $session->schedule->classroom->name }}
                    </span>
                    @if(!empty($session->schedule->subject->code))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/80 shadow-sm mono-font">
                            <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                            </svg>
                            {{ $session->schedule->subject->code }}
                        </span>
                    @endif
                </div>
                <h2 class="text-lg font-bold text-slate-900 tracking-tight leading-snug heading-font">
                    {{ $session->schedule->subject->name }}
                </h2>
                <div class="flex items-center text-xs text-slate-500 font-medium mt-1">
                    <svg class="w-3.5 h-3.5 mr-1 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                    </svg>
                    <span class="mono-font">{{ substr($session->schedule->start_time, 0, 5) }} – {{ substr($session->schedule->end_time, 0, 5) }} WIB</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2 mt-4 pt-3.5 border-t border-slate-100 text-center">
            <div class="bg-slate-50 border border-slate-200/60 rounded-xl p-2.5">
                <span class="text-[10px] font-medium text-slate-500 block leading-tight">Total Siswa</span>
                <span class="text-base font-bold text-slate-800 mt-0.5 block leading-tight mono-font heading-font">{{ $totalCount }}</span>
            </div>
            <div class="bg-emerald-50/70 border border-emerald-100 rounded-xl p-2.5">
                <span class="text-[10px] font-medium text-emerald-700 block leading-tight">Verifikasi PIN</span>
                <span class="text-base font-bold text-emerald-700 mt-0.5 block leading-tight mono-font heading-font">{{ $hadirCount }}</span>
            </div>
            <div class="bg-rose-50/70 border border-rose-100 rounded-xl p-2.5">
                <span class="text-[10px] font-medium text-rose-700 block leading-tight">Perlu Konfirmasi</span>
                <span class="text-base font-bold text-rose-600 mt-0.5 block leading-tight mono-font heading-font">{{ $unverifiedCount }}</span>
            </div>
        </div>
    </section>
    <!-- END: SessionMetaCard -->

    <!-- BEGIN: AlertBanner (LOCKED) -->
    @if($isLocked ?? false)
        <section class="bg-gradient-to-br from-rose-50 via-rose-50/60 to-white border border-rose-200 rounded-2xl p-4 shadow-soft" data-purpose="status-alert-banner">
            <div class="flex items-start space-x-3">
                <div class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-rose-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                    </svg>
                </div>
                <div class="flex-1 text-xs">
                    <h3 class="text-sm font-bold text-rose-950 leading-snug heading-font">Presensi Kelas Selesai &amp; Disimpan Permanen</h3>
                    <p class="text-rose-800/80 leading-relaxed mt-1 text-[11px]">
                        Data kehadiran kelas ini sudah berstatus final dan terkunci. Jika terdapat penyesuaian surat dokter atau izin resmi susulan, silakan hubungi <strong class="text-rose-900 font-semibold underline decoration-rose-300 underline-offset-2">Admin Madrasah</strong>.
                    </p>
                </div>
            </div>
        </section>
    @endif
    <!-- END: AlertBanner -->

    <!-- BEGIN: FilteringAndSearch -->
    <section class="space-y-2.5" data-purpose="filtering-and-search">
        <div class="flex items-center space-x-1.5 overflow-x-auto no-scrollbar py-0.5">
            <button type="button" @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80'" class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition-colors cursor-pointer">
                Semua ({{ $totalCount }})
            </button>
            <button type="button" @click="activeTab = 'unverified'" :class="activeTab === 'unverified' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80'" class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition-colors cursor-pointer">
                Perlu Diisi ({{ $unverifiedCount }})
            </button>
            <button type="button" @click="activeTab = 'verified'" :class="activeTab === 'verified' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80'" class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition-colors cursor-pointer">
                Hadir PIN ({{ $hadirCount }})
            </button>
        </div>

        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                </svg>
            </div>
            <input x-model="searchQuery" class="w-full bg-white text-slate-800 text-xs rounded-xl pl-9 pr-4 py-2.5 border border-slate-200/90 focus:outline-none focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600 transition placeholder:text-slate-400 shadow-soft" placeholder="Cari nama atau NISN..." type="search">
        </div>
    </section>
    <!-- END: FilteringAndSearch -->

    <!-- BEGIN: StudentCardsList (FORM) -->
    <form action="{{ ($isLocked ?? false) ? '#' : route('guru.session.reconcile.save', $session) }}" method="POST" data-loading-form class="space-y-4">
        @csrf

        <section class="space-y-3 pt-1" data-purpose="students-attendance-list">
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

                <!-- Student Article Card -->
                <article x-show="(activeTab === 'all' || (activeTab === 'verified' && {{ $isVerifiedHadir ? 'true' : 'false' }}) || (activeTab === 'unverified' && {{ $isVerifiedHadir ? 'false' : 'true' }})) && ('{{ strtolower($st->name) }} {{ $st->identity_number }}'.includes(searchQuery.toLowerCase().trim()))"
                         class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200/90 shadow-soft space-y-3">
                    
                    <!-- Student Header Info -->
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-200/60 heading-font select-none">
                            {{ $initials ?: 'S' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline space-x-1.5">
                                <h4 class="text-sm font-bold text-slate-900 truncate heading-font">{{ $st->name }}</h4>
                                <span class="text-[11px] font-medium text-slate-400 shrink-0">({{ $isVerifiedHadir ? 'Hadir PIN' : 'Belum Hadir' }})</span>
                            </div>
                            <p class="text-[11px] font-medium text-slate-500 tracking-tight mono-font">NISN: {{ $st->identity_number }}</p>
                        </div>
                    </div>

                    <!-- Note / Justification Input -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                            </svg>
                        </div>
                        <input class="w-full text-xs pl-8 pr-3 py-2 bg-slate-50/70 rounded-xl border border-slate-200 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 text-slate-700 placeholder:text-slate-400 transition {{ ($isLocked ?? false) ? 'cursor-not-allowed opacity-75' : '' }}"
                               placeholder="Keterangan surat izin / alasan..."
                               type="text"
                               name="notes[{{ $st->id }}]"
                               value="{{ $att->notes ?? '' }}"
                               {{ ($isLocked ?? false) ? 'disabled readonly' : '' }}>
                    </div>

                    <!-- Hidden Radio Inputs for Form Submission -->
                    <input type="radio" name="statuses[{{ $st->id }}]" value="HADIR" class="hidden" {{ $currentStatus === 'HADIR' ? 'checked' : '' }} {{ ($isLocked ?? false) ? 'disabled' : '' }}>
                    <input type="radio" name="statuses[{{ $st->id }}]" value="IZIN" class="hidden" {{ $currentStatus === 'IZIN' ? 'checked' : '' }} {{ ($isLocked ?? false) ? 'disabled' : '' }}>
                    <input type="radio" name="statuses[{{ $st->id }}]" value="SAKIT" class="hidden" {{ $currentStatus === 'SAKIT' ? 'checked' : '' }} {{ ($isLocked ?? false) ? 'disabled' : '' }}>
                    <input type="radio" name="statuses[{{ $st->id }}]" value="ALPA" class="hidden" {{ $currentStatus === 'ALPA' ? 'checked' : '' }} {{ ($isLocked ?? false) ? 'disabled' : '' }}>

                    <!-- Status Action Pills -->
                    <div class="grid grid-cols-4 gap-1.5 pt-0.5">
                        <!-- Hadir -->
                        <button type="button" @click="setStatus('{{ $st->id }}', 'HADIR')"
                                :class="selectedStatuses['{{ $st->id }}'] === 'HADIR'
                                    ? 'bg-emerald-600 text-white font-bold border border-emerald-600 shadow-sm'
                                    : 'text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-200 font-semibold'"
                                class="inline-flex items-center justify-center space-x-1 py-1.5 rounded-lg text-xs transition-all cursor-pointer select-none {{ ($isLocked ?? false) ? 'cursor-not-allowed opacity-80' : '' }}">
                            <span :class="selectedStatuses['{{ $st->id }}'] === 'HADIR' ? 'text-white' : 'text-emerald-600 font-bold'">✓</span>
                            <span>Hadir</span>
                        </button>

                        <!-- Izin -->
                        <button type="button" @click="setStatus('{{ $st->id }}', 'IZIN')"
                                :class="selectedStatuses['{{ $st->id }}'] === 'IZIN'
                                    ? 'bg-blue-600 text-white font-bold border border-blue-600 shadow-sm'
                                    : 'text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-200 font-semibold'"
                                class="inline-flex items-center justify-center space-x-1 py-1.5 rounded-lg text-xs transition-all cursor-pointer select-none {{ ($isLocked ?? false) ? 'cursor-not-allowed opacity-80' : '' }}">
                            <span :class="selectedStatuses['{{ $st->id }}'] === 'IZIN' ? 'text-white' : 'text-blue-500 font-semibold'">📄</span>
                            <span>Izin</span>
                        </button>

                        <!-- Sakit -->
                        <button type="button" @click="setStatus('{{ $st->id }}', 'SAKIT')"
                                :class="selectedStatuses['{{ $st->id }}'] === 'SAKIT'
                                    ? 'bg-amber-500 text-white font-bold border border-amber-500 shadow-sm'
                                    : 'text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-200 font-semibold'"
                                class="inline-flex items-center justify-center space-x-1 py-1.5 rounded-lg text-xs transition-all cursor-pointer select-none {{ ($isLocked ?? false) ? 'cursor-not-allowed opacity-80' : '' }}">
                            <span :class="selectedStatuses['{{ $st->id }}'] === 'SAKIT' ? 'text-white' : 'text-amber-500 font-bold'">⚡</span>
                            <span>Sakit</span>
                        </button>

                        <!-- Alpa -->
                        <button type="button" @click="setStatus('{{ $st->id }}', 'ALPA')"
                                :class="selectedStatuses['{{ $st->id }}'] === 'ALPA'
                                    ? 'bg-rose-600 text-white font-bold border border-rose-600 shadow-sm'
                                    : 'text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-200 font-semibold'"
                                class="inline-flex items-center justify-center space-x-1 py-1.5 rounded-lg text-xs transition-all cursor-pointer select-none {{ ($isLocked ?? false) ? 'cursor-not-allowed opacity-80' : '' }}">
                            <span :class="selectedStatuses['{{ $st->id }}'] === 'ALPA' ? 'text-white' : 'text-slate-400'">✕</span>
                            <span>Alpa</span>
                        </button>
                    </div>
                </article>
            @endforeach
        </section>

        <!-- Save Button -->
        @if(!($isLocked ?? false))
            <div class="pt-2">
                <button type="submit"
                        data-confirm="Simpan dan kunci kehadiran kelas ini sekarang? Data yang telah disimpan tidak dapat diubah kembali oleh Bapak/Ibu Guru."
                        data-confirm-title="Simpan Kehadiran Kelas"
                        data-confirm-type="primary"
                        data-confirm-btn="Ya, Simpan Sekarang"
                        data-confirm-icon="lock"
                        class="w-full bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-semibold py-3.5 px-4 rounded-xl shadow-sm flex items-center justify-center gap-2 text-sm heading-font transition active:scale-[0.98] cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                    </svg>
                    <span>Simpan Kehadiran Kelas</span>
                </button>
            </div>
        @endif
    </form>
    <!-- END: StudentCardsList -->

</div>
@endsection
