@extends('layouts.app')

@section('title', 'Riwayat Presensi — MA Ma\'arif Cilageni')

@section('content')
<div class="space-y-5">
    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Riwayat Presensi</h1>
            <p class="text-xs sm:text-sm text-slate-500">Catatan lengkap riwayat presensi mata pelajaran yang telah terverifikasi</p>
        </div>
    </div>

    <!-- Attendance Table / Cards -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-bold text-slate-900 heading-font">Catatan Presensi Mata Pelajaran</h3>
                <p class="text-xs text-slate-400">Daftar catatan kehadiran kelas</p>
            </div>
            <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs mono-font">
                Total {{ $attendances->total() }} Rekaman
            </span>
        </div>
        
        <!-- 1. DESKTOP VIEW: Clean Responsive Table (Hidden on Mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-600 uppercase tracking-wider font-extrabold text-[11px]">
                        <th class="py-3.5 px-4 rounded-l-xl">Tanggal</th>
                        <th class="py-3.5 px-4">Mata Pelajaran</th>
                        <th class="py-3.5 px-4">Bapak/Ibu Guru</th>
                        <th class="py-3.5 px-4">Waktu Presensi</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 rounded-r-xl">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 text-slate-700 font-bold">
                                {{ $att->attendance_date->translatedFormat('d F Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $att->schedule->subject->name ?? 'Mata Pelajaran' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $att->schedule->teacher->name ?? 'Guru' }}
                            </td>
                            <td class="py-3.5 px-4 mono-font text-slate-600 font-semibold">
                                {{ $att->verified_at ? $att->verified_at->format('H:i:s') . ' WIB' : '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold inline-flex items-center {{ $att->status === 'HADIR' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($att->status === 'IZIN' ? 'bg-amber-100 text-amber-800 border border-amber-200' : ($att->status === 'SAKIT' ? 'bg-sky-100 text-sky-800 border border-sky-200' : 'bg-rose-100 text-rose-800 border border-rose-200')) }}">
                                    @if($att->status === 'HADIR')
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @endif
                                    {{ $att->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 italic text-[11px]">
                                {{ $att->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                Belum ada rekaman riwayat presensi yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 2. MOBILE VIEW: Compact Cards (Hidden on Desktop) -->
        <div class="md:hidden space-y-3">
            @forelse($attendances as $att)
                <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/80 space-y-2 text-xs">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-bold text-slate-900 text-sm heading-font">
                                {{ $att->schedule->subject->name ?? 'Mata Pelajaran' }}
                            </p>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                {{ $att->schedule->teacher->name ?? 'Guru' }}
                            </p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $att->status === 'HADIR' ? 'bg-emerald-100 text-emerald-800' : ($att->status === 'IZIN' ? 'bg-amber-100 text-amber-800' : ($att->status === 'SAKIT' ? 'bg-sky-100 text-sky-800' : 'bg-rose-100 text-rose-800')) }}">
                            {{ $att->status }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 text-[11px] text-slate-500">
                        <span>{{ $att->attendance_date->translatedFormat('d F Y') }}</span>
                        <span class="font-mono">{{ $att->verified_at ? $att->verified_at->format('H:i') . ' WIB' : '-' }}</span>
                    </div>
                    @if($att->notes)
                        <p class="text-[11px] text-slate-600 italic bg-white p-2.5 rounded-xl border border-slate-200/70">
                            Keterangan: {{ $att->notes }}
                        </p>
                    @endif
                </div>
            @empty
                <p class="text-center py-8 text-xs text-slate-400">Belum ada rekaman riwayat presensi.</p>
            @endforelse
        </div>

        <div class="pt-2">
            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection
