@extends('layouts.app')

@section('title', 'Jadwal Mengajar — MA Ma\'arif Cilageni')
@section('page-title', 'Jadwal Mengajar')

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

        $totalWeeklySessions = 0;
        $totalTodaySessions = 0;
        foreach ($schedules as $day => $items) {
            $totalWeeklySessions += count($items);
            if (strtoupper($day) === $todayName) {
                $totalTodaySessions = count($items);
            }
        }
    @endphp

    {{-- ── 1. PAGE TITLE ────────────────────────────── --}}
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 heading-font tracking-tight">
            Jadwal Mengajar
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Jadwal kegiatan belajar mengajar mingguan untuk <strong class="text-slate-800 font-bold">{{ $teacher->name }}</strong>
        </p>
    </div>

    {{-- ── 2. WEEKLY SCHEDULE LIST ─────────────────────────────────── --}}
    <div class="space-y-8">
        @forelse($schedules as $day => $items)
            @php
                $isToday = strtoupper($day) === $todayName;
            @endphp
            <div class="space-y-3.5">
                
                {{-- Day Section Header Bar (Clean, no card wrapper) --}}
                <div class="flex items-baseline justify-between pb-2.5 border-b border-slate-200/70">
                    <h2 class="text-base sm:text-lg font-bold {{ $isToday ? 'text-emerald-700' : 'text-slate-900' }} heading-font uppercase tracking-wide">
                        {{ $day }}
                    </h2>
                    <span class="text-xs text-slate-400 font-medium">
                        {{ count($items) }} sesi mengajar
                    </span>
                </div>

                {{-- Lessons List with High Contrast Typography --}}
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
                            
                            {{-- Left: Sesi Badge + Subject High Contrast + Classroom Info --}}
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
                                            <i data-lucide="door-closed" class="w-3.5 h-3.5 text-slate-400"></i>
                                            <span>Kelas {{ $sch->classroom->name ?? '-' }}</span>
                                        </span>
                                        @if(!empty($sch->subject->code))
                                            <span class="mono-font text-[11px] text-slate-400 font-medium">
                                                Kode: {{ $sch->subject->code }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Clean Plain Time Display --}}
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
                <h3 class="text-sm font-bold text-slate-800">Belum Ada Jadwal Mengajar</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    Bapak/Ibu Guru belum memiliki jadwal mengajar mingguan yang terdaftar pada sistem. Silakan hubungi bagian Admin jika ada kekeliruan.
                </p>
            </div>
        @endforelse
    </div>
</div>
@endsection
