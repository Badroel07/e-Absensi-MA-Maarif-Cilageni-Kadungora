@extends('layouts.app')

@section('title', 'Jadwal Pelajaran — MA Ma\'arif Cilageni')
@section('page-title', 'Jadwal Pelajaran')

@push('styles')
<style>
    .mono-font {
        font-family: 'JetBrains Mono', monospace;
    }
</style>
@endpush

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

        // Hitung total mapel seminggu & mapel hari ini
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
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 heading-font tracking-tight">
            Jadwal Pelajaran
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Agenda kegiatan belajar mengajar mingguan Kelas <strong class="text-slate-800 font-bold">{{ $student->classroom->name ?? '-' }}</strong>
        </p>
    </div>

    {{-- ── 2. WEEKLY SCHEDULE CARDS ─────────────────────────────────── --}}
    <div class="space-y-6">
        @forelse($schedules as $day => $items)
            @php
                $isToday = strtoupper($day) === $todayName;
            @endphp
            <div class="bg-white rounded-3xl p-5 sm:p-7 border {{ $isToday ? 'border-emerald-500 shadow-md shadow-emerald-500/10 ring-1 ring-emerald-500/20' : 'border-slate-200/80 shadow-xs' }} transition-all">
                
                {{-- Day Section Header Bar --}}
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl {{ $isToday ? 'bg-emerald-700 text-white shadow-xs' : 'bg-slate-100 text-slate-700' }} flex items-center justify-center font-black text-sm shrink-0">
                            <i data-lucide="calendar-days" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5">
                                <h2 class="text-base font-black text-slate-900 heading-font uppercase tracking-wider">
                                    {{ $day }}
                                </h2>
                                @if($isToday)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                        Hari Ini
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">
                                {{ count($items) }} mata pelajaran terjadwal
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Lessons List with High Contrast Typography --}}
                <div class="space-y-3">
                    @foreach($items as $idx => $sch)
                        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 hover:shadow-xs transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 group">
                            
                            {{-- Left: Sesi Badge + Nama Mapel High Contrast + Teacher Info --}}
                            <div class="flex items-start sm:items-center space-x-3.5 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-black mono-font text-xs flex items-center justify-center shrink-0 shadow-2xs group-hover:bg-emerald-700 group-hover:text-white group-hover:border-emerald-700 transition">
                                    {{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}
                                </div>
                                <div class="min-w-0 flex-1 space-y-1">
                                    <h3 class="text-base sm:text-lg font-black text-slate-950 heading-font leading-snug tracking-tight break-words group-hover:text-emerald-900 transition">
                                        {{ $sch->subject->name ?? 'Mata Pelajaran' }}
                                    </h3>
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1.5 text-slate-600 font-semibold">
                                            <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                            <span>{{ $sch->teacher->name ?? 'Dewan Guru' }}</span>
                                        </span>
                                        @if(!empty($sch->subject->code))
                                            <span class="text-slate-300">•</span>
                                            <span class="mono-font text-[11px] text-slate-400 font-medium">
                                                Kode: {{ $sch->subject->code }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Right: High Contrast Time Pill --}}
                            <div class="flex items-center justify-start sm:justify-end shrink-0 pt-2.5 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                <div class="px-3.5 py-2 rounded-xl bg-slate-900 text-white font-extrabold mono-font text-xs sm:text-sm shadow-xs flex items-center gap-2 tracking-wide">
                                    <i data-lucide="clock" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <span class="text-white">{{ substr($sch->start_time, 0, 5) }} – {{ substr($sch->end_time, 0, 5) }}</span>
                                    <span class="text-[10px] text-slate-400 uppercase font-black tracking-wider">WIB</span>
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
