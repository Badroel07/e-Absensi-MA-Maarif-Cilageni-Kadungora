<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Presensi Madrasah — MA Ma'arif Cilageni Kadungora</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $fontsFile = null;
        $fontsManifestPath = public_path('build/fonts-manifest.json');
        if (file_exists($fontsManifestPath)) {
            $fm = @json_decode(@file_get_contents($fontsManifestPath), true);
            $fontsFile = $fm['style']['file'] ?? null;
        }
        if (!$fontsFile) {
            $manifest = @json_decode(@file_get_contents(public_path('build/manifest.json')), true);
            if (is_array($manifest)) {
                foreach ($manifest as $key => $meta) {
                    if (str_starts_with($key, '_fonts') && str_ends_with($key, '.css')) {
                        $fontsFile = $meta['file'] ?? null;
                        break;
                    }
                }
            }
        }
    @endphp
    @if($fontsFile)
        <link rel="stylesheet" href="{{ asset('build/' . $fontsFile) }}">
    @endif
    <style>
        .mono-font { font-family: 'JetBrains Mono', monospace; }
        .heading-font { font-family: 'Inter', system-ui, sans-serif; }
        @keyframes scale-up {
            0% { transform: scale(0.9); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-scale-up {
            animation: scale-up 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="h-full bg-[#080c14] text-slate-100 flex flex-col justify-between p-5 sm:p-7 lg:p-10 select-none relative overflow-x-hidden antialiased">

    <!-- Top Status Bar & Identity -->
    <header class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6 pb-6 border-b border-slate-800/80">
        <!-- Institution Identification -->
        <div class="flex items-center gap-4">
            <img src="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}" 
                 alt="Logo MA Ma'arif Cilageni" 
                 class="w-14 h-14 sm:w-16 sm:h-16 object-contain shrink-0">
            <div>
                <p class="text-xs font-semibold tracking-wider text-emerald-400 uppercase">
                    Terminal Presensi Mandiri
                </p>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white heading-font mt-0.5">
                    MA Ma'arif Cilageni
                </h1>
                <p class="text-xs text-slate-400">
                    Kadungora — Garut &bull; Ruang Dewan Guru
                </p>
            </div>
        </div>

        <!-- Center / Right: Big Digital Clock & Fullscreen Control -->
        <div class="flex items-center justify-between md:justify-end gap-6 sm:gap-8">
            <div class="text-left md:text-right">
                <div class="flex items-baseline md:justify-end gap-2">
                    <span id="liveClock" class="text-4xl sm:text-5xl lg:text-6xl font-extrabold mono-font text-white tracking-tight leading-none">
                        --:--:--
                    </span>
                    <span class="text-sm font-bold text-emerald-400 mono-font">WIB</span>
                </div>
                <p class="text-xs sm:text-sm font-medium text-slate-400 mt-1">
                    {{ $currentDate }}
                </p>
            </div>
        </div>
    </header>

    <!-- Main Dual-Pillar Content -->
    <main class="relative z-10 flex-1 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center my-8 max-w-7xl mx-auto w-full">
        
        <!-- Left Pillar: Monolithic QR Viewport (lg:col-span-6) -->
        <div class="lg:col-span-6 flex flex-col items-center justify-center">
            
            <div class="w-full max-w-md bg-slate-900/90 border border-slate-800 rounded-3xl p-7 sm:p-8 shadow-2xl flex flex-col items-center">
                
                <div class="w-full text-center pb-4">
                    <p class="text-xs font-bold text-emerald-400 tracking-wider uppercase font-mono mb-1">
                        KODE QR PRESENSI
                    </p>
                    <p class="text-sm font-semibold text-white tracking-wide">
                        Arahkan Kamera Ponsel ke Layar
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Buka menu Pindai QR pada aplikasi presensi Anda
                    </p>
                </div>

                <!-- High-Contrast Monolith White Canvas -->
                <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-inner flex items-center justify-center">
                    <div id="qrContainer" class="w-68 h-68 sm:w-80 sm:h-80 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full select-none">
                        {!! $qrSvg !!}
                    </div>
                </div>

                <!-- 20-Second Refresh Cycle Info -->
                <div class="w-full mt-6 space-y-2.5">
                    <div class="flex items-center justify-between text-xs font-semibold mono-font">
                        <span class="text-slate-400 uppercase tracking-wider">Masa Berlaku Kode QR</span>
                        <span class="text-emerald-400 font-bold">
                            <span id="countdownText">{{ $remainingSeconds }}s</span>
                        </span>
                    </div>

                    <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div id="countdownBar" 
                             class="h-full bg-emerald-500 transition-all duration-1000 ease-linear rounded-full" 
                             style="width: {{ ($remainingSeconds / 10) * 100 }}%">
                        </div>
                    </div>
                </div>

            </div>

            <p class="text-xs text-slate-400 text-center mt-4">
                Kode QR dinamis otomatis berganti setiap 10 detik &bull; Radius Geofence {{ $school->radius_meters ?? 75 }}m
            </p>
        </div>

        <!-- Right Pillar: Realtime Activity Feed & Guidance (lg:col-span-6) -->
        <div class="lg:col-span-6 flex flex-col justify-center space-y-6">
            
            <!-- Live Scans Feed -->
            <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-white heading-font">
                            Aktivitas Presensi Hari Ini
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Pembaruan otomatis secara langsung dari server
                        </p>
                    </div>
                    <span id="recentScansCount" class="text-xs font-mono font-medium text-slate-400">
                        {{ count($recentAttendances) }} Terdata
                    </span>
                </div>

                <!-- Clean Tabular Feed (No Badges, Pure Typography) -->
                <div id="recentScansList" class="divide-y divide-slate-800/80 max-h-76 overflow-y-auto">
                    @forelse($recentAttendances as $att)
                        @php
                            $isPulang = !empty($att->check_out_time);
                            $initial = substr($att->user->name ?? 'G', 0, 1);
                        @endphp
                        <div class="py-3 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5 min-w-0">
                                @if($att->user && $att->user->profile_photo_url)
                                    <img src="{{ $att->user->profile_photo_url }}" 
                                         loading="lazy" 
                                         decoding="async" 
                                         alt="{{ $att->user->name }}" 
                                         class="w-9 h-9 rounded-xl object-cover shrink-0 border border-slate-700">
                                @else
                                    <div class="w-9 h-9 rounded-xl bg-slate-800 text-slate-200 border border-slate-700 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ $initial }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-100 text-sm truncate">
                                        {{ $att->user->name ?? 'Dewan Guru' }}
                                    </p>
                                    <p class="text-xs text-slate-400 font-mono mt-0.5">
                                        @if($isPulang)
                                            Pulang: {{ substr($att->check_out_time, 0, 5) }} WIB
                                        @else
                                            Masuk: {{ substr($att->check_in_time, 0, 5) }} WIB ({{ $att->check_in_status ?? 'HADIR' }})
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold tracking-wide shrink-0 {{ $isPulang ? 'text-sky-400' : 'text-emerald-400' }}">
                                {{ $isPulang ? 'Sudah Pulang' : 'Hadir' }}
                            </span>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">
                            <p class="font-medium text-slate-300 text-sm">Belum ada presensi tercatat hari ini</p>
                            <p class="text-xs text-slate-400 mt-1">Pindai kode QR untuk mencatat kehadiran perdana.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 3 Simple Guidance Points (Architectural Lines) -->
            <div class="border-t border-slate-800 pt-5 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs text-slate-300">
                <div class="space-y-1">
                    <p class="text-emerald-400 font-bold uppercase tracking-wider text-[11px]">Langkah 1</p>
                    <p class="text-slate-300 leading-snug">Buka menu <strong>Pindai QR</strong> pada aplikasi HP Anda.</p>
                </div>
                <div class="space-y-1">
                    <p class="text-emerald-400 font-bold uppercase tracking-wider text-[11px]">Langkah 2</p>
                    <p class="text-slate-300 leading-snug">Arahkan kamera ke layar QR di samping.</p>
                </div>
                <div class="space-y-1">
                    <p class="text-emerald-400 font-bold uppercase tracking-wider text-[11px]">Langkah 3</p>
                    <p class="text-slate-300 leading-snug">Kehadiran tercatat otomatis & sambutan tampil.</p>
                </div>
            </div>

        </div>
    </main>

    <!-- Audio Unlock Floating Button (autoplay policy) -->
    <button id="audioUnlockBtn" type="button" class="fixed bottom-5 right-5 z-40 hidden items-center gap-2 px-4 py-2.5 rounded-full bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold shadow-lg shadow-amber-500/20 active:scale-95 transition-all cursor-pointer">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5L6 9H2v6h4l5 4V5zM15.54 8.46a5 5 0 010 7.07M19.07 4.93a10 10 0 010 14.14"/></svg>
        Aktifkan Suara Sambutan
    </button>
    <div id="audioStatusPill" class="fixed bottom-5 left-5 z-40 hidden items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-800 border border-slate-700 text-[11px] font-medium text-slate-300">
        <span id="audioStatusDot" class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
        <span id="audioStatusText">Suara Aktif</span>
    </div>

    <!-- Footer System Status -->
    <footer class="relative z-10 border-t border-slate-800/80 pt-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-400">
        <div>
            Status Terminal: <strong class="text-emerald-400 font-semibold">Aktif &bull; Terhubung ke Server Presensi</strong>
        </div>
        <div>
            MA Ma'arif Cilageni Kadungora — Garut &bull; Sistem Absensi Modern v2.0
        </div>
    </footer>

    <!-- ============================================================== -->
    <!-- INTERACTIVE CELEBRATION GREETING MODAL (POP-UP SAMBUTAN GURU) -->
    <!-- ============================================================== -->
    <div id="greetingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-sm transition-opacity duration-200">
        
        <div id="greetingCard" class="relative w-full max-w-md bg-slate-900 border-2 rounded-3xl p-7 shadow-2xl text-center overflow-hidden flex flex-col items-center animate-scale-up">
            
            <!-- Close Button (Top Right) -->
            <button type="button" 
                    onclick="closeGreetingModal()" 
                    aria-label="Tutup sambutan" 
                    class="absolute top-4 right-4 p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 active:scale-95 transition-all duration-150 inline-flex items-center justify-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Icon Ring & Status Verification Glyph -->
            <div id="modalIconContainer" class="relative my-2">
                <div id="modalIconRing" class="w-20 h-20 rounded-2xl bg-emerald-700 text-white flex items-center justify-center shadow-lg border-2 border-white/20">
                    <svg id="modalIconCheck" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            <!-- Status Type: Presensi Masuk vs Kepulangan -->
            <div class="mt-3">
                <span id="modalBadgeType" class="text-xs font-semibold uppercase tracking-wider text-emerald-400">
                    Presensi Kedatangan Terverifikasi
                </span>
            </div>

            <!-- Greeting Headline -->
            <h2 id="modalGreetingTitle" class="text-2xl sm:text-3xl font-bold text-white heading-font mt-2.5 tracking-tight">
                Selamat Datang!
            </h2>

            <!-- Teacher Name -->
            <div class="my-2 max-w-sm">
                <p id="modalTeacherName" class="text-lg sm:text-xl font-bold text-emerald-300 heading-font">
                    Ust. H. Ahmad Dahlan, S.Pd.I
                </p>
                <p id="modalTimeAndStatus" class="text-xs font-mono font-medium text-slate-300 mt-1">
                    Pukul 06:45:12 WIB &bull; Status: Tepat Waktu
                </p>
            </div>

            <!-- Appreciation Message -->
            <div class="mt-2 p-3.5 rounded-xl bg-slate-950/80 border border-slate-800 w-full max-w-xs">
                <p id="modalSubMessage" class="text-xs text-slate-300 leading-relaxed font-medium">
                    Presensi berhasil dicatat. Selamat beraktivitas dan mengajar siswa-siswi MA Ma'arif Cilageni!
                </p>
            </div>

            <!-- Auto-Dismiss Timer Bar -->
            <div class="w-full max-w-xs mt-5">
                <div class="flex items-center justify-between text-[11px] text-slate-400 mb-1.5 font-medium">
                    <span>Otomatis kembali ke layar QR</span>
                    <span id="modalTimerCountdown" class="font-mono text-emerald-400 font-semibold">7s</span>
                </div>
                <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                    <div id="modalTimerBar" class="h-full bg-emerald-500 rounded-full transition-all duration-100 ease-linear" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kiosk Auto Refresh, Audio Synthesizer, Fullscreen & Polling Script -->
    <script>
        let remainingSeconds = {{ $remainingSeconds }};
        const countdownText = document.getElementById('countdownText');
        const countdownBar = document.getElementById('countdownBar');
        const qrContainer = document.getElementById('qrContainer');
        const liveClock = document.getElementById('liveClock');

        // Modal Elements
        const greetingModal = document.getElementById('greetingModal');
        const greetingCard = document.getElementById('greetingCard');
        const modalIconRing = document.getElementById('modalIconRing');
        const modalBadgeType = document.getElementById('modalBadgeType');
        const modalGreetingTitle = document.getElementById('modalGreetingTitle');
        const modalTeacherName = document.getElementById('modalTeacherName');
        const modalTimeAndStatus = document.getElementById('modalTimeAndStatus');
        const modalSubMessage = document.getElementById('modalSubMessage');
        const modalTimerCountdown = document.getElementById('modalTimerCountdown');
        const modalTimerBar = document.getElementById('modalTimerBar');
        const recentScansList = document.getElementById('recentScansList');
        const recentScansCount = document.getElementById('recentScansCount');

        let lastEventId = null;
        let modalDismissTimer = null;
        let modalCountdownInterval = null;

        // ── Audio Unlock (fix autoplay block) ──────────────────────────
        let kioskAudioCtx = null;
        let audioUnlocked = false;
        const audioUnlockBtn = document.getElementById('audioUnlockBtn');
        const audioStatusPill = document.getElementById('audioStatusPill');
        const audioStatusText = document.getElementById('audioStatusText');
        const audioStatusDot = document.getElementById('audioStatusDot');

        function getAudioCtx() {
            if (!kioskAudioCtx) {
                const AC = window.AudioContext || window.webkitAudioContext;
                if (!AC) return null;
                kioskAudioCtx = new AC();
            }
            return kioskAudioCtx;
        }

        function updateAudioUi() {
            const ctx = kioskAudioCtx;
            const isReady = ctx && ctx.state === 'running' && audioUnlocked;
            if (audioUnlockBtn) {
                audioUnlockBtn.classList.toggle('hidden', isReady);
                audioUnlockBtn.classList.toggle('flex', !isReady);
            }
            if (audioStatusPill) {
                audioStatusPill.classList.toggle('hidden', !isReady);
                audioStatusPill.classList.toggle('flex', isReady);
            }
        }

        async function unlockAudio() {
            try {
                const ctx = getAudioCtx();
                if (!ctx) return false;
                if (ctx.state === 'suspended') await ctx.resume();
                // iOS silent blip to fully unlock
                if (!audioUnlocked) {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    gain.gain.value = 0.0001;
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.02);
                    audioUnlocked = true;
                }
                updateAudioUi();
                return ctx.state === 'running';
            } catch (e) {
                console.warn('[KioskAudio] unlock failed:', e);
                return false;
            }
        }

        // Try unlock on any user gesture
        ['click', 'touchstart', 'keydown'].forEach(evt => {
            document.addEventListener(evt, () => { unlockAudio(); }, { once: false, passive: true });
        });
        if (audioUnlockBtn) {
            audioUnlockBtn.addEventListener('click', async (e) => {
                e.stopPropagation();
                const ok = await unlockAudio();
                if (ok) {
                    // Play preview chime so user knows it's active
                    playGreetingChime(true);
                }
            });
        }
        // Initial UI check after load
        setTimeout(updateAudioUi, 500);
        // Also poll context state in case browser auto-suspends
        setInterval(updateAudioUi, 2000);

        // Synchronize with simulated server time
        window.__serverTimeMs = {{ \Carbon\Carbon::now()->getTimestampMs() }};
        window.__clientInitMs = Date.now();
        window.getServerNow = function() {
            return new Date(window.__serverTimeMs + (Date.now() - window.__clientInitMs));
        };

        // Update live clock
        function updateClock() {
            const now = window.getServerNow();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            liveClock.textContent = `${h}:${m}:${s}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Harmonious Audio Chime via Web Audio API (Synthesizer, zero external MP3 dependencies)
        async function playGreetingChime(isCheckIn = true) {
            try {
                const audioCtx = getAudioCtx();
                if (!audioCtx) return;
                if (audioCtx.state === 'suspended') {
                    try { await audioCtx.resume(); } catch(e) {}
                }
                // If still suspended (no user gesture yet), show unlock button and queue retry
                if (audioCtx.state !== 'running') {
                    updateAudioUi();
                    // Queue one retry after next user interaction
                    const retryOnce = async () => {
                        document.removeEventListener('click', retryOnce);
                        document.removeEventListener('touchstart', retryOnce);
                        await unlockAudio();
                        playGreetingChime(isCheckIn);
                    };
                    document.addEventListener('click', retryOnce, { once: true });
                    document.addEventListener('touchstart', retryOnce, { once: true, passive: true });
                    return;
                }

                const now = audioCtx.currentTime;
                // Cheerful chord arpeggio for Check-in (C5, E5, G5, C6) or warm cadence for Check-out (G5, E5, C5)
                const notes = isCheckIn ? [523.25, 659.25, 783.99, 1046.50] : [783.99, 659.25, 523.25];

                notes.forEach((freq, idx) => {
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, now + idx * 0.12);

                    gain.gain.setValueAtTime(0.28, now + idx * 0.12);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.12 + 0.6);

                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now + idx * 0.12);
                    osc.stop(now + idx * 0.12 + 0.65);
                });
                audioUnlocked = true;
                updateAudioUi();
            } catch (err) {
                console.warn("Audio playback notice:", err);
            }
        }

        // Show Greeting Modal when teacher successfully scans
        function showGreetingModal(event) {
            const isCheckIn = event.type === 'check_in';

            if (isCheckIn) {
                greetingCard.className = "relative w-full max-w-md bg-slate-900 border-2 border-emerald-500 rounded-3xl p-7 shadow-2xl text-center overflow-hidden flex flex-col items-center animate-scale-up";
                modalIconRing.className = "w-20 h-20 rounded-2xl bg-emerald-700 text-white flex items-center justify-center shadow-lg border-2 border-white/20";
                modalBadgeType.className = "text-xs font-semibold uppercase tracking-wider text-emerald-400";
                modalBadgeType.textContent = "Presensi Kedatangan Terverifikasi";
                modalGreetingTitle.textContent = "Selamat Datang!";
                modalGreetingTitle.className = "text-2xl sm:text-3xl font-bold text-white heading-font mt-2.5 tracking-tight";
                modalTeacherName.className = "text-lg sm:text-xl font-bold text-emerald-300 heading-font";
                modalTimerBar.className = "h-full bg-emerald-500 rounded-full transition-all duration-100 ease-linear";
            } else {
                greetingCard.className = "relative w-full max-w-md bg-slate-900 border-2 border-sky-500 rounded-3xl p-7 shadow-2xl text-center overflow-hidden flex flex-col items-center animate-scale-up";
                modalIconRing.className = "w-20 h-20 rounded-2xl bg-sky-700 text-white flex items-center justify-center shadow-lg border-2 border-white/20";
                modalBadgeType.className = "text-xs font-semibold uppercase tracking-wider text-sky-400";
                modalBadgeType.textContent = "Presensi Kepulangan Terverifikasi";
                modalGreetingTitle.textContent = "Selamat Pulang!";
                modalGreetingTitle.className = "text-2xl sm:text-3xl font-bold text-white heading-font mt-2.5 tracking-tight";
                modalTeacherName.className = "text-lg sm:text-xl font-bold text-sky-300 heading-font";
                modalTimerBar.className = "h-full bg-sky-500 rounded-full transition-all duration-100 ease-linear";
            }

            modalTeacherName.textContent = event.teacher_name;
            modalTimeAndStatus.textContent = `Pukul ${event.time} WIB • Status: ${event.status}`;
            modalSubMessage.textContent = event.message || "Presensi berhasil dicatat.";

            // Show Modal
            greetingModal.classList.remove('hidden');

            // Play Chime
            playGreetingChime(isCheckIn);

            // Setup Auto-dismiss (7 seconds)
            clearTimeout(modalDismissTimer);
            clearInterval(modalCountdownInterval);

            const durationSeconds = 7;
            modalTimerCountdown.textContent = durationSeconds + 's';
            modalTimerBar.style.width = '100%';

            const startTime = Date.now();
            modalCountdownInterval = setInterval(() => {
                const elapsed = (Date.now() - startTime) / 1000;
                const rem = Math.max(0, durationSeconds - elapsed);
                modalTimerCountdown.textContent = Math.ceil(rem) + 's';
                modalTimerBar.style.width = ((rem / durationSeconds) * 100) + '%';

                if (rem <= 0) {
                    clearInterval(modalCountdownInterval);
                }
            }, 100);

            modalDismissTimer = setTimeout(() => {
                closeGreetingModal();
            }, durationSeconds * 1000);
        }

        function closeGreetingModal() {
            greetingModal.classList.add('hidden');
            clearTimeout(modalDismissTimer);
            clearInterval(modalCountdownInterval);
        }

        // Close on Escape Key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !greetingModal.classList.contains('hidden')) {
                closeGreetingModal();
            }
        });

        // Fetch fresh QR token
        async function fetchFreshQr() {
            try {
                const res = await fetch("{{ route('kiosk.token') }}");
                if (res.ok) {
                    const data = await res.json();
                    qrContainer.innerHTML = data.qr_svg;
                    remainingSeconds = data.remaining_seconds || 10;
                    if (data.server_time_ms) {
                        window.__serverTimeMs = data.server_time_ms;
                        window.__clientInitMs = Date.now();
                        updateClock();
                    }
                    updateCountdownDisplay();
                }
            } catch (err) {
                console.error("Gagal memperbarui QR:", err);
            }
        }

        function updateCountdownDisplay() {
            countdownText.textContent = remainingSeconds + 's';
            const pct = Math.max(0, (remainingSeconds / 10) * 100);
            countdownBar.style.width = pct + '%';
        }

        // Countdown interval for QR renewal (10s)
        setInterval(() => {
            remainingSeconds--;
            if (remainingSeconds <= 0) {
                fetchFreshQr();
            } else {
                updateCountdownDisplay();
            }
        }, 1000);

        // Fast Polling for Scan Events (Every 2 seconds)
        async function pollKioskEvents() {
            try {
                const res = await fetch("{{ route('kiosk.poll-event') }}");
                if (!res.ok) return;

                const data = await res.json();

                // 1. Check for incoming new scan event
                if (data.latest_event && data.latest_event.id) {
                    const evt = data.latest_event;
                    const serverNowMs = (typeof window.getServerNow === 'function') ? window.getServerNow().getTime() : Date.now();
                    
                    // Trigger only if event is fresh (< 35 seconds from server clock)
                    const isFresh = evt.timestamp_ms ? (Math.abs(serverNowMs - evt.timestamp_ms) < 35000) : true;
                    if (lastEventId === null) {
                        lastEventId = evt.id;
                        if (isFresh) {
                            showGreetingModal(evt);
                        }
                    } else if (evt.id !== lastEventId && isFresh) {
                        lastEventId = evt.id;
                        showGreetingModal(evt);
                    }
                }

                // 2. Update recent attendances list on kiosk screen
                if (data.recent_attendances && data.recent_attendances.length > 0) {
                    renderRecentScans(data.recent_attendances);
                }
            } catch (e) {
                console.warn("Poll kiosk error:", e);
            }
        }

        function renderRecentScans(items) {
            if (!recentScansList) return;
            if (recentScansCount) {
                recentScansCount.textContent = `${items.length} Terdata`;
            }
            let html = '';
            items.forEach(att => {
                const isPulang = !!att.check_out_time;
                const initial = att.name.charAt(0);
                const statusColor = isPulang ? 'text-sky-400' : 'text-emerald-400';
                const statusText = isPulang ? 'Sudah Pulang' : 'Hadir';
                const timeText = isPulang ? `Pulang: ${att.check_out_time} WIB` : `Masuk: ${att.check_in_time} WIB (${att.check_in_status})`;

                html += `
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-slate-800 text-slate-200 border border-slate-700 font-bold text-xs flex items-center justify-center shrink-0">
                                ${initial}
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-100 text-sm truncate">${att.name}</p>
                                <p class="text-xs text-slate-400 font-mono mt-0.5">${timeText}</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold tracking-wide shrink-0 ${statusColor}">
                            ${statusText}
                        </span>
                    </div>
                `;
            });
            recentScansList.innerHTML = html;
        }

        // Start polling every 2 seconds
        setInterval(pollKioskEvents, 2000);
    </script>
    @if(app()->environment('local'))
        @include('partials.time-simulator')
    @endif
</body>
</html>
