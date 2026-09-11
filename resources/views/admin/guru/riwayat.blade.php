@extends('layouts.admin')

@section('title', 'Riwayat Presensi Guru: ' . $teacher->name . ' — Admin')
@section('page-title', 'Riwayat Presensi Dewan Guru')

@section('content')
<div class="space-y-6">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 heading-font tracking-tight">Riwayat Presensi Guru</h1>
            <p class="text-xs text-slate-500 mt-0.5">Catatan presensi harian, jam kedatangan-pulang, dan jarak GPS guru.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.guru.index', ['search' => $teacher->identity_number]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 active:bg-slate-100 text-slate-700 font-semibold rounded-xl text-xs border border-slate-200/80 transition-all duration-150 active:scale-95 shadow-2xs">
                <i data-lucide="pencil" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Edit Data Guru</span>
            </a>
            <a href="{{ route('admin.guru.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white font-semibold rounded-xl text-xs transition-all duration-150 active:scale-95 shadow-sm">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    {{-- ── TEACHER PROFILE HERO CARD ─────────────────────────────────── --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            @if($teacher->profile_photo_url)
                <img src="{{ $teacher->profile_photo_url }}" loading="lazy" decoding="async" alt="{{ $teacher->name }}"
                     class="w-14 h-14 rounded-xl object-cover shrink-0 border border-slate-200">
            @else
                <div class="w-14 h-14 rounded-xl bg-maarif-700 text-white font-bold text-xl flex items-center justify-center shrink-0">
                    {{ mb_substr($teacher->name, 0, 1) }}
                </div>
            @endif
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-lg font-bold text-slate-900 heading-font leading-tight">{{ $teacher->name }}</h2>
                    @if($teacher->is_active)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/70">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
                            Nonaktif
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-500 font-medium">
                    <span>NIP/NUPTK: <strong class="font-mono text-slate-800">{{ $teacher->identity_number }}</strong></span>
                    <span class="text-slate-300">•</span>
                    <span>No. HP: <span class="text-slate-700">{{ $teacher->phone_number ?? '-' }}</span></span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start md:self-auto">
            <span class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 font-semibold text-xs">
                Email: <span class="mono-font text-slate-900 font-semibold">{{ $teacher->email ?? '-' }}</span>
            </span>
        </div>
    </div>

    {{-- ── ATTENDANCE STATS CARDS (HIERARCHICAL) ──────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card Utama: Persentase Kehadiran --}}
        <div class="bg-gradient-to-br from-maarif-800 to-maarif-900 rounded-2xl p-5 text-white flex flex-col justify-between">
            <div>
                <p class="text-[11px] font-semibold text-emerald-300 uppercase tracking-widest">Tingkat Kehadiran</p>
                <div class="mt-2.5 flex items-baseline gap-2">
                    <span class="text-4xl font-bold mono-font tracking-tight leading-none">{{ $persenHadir }}%</span>
                    <span class="text-xs font-semibold text-emerald-200/80">({{ $totalHadir }} Hari)</span>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-white/15 rounded-full h-1.5">
                    <div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ min($persenHadir, 100) }}%"></div>
                </div>
                <p class="text-[11px] text-emerald-200/80 font-medium mt-1.5">Total {{ $totalPresensi }} hari presensi tercatat</p>
            </div>
        </div>

        {{-- Tepat Waktu --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Tepat Waktu</p>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-200/70 text-emerald-700 flex items-center justify-center">
                    <i data-lucide="clock-check" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-bold text-emerald-700 mono-font">{{ $totalHadir }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Presensi &le; 07:15 WIB</p>
            </div>
        </div>

        {{-- Terlambat --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Terlambat</p>
                <span class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-200/70 text-amber-600 flex items-center justify-center">
                    <i data-lucide="clock-alert" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-bold text-amber-600 mono-font">{{ $totalTerlambat }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Presensi &gt; 07:15 WIB</p>
            </div>
        </div>

        {{-- Izin / Sakit --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Dispensasi / Izin</p>
                <span class="w-8 h-8 rounded-xl bg-sky-50 border border-sky-200/70 text-sky-600 flex items-center justify-center">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-bold text-sky-600 mono-font">{{ $totalIzinSakit }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Izin dinas / sakit</p>
            </div>
        </div>
    </div>

    {{-- ── DATE RANGE FILTER BAR ────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4">
        <form method="GET" action="{{ route('admin.guru.riwayat', $teacher) }}" data-loading-form class="flex flex-wrap items-center gap-3 text-xs">
            <div>
                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white mono-font font-medium transition">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white mono-font font-medium transition">
            </div>

            <div class="self-end pt-1 flex items-center gap-2">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>

                @if(request()->hasAny(['start_date', 'end_date']))
                    <a href="{{ route('admin.guru.riwayat', $teacher) }}"
                       class="text-xs text-slate-500 hover:text-slate-800 font-semibold transition-colors duration-150 cursor-pointer">
                       Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── DETAILED DAILY TABLE ─────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="font-bold text-slate-900 heading-font text-sm">Rekam Jejak Presensi Harian Guru</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Catatan presensi masuk dan kepulangan di lingkungan madrasah.</p>
            </div>
            <span class="text-xs font-semibold text-slate-700 bg-slate-100 px-3 py-1 rounded-md border border-slate-200 mono-font self-start sm:self-auto">
                Rentang: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-semibold text-[10px] tracking-widest">
                        <th class="py-3 px-5">Hari &amp; Tanggal</th>
                        <th class="py-3 px-5 text-center">Status Masuk</th>
                        <th class="py-3 px-5 text-center">Jam Masuk</th>
                        <th class="py-3 px-5 text-center">Jam Pulang</th>
                        <th class="py-3 px-5 text-right">Jarak Validasi GPS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-slate-50/60 transition-colors duration-100">
                            {{-- Tanggal --}}
                            <td class="py-3.5 px-5">
                                <p class="font-semibold text-slate-900">{{ $att->attendance_date->translatedFormat('l, d F Y') }}</p>
                            </td>

                            {{-- Status Masuk --}}
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                @if($att->check_in_status === 'HADIR')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/70">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                        Tepat Waktu
                                    </span>
                                @elseif($att->check_in_status === 'TERLAMBAT')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                        Terlambat
                                    </span>
                                @elseif($att->check_in_status === 'IZIN')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        Izin
                                    </span>
                                @elseif($att->check_in_status === 'SAKIT')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-sky-50 text-sky-800 border border-sky-200">
                                        Sakit
                                    </span>
                                @elseif($att->check_in_status === 'ALPA')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-800 border border-rose-200/70">
                                        Alpa
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ $att->check_in_status ?: '-' }}
                                    </span>
                                @endif
                            </td>

                            {{-- Jam Masuk --}}
                            <td class="py-3.5 px-5 text-center font-mono">
                                @if($att->check_in_time)
                                    <span class="inline-block font-semibold text-slate-900 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200 text-xs">
                                        {{ substr($att->check_in_time, 0, 5) }} WIB
                                    </span>
                                @else
                                    <span class="text-slate-300 font-sans">—</span>
                                @endif
                            </td>

                            {{-- Jam Pulang --}}
                            <td class="py-3.5 px-5 text-center font-mono">
                                @if($att->check_out_time)
                                    <span class="inline-block font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/70 text-xs">
                                        {{ substr($att->check_out_time, 0, 5) }} WIB
                                    </span>
                                @else
                                    <span class="text-slate-400 font-sans italic text-xs">Belum check-out</span>
                                @endif
                            </td>

                            {{-- Jarak GPS --}}
                            <td class="py-3.5 px-5 text-right font-mono whitespace-nowrap">
                                @if($att->check_in_distance_meters !== null)
                                    <span class="font-semibold text-slate-800 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-200/70 text-xs">
                                        {{ round($att->check_in_distance_meters, 1) }} m
                                    </span>
                                @else
                                    <span class="text-slate-400 font-sans text-xs">Layar Madrasah</span>
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
                                    <p class="text-sm font-semibold text-slate-600">Belum ada catatan presensi pada rentang tanggal ini</p>
                                    <p class="text-xs text-slate-400">Coba sesuaikan tanggal awal dan akhir filter di atas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
