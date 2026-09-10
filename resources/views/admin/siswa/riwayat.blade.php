@extends('layouts.admin')

@section('title', 'Riwayat Presensi Siswa: ' . $student->name . ' — Admin')
@section('page-title', 'Riwayat Presensi Siswa')

@section('content')
<div class="space-y-6">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-black text-slate-900 heading-font tracking-tight">Riwayat Presensi Siswa</h1>
            <p class="text-xs text-slate-500 mt-0.5">Catatan lengkap rekam kehadiran dan log presensi per sesi kelas.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.siswa.index', ['search' => $student->identity_number]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 active:bg-slate-100 text-slate-700 font-bold rounded-xl text-xs border border-slate-200/80 transition-all duration-150 active:scale-95 shadow-2xs">
                <i data-lucide="pencil" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Edit Data Siswa</span>
            </a>
            <a href="{{ route('admin.siswa.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white font-bold rounded-xl text-xs transition-all duration-150 active:scale-95 shadow-sm">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    {{-- ── STUDENT PROFILE HERO CARD ─────────────────────────────────── --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            @if($student->profile_photo_url)
                <img src="{{ $student->profile_photo_url }}" alt="{{ $student->name }}"
                     class="w-14 h-14 rounded-xl object-cover shrink-0 border border-slate-200">
            @else
                <div class="w-14 h-14 rounded-xl bg-maarif-700 text-white font-black text-xl flex items-center justify-center shrink-0">
                    {{ mb_substr($student->name, 0, 1) }}
                </div>
            @endif
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-lg font-black text-slate-900 heading-font leading-tight">{{ $student->name }}</h2>
                    @if($student->is_active)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/70">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-extrabold bg-slate-100 text-slate-600 border border-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
                            Nonaktif
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-500 font-medium">
                    <span>NISN: <strong class="font-mono text-slate-800">{{ $student->identity_number }}</strong></span>
                    <span class="text-slate-300">•</span>
                    <span>Kelas: <strong class="text-maarif-800 font-bold">Kelas {{ $student->classroom->name ?? '-' }}</strong></span>
                    <span class="text-slate-300">•</span>
                    <span>No. HP: <span class="text-slate-700">{{ $student->phone_number ?? '-' }}</span></span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start md:self-auto">
            <span class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 font-semibold text-xs">
                Email: <span class="mono-font text-slate-900 font-bold">{{ $student->email ?? '-' }}</span>
            </span>
        </div>
    </div>

    {{-- ── ATTENDANCE STATS CARDS (HIERARCHICAL) ──────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card Utama: Persentase Kehadiran --}}
        <div class="bg-gradient-to-br from-maarif-800 to-maarif-900 rounded-2xl p-5 text-white flex flex-col justify-between">
            <div>
                <p class="text-[11px] font-extrabold text-emerald-300 uppercase tracking-widest">Tingkat Kehadiran</p>
                <div class="mt-2.5 flex items-baseline gap-2">
                    <span class="text-4xl font-black mono-font tracking-tight leading-none">{{ $persenHadir }}%</span>
                    <span class="text-xs font-bold text-emerald-200/80">({{ $totalHadir }} Hadir)</span>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-white/15 rounded-full h-1.5">
                    <div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ min($persenHadir, 100) }}%"></div>
                </div>
                <p class="text-[11px] text-emerald-200/80 font-medium mt-1.5">Total {{ $total }} sesi pelajaran tercatat</p>
            </div>
        </div>

        {{-- Izin --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Izin</p>
                <span class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-200/70 text-amber-600 flex items-center justify-center">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-black text-amber-600 mono-font">{{ $totalIzin }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Sesi dengan dispensasi</p>
            </div>
        </div>

        {{-- Sakit --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Sakit</p>
                <span class="w-8 h-8 rounded-xl bg-sky-50 border border-sky-200/70 text-sky-600 flex items-center justify-center">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-black text-sky-600 mono-font">{{ $totalSakit }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Sesi istirahat sakit</p>
            </div>
        </div>

        {{-- Alpa --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Tanpa Keterangan</p>
                <span class="w-8 h-8 rounded-xl bg-rose-50 border border-rose-200/70 text-rose-600 flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-black text-rose-600 mono-font">{{ $totalAlpa }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Sesi tidak hadir</p>
            </div>
        </div>
    </div>

    {{-- ── DATE RANGE FILTER BAR ────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4">
        <form method="GET" action="{{ route('admin.siswa.riwayat', $student) }}" class="flex flex-wrap items-center gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1 text-[11px]">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white mono-font font-bold transition">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1 text-[11px]">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white mono-font font-bold transition">
            </div>

            <div class="self-end pt-1 flex items-center gap-2">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>

                @if(request()->hasAny(['start_date', 'end_date']))
                    <a href="{{ route('admin.siswa.riwayat', $student) }}"
                       class="text-xs text-slate-500 hover:text-slate-800 font-semibold transition-colors duration-150 cursor-pointer">
                       Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── DETAILED TIMELINE TABLE ──────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="font-extrabold text-slate-900 heading-font text-sm">Rekam Jejak Presensi Mata Pelajaran</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Catatan kehadiran per sesi jam pelajaran dalam rentang waktu terpilih.</p>
            </div>
            <span class="text-xs font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded-md border border-slate-200 mono-font self-start sm:self-auto">
                {{ $total }} Sesi Tercatat
            </span>
        </div>

        {{-- 1. Desktop View Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-bold text-[10px] tracking-widest">
                        <th class="py-3 px-5">Tanggal &amp; Waktu</th>
                        <th class="py-3 px-5">Mata Pelajaran</th>
                        <th class="py-3 px-5">Guru Pengampu</th>
                        <th class="py-3 px-5 text-center">Status Kehadiran</th>
                        <th class="py-3 px-5">Keterangan / Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-slate-50/60 transition-colors duration-100">
                            {{-- Tanggal & Waktu --}}
                            <td class="py-3.5 px-5 whitespace-nowrap">
                                <p class="font-bold text-slate-900">{{ $att->attendance_date->format('d M Y') }}</p>
                                <p class="font-mono text-slate-400 text-[11px] mt-0.5">
                                    {{ $att->verified_at ? $att->verified_at->format('H:i:s').' WIB' : 'Oleh Guru' }}
                                </p>
                            </td>

                            {{-- Mapel --}}
                            <td class="py-3.5 px-5">
                                <span class="font-bold text-slate-900 text-sm block">{{ $att->schedule->subject->name ?? 'Mata Pelajaran' }}</span>
                                @if($att->schedule)
                                    <span class="text-[11px] text-slate-400 font-mono">{{ substr($att->schedule->start_time, 0, 5) }} – {{ substr($att->schedule->end_time, 0, 5) }} WIB</span>
                                @endif
                            </td>

                            {{-- Guru Pengampu --}}
                            <td class="py-3.5 px-5 text-slate-700 font-medium">
                                {{ $att->schedule->teacher->name ?? '-' }}
                            </td>

                            {{-- Status --}}
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                @if($att->status === 'HADIR')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/70">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                        Hadir
                                    </span>
                                @elseif($att->status === 'IZIN')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i data-lucide="file-text" class="w-3 h-3 text-amber-600"></i>
                                        Izin
                                    </span>
                                @elseif($att->status === 'SAKIT')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-extrabold bg-sky-50 text-sky-800 border border-sky-200">
                                        <i data-lucide="activity" class="w-3 h-3 text-sky-600"></i>
                                        Sakit
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-extrabold bg-rose-50 text-rose-800 border border-rose-200/70">
                                        <i data-lucide="alert-circle" class="w-3 h-3 text-rose-600"></i>
                                        Alpa
                                    </span>
                                @endif
                            </td>

                            {{-- Keterangan --}}
                            <td class="py-3.5 px-5 text-slate-600">
                                @if($att->notes)
                                    <span class="italic bg-slate-50 px-2 py-0.5 rounded-md border border-slate-200/60 text-xs inline-block">
                                        {{ $att->notes }}
                                    </span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center">
                                <div class="inline-flex flex-col items-center gap-2">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i data-lucide="calendar-x" class="w-6 h-6 text-slate-400"></i>
                                    </span>
                                    <p class="text-sm font-bold text-slate-600">Belum ada riwayat presensi pada rentang tanggal ini</p>
                                    <p class="text-xs text-slate-400">Coba sesuaikan tanggal awal dan akhir filter di atas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 2. Mobile View Cards --}}
        <div class="md:hidden p-4 space-y-3">
            @forelse($attendances as $att)
                @php
                    $borderClass = match($att->status) {
                        'HADIR' => 'border-l-4 border-l-emerald-500',
                        'IZIN' => 'border-l-4 border-l-amber-500',
                        'SAKIT' => 'border-l-4 border-l-sky-500',
                        default => 'border-l-4 border-l-rose-500',
                    };
                @endphp
                <div class="p-4 rounded-2xl border border-slate-200/80 bg-white {{ $borderClass }} shadow-xs space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-extrabold text-slate-900 text-xs">
                                {{ $att->attendance_date->translatedFormat('l, d M Y') }}
                            </span>
                            @if($att->schedule)
                                <span class="block text-[11px] text-slate-400 mono-font mt-0.5">
                                    {{ substr($att->schedule->start_time, 0, 5) }} – {{ substr($att->schedule->end_time, 0, 5) }} WIB
                                </span>
                            @endif
                        </div>

                        @if($att->status === 'HADIR')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/80 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Hadir
                            </span>
                        @elseif($att->status === 'IZIN')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200/80 shrink-0">
                                <i data-lucide="file-text" class="w-3 h-3 text-amber-600"></i>
                                Izin
                            </span>
                        @elseif($att->status === 'SAKIT')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-sky-50 text-sky-800 border border-sky-200/80 shrink-0">
                                <i data-lucide="activity" class="w-3 h-3 text-sky-600"></i>
                                Sakit
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-rose-50 text-rose-800 border border-rose-200/80 shrink-0">
                                <i data-lucide="alert-circle" class="w-3 h-3 text-rose-600"></i>
                                Alpa
                            </span>
                        @endif
                    </div>

                    <div class="pt-1">
                        <h4 class="font-bold text-slate-900 text-sm heading-font">
                            {{ $att->schedule->subject->name ?? 'Mata Pelajaran' }}
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>{{ $att->schedule->teacher->name ?? '-' }}</span>
                        </p>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex flex-col gap-1 text-[11px]">
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Waktu Presensi:</span>
                            <span class="font-bold text-slate-800 mono-font">
                                {{ $att->verified_at ? $att->verified_at->format('H:i:s').' WIB' : 'Oleh Guru' }}
                            </span>
                        </div>
                        @if($att->notes)
                            <div class="mt-1 p-2 rounded-xl bg-slate-50 border border-slate-200/70 text-slate-600 text-xs italic">
                                Catatan: {{ $att->notes }}
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-12 text-center space-y-2">
                    <span class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                        <i data-lucide="calendar-x" class="w-6 h-6"></i>
                    </span>
                    <p class="text-sm font-bold text-slate-600">Belum ada riwayat presensi pada rentang tanggal ini</p>
                    <p class="text-xs text-slate-400">Coba sesuaikan tanggal awal dan akhir filter di atas.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
