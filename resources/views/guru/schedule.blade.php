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
    @endphp

    <!-- Section: Page Heading & Teacher Brief -->
    <section class="space-y-1.5" data-purpose="heading-section">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 heading-font">Jadwal Mengajar</h2>
        <p class="text-[13px] leading-relaxed text-slate-600">
            Jadwal kegiatan belajar mengajar mingguan untuk <span class="font-semibold text-slate-800">{{ $teacher->name }}</span>
        </p>
    </section>

    <!-- Section: Day Quick Filters / Chips -->
    <section class="overflow-x-auto no-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0" data-purpose="quick-day-selector">
        <div class="flex items-center gap-2 w-max pb-1">
            <a class="px-3.5 py-1.5 rounded-full bg-emerald-700 text-white text-xs font-semibold shadow-sm transition hover:bg-emerald-800" href="#semua">Semua</a>
            @foreach($schedules as $day => $items)
                <a class="px-3.5 py-1.5 rounded-full bg-white border border-slate-200 text-slate-700 text-xs font-medium hover:border-emerald-500 hover:text-emerald-700 transition" href="#{{ strtolower($day) }}">
                    {{ ucfirst(strtolower($day)) }}
                </a>
            @endforeach
        </div>
    </section>

    <!-- Section: Day Schedule Groups -->
    <div class="space-y-6">
        @forelse($schedules as $day => $items)
            @php
                $isToday = strtoupper($day) === $todayName;
                $currentTime = now()->format('H:i:s');
            @endphp
            <section class="space-y-3" data-purpose="day-schedule-group" id="{{ strtolower($day) }}">
                <div class="flex items-center justify-between pt-2">
                    <h3 class="text-sm font-bold tracking-wider {{ $isToday ? 'text-emerald-800' : 'text-slate-900' }} uppercase heading-font">
                        <span>{{ $day }}</span>
                    </h3>
                    <span class="text-xs font-semibold text-emerald-700">{{ count($items) }} sesi mengajar</span>
                </div>

                <div class="space-y-3">
                    @foreach($items as $idx => $sch)
                        @php
                            $startTime = strlen($sch->start_time) === 5 ? $sch->start_time . ':00' : $sch->start_time;
                            $endTime = strlen($sch->end_time) === 5 ? $sch->end_time . ':00' : $sch->end_time;
                            $isCurrentSlot = $isToday && ($currentTime >= $startTime && $currentTime <= $endTime);
                            $session = $sch->todaySession;
                            $isDone = $isToday && $session && $session->isLocked();
                        @endphp

                        <article class="bg-white rounded-2xl p-4 border {{ $isCurrentSlot ? 'border-emerald-300 shadow-sm ring-1 ring-emerald-400/20' : 'border-slate-200/90 shadow-sm' }} hover:border-emerald-300 transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                    <span class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-xs font-bold {{ $isCurrentSlot ? 'bg-emerald-700 text-white shadow-2xs' : 'bg-slate-100 text-slate-700 border border-slate-200' }} shrink-0 heading-font mono-font">
                                        #{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <h4 class="text-[15px] font-bold text-slate-900 leading-snug heading-font truncate">
                                                {{ $sch->subject->name ?? 'Mata Pelajaran' }}
                                            </h4>
                                            @if($isToday)
                                                <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.2 rounded">Hari Ini</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 mt-1 text-xs text-slate-600 font-medium flex-wrap">
                                            <span class="flex items-center gap-1 text-slate-700">
                                                <i data-lucide="door-closed" class="w-3.5 h-3.5 {{ $isToday ? 'text-emerald-700' : 'text-slate-400' }}"></i>
                                                <span>Kelas {{ $sch->classroom->name ?? '-' }}</span>
                                            </span>
                                            @if(!empty($sch->subject->code))
                                                <span class="text-slate-300">/</span>
                                                <span class="text-slate-500 font-mono text-[11px]">Kode: {{ $sch->subject->code }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                                @if($isDone)
                                    <span class="text-[11px] text-emerald-700 font-medium flex items-center gap-1">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>Sesi Tuntas</span>
                                    </span>
                                @elseif($isCurrentSlot)
                                    <span class="text-[11px] text-amber-700 font-medium flex items-center gap-1">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                        </span>
                                        <span>Sedang Berlangsung</span>
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-400 font-medium">Sesi Terjadwal</span>
                                @endif

                                <div class="flex items-center gap-1.5 text-xs font-semibold {{ $isToday ? 'text-emerald-800 bg-emerald-50 border border-emerald-100' : 'text-slate-600 bg-slate-50' }} px-2.5 py-1 rounded-md">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 {{ $isToday ? 'text-emerald-700' : 'text-slate-400' }}"></i>
                                    <span class="mono-font">{{ substr($sch->start_time, 0, 5) }} – {{ substr($sch->end_time, 0, 5) }} <span class="text-[10px] opacity-75 font-normal">WIB</span></span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200/80 shadow-sm space-y-3">
                <i data-lucide="calendar-x-2" class="w-10 h-10 text-slate-300 mx-auto"></i>
                <h3 class="text-sm font-bold text-slate-800 heading-font">Belum Ada Jadwal Mengajar</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    Bapak/Ibu Guru belum memiliki jadwal mengajar mingguan yang terdaftar pada sistem.
                </p>
            </div>
        @endforelse
    </div>

</div>
@endsection
