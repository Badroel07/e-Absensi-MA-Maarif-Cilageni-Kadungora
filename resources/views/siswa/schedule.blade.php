@extends('layouts.app')

@section('title', 'Jadwal Pelajaran — MA Ma\'arif Cilageni')
@section('page-title', 'Jadwal Pelajaran')

@section('content')
<div class="space-y-6">

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

        $totalWeeklySubjects = 0;
        $totalTodaySubjects = 0;
        foreach ($schedules as $day => $items) {
            $totalWeeklySubjects += count($items);
            if (strtoupper($day) === $todayName) {
                $totalTodaySubjects = count($items);
            }
        }
    @endphp

    {{-- ── 1. PAGE TITLE ────────────────────────────── --}}
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 heading-font tracking-tight">
            Jadwal Pelajaran
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Agenda kegiatan belajar mengajar mingguan Kelas <strong class="text-slate-800 font-bold">{{ $student->classroom->name ?? '-' }}</strong>
        </p>
    </div>

    {{-- ── 2. WEEKLY SCHEDULE LIST — Unboxed Day Sections (Anti-Nested Card) ── --}}
    <div class="space-y-8">
        @forelse($schedules as $day => $items)
            @php
                $isToday = strtoupper($day) === $todayName;
            @endphp
            <div class="space-y-3.5">

                {{-- Day Section Header Bar (Clean, no card wrapper, no icon, no pill) --}}
                <div class="flex items-baseline justify-between pb-2.5 border-b border-slate-200/70">
                    <h2 class="text-base sm:text-lg font-bold {{ $isToday ? 'text-emerald-700' : 'text-slate-900' }} heading-font uppercase tracking-wide">
                        {{ $day }}
                    </h2>
                    <span class="text-xs text-slate-400 font-medium">
                        {{ count($items) }} mata pelajaran terjadwal
                    </span>
                </div>

                {{-- Lessons List — Bare Number + Plain Time (DESIGN.md §9.D) --}}
                <div class="space-y-3">
                    @php
                        $currentTime = now()->format('H:i:s');
                    @endphp
                    @foreach($items as $idx => $sch)
                        @php
                            $startTime = strlen($sch->start_time) === 5 ? $sch->start_time . ':00' : $sch->start_time;
                            $endTime = strlen($sch->end_time) === 5 ? $sch->end_time . ':00' : $sch->end_time;
                            $isCurrentSlot = $isToday && $currentTime >= $startTime && $currentTime <= $endTime;
                        @endphp
                        <div class="p-4 sm:p-5 rounded-2xl bg-white border {{ $isCurrentSlot ? 'border-emerald-500 shadow-sm ring-1 ring-emerald-500/20' : 'border-slate-200/90 shadow-2xs hover:border-emerald-300 hover:shadow-xs' }} transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 group">

                            {{-- Left: Sesi Number (bare mono, no box) + Mapel & Guru --}}
                            <div class="flex items-start sm:items-center space-x-3.5 min-w-0 flex-1">
                                <span class="mono-font font-bold text-sm sm:text-base {{ $isCurrentSlot ? 'text-emerald-600 font-extrabold' : 'text-slate-400' }} shrink-0 w-6 text-center select-none pt-0.5 transition-colors">
                                    {{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <div class="min-w-0 flex-1 space-y-1">
                                    <h3 class="text-base sm:text-lg font-semibold text-slate-900 heading-font leading-snug tracking-tight break-words group-hover:text-emerald-900 transition">
                                        {{ $sch->subject->name ?? 'Mata Pelajaran' }}
                                    </h3>
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600">
                                            <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                            <span>{{ $sch->teacher->name ?? 'Dewan Guru' }}</span>
                                        </span>
                                        @if(!empty($sch->subject->code))
                                            <span class="mono-font text-[11px] text-slate-400 font-medium">
                                                Kode: {{ $sch->subject->code }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Clean Plain Time Display (no dark pill, WIB baseline aligned) --}}
                            <div class="flex items-center justify-end shrink-0 pt-2.5 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                <div class="font-semibold mono-font text-xs sm:text-sm flex items-center gap-1.5 tracking-wide {{ $isCurrentSlot ? 'text-emerald-700 font-bold' : 'text-slate-700' }}">
                                    <i data-lucide="clock" class="w-4 h-4 {{ $isCurrentSlot ? 'text-emerald-600' : 'text-slate-400' }} shrink-0"></i>
                                    <span class="leading-none">{{ substr($sch->start_time, 0, 5) }} – {{ substr($sch->end_time, 0, 5) }}</span>
                                    <span class="text-[11px] {{ $isCurrentSlot ? 'text-emerald-600 font-bold' : 'text-slate-400 font-medium' }} uppercase tracking-wider leading-none">WIB</span>
                                </div>
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
                <h3 class="text-sm font-bold text-slate-800">Belum Ada Jadwal Pelajaran</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    Belum ada jadwal mingguan yang ditentukan untuk kelas ini. Silakan hubungi bagian tata usaha jika terdapat kendala.
                </p>
            </div>
        @endforelse
    </div>
</div>
@endsection
