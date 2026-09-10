@extends('layouts.app')

@section('title', 'Riwayat Kelas Mengajar — MA Ma\'arif Cilageni')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Riwayat Kelas Mengajar</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Daftar kelas dan presensi yang telah dilaksanakan oleh Bapak/Ibu Guru</p>
    </div>

    <!-- History list & Desktop Table -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
        
        <!-- 1. DESKTOP VIEW: Clean Responsive Table (Hidden on Mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/70 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4 rounded-l-xl">Waktu & Tanggal</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">Mata Pelajaran</th>
                        <th class="py-3 px-4">PIN Sesi</th>
                        <th class="py-3 px-4">Durasi</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 rounded-r-xl text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($sessions as $ses)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 text-slate-700">
                                <p class="font-bold text-slate-900">{{ $ses->created_at->translatedFormat('d F Y') }}</p>
                                <p class="text-[11px] text-slate-400 mono-font">{{ $ses->created_at->format('H:i') }} WIB</p>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-md text-xs font-black bg-maarif-700 text-white">
                                    {{ $ses->schedule->classroom->name ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $ses->schedule->subject->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 mono-font font-bold text-slate-800 text-sm">
                                {{ $ses->pin_code }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 mono-font">
                                {{ $ses->duration_minutes }} menit
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $ses->status === 'LOCKED' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                                    {{ $ses->status === 'LOCKED' ? 'Selesai' : 'Aktif' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('guru.session.reconcile', $ses) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-maarif-50 active:bg-maarif-100 text-slate-700 hover:text-maarif-800 border border-slate-200/80 font-bold transition-all duration-150 active:scale-95 text-xs shadow-2xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                                    <span>Lihat Kehadiran</span>
                                    <svg class="w-3.5 h-3.5 ml-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                Belum ada riwayat sesi mengajar yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 2. MOBILE VIEW: Compact Cards (Hidden on Desktop) -->
        <div class="md:hidden space-y-3">
            @forelse($sessions as $ses)
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/80 space-y-2 text-xs">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-maarif-700 text-white">
                                    {{ $ses->schedule->classroom->name ?? '-' }}
                                </span>
                                <span class="font-bold text-slate-900">{{ $ses->schedule->subject->name ?? '-' }}</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">
                                {{ $ses->created_at->translatedFormat('d F Y, H:i') }} WIB &bull; Durasi {{ $ses->duration_minutes }}m
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $ses->status === 'LOCKED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $ses->status === 'LOCKED' ? 'Selesai' : 'Aktif' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 text-[11px] text-slate-600">
                        <span>PIN Sesi: <strong class="mono-font font-bold">{{ $ses->pin_code }}</strong></span>
                        <a href="{{ route('guru.session.reconcile', $ses) }}" class="inline-flex items-center gap-1 font-bold text-maarif-700 hover:text-maarif-800 active:text-maarif-900 bg-white border border-slate-200 px-2.5 py-1 rounded-lg transition-all duration-150 active:scale-95 shadow-2xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                            <span>Lihat Kehadiran</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-center py-8 text-xs text-slate-400">Belum ada riwayat sesi mengajar.</p>
            @endforelse
        </div>

        <div class="pt-2">
            {{ $sessions->links() }}
        </div>
    </div>
</div>
@endsection
