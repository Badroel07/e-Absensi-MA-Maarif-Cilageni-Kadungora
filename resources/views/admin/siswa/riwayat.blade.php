@extends('layouts.admin')

@section('title', 'Riwayat Presensi Siswa: ' . $student->name . ' — Admin')
@section('page-title', 'Riwayat Presensi Siswa')

@section('content')
<div class="space-y-6">
    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Riwayat Presensi Siswa</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Catatan lengkap rekam kehadiran dan log presensi individual siswa</p>
        </div>
    </div>

    <!-- Header / Student Profile Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            @if($student->profile_photo_url)
                <img src="{{ $student->profile_photo_url }}" alt="{{ $student->name }}" class="w-14 h-14 rounded-2xl object-cover shadow-2xs shrink-0 border border-slate-200">
            @else
                <div class="w-14 h-14 rounded-2xl bg-emerald-700 text-white font-black text-xl flex items-center justify-center shadow-2xs shrink-0 border border-emerald-600">
                    {{ substr($student->name, 0, 1) }}
                </div>
            @endif
            <div>
                <div class="flex items-center space-x-2.5">
                    <h2 class="text-base sm:text-lg font-black text-slate-900 heading-font">{{ $student->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold {{ $student->is_active ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                        {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 font-medium mt-0.5">NISN: <span class="mono-font font-bold text-slate-800">{{ $student->identity_number }}</span> &bull; Kelas: <span class="font-bold text-emerald-800">{{ $student->classroom->name ?? '-' }}</span> &bull; No. HP: {{ $student->phone_number ?? '-' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.siswa.index', ['search' => $student->identity_number]) }}" class="px-4 py-2.5 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-bold rounded-2xl text-xs transition-all duration-150 active:scale-95 inline-flex items-center gap-1.5 shadow-xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
                <i data-lucide="pencil" class="w-3.5 h-3.5 text-white/90"></i>
                <span>Edit Data & Foto</span>
            </a>
            <a href="{{ route('admin.siswa.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white font-bold rounded-2xl text-xs transition-all duration-150 active:scale-95 inline-flex items-center gap-1.5 shadow-xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-white/90"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Attendance Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">% Kehadiran</p>
            <h3 class="text-2xl sm:text-3xl font-black text-emerald-700 mono-font mt-1.5">{{ $persenHadir }}%</h3>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Partisipasi Kelas</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Total Hadir</p>
            <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 mono-font mt-1.5">{{ $totalHadir }}</h3>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Jam Pelajaran</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Izin</p>
            <h3 class="text-2xl sm:text-3xl font-black text-amber-600 mono-font mt-1.5">{{ $totalIzin }}</h3>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Surat Keterangan</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-bold text-sky-600 uppercase tracking-wider">Sakit</p>
            <h3 class="text-2xl sm:text-3xl font-black text-sky-600 mono-font mt-1.5">{{ $totalSakit }}</h3>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Keterangan Sakit</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs col-span-2 md:col-span-1">
            <p class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Alpa</p>
            <h3 class="text-2xl sm:text-3xl font-black text-rose-600 mono-font mt-1.5">{{ $totalAlpa }}</h3>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Tanpa Keterangan</p>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.siswa.riwayat', $student) }}" class="flex flex-wrap items-center gap-4">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="px-4 py-2.5 text-xs border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-mono transition">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="px-4 py-2.5 text-xs border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-mono transition">
            </div>

            <div class="pt-6 flex items-center space-x-2.5">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white rounded-2xl text-xs font-extrabold transition-all duration-150 active:scale-95 shadow-md shadow-slate-800/20 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Filter Riwayat</span>
                </button>
                @if(request()->hasAny(['start_date', 'end_date']))
                    <a href="{{ route('admin.siswa.riwayat', $student) }}" class="px-3.5 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 active:text-slate-900 rounded-2xl hover:bg-slate-100 transition-all duration-150 cursor-pointer">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Detailed Timeline Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="font-extrabold text-slate-900 heading-font text-base">Rekam Jejak Presensi Mata Pelajaran</h3>
                <p class="text-xs text-slate-500 mt-0.5">Catatan presensi per sesi kelas dalam kurun waktu yang dipilih</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">
                Rentang: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} ({{ $total }} Data)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-700 uppercase font-bold text-[11px] tracking-wider">
                        <th class="py-3.5 px-5">Tanggal & Waktu</th>
                        <th class="py-3.5 px-5">Mata Pelajaran</th>
                        <th class="py-3.5 px-5">Guru Pengampu</th>
                        <th class="py-3.5 px-5 text-center">Status Kehadiran</th>
                        <th class="py-3.5 px-5">Keterangan / Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-5">
                                <p class="font-bold text-slate-900">{{ $att->attendance_date->format('d M Y') }}</p>
                                <p class="font-mono text-slate-500 text-[11px] mt-0.5">
                                    {{ $att->verified_at ? $att->verified_at->format('H:i:s').' WIB' : '-' }}
                                </p>
                            </td>

                            <td class="py-3.5 px-5 font-bold text-slate-900 text-sm">
                                {{ $att->schedule->subject->name ?? 'Mata Pelajaran' }}
                            </td>

                            <td class="py-3.5 px-5 text-slate-700 font-semibold">
                                {{ $att->schedule->teacher->name ?? '-' }}
                            </td>

                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                @if($att->status === 'HADIR')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        HADIR
                                    </span>
                                @elseif($att->status === 'IZIN')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                        IZIN
                                    </span>
                                @elseif($att->status === 'SAKIT')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-sky-100 text-sky-800 border border-sky-200">
                                        SAKIT
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-rose-100 text-rose-800 border border-rose-200">
                                        ALPA
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-5 text-slate-600">
                                {{ $att->notes ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400">
                                <p class="text-xs font-medium">Belum ada riwayat presensi siswa pada rentang tanggal ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
