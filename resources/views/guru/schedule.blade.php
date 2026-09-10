@extends('layouts.app')

@section('title', 'Jadwal Mengajar — MA Ma\'arif Cilageni')

@push('styles')
<style>
    .mono-font {
        font-family: 'JetBrains Mono', monospace;
    }
</style>
@endpush

@section('content')
<div class="space-y-5">
    <!-- 1. Page Title Header -->
    <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Jadwal Mengajar</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Jadwal kegiatan belajar mengajar mingguan untuk <strong class="text-slate-700 font-bold">{{ $teacher->name }}</strong></p>
    </div>

    @php
        $indonesianDays = [
            'Sunday' => 'AHAD',
            'Monday' => 'SENIN',
            'Tuesday' => 'SELASA',
            'Wednesday' => 'RABU',
            'Thursday' => 'KAMIS',
            'Friday' => 'JUMAT',
            'Saturday' => 'SABTU',
        ];
        $todayName = $indonesianDays[now()->format('l')] ?? 'SENIN';
    @endphp

    <!-- 2. Weekly Schedule Cards -->
    <div class="space-y-4">
        @forelse($schedules as $day => $items)
            @php
                $isToday = strtoupper($day) === $todayName;
            @endphp
            <div class="bg-white rounded-3xl p-5 sm:p-6 border {{ $isToday ? 'border-emerald-500 shadow-md shadow-emerald-500/10 ring-1 ring-emerald-500/20' : 'border-slate-200/80 shadow-xs' }} hover:border-slate-300 transition">
                <!-- Day Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl {{ $isToday ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700' }} flex items-center justify-center font-black text-xs">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-slate-900 heading-font uppercase tracking-wider flex items-center gap-2">
                                <span>{{ $day }}</span>
                                @if($isToday)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Hari Ini
                                    </span>
                                @endif
                            </h2>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-3 py-1 rounded-full {{ $isToday ? 'bg-emerald-100/70 text-emerald-800' : 'bg-slate-100 text-slate-600' }} mono-font">
                        {{ count($items) }} Kelas Mengajar
                    </span>
                </div>

                <!-- Lessons in this day (1 Single Column) -->
                <div class="space-y-3">
                    @foreach($items as $sch)
                        <div class="p-4 sm:p-4.5 rounded-2xl bg-slate-50/90 border border-slate-200/70 hover:bg-slate-100/90 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 sm:gap-4">
                            <div class="flex items-center space-x-3.5 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-2xl bg-emerald-100/80 text-emerald-800 font-bold flex items-center justify-center border border-emerald-200 shrink-0">
                                    <i data-lucide="book-open" class="w-5 h-5 text-emerald-700"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-slate-900 text-sm leading-snug break-words">{{ $sch->subject->name ?? 'Mata Pelajaran' }}</p>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-white border border-slate-200 text-slate-700 font-bold text-[11px] shadow-2xs">
                                            <i data-lucide="door-closed" class="w-3 h-3 mr-1 text-slate-500"></i>
                                            Kelas {{ $sch->classroom->name ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200/60 text-xs">
                                <span class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-800 font-extrabold mono-font text-xs shadow-2xs flex items-center gap-1.5">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-500"></i>
                                    {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }} WIB
                                </span>

                                @if($isToday)
                                    <a href="{{ route('guru.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-800 active:text-emerald-900 bg-white border border-slate-200 px-3 py-1.5 rounded-xl transition-all duration-150 active:scale-95 shadow-2xs cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 whitespace-nowrap">
                                        <span>Buka di Dashboard</span>
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 shrink-0"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-xs space-y-3">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                    <i data-lucide="calendar-x-2" class="w-7 h-7"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-800">Belum Ada Jadwal Mengajar</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    Bapak/Ibu Guru belum memiliki jadwal mengajar mingguan yang terdaftar pada sistem. Silakan hubungi bagian Admin jika ada kekeliruan.
                </p>
            </div>
        @endforelse
    </div>
</div>
@endsection
