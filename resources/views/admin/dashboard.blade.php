@extends('layouts.admin')

@section('title', 'Dashboard Admin — Sistem Presensi Madrasah')
@section('page-title', 'Dashboard Utama')

@section('content')
@php
    $persenSiswaHadir = $totalSiswa > 0 ? round(($siswaHadir / $totalSiswa) * 100, 1) : 0;
    $persenGuruHadir  = $totalGuru > 0 ? round(($guruHadir / $totalGuru) * 100, 1) : 0;
    $today = \Carbon\Carbon::now();
@endphp

<div class="space-y-7">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-1.5">
        <div>
            <p class="text-xs font-semibold text-maarif-700 uppercase tracking-widest mb-0.5">
                {{ $today->isoFormat('dddd, D MMMM Y') }}
            </p>
            <h1 class="text-2xl font-black text-slate-900 heading-font tracking-tight leading-tight">
                Dashboard Utama
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Pantauan presensi siswa, dewan guru, dan sesi kelas aktif hari ini.
            </p>
        </div>
        {{-- Jam live --}}
        <div class="shrink-0 text-right">
            <p id="liveDashClock" class="text-xl font-black mono-font text-slate-800 tracking-tight leading-none tabular-nums"></p>
            <p class="text-[11px] text-slate-400 font-medium mt-0.5">Waktu Indonesia Barat</p>
        </div>
    </div>

    {{-- ── KPI SECTION ──────────────────────────────────────────────── --}}
    {{-- Layout: hero card kiri (2/5) + tiga mini cards kanan (3/5) --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        {{-- Hero card: Siswa Hadir — prioritas utama admin --}}
        <div class="lg:col-span-2 bg-gradient-to-br from-maarif-800 to-maarif-900 rounded-2xl p-6 flex flex-col justify-between text-white relative overflow-hidden">
            {{-- Background motif --}}
            <div class="absolute inset-0 opacity-[0.04]" style="background-image:radial-gradient(circle at 70% 20%, #fff 0%, transparent 55%), radial-gradient(circle at 20% 80%, #fff 0%, transparent 45%);"></div>
            <div class="relative">
                <p class="text-xs font-extrabold text-emerald-300 uppercase tracking-widest">Kehadiran Siswa Hari Ini</p>
                <div class="mt-3 flex items-baseline gap-2.5">
                    <span class="text-5xl font-black mono-font tracking-tight leading-none">{{ number_format($siswaHadir, 0, ',', '.') }}</span>
                    <span class="text-sm font-bold text-emerald-200/80">/ {{ number_format($totalSiswa, 0, ',', '.') }} siswa</span>
                </div>
                {{-- Progress bar --}}
                <div class="mt-4">
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="text-xs font-semibold text-emerald-200">Tingkat Partisipasi</span>
                        <span class="text-xs font-black mono-font text-white">{{ $persenSiswaHadir }}%</span>
                    </div>
                    <div class="w-full bg-white/15 rounded-full h-2">
                        <div class="bg-emerald-400 h-2 rounded-full transition-all duration-700"
                             style="width: {{ min($persenSiswaHadir, 100) }}%"></div>
                    </div>
                </div>
            </div>
            <div class="relative mt-5 flex items-center gap-4 text-xs font-semibold text-emerald-200/80">
                <span><span class="font-black text-amber-300 text-sm">{{ number_format($siswaIzin, 0, ',', '.') }}</span> Izin</span>
                <span class="text-white/20">|</span>
                <span><span class="font-black text-sky-300 text-sm">{{ number_format($siswaSakit, 0, ',', '.') }}</span> Sakit</span>
                <span class="text-white/20">|</span>
                <span><span class="font-black text-rose-300 text-sm">{{ number_format($siswaAlpa, 0, ',', '.') }}</span> Alpa</span>
            </div>
        </div>

        {{-- 3 mini cards kanan --}}
        <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-4">

            {{-- Guru Hadir --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Guru Hadir</p>
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-200/70 flex items-center justify-center shrink-0">
                        <i data-lucide="users" class="w-4 h-4 text-emerald-700"></i>
                    </span>
                </div>
                <div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl font-black text-slate-900 mono-font">{{ number_format($guruHadir, 0, ',', '.') }}</span>
                        <span class="text-xs text-slate-400 font-semibold">/ {{ number_format($totalGuru, 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-2.5 w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $persenGuruHadir }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500 font-medium mt-1.5">{{ $persenGuruHadir }}% dari total guru</p>
                </div>
            </div>

            {{-- Izin & Sakit --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Izin &amp; Sakit</p>
                    <span class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-200/70 flex items-center justify-center shrink-0">
                        <i data-lucide="file-text" class="w-4 h-4 text-amber-600"></i>
                    </span>
                </div>
                <div>
                    <span class="text-3xl font-black text-amber-700 mono-font">{{ number_format($siswaIzin + $siswaSakit, 0, ',', '.') }}</span>
                    <p class="text-[11px] text-slate-500 font-medium mt-2">
                        <span class="font-extrabold text-amber-700">{{ number_format($siswaIzin, 0, ',', '.') }}</span> izin &bull;
                        <span class="font-extrabold text-sky-700">{{ number_format($siswaSakit, 0, ',', '.') }}</span> sakit
                    </p>
                </div>
            </div>

            {{-- Alpa --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Tanpa Keterangan</p>
                    <span class="w-8 h-8 rounded-xl bg-rose-50 border border-rose-200/70 flex items-center justify-center shrink-0">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    </span>
                </div>
                <div>
                    <span class="text-3xl font-black text-rose-700 mono-font">{{ number_format($siswaAlpa, 0, ',', '.') }}</span>
                    <p class="text-[11px] text-slate-500 font-medium mt-2">Belum terverifikasi hadir</p>
                </div>
            </div>

        </div>
    </div>

    {{-- ── PRESENSI DEWAN GURU ───────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
        {{-- Section header dengan left-accent stripe --}}
        <div class="p-5 border-b border-slate-100 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-1 h-8 rounded-full bg-maarif-700 shrink-0"></div>
                <div>
                    <h3 class="font-extrabold text-slate-900 heading-font text-sm leading-tight">
                        Presensi Dewan Guru
                        <span class="ml-2 text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 border border-slate-200 align-middle">10 Terbaru</span>
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Kehadiran dan kepulangan guru di lingkungan madrasah hari ini.</p>
                </div>
            </div>
            <a href="{{ route('admin.presensi-guru.index') }}"
               class="text-xs font-bold text-white inline-flex items-center gap-1.5 shrink-0 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 px-3.5 py-2 rounded-xl transition-all duration-150 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                <span>Kelola</span>
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-bold text-[10px] tracking-widest">
                        <th class="py-3 px-5">Guru / NIP</th>
                        <th class="py-3 px-5 text-center">Jam Masuk</th>
                        <th class="py-3 px-5 text-center">Jam Pulang</th>
                        <th class="py-3 px-5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentGuruAttendances as $gAtt)
                        <tr class="hover:bg-slate-50/60 transition-colors duration-100">
                            <td class="py-3.5 px-5">
                                <p class="font-bold text-slate-900 text-sm leading-snug">{{ $gAtt->user->name ?? 'Guru' }}</p>
                                <p class="text-[11px] text-slate-400 mono-font mt-0.5">NIP: {{ $gAtt->user->identity_number ?? '-' }}</p>
                            </td>
                            <td class="py-3.5 px-5 text-center">
                                @if($gAtt->check_in_time)
                                    <span class="inline-block font-bold text-emerald-700 bg-emerald-50 mono-font px-2.5 py-1 rounded-lg border border-emerald-200/70 text-xs">
                                        {{ \Carbon\Carbon::parse($gAtt->check_in_time)->format('H:i') }} WIB
                                    </span>
                                @else
                                    <span class="text-slate-300 font-medium">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-center">
                                @if($gAtt->check_out_time)
                                    <span class="inline-block font-bold text-emerald-700 bg-emerald-50 mono-font px-2.5 py-1 rounded-lg border border-emerald-200/70 text-xs">
                                        {{ \Carbon\Carbon::parse($gAtt->check_out_time)->format('H:i') }} WIB
                                    </span>
                                @else
                                    <span class="text-slate-400 italic font-medium text-xs">Belum</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                @php $status = $gAtt->check_in_status ?? 'HADIR'; @endphp
                                @if($status === 'HADIR')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/70">Tepat Waktu</span>
                                @elseif($status === 'TERLAMBAT')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-200">Terlambat</span>
                                @elseif($status === 'IZIN')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-200">Izin</span>
                                @elseif($status === 'SAKIT')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-sky-50 text-sky-800 border border-sky-200">Sakit</span>
                                @elseif($status === 'ALPA')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-rose-50 text-rose-800 border border-rose-200/70">Alpa</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-slate-100 text-slate-700 border border-slate-200">{{ $status }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-14 text-center">
                                <div class="inline-flex flex-col items-center gap-2">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i data-lucide="user-x" class="w-6 h-6 text-slate-400"></i>
                                    </span>
                                    <p class="text-sm font-bold text-slate-600">Belum ada presensi guru hari ini</p>
                                    <p class="text-xs text-slate-400">Guru perlu check-in melalui GPS dari area madrasah.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── PRESENSI SISWA ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-1 h-8 rounded-full bg-sky-500 shrink-0"></div>
                <div>
                    <h3 class="font-extrabold text-slate-900 heading-font text-sm leading-tight">
                        Presensi Siswa
                        <span class="ml-2 text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 border border-slate-200 align-middle">10 Terbaru</span>
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Catatan kehadiran siswa dalam sesi kelas yang terverifikasi hari ini.</p>
                </div>
            </div>
            <a href="{{ route('admin.presensi-siswa.index') }}"
               class="text-xs font-bold text-white inline-flex items-center gap-1.5 shrink-0 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 px-3.5 py-2 rounded-xl transition-all duration-150 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                <span>Kelola</span>
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-bold text-[10px] tracking-widest">
                        <th class="py-3 px-5">Siswa / NISN</th>
                        <th class="py-3 px-5">Kelas &amp; Mapel</th>
                        <th class="py-3 px-5 text-center">Waktu Presensi</th>
                        <th class="py-3 px-5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentSiswaAttendances as $sAtt)
                        <tr class="hover:bg-slate-50/60 transition-colors duration-100">
                            <td class="py-3.5 px-5">
                                <p class="font-bold text-slate-900 text-sm leading-snug">{{ $sAtt->student->name ?? 'Siswa' }}</p>
                                <p class="text-[11px] text-slate-400 mono-font mt-0.5">NISN: {{ $sAtt->student->identity_number ?? '-' }}</p>
                            </td>
                            <td class="py-3.5 px-5">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-extrabold border border-slate-200">
                                        {{ $sAtt->student->classroom->name ?? ($sAtt->schedule->classroom->name ?? '-') }}
                                    </span>
                                    <span class="text-slate-700 font-semibold text-xs">{{ $sAtt->schedule->subject->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-5 text-center font-bold mono-font text-slate-600 text-xs">
                                {{ ($sAtt->verified_at ?? $sAtt->created_at)?->format('H:i') ? ($sAtt->verified_at ?? $sAtt->created_at)->format('H:i') . ' WIB' : '—' }}
                            </td>
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                @if($sAtt->status === 'HADIR')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/70">Hadir</span>
                                @elseif($sAtt->status === 'IZIN')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-200">Izin</span>
                                @elseif($sAtt->status === 'SAKIT')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-sky-50 text-sky-800 border border-sky-200">Sakit</span>
                                @elseif($sAtt->status === 'ALPA')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-rose-50 text-rose-800 border border-rose-200/70">Alpa</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-slate-100 text-slate-700 border border-slate-200">{{ $sAtt->status }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-14 text-center">
                                <div class="inline-flex flex-col items-center gap-2">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i data-lucide="clipboard-x" class="w-6 h-6 text-slate-400"></i>
                                    </span>
                                    <p class="text-sm font-bold text-slate-600">Belum ada presensi siswa hari ini</p>
                                    <p class="text-xs text-slate-400">Guru perlu membuka sesi kelas agar siswa dapat presensi via PIN.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── SESI KELAS BERLANGSUNG ────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center gap-3">
            <div class="w-1 h-8 rounded-full bg-amber-500 shrink-0"></div>
            <div>
                <h3 class="font-extrabold text-slate-900 heading-font text-sm leading-tight">Sesi Kelas Berlangsung</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Token presensi aktif dan sesi kelas yang dibuka guru hari ini.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-bold text-[10px] tracking-widest">
                        <th class="py-3 px-5">Kelas &amp; Mapel</th>
                        <th class="py-3 px-5">Guru Pengampu</th>
                        <th class="py-3 px-5 text-center">Kode PIN</th>
                        <th class="py-3 px-5">Waktu Dibuka</th>
                        <th class="py-3 px-5 text-center">Status Sesi</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($activeSessions as $ses)
                        <tr class="hover:bg-slate-50/60 transition-colors duration-100">
                            <td class="py-3.5 px-5">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-md bg-maarif-50 text-maarif-800 text-[11px] font-extrabold border border-maarif-200/70">
                                        Kelas {{ $ses->schedule->classroom->name }}
                                    </span>
                                    <span class="font-bold text-slate-900 text-sm">{{ $ses->schedule->subject->name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-5 text-slate-700 font-semibold">{{ $ses->teacher->name }}</td>
                            <td class="py-3.5 px-5 text-center font-black mono-font text-maarif-700 text-base tracking-widest">{{ $ses->pin_code }}</td>
                            <td class="py-3.5 px-5 text-slate-500 mono-font font-semibold">{{ $ses->started_at->format('H:i:s') }} WIB</td>
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                @if($ses->status === 'ACTIVE' && !$ses->isExpired())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                                        Aktif ({{ $ses->remaining_seconds }}d)
                                    </span>
                                @elseif($ses->status === 'LOCKED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/70">
                                        <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                                        Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-rose-50 text-rose-800 border border-rose-200/70">Waktu Habis</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <a href="{{ route('guru.session.reconcile', $ses) }}" target="_blank"
                                   title="Buka Rekap Kehadiran Kelas"
                                   class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white rounded-lg text-xs font-extrabold transition-all duration-150 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5 shrink-0"></i>
                                    <span>Rekap</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-14 text-center">
                                <div class="inline-flex flex-col items-center gap-2">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i data-lucide="layout-dashboard" class="w-6 h-6 text-slate-400"></i>
                                    </span>
                                    <p class="text-sm font-bold text-slate-600">Tidak ada sesi kelas aktif saat ini</p>
                                    <p class="text-xs text-slate-400">Sesi akan muncul di sini saat guru membuka presensi dari dashboard guru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
(function () {
    const el = document.getElementById('liveDashClock');
    if (!el) return;
    function tick() {
        const now = window.getServerNow ? window.getServerNow() : new Date();
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        const ss = String(now.getSeconds()).padStart(2, '0');
        el.textContent = `${hh}:${mm}:${ss}`;
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endsection
