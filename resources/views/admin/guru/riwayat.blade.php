@extends('layouts.admin')

@section('title', 'Riwayat Presensi Guru: ' . $teacher->name . ' — Admin')
@section('page-title', 'Riwayat Presensi Bapak/Ibu Guru')

@section('content')
<div class="space-y-6">
    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Riwayat Presensi Guru</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Catatan kehadiran harian dan riwayat jam masuk-pulang untuk {{ $teacher->name }}</p>
        </div>
    </div>

    <!-- Header / Teacher Profile Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            @if($teacher->profile_photo_url)
                <img src="{{ $teacher->profile_photo_url }}" alt="{{ $teacher->name }}" class="w-14 h-14 rounded-2xl object-cover shadow-sm shrink-0 border border-slate-200">
            @else
                <div class="w-14 h-14 rounded-2xl bg-emerald-800 text-white font-black text-xl flex items-center justify-center shadow-sm shrink-0">
                    {{ substr($teacher->name, 0, 1) }}
                </div>
            @endif
            <div>
                <div class="flex items-center space-x-2.5">
                    <h2 class="text-lg font-extrabold text-slate-900 heading-font">{{ $teacher->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold {{ $teacher->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/80' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                        {{ $teacher->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 font-mono mt-1 font-medium">NIP: {{ $teacher->identity_number }} &bull; Email: {{ $teacher->email ?? '-' }} &bull; No. HP: {{ $teacher->phone_number ?? '-' }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.guru.index', ['search' => $teacher->identity_number]) }}" class="px-4 py-2.5 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-extrabold rounded-2xl text-xs transition-all duration-150 active:scale-95 inline-flex items-center gap-2 shadow-xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
                <i data-lucide="pencil" class="w-4 h-4 text-white/90"></i>
                <span>Edit Data & Foto</span>
            </a>
            <a href="{{ route('admin.guru.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white font-extrabold rounded-2xl text-xs transition-all duration-150 active:scale-95 inline-flex items-center gap-2 shadow-xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700">
                <i data-lucide="arrow-left" class="w-4 h-4 text-white/90"></i>
                <span>Kembali ke Data Guru</span>
            </a>
        </div>
    </div>

    <!-- Attendance Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">% Kehadiran</p>
            <h3 class="text-2xl sm:text-3xl font-black text-emerald-700 mono-font mt-1.5">{{ $persenHadir }}%</h3>
            <p class="text-[11px] font-medium text-slate-400 mt-1">Hari Efektif</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-extrabold text-emerald-600 uppercase tracking-wider">Tepat Waktu</p>
            <h3 class="text-2xl sm:text-3xl font-black text-emerald-700 mono-font mt-1.5">{{ $totalHadir }}</h3>
            <p class="text-[11px] font-medium text-slate-400 mt-1">&le; 07:15 WIB</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-extrabold text-amber-600 uppercase tracking-wider">Terlambat</p>
            <h3 class="text-2xl sm:text-3xl font-black text-amber-600 mono-font mt-1.5">{{ $totalTerlambat }}</h3>
            <p class="text-[11px] font-medium text-slate-400 mt-1">&gt; 07:15 WIB</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-extrabold text-blue-600 uppercase tracking-wider">Izin / Sakit</p>
            <h3 class="text-2xl sm:text-3xl font-black text-blue-600 mono-font mt-1.5">{{ $totalIzinSakit }}</h3>
            <p class="text-[11px] font-medium text-slate-400 mt-1">Dispensasi TU</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs col-span-2 md:col-span-1">
            <p class="text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Total Presensi</p>
            <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mono-font mt-1.5">{{ $totalPresensi }}</h3>
            <p class="text-[11px] font-medium text-slate-400 mt-1">Hari Tercatat</p>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.guru.riwayat', $teacher) }}" class="flex flex-wrap items-end gap-3.5">
            <div>
                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="px-4 py-2 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium">
            </div>

            <div>
                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="px-4 py-2 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white rounded-2xl text-xs font-extrabold transition-all duration-150 active:scale-95 shadow-md shadow-slate-800/20 cursor-pointer">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>Filter Riwayat</span>
                </button>
                @if(request()->hasAny(['start_date', 'end_date']))
                    <a href="{{ route('admin.guru.riwayat', $teacher) }}" class="px-3.5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 active:text-slate-900 rounded-2xl hover:bg-slate-100 transition-all duration-150 cursor-pointer">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Detailed Daily Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="font-extrabold text-slate-900 heading-font text-sm sm:text-base">Rekam Jejak Presensi Harian di Madrasah</h3>
                <p class="text-xs text-slate-500 mt-0.5">Rentang: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-500 uppercase font-extrabold text-[11px] tracking-wider">
                        <th class="py-3.5 px-5">Tanggal & Hari</th>
                        <th class="py-3.5 px-5 text-center">Status Masuk</th>
                        <th class="py-3.5 px-5">Jam Masuk</th>
                        <th class="py-3.5 px-5">Jam Pulang</th>
                        <th class="py-3.5 px-5">Jarak Pindai (Meter)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-5">
                                <p class="font-bold text-slate-900">{{ $att->attendance_date->translatedFormat('l, d M Y') }}</p>
                            </td>

                            <td class="py-4 px-5 text-center">
                                @if($att->check_in_status === 'HADIR')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                        TEPAT WAKTU
                                    </span>
                                @elseif($att->check_in_status === 'TERLAMBAT')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-200/80">
                                        TERLAMBAT
                                    </span>
                                @elseif($att->check_in_status === 'IZIN')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-300">
                                        IZIN
                                    </span>
                                @elseif($att->check_in_status === 'SAKIT')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-sky-50 text-sky-800 border border-sky-300">
                                        SAKIT
                                    </span>
                                @elseif($att->check_in_status === 'ALPA')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-rose-50 text-rose-800 border border-rose-200/80">
                                        ALPA
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $att->check_in_status ?: '-' }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-4 px-5 font-mono">
                                @if($att->check_in_time)
                                    <span class="font-bold text-slate-900">{{ substr($att->check_in_time, 0, 5) }} WIB</span>
                                @else
                                    <span class="text-slate-400 font-sans">-</span>
                                @endif
                            </td>

                            <td class="py-4 px-5 font-mono">
                                @if($att->check_out_time)
                                    <span class="font-bold text-emerald-700">{{ substr($att->check_out_time, 0, 5) }} WIB</span>
                                @else
                                    <span class="text-slate-400 italic font-sans text-xs">Belum presensi pulang</span>
                                @endif
                            </td>

                            <td class="py-4 px-5 text-slate-600 font-mono">
                                @if($att->check_in_distance_meters !== null)
                                    <span class="font-bold text-slate-800">{{ round($att->check_in_distance_meters, 1) }} m</span>
                                @else
                                    <span class="text-slate-400 font-sans text-xs">Layar Presensi Madrasah</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 font-medium">Belum ada catatan presensi pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
