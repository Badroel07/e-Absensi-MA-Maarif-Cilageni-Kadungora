@extends('layouts.admin')

@section('title', 'Dashboard Admin — Sistem Presensi Madrasah')
@section('page-title', 'Dashboard Utama')

@section('content')
@php
    $persenSiswaHadir = $totalSiswa > 0 ? round(($siswaHadir / $totalSiswa) * 100, 1) : 0;
@endphp
<div class="space-y-6">
    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Dashboard Utama</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Presensi kelas, statistik kehadiran siswa, dan rekapitulasi dewan guru hari ini</p>
        </div>
    </div>

    <!-- Top KPI Cards Overview (High Contrast Minimalist) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Siswa Hadir -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <p class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Siswa Hadir Hari Ini</p>
                <div class="mt-2.5 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-emerald-800 mono-font tracking-tight">
                        {{ number_format($siswaHadir, 0, ',', '.') }}
                    </span>
                    <span class="text-xs font-bold text-slate-600">
                        / {{ number_format($totalSiswa, 0, ',', '.') }} Siswa
                    </span>
                </div>
            </div>
            <p class="text-xs text-slate-600 font-medium mt-3">
                <span class="font-extrabold text-emerald-800">{{ $persenSiswaHadir }}%</span> tingkat partisipasi
            </p>
        </div>

        <!-- Siswa Izin / Sakit -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <p class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Izin & Sakit</p>
                <div class="mt-2.5 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-amber-700 mono-font tracking-tight">
                        {{ number_format($siswaIzin + $siswaSakit, 0, ',', '.') }}
                    </span>
                    <span class="text-xs font-bold text-slate-600">Siswa</span>
                </div>
            </div>
            <p class="text-xs text-slate-600 font-medium mt-3">
                <span class="font-extrabold text-amber-800">{{ number_format($siswaIzin, 0, ',', '.') }} izin</span> &bull; <span class="font-extrabold text-sky-800">{{ number_format($siswaSakit, 0, ',', '.') }} sakit</span>
            </p>
        </div>

        <!-- Siswa Alpa -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <p class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Tanpa Keterangan</p>
                <div class="mt-2.5 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-rose-700 mono-font tracking-tight">
                        {{ number_format($siswaAlpa, 0, ',', '.') }}
                    </span>
                    <span class="text-xs font-bold text-slate-600">Siswa</span>
                </div>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-3">
                Belum terverifikasi hadir
            </p>
        </div>

        <!-- Guru Hadir di Madrasah -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <p class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Guru Hadir di Madrasah</p>
                <div class="mt-2.5 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-emerald-800 mono-font tracking-tight">
                        {{ number_format($guruHadir, 0, ',', '.') }}
                    </span>
                    <span class="text-xs font-bold text-slate-600">
                        / {{ number_format($totalGuru, 0, ',', '.') }} Guru
                    </span>
                </div>
            </div>
            <p class="text-xs text-slate-600 font-medium mt-3">
                Dewan guru tercatat hadir
            </p>
        </div>
    </div>

    <!-- 1-Kolom Stacked Tables: Pemantauan Presensi Guru -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="font-extrabold text-slate-900 heading-font text-base">Presensi Dewan Guru</h3>
                    <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">10 Terbaru</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Kehadiran dan kepulangan harian guru di lingkungan madrasah hari ini</p>
            </div>
            <a href="{{ route('admin.presensi-guru.index') }}" class="text-xs font-bold text-white inline-flex items-center gap-1.5 shrink-0 transition-all duration-150 active:scale-95 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 px-3.5 py-2 rounded-2xl cursor-pointer shadow-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                <span>Kelola Presensi Guru</span>
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-white/90"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-700 uppercase font-bold text-[11px] tracking-wider">
                        <th class="py-3.5 px-5">Guru / NIP</th>
                        <th class="py-3.5 px-5 text-center">Jam Masuk</th>
                        <th class="py-3.5 px-5 text-center">Jam Pulang</th>
                        <th class="py-3.5 px-5 text-center">Status Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentGuruAttendances as $gAtt)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-5">
                                <p class="font-bold text-slate-900 text-sm leading-snug">{{ $gAtt->user->name ?? 'Guru' }}</p>
                                <p class="text-[11px] text-slate-500 mono-font mt-0.5">NIP: {{ $gAtt->user->identity_number ?? '-' }}</p>
                            </td>
                            <td class="py-4 px-5 text-center mono-font text-slate-700">
                                @if($gAtt->check_in_time)
                                    <span class="font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-xl border border-emerald-200/70">{{ \Carbon\Carbon::parse($gAtt->check_in_time)->format('H:i') }} WIB</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-center mono-font text-slate-700">
                                @if($gAtt->check_out_time)
                                    <span class="font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-xl border border-emerald-200/70">{{ \Carbon\Carbon::parse($gAtt->check_out_time)->format('H:i') }} WIB</span>
                                @else
                                    <span class="text-slate-400 font-medium italic">Belum</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-center whitespace-nowrap">
                                @php
                                    $status = $gAtt->check_in_status ?? 'HADIR';
                                @endphp
                                @if($status === 'HADIR')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                        Tepat Waktu
                                    </span>
                                @elseif($status === 'TERLAMBAT')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-200/80">
                                        Terlambat
                                    </span>
                                @elseif($status === 'IZIN')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-300">
                                        Izin
                                    </span>
                                @elseif($status === 'SAKIT')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-sky-50 text-sky-800 border border-sky-300">
                                        Sakit
                                    </span>
                                @elseif($status === 'ALPA')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-rose-50 text-rose-800 border border-rose-200/80">
                                        Alpa
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $status }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-slate-400">
                                <i data-lucide="user-x" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <p class="text-xs font-medium">Belum ada presensi dewan guru yang tercatat hari ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 1-Kolom Stacked Tables: Pemantauan Presensi Siswa -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="font-extrabold text-slate-900 heading-font text-base">Presensi Siswa</h3>
                    <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">10 Terbaru</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Kehadiran siswa dalam sesi kelas mapel yang telah terverifikasi hari ini</p>
            </div>
            <a href="{{ route('admin.presensi-siswa.index') }}" class="text-xs font-bold text-white inline-flex items-center gap-1.5 shrink-0 transition-all duration-150 active:scale-95 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 px-3.5 py-2 rounded-2xl cursor-pointer shadow-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                <span>Kelola Presensi Siswa</span>
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-white/90"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-700 uppercase font-bold text-[11px] tracking-wider">
                        <th class="py-3.5 px-5">Siswa / NISN</th>
                        <th class="py-3.5 px-5">Kelas & Mapel</th>
                        <th class="py-3.5 px-5 text-center">Waktu Presensi</th>
                        <th class="py-3.5 px-5 text-center">Status Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentSiswaAttendances as $sAtt)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-5">
                                <p class="font-bold text-slate-900 text-sm leading-snug">{{ $sAtt->student->name ?? 'Siswa' }}</p>
                                <p class="text-[11px] text-slate-500 mono-font mt-0.5">NISN: {{ $sAtt->student->identity_number ?? '-' }}</p>
                            </td>
                            <td class="py-4 px-5">
                                <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-800 text-xs font-extrabold mr-1.5 border border-emerald-200/80">
                                    Kelas {{ $sAtt->student->classroom->name ?? ($sAtt->schedule->classroom->name ?? '-') }}
                                </span>
                                <span class="text-slate-800 font-bold">{{ $sAtt->schedule->subject->name ?? '-' }}</span>
                            </td>
                            <td class="py-4 px-5 text-center mono-font text-slate-600 font-bold">
                                {{ ($sAtt->verified_at ?? $sAtt->created_at)?->format('H:i') ? ($sAtt->verified_at ?? $sAtt->created_at)->format('H:i') . ' WIB' : '-' }}
                            </td>
                            <td class="py-4 px-5 text-center whitespace-nowrap">
                                @if($sAtt->status === 'HADIR')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                        Hadir
                                    </span>
                                @elseif($sAtt->status === 'IZIN')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-300">
                                        Izin
                                    </span>
                                @elseif($sAtt->status === 'SAKIT')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-sky-50 text-sky-800 border border-sky-300">
                                        Sakit
                                    </span>
                                @elseif($sAtt->status === 'ALPA')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-rose-50 text-rose-800 border border-rose-200/80">
                                        Alpa
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $sAtt->status }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-slate-400">
                                <i data-lucide="clipboard-x" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <p class="text-xs font-medium">Belum ada catatan presensi siswa hari ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 1-Kolom Stacked Tables: Live Classroom Sessions Monitoring Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="font-extrabold text-slate-900 heading-font text-base">Pemantauan Sesi Kelas Berlangsung</h3>
            <p class="text-xs text-slate-500 mt-0.5">Pantauan pembukaan sesi kelas dan token presensi aktif oleh Bapak/Ibu Guru hari ini</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-700 uppercase font-bold text-[11px] tracking-wider">
                        <th class="py-3.5 px-5">Kelas & Mapel</th>
                        <th class="py-3.5 px-5">Guru Pengampu</th>
                        <th class="py-3.5 px-5 text-center">Kode PIN</th>
                        <th class="py-3.5 px-5">Waktu Dibuka</th>
                        <th class="py-3.5 px-5 text-center">Status Sesi</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($activeSessions as $ses)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-5 font-bold text-slate-900 text-sm">
                                <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-800 text-xs mr-1.5 font-extrabold border border-emerald-200/80">
                                    Kelas {{ $ses->schedule->classroom->name }}
                                </span>
                                {{ $ses->schedule->subject->name }}
                            </td>
                            <td class="py-4 px-5 text-slate-700 font-semibold">{{ $ses->teacher->name }}</td>
                            <td class="py-4 px-5 text-center font-black mono-font text-emerald-700 text-base tracking-widest">{{ $ses->pin_code }}</td>
                            <td class="py-4 px-5 text-slate-600 mono-font font-semibold">{{ $ses->started_at->format('H:i:s') }} WIB</td>
                            <td class="py-4 px-5 text-center whitespace-nowrap">
                                @if($ses->status === 'ACTIVE' && !$ses->isExpired())
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-200 animate-pulse">
                                        Aktif ({{ $ses->remaining_seconds }}s)
                                    </span>
                                @elseif($ses->status === 'LOCKED')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                        <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>Selesai & Tersimpan</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-rose-50 text-rose-800 border border-rose-200/80">
                                        Waktu Habis
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <a href="{{ route('guru.session.reconcile', $ses) }}" target="_blank" title="Buka Rekap Kehadiran Kelas" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white rounded-xl text-xs font-extrabold transition-all duration-150 active:scale-95 cursor-pointer shadow-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                    <span>Buka Rekap</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <p class="text-xs font-medium">Belum ada sesi presensi kelas yang dibuka hari ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
