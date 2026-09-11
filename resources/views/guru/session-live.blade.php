@extends('layouts.app')

@section('title', 'Sesi Kelas Berlangsung — MA Ma\'arif Cilageni')

@section('content')
<div class="space-y-4">
    <!-- Top Action & Info Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between sm:justify-start">
            <a href="{{ route('guru.dashboard') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 text-xs font-semibold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali ke Jadwal</span>
            </a>
        </div>
        <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap sm:justify-end">
            <span class="px-2.5 sm:px-3 py-1 rounded-lg text-xs font-semibold bg-maarif-700 text-white shrink-0 shadow-xs">
                Kelas {{ $session->schedule->classroom->name }}
            </span>
            <span class="text-sm sm:text-base font-semibold text-slate-900 heading-font truncate">
                {{ $session->schedule->subject->name }}
            </span>
        </div>
    </div>

    <!-- 2-Column Responsive Layout for Laptop / Infocus Projector -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left: Huge PIN Display & Control Card (lg:col-span-5) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-3xl p-5 sm:p-7 lg:p-8 border border-slate-200/80 shadow-md text-center space-y-5">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold animate-pulse">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    <span>SESI PRESENSI KELAS AKTIF</span>
                </div>

                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kode PIN Kelas (4 Angka)</p>
                    <div class="my-3 py-4 sm:py-6 px-3 sm:px-6 bg-slate-950 text-emerald-400 rounded-3xl mono-font font-bold text-5xl sm:text-6xl lg:text-7xl tracking-widest sm:tracking-[0.25em] pl-[0.1em] sm:pl-[0.25em] shadow-inner flex items-center justify-center select-all border-2 border-slate-800">
                        {{ $session->pin_code }}
                    </div>
                    <p class="text-xs text-slate-500">Tuliskan di papan tulis atau bacakan kepada siswa di kelas.</p>
                </div>

                <!-- Countdown Timer -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-2">
                        <span>Sisa Waktu Presensi:</span>
                        <span id="timerText" class="mono-font text-base sm:text-lg text-amber-600 font-bold">--:--</span>
                    </div>
                    <div class="w-full h-2.5 sm:h-3 bg-slate-200 rounded-full overflow-hidden">
                        <div id="timerBar" class="h-full bg-amber-500 transition-all duration-1000 ease-linear rounded-full" style="width: 100%"></div>
                    </div>
                </div>

                <!-- Live Attended Counter -->
                <div class="grid grid-cols-2 gap-3 sm:gap-4 pt-2 border-t border-slate-100 text-center">
                    <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <p class="text-[11px] font-semibold text-slate-500">Total Siswa Rombel</p>
                        <p class="text-xl sm:text-2xl font-bold text-slate-800 mono-font mt-0.5">{{ $totalStudents }}</p>
                    </div>
                    <div class="p-3 sm:p-3.5 rounded-2xl bg-emerald-50 border border-emerald-100">
                        <p class="text-[11px] font-semibold text-emerald-700">Siswa Hadir</p>
                        <p id="verifiedCounter" class="text-xl sm:text-2xl font-bold text-emerald-600 mono-font mt-0.5">{{ $verifiedCount }}</p>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="pt-2">
                    <a href="{{ route('guru.session.reconcile', $session) }}" class="w-full bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 active:scale-[0.98] text-white font-semibold py-3.5 px-4 rounded-xl sm:rounded-2xl text-xs sm:text-sm inline-flex items-center justify-center gap-2 transition-all duration-150 shadow-lg shadow-maarif-700/25 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-maarif-600">
                        <span>Selesaikan Sesi & Catat Keterangan Siswa</span>
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Right: Real-time Live Feed of Attending Students (lg:col-span-7) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col h-full min-h-[400px] sm:min-h-[450px]">
                <div class="pb-3 border-b border-slate-100 mb-3">
                    <h3 class="text-sm font-semibold text-slate-800 uppercase tracking-wider heading-font">Daftar Siswa yang Sudah Hadir</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Siswa yang sudah berhasil memasukkan PIN di dalam kelas</p>
                </div>

                <div id="studentListContainer" class="space-y-2.5 flex-1 overflow-y-auto pr-1 max-h-[500px]">
                    @forelse($session->attendances()->where('status', 'HADIR')->with('student')->get() as $att)
                        <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-2.5 sm:gap-3 text-xs hover:bg-slate-100/70 transition">
                            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-slate-900 text-xs sm:text-sm leading-snug break-words">{{ $att->student->name }}</p>
                                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5">NISN: <span class="mono-font">{{ $att->student->identity_number }}</span></p>
                                </div>
                            </div>
                            <span class="shrink-0 px-2 sm:px-2.5 py-1 rounded-lg text-[10px] sm:text-xs font-semibold bg-emerald-100 text-emerald-800 mono-font whitespace-nowrap">
                                {{ $att->verified_at ? $att->verified_at->format('H:i:s') : 'HADIR' }} WIB
                            </span>
                        </div>
                    @empty
                        <div id="emptyText" class="text-center py-12 sm:py-16 text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-2 text-slate-300 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            <p class="text-xs font-semibold">Menunggu siswa memasukkan PIN...</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Siswa yang berhasil presensi akan otomatis muncul di sini</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let remainingSeconds = {{ $session->remaining_seconds }};
    const totalDurationSeconds = {{ $session->duration_minutes * 60 }};
    const timerText = document.getElementById('timerText');
    const timerBar = document.getElementById('timerBar');
    const verifiedCounter = document.getElementById('verifiedCounter');
    const listContainer = document.getElementById('studentListContainer');

    function formatTime(sec) {
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    function updateTimerUI() {
        if (remainingSeconds <= 0) {
            timerText.textContent = "00:00 (Waktu Habis)";
            timerBar.style.width = "0%";
            return;
        }
        timerText.textContent = formatTime(remainingSeconds);
        const pct = Math.max(0, (remainingSeconds / totalDurationSeconds) * 100);
        timerBar.style.width = pct + "%";
    }

    // Tick countdown
    setInterval(() => {
        if (remainingSeconds > 0) {
            remainingSeconds--;
            updateTimerUI();
        }
    }, 1000);
    updateTimerUI();

    function escapeHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    // Poll live status every 2.5 seconds
    async function pollStatus() {
        try {
            const res = await fetch("{{ route('guru.session.status', $session) }}");
            if (res.ok) {
                const data = await res.json();
                verifiedCounter.textContent = data.verified_count;

                if (data.verified_students && data.verified_students.length > 0) {
                    listContainer.innerHTML = '';
                    data.verified_students.forEach(st => {
                        const item = document.createElement('div');
                        item.className = "p-3 sm:p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-2.5 sm:gap-3 text-xs hover:bg-slate-100/70 transition";
                        item.innerHTML = `
                            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-slate-900 text-xs sm:text-sm leading-snug break-words">${escapeHtml(st.name)}</p>
                                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5">NISN: <span class="mono-font">${escapeHtml(st.identity_number)}</span></p>
                                </div>
                            </div>
                            <span class="shrink-0 px-2 sm:px-2.5 py-1 rounded-lg text-[10px] sm:text-xs font-semibold bg-emerald-100 text-emerald-800 mono-font whitespace-nowrap">
                                ${escapeHtml(st.verified_at)} WIB
                            </span>
                        `;
                        listContainer.appendChild(item);
                    });
                }
            }
        } catch(e) {}
    }

    setInterval(pollStatus, 2500);
</script>
@endpush
