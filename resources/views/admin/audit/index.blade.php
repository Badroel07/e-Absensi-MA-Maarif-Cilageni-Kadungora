@extends('layouts.admin')

@section('title', 'Riwayat Perubahan Data — Admin')
@section('page-title', 'Riwayat Perubahan Data Kehadiran Siswa')

@section('content')
<div class="space-y-6">
    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Riwayat Perubahan Data</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Catatan audit log perubahan status presensi dan penyesuaian oleh operator</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-800 flex items-center justify-center border border-indigo-200/80 shrink-0">
                    <i data-lucide="history" class="w-5 h-5 text-indigo-700"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 heading-font text-sm sm:text-base">Catatan Riwayat Perubahan Kehadiran Siswa</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Merekam riwayat perubahan status oleh Bapak/Ibu Guru atau Admin beserta alasan perubahan.</p>
                </div>
            </div>
            <span class="text-xs font-bold px-3 py-1.5 bg-slate-100 text-slate-700 rounded-xl mono-font border border-slate-200 self-start sm:self-auto">
                {{ $auditLogs->total() }} Riwayat Tercatat
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-500 uppercase font-extrabold text-[11px] tracking-wider">
                        <th class="py-3.5 px-5">Waktu</th>
                        <th class="py-3.5 px-5">Siswa</th>
                        <th class="py-3.5 px-5 text-center">Status Awal</th>
                        <th class="py-3.5 px-5 text-center">Status Baru</th>
                        <th class="py-3.5 px-5">Diubah Oleh</th>
                        <th class="py-3.5 px-5">Alasan Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($auditLogs as $log)
                        @php
                            $oldBadgeClass = match($log->old_status) {
                                'HADIR' => 'bg-emerald-50 text-emerald-800 border-emerald-200/80',
                                'IZIN' => 'bg-amber-50 text-amber-800 border-amber-300',
                                'SAKIT' => 'bg-sky-50 text-sky-800 border-sky-300',
                                'ALPA' => 'bg-rose-50 text-rose-800 border-rose-200/80',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                            $newBadgeClass = match($log->new_status) {
                                'HADIR' => 'bg-emerald-50 text-emerald-800 border-emerald-200/80',
                                'IZIN' => 'bg-amber-50 text-amber-800 border-amber-300',
                                'SAKIT' => 'bg-sky-50 text-sky-800 border-sky-300',
                                'ALPA' => 'bg-rose-50 text-rose-800 border-rose-200/80',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-5 font-mono text-slate-500 text-[11px]">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="py-4 px-5 font-bold text-slate-900">{{ $log->lessonAttendance->student->name ?? '-' }}</td>
                            <td class="py-4 px-5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold border {{ $oldBadgeClass }}">
                                    {{ $log->old_status }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold border {{ $newBadgeClass }}">
                                    {{ $log->new_status }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-slate-700 font-medium">{{ $log->changedBy->name ?? '-' }}</td>
                            <td class="py-4 px-5 text-slate-600 italic">{{ $log->reason }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 font-medium">Belum ada catatan riwayat perubahan data kehadiran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-slate-100">
            {{ $auditLogs->links() }}
        </div>
    </div>
</div>
@endsection
