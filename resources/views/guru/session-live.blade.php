@extends('layouts.app')

@section('title', 'Sesi Presensi Kelas Aktif — MA Ma\'arif Cilageni')
@section('page-title', 'Sesi Presensi Kelas')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-8">
    <!-- Header Navigation & Context (Clean minimal bar matching portal guru) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex items-center space-x-3">
            <a href="{{ route('guru.dashboard') }}" aria-label="Kembali ke Dashboard" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 inline-flex items-center justify-center transition-all duration-150 active:scale-95 shrink-0 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 heading-font leading-tight">
                        {{ $session->schedule->subject->name }}
                    </h1>
                    <span class="px-2 py-0.5 rounded-md bg-maarif-700 text-white font-semibold text-[11px] tracking-wide shrink-0">
                        Kelas {{ $session->schedule->classroom->name }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-2 flex-wrap">
                    <span>Durasi Sesi: <strong class="text-slate-700 font-semibold">{{ $session->duration_minutes }} Menit</strong></span>
                    <span class="text-slate-300">&bull;</span>
                    <span>Total Siswa: <strong class="text-slate-700 font-semibold">{{ $totalStudents }} Siswa</strong></span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-center">
            <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-700 text-white shrink-0">
                Sesi Berlangsung
            </span>
        </div>
    </div>

    <!-- 2-Column Responsive Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        
        <!-- Left: PIN Display & Control Panel (lg:col-span-5) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-5">
                
                <!-- PIN Box -->
                <div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-xs font-semibold text-slate-700 uppercase tracking-wider">Kode PIN Presensi</span>
                        <span class="text-xs text-slate-500 font-medium">4 Angka</span>
                    </div>

                    <div class="mt-3.5 py-4 sm:py-5 px-4 bg-slate-900 text-white rounded-xl mono-font font-bold text-5xl sm:text-6xl tracking-widest text-center select-all border border-slate-800">
                        {{ $session->pin_code }}
                    </div>
                    <p class="text-xs text-slate-500 mt-2 text-center leading-relaxed">
                        Tuliskan di papan tulis atau sebutkan kepada siswa di kelas.
                    </p>
                </div>

                <!-- Timer Section (Clean Progress Bar) -->
                <div class="pt-4 border-t border-slate-100 space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold">
                        <span class="text-slate-700">Sisa Waktu Presensi</span>
                        <span id="timerText" class="mono-font text-sm font-bold text-slate-900">--:--</span>
                    </div>
                    <div class="w-full h-2 bg-slate-200 rounded-sm overflow-hidden">
                        <div id="timerBar" class="h-full bg-emerald-700 transition-all duration-1000 ease-linear" style="width: 100%"></div>
                    </div>
                </div>

                <!-- Counter Stats (Unified 2-column metrics) -->
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-100">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-center">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Siswa</p>
                        <p class="text-2xl font-bold text-slate-900 mono-font mt-1">{{ $totalStudents }}</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
                        <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">Siswa Hadir</p>
                        <p id="verifiedCounter" class="text-2xl font-bold text-emerald-700 mono-font mt-1">{{ $verifiedCount }}</p>
                    </div>
                </div>

                <!-- Action CTA Button -->
                <div class="pt-2">
                    <a href="{{ route('guru.session.reconcile', $session) }}" class="w-full bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 active:scale-[0.98] text-white font-semibold py-3.5 px-4 rounded-xl text-xs sm:text-sm inline-flex items-center justify-center gap-2 transition-all duration-150 shadow-md shadow-maarif-700/25 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-maarif-600">
                        <span>Selesaikan Sesi & Catat Keterangan</span>
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Right: Real-time Live Feed of Attending Students (lg:col-span-7) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-xs flex flex-col h-full min-h-[420px]">
                <div class="pb-3.5 border-b border-slate-100 mb-3">
                    <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-wider heading-font">Daftar Siswa yang Sudah Hadir</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Siswa yang berhasil memasukkan PIN di dalam kelas</p>
                </div>

                <div id="studentListContainer" class="space-y-2.5 flex-1 overflow-y-auto pr-1 max-h-[500px]">
                    @forelse($session->attendances()->where('status', 'HADIR')->with('student')->get() as $att)
                        @php
                            $initials = collect(explode(' ', $att->student->name))
                                ->filter()
                                ->map(fn($w) => mb_substr($w, 0, 1))
                                ->take(2)
                                ->join('');
                        @endphp
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3 text-xs hover:bg-slate-100 transition">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-lg bg-emerald-700 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ $initials ?: 'S' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-slate-900 text-xs sm:text-sm leading-snug truncate">{{ $att->student->name }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">NISN: <span class="mono-font">{{ $att->student->identity_number }}</span></p>
                                </div>
                            </div>
                            <span class="shrink-0 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800 mono-font whitespace-nowrap">
                                {{ $att->verified_at ? $att->verified_at->format('H:i:s') : 'HADIR' }} WIB
                            </span>
                        </div>
                    @empty
                        <div id="emptyText" class="text-center py-14 sm:py-16 text-slate-400">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-xs font-semibold text-slate-700">Menunggu siswa memasukkan PIN...</p>
                            <p class="text-xs text-slate-400 mt-1">Siswa yang berhasil presensi akan otomatis muncul di sini</p>
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

    function formatTime(sec) {
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    function updateTimerUI() {
        const timerText = document.getElementById('timerText');
        const timerBar = document.getElementById('timerBar');
        if (remainingSeconds <= 0) {
            if (timerText) timerText.textContent = "00:00 (Waktu Habis)";
            if (timerBar) timerBar.style.width = "0%";
            return;
        }
        if (timerText) timerText.textContent = formatTime(remainingSeconds);
        const pct = Math.max(0, (remainingSeconds / totalDurationSeconds) * 100);
        if (timerBar) timerBar.style.width = pct + "%";
    }

    // Tick countdown
    const countdownInterval = setInterval(() => {
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

    function getInitials(name) {
        if (!name) return 'S';
        const parts = name.trim().split(/\s+/).filter(Boolean);
        if (parts.length === 0) return 'S';
        if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    // Poll live status every 2.5 seconds
    async function pollStatus() {
        try {
            const res = await fetch("{{ route('guru.session.status', $session) }}");
            if (res.ok) {
                const data = await res.json();
                const verifiedCounter = document.getElementById('verifiedCounter');
                if (verifiedCounter) {
                    verifiedCounter.textContent = data.verified_count;
                }

                const listContainer = document.getElementById('studentListContainer');
                if (data.verified_students && data.verified_students.length > 0 && listContainer) {
                    listContainer.innerHTML = '';
                    data.verified_students.forEach(st => {
                        const item = document.createElement('div');
                        item.className = "p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3 text-xs hover:bg-slate-100 transition";
                        item.innerHTML = `
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-lg bg-emerald-700 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                    ${escapeHtml(getInitials(st.name))}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-slate-900 text-xs sm:text-sm leading-snug truncate">${escapeHtml(st.name)}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">NISN: <span class="mono-font">${escapeHtml(st.identity_number)}</span></p>
                                </div>
                            </div>
                            <span class="shrink-0 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800 mono-font whitespace-nowrap">
                                ${escapeHtml(st.verified_at)} WIB
                            </span>
                        `;
                        listContainer.appendChild(item);
                    });
                }
            }
        } catch(e) {}
    }

    const livePollingInterval = setInterval(pollStatus, 2500);

    function cleanupLiveSession() {
        if (countdownInterval) clearInterval(countdownInterval);
        if (livePollingInterval) clearInterval(livePollingInterval);
    }

    if (window.MaarifSPA && typeof window.MaarifSPA.onPageUnload === 'function') {
        window.MaarifSPA.onPageUnload(cleanupLiveSession);
    }
    window.addEventListener('app:before-page-unload', cleanupLiveSession, { once: true });
</script>
@endpush
