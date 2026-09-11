@extends('layouts.app')

@section('title', 'Konfirmasi Kehadiran Siswa — MA Ma\'arif Cilageni')

@section('content')
<div class="space-y-4 max-w-7xl mx-auto pb-8">
    <!-- Header Navigation & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center space-x-3">
            <a href="{{ route('guru.dashboard') }}" aria-label="Kembali ke Dashboard" class="w-10 h-10 rounded-2xl bg-slate-50 border border-slate-200 hover:bg-slate-100 active:bg-slate-200 text-slate-700 inline-flex items-center justify-center transition-all duration-150 active:scale-95 shadow-2xs shrink-0 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="min-w-0">
                <h2 class="text-base sm:text-lg font-bold text-slate-900 heading-font leading-tight">Konfirmasi Kehadiran Siswa</h2>
                <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
                    <span class="font-medium text-slate-700">{{ $session->schedule->subject->name }}</span>
                    <span class="text-slate-300">&bull;</span>
                    <span>Kelas {{ $session->schedule->classroom->name }}</span>
                </p>
            </div>
        </div>

        @php
            $hadirCount = $existingAttendances->where('status', 'HADIR')->count();
            $totalCount = $students->count();
            $unverifiedCount = max(0, $totalCount - $hadirCount);
        @endphp

        <!-- Quick Summary Badges -->
        <div class="flex items-center gap-2 self-start sm:self-center overflow-x-auto max-w-full pb-1 sm:pb-0">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200/60 shrink-0">
                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                <span>Total: <strong>{{ $totalCount }}</strong></span>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/80 shrink-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Hadir: <strong>{{ $hadirCount }}</strong></span>
            </span>
            @if($unverifiedCount > 0)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200/80 shrink-0">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span>Perlu Diisi: <strong>{{ $unverifiedCount }}</strong></span>
                </span>
            @endif
        </div>
    </div>

    @if($isLocked ?? false)
        <!-- Locked State Banner -->
        <div class="bg-rose-50 border border-rose-200/80 rounded-3xl p-4 sm:p-5 text-xs text-rose-900 shadow-xs">
            <div class="flex items-start space-x-3.5">
                <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-rose-950 heading-font">Data Kehadiran Kelas Telah Ditutup & Disimpan Permanen</h3>
                    <p class="text-rose-700 leading-relaxed mt-1">
                        Data kehadiran kelas ini sudah berstatus final dan tersimpan. Jika terdapat kesalahan absensi siswa atau surat izin susulan, perubahan dapat dibantu oleh <strong>Admin</strong>.
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Instruction Banner -->
        <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 sm:p-4 text-xs text-slate-600 flex items-start gap-3">
            <div class="w-7 h-7 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="leading-relaxed">
                <span class="font-semibold text-slate-800">Petunjuk Konfirmasi:</span>
                Siswa yang belum memasukkan PIN otomatis ditandai <span class="font-semibold text-rose-700">Alpa</span>. Bapak/Ibu Guru dapat memilih <span class="font-semibold text-emerald-700">Hadir</span> (manual jika siswa terkendala PIN), <span class="font-semibold text-amber-700">Izin</span>, atau <span class="font-semibold text-sky-700">Sakit</span>.
            </div>
        </div>
    @endif

    <!-- Reconciliation Form -->
    <form action="{{ ($isLocked ?? false) ? '#' : route('guru.session.reconcile.save', $session) }}" method="POST" data-loading-form class="space-y-6">
        @csrf

        <div class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider heading-font">Daftar Siswa Kelas</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Tinjau status kehadiran masing-masing siswa di bawah ini</p>
                </div>
            </div>

            <!-- Single Column Student List -->
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
                        <!-- Row Hadir (PIN) -->
                        <div class="p-4 sm:p-4.5 rounded-2xl border border-emerald-200/90 bg-emerald-50/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 transition hover:border-emerald-300">
                            <div class="flex items-center gap-3.5 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white font-bold text-sm flex items-center justify-center shrink-0 shadow-xs">
                                    {{ $initials ?: 'S' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-900 leading-snug">{{ $st->name }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">NISN: <span class="mono-font">{{ $st->identity_number }}</span></p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-emerald-100">
                                <span class="text-xs text-emerald-800 font-medium">
                                    Terverifikasi PIN: <strong class="mono-font font-semibold">{{ $att->verified_at ? $att->verified_at->format('H:i:s') . ' WIB' : 'Tercatat' }}</strong>
                                </span>
                                <span class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1.5 whitespace-nowrap">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>HADIR (PIN)</span>
                                </span>
                            </div>
                        </div>
                    @else
                        <!-- Row Belum Hadir (Manual Reconcile) -->
                        <div class="p-4 sm:p-4.5 rounded-2xl border border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs transition flex flex-col lg:flex-row lg:items-center justify-between gap-3.5 sm:gap-4">
                            <!-- Left: Student Info -->
                            <div class="flex items-center gap-3.5 min-w-0 lg:w-1/3">
                                <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-700 font-bold text-sm flex items-center justify-center shrink-0 border border-slate-200/70">
                                    {{ $initials ?: 'S' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-semibold text-slate-900 leading-snug truncate" title="{{ $st->name }}">{{ $st->name }}</p>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-500 bg-slate-100 border border-slate-200 shrink-0">
                                            Belum Hadir
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-0.5">NISN: <span class="mono-font">{{ $st->identity_number }}</span></p>
                                </div>
                            </div>

                            <!-- Middle: Note Input -->
                            <div class="w-full lg:flex-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                                <input type="text" name="notes[{{ $st->id }}]" value="{{ $att->notes ?? '' }}" placeholder="Catatan / keterangan surat (opsional)..."
                                    {{ ($isLocked ?? false) ? 'disabled readonly' : '' }}
                                    class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 hover:bg-slate-100/70 focus:bg-white border border-slate-200/80 rounded-xl text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-maarif-600 focus:border-transparent transition {{ ($isLocked ?? false) ? 'bg-slate-100/70 cursor-not-allowed' : '' }}">
                            </div>

                            <!-- Right: Segmented Status Switcher (Hadir, Izin, Sakit, Alpa) -->
                            <div class="bg-slate-100/90 p-1 rounded-xl grid grid-cols-4 gap-1 border border-slate-200/60 shrink-0 w-full sm:w-auto min-w-[290px]">
                                <label class="{{ ($isLocked ?? false) ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }} select-none">
                                    <input type="radio" name="statuses[{{ $st->id }}]" value="HADIR" class="peer hidden" {{ $currentStatus === 'HADIR' ? 'checked' : '' }} {{ ($isLocked ?? false) ? 'disabled' : '' }}>
                                    <span class="block py-1.5 px-2 text-center text-xs font-semibold rounded-lg text-slate-600 hover:text-slate-900 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:font-semibold peer-checked:shadow-xs transition-all">
                                        <span class="inline-flex items-center justify-center gap-1">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Hadir</span>
                                        </span>
                                    </span>
                                </label>

                                <label class="{{ ($isLocked ?? false) ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }} select-none">
                                    <input type="radio" name="statuses[{{ $st->id }}]" value="IZIN" class="peer hidden" {{ $currentStatus === 'IZIN' ? 'checked' : '' }} {{ ($isLocked ?? false) ? 'disabled' : '' }}>
                                    <span class="block py-1.5 px-2 text-center text-xs font-semibold rounded-lg text-slate-600 hover:text-slate-900 peer-checked:bg-amber-500 peer-checked:text-white peer-checked:font-semibold peer-checked:shadow-xs transition-all">
                                        <span class="inline-flex items-center justify-center gap-1">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span>Izin</span>
                                        </span>
                                    </span>
                                </label>

                                <label class="{{ ($isLocked ?? false) ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }} select-none">
                                    <input type="radio" name="statuses[{{ $st->id }}]" value="SAKIT" class="peer hidden" {{ $currentStatus === 'SAKIT' ? 'checked' : '' }} {{ ($isLocked ?? false) ? 'disabled' : '' }}>
                                    <span class="block py-1.5 px-2 text-center text-xs font-semibold rounded-lg text-slate-600 hover:text-slate-900 peer-checked:bg-sky-600 peer-checked:text-white peer-checked:font-semibold peer-checked:shadow-xs transition-all">
                                        <span class="inline-flex items-center justify-center gap-1">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                            <span>Sakit</span>
                                        </span>
                                    </span>
                                </label>

                                <label class="{{ ($isLocked ?? false) ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }} select-none">
                                    <input type="radio" name="statuses[{{ $st->id }}]" value="ALPA" class="peer hidden" {{ $currentStatus === 'ALPA' ? 'checked' : '' }} {{ ($isLocked ?? false) ? 'disabled' : '' }}>
                                    <span class="block py-1.5 px-2 text-center text-xs font-semibold rounded-lg text-slate-600 hover:text-slate-900 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:font-semibold peer-checked:shadow-xs transition-all">
                                        <span class="inline-flex items-center justify-center gap-1">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
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

        @if($isLocked ?? false)
            <!-- Locked State Return Navigation -->
            <div class="pt-2 max-w-xl mx-auto space-y-2.5 text-center">
                <a href="{{ route('guru.dashboard') }}"
                    class="w-full bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white font-semibold py-3.5 px-6 rounded-2xl text-xs inline-flex items-center justify-center gap-2 transition-all duration-150 shadow-md shadow-slate-900/15 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-slate-700">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Beranda</span>
                </a>
                <div class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100/80 border border-slate-200/80 text-slate-500 text-xs text-center leading-relaxed">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span class="text-[11px] sm:text-xs">Data kehadiran telah disimpan permanen. Hubungi Admin jika memerlukan perubahan data.</span>
                </div>
            </div>
        @else
            <!-- Submit and Lock Button -->
            <div class="pt-2 max-w-xl mx-auto">
                <button type="submit"
                    data-confirm="Simpan dan kunci kehadiran kelas ini sekarang? Data yang telah disimpan tidak dapat diubah kembali oleh Bapak/Ibu Guru."
                    data-confirm-title="Simpan Kehadiran Kelas"
                    data-confirm-type="primary"
                    data-confirm-btn="Ya, Simpan Sekarang"
                    data-confirm-icon="lock"
                    class="w-full min-h-[48px] bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold py-3.5 sm:py-4 px-6 rounded-2xl text-sm inline-flex items-center justify-center gap-2 shadow-lg shadow-maarif-700/25 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-maarif-600">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Simpan Kehadiran Kelas</span>
                </button>
            </div>
        @endif
    </form>
</div>
@endsection

