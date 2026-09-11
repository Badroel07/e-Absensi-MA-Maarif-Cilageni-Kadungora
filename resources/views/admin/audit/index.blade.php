@extends('layouts.admin')

@section('title', 'Riwayat Perubahan Data — Admin')
@section('page-title', 'Riwayat Perubahan Data Kehadiran Siswa')

@section('content')
<div class="space-y-6">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 heading-font tracking-tight">Riwayat Perubahan Data</h1>
            <p class="text-xs text-slate-500 mt-0.5">Catatan audit log resmi perubahan status presensi dan penyesuaian oleh operator.</p>
        </div>
        <span class="text-xs font-semibold px-3 py-1.5 bg-white text-slate-700 rounded-xl mono-font border border-slate-200/80 shadow-2xs self-start sm:self-auto">
            {{ $auditLogs->total() }} Catatan Riwayat
        </span>
    </div>

    {{-- ── TABLE AUDIT LOG ──────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden"
         x-data="{ ready: false }"
         x-init="$nextTick(() => { setTimeout(() => { ready = true; }, window.__isLiveSearching ? 0 : 450); })">

        {{-- Skeleton placeholder — visible immediately on page load (NO x-cloak) --}}
        <div x-show="!ready" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true">
            <x-skeleton :count="8" :columns="5" :avatar="false" />
        </div>

        {{-- Real Table Content --}}
        <div x-show="ready" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-semibold text-[10px] tracking-widest">
                        <th class="py-3 px-5">Waktu Perubahan</th>
                        <th class="py-3 px-5">Siswa</th>
                        <th class="py-3 px-5 text-center">Perubahan Status</th>
                        <th class="py-3 px-5">Diubah Oleh</th>
                        <th class="py-3 px-5">Alasan Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($auditLogs as $log)
                        @php
                            $badgeOld = match($log->old_status) {
                                'HADIR' => 'bg-emerald-50 text-emerald-800 border-emerald-200/70',
                                'IZIN' => 'bg-amber-50 text-amber-800 border-amber-200',
                                'SAKIT' => 'bg-sky-50 text-sky-800 border-sky-200',
                                default => 'bg-rose-50 text-rose-800 border-rose-200/70',
                            };
                            $badgeNew = match($log->new_status) {
                                'HADIR' => 'bg-emerald-50 text-emerald-800 border-emerald-200/70',
                                'IZIN' => 'bg-amber-50 text-amber-800 border-amber-200',
                                'SAKIT' => 'bg-sky-50 text-sky-800 border-sky-200',
                                default => 'bg-rose-50 text-rose-800 border-rose-200/70',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors duration-100">
                            {{-- Waktu --}}
                            <td class="py-3.5 px-5 whitespace-nowrap">
                                <span class="font-mono text-slate-700 text-xs font-medium block">{{ $log->created_at->format('d/m/Y') }}</span>
                                <span class="font-mono text-slate-400 text-[11px]">{{ $log->created_at->format('H:i:s') }} WIB</span>
                            </td>

                            {{-- Siswa --}}
                            <td class="py-3.5 px-5">
                                <p class="font-semibold text-slate-900 text-sm leading-snug">{{ $log->lessonAttendance->student->name ?? '-' }}</p>
                                <p class="font-mono text-slate-400 text-[11px] mt-0.5">NISN: {{ $log->lessonAttendance->student->identity_number ?? '-' }}</p>
                            </td>

                            {{-- Perubahan Status (Awal -> Baru) --}}
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $badgeOld }}">
                                        {{ $log->old_status }}
                                    </span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $badgeNew }}">
                                        {{ $log->new_status }}
                                    </span>
                                </div>
                            </td>

                            {{-- Diubah Oleh --}}
                            <td class="py-3.5 px-5">
                                <div class="inline-flex items-center gap-1.5 text-slate-800 font-semibold text-xs">
                                    <i data-lucide="user-pen" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                    <span>{{ $log->changedBy->name ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- Alasan Perubahan --}}
                            <td class="py-3.5 px-5">
                                <span class="text-slate-700 text-xs italic bg-slate-50 px-2.5 py-1 rounded-md border border-slate-200/60 inline-block max-w-md">
                                    "{{ $log->reason }}"
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center">
                                <div class="inline-flex flex-col items-center gap-2">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i data-lucide="history" class="w-6 h-6 text-slate-400"></i>
                                    </span>
                                    <p class="text-sm font-semibold text-slate-600">Belum ada riwayat perubahan presensi</p>
                                    <p class="text-xs text-slate-400">Setiap koreksi status kehadiran siswa oleh guru atau admin akan otomatis tercatat di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

            @if($auditLogs->hasPages())
                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $auditLogs->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
