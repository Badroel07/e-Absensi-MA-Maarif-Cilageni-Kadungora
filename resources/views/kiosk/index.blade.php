<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Presensi Madrasah — MA Ma'arif Cilageni Kadungora</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700;800&family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        maarif: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803D',
                            800: '#166534',
                            900: '#14532d',
                            950: '#052e16',
                            gold: '#EAB308',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        .mono-font { font-family: 'JetBrains Mono', monospace; }
        .heading-font { font-family: 'Plus Jakarta Sans', sans-serif; }
        @keyframes scale-up {
            0% { transform: scale(0.9); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-scale-up {
            animation: scale-up 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="h-full bg-radial from-slate-900 to-slate-950 text-white flex flex-col justify-between p-6 antialiased select-none relative overflow-hidden">
    
    <!-- Kiosk Top Header -->
    <header class="flex items-center justify-between border-b border-slate-800 pb-5">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white font-extrabold text-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
                M
            </div>
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-400">Madrasah Aliyah (Setingkat SMA)</span>
                <h1 class="text-2xl font-black text-white heading-font tracking-tight">MA Ma'arif Cilageni Kadungora</h1>
                <p class="text-xs text-slate-400">Layar Presensi Mandiri Dewan Guru & Karyawan</p>
            </div>
        </div>

        <div class="text-right">
            <div id="liveClock" class="text-4xl font-extrabold mono-font text-emerald-400 tracking-wider">
                --:--:--
            </div>
            <div class="text-sm font-medium text-slate-400">
                {{ $currentDate }}
            </div>
        </div>
    </header>

    <!-- Main Kiosk Body -->
    <main class="flex-1 flex flex-col lg:flex-row items-center justify-center gap-10 my-6">
        
        <!-- Left: QR Code Board -->
        <div class="bg-white p-7 rounded-3xl shadow-2xl shadow-emerald-950/50 flex flex-col items-center border-4 border-emerald-500/30">
            <!-- QR Container -->
            <div id="qrContainer" class="w-72 h-72 sm:w-80 sm:h-80 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                {!! $qrSvg !!}
            </div>

            <!-- 20-Second Refresh Progress Bar -->
            <div class="w-full mt-5">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-1.5 mono-font">
                    <span>KODE QR PRESENSI (Berganti Otomatis)</span>
                    <span>Berganti dalam: <span id="countdownText" class="text-emerald-700 font-extrabold">{{ $remainingSeconds }}s</span></span>
                </div>
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                    <div id="countdownBar" class="h-full bg-emerald-600 transition-all duration-1000 ease-linear rounded-full" style="width: {{ ($remainingSeconds / 20) * 100 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Right: Guide, Security & Live Scans Feed -->
        <div class="max-w-md w-full space-y-4 text-slate-300">
            
            <!-- Petunjuk Singkat -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 space-y-3 backdrop-blur shadow-xl">
                <div class="flex items-center space-x-3 text-emerald-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <h3 class="text-base font-bold text-white heading-font">Petunjuk Presensi Guru</h3>
                </div>

                <ol class="space-y-2 text-xs text-slate-300">
                    <li class="flex items-start space-x-2.5">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">1</span>
                        <span>Buka menu <strong>Pindai QR</strong> pada aplikasi HP Bapak/Ibu Guru.</span>
                    </li>
                    <li class="flex items-start space-x-2.5">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">2</span>
                        <span>Arahkan kamera HP ke kode QR di layar ini.</span>
                    </li>
                    <li class="flex items-start space-x-2.5">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">3</span>
                        <span>Layar presensi akan otomatis menyambut kedatangan Bapak/Ibu Guru.</span>
                    </li>
                </ol>
            </div>

            <!-- Live Feed: Guru Terakhir Presensi Hari Ini -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 backdrop-blur shadow-xl space-y-2.5">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800/80 text-xs">
                    <span class="font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Aktivitas Presensi Terkini
                    </span>
                    <span id="recentScansCount" class="text-[11px] text-slate-500 font-mono">Hari Ini</span>
                </div>

                <div id="recentScansList" class="space-y-2 text-xs">
                    @forelse($recentAttendances as $att)
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-950/60 border border-slate-800/60">
                            <div class="flex items-center space-x-2.5 min-w-0">
                                @if($att->user && $att->user->profile_photo_url)
                                    <img src="{{ $att->user->profile_photo_url }}" alt="{{ $att->user->name }}" class="w-7 h-7 rounded-lg object-cover shrink-0 border border-slate-700">
                                @else
                                    <div class="w-7 h-7 rounded-lg {{ $att->check_out_time ? 'bg-sky-500/20 text-sky-400 border border-sky-400/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-400/30' }} font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ substr($att->user->name ?? 'G', 0, 1) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-200 truncate">{{ $att->user->name ?? 'Dewan Guru' }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">
                                        @if($att->check_out_time)
                                            Pulang: {{ substr($att->check_out_time, 0, 5) }} WIB
                                        @else
                                            Masuk: {{ substr($att->check_in_time, 0, 5) }} WIB ({{ $att->check_in_status ?? 'HADIR' }})
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $att->check_out_time ? 'bg-sky-500/20 text-sky-300 border border-sky-400/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' }}">
                                {{ $att->check_out_time ? 'SUDAH PULANG' : 'HADIR' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-2">Belum ada presensi guru hari ini.</p>
                    @endforelse
                </div>
            </div>

            <!-- Anti Fake-GPS & Integrity Notice -->
            <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-3.5 flex items-start space-x-3 text-amber-300/90 text-xs">
                <svg class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-[11px] leading-relaxed">
                    <strong>Keamanan Kode QR</strong>: Kode QR berganti otomatis setiap 20 detik agar presensi hanya dapat dilakukan langsung di lingkungan madrasah.
                </p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 pt-4 flex items-center justify-between text-xs text-slate-500">
        <div>
            Status: <span class="text-emerald-400 font-semibold inline-flex items-center"><span class="w-2 h-2 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span> Sistem Terhubung & Siap Digunakan</span>
        </div>
        <div>
            MA Ma'arif Cilageni Kadungora — Garut &bull; Sistem Absensi Modern v2.0
        </div>
    </footer>

    <!-- ============================================================== -->
    <!-- INTERACTIVE CELEBRATION GREETING MODAL (POP-UP SAMBUTAN GURU) -->
    <!-- ============================================================== -->
    <div id="greetingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md transition-opacity duration-300">
        
        <div id="greetingCard" class="relative w-full max-w-lg bg-slate-900 border-2 rounded-3xl p-8 shadow-2xl text-center overflow-hidden flex flex-col items-center animate-scale-up">
            
            <!-- Ambient Lighting Glow in Modal Background -->
            <div id="modalAmbientGlow" class="absolute -top-24 -right-24 w-64 h-64 rounded-full bg-emerald-500/20 blur-3xl pointer-events-none"></div>
            <div id="modalAmbientGlow2" class="absolute -bottom-24 -left-24 w-64 h-64 rounded-full bg-emerald-600/15 blur-3xl pointer-events-none"></div>

            <!-- Close Button (Top Right) -->
            <button type="button" onclick="closeGreetingModal()" aria-label="Tutup sambutan" class="absolute top-4 right-4 p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 active:scale-95 transition-all duration-150 inline-flex items-center justify-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Big Avatar / Icon with Animated Glow -->
            <div id="modalIconContainer" class="relative my-2">
                <div id="modalIconRing" class="w-24 h-24 rounded-3xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center shadow-xl shadow-emerald-500/30 border-4 border-white/20">
                    <svg id="modalIconCheck" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            <!-- Badge Type: Presensi Masuk vs Kepulangan -->
            <div class="mt-4">
                <span id="modalBadgeType" class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest bg-emerald-500/20 border border-emerald-400/40 text-emerald-300">
                    PRESENSI KEDATANGAN TERVERIFIKASI
                </span>
            </div>

            <!-- Greeting Headline -->
            <h2 id="modalGreetingTitle" class="text-3xl sm:text-4xl font-black text-white heading-font mt-3 tracking-tight">
                Selamat Datang!
            </h2>

            <!-- Teacher Name -->
            <div class="my-2 max-w-md">
                <p id="modalTeacherName" class="text-xl sm:text-2xl font-black text-emerald-300 heading-font drop-shadow-sm">
                    Ust. H. Ahmad Dahlan, S.Pd.I
                </p>
                <p id="modalTimeAndStatus" class="text-xs font-mono font-semibold text-slate-300 mt-1">
                    Pukul 06:45:12 WIB &bull; Status: Tepat Waktu
                </p>
            </div>

            <!-- Appreciation / Prayer Submessage -->
            <div class="mt-3 p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80 w-full max-w-sm">
                <p id="modalSubMessage" class="text-xs text-slate-300 leading-relaxed font-medium">
                    Presensi berhasil dicatat. Selamat beraktivitas dan mengajar siswa-siswi MA Ma'arif Cilageni!
                </p>
            </div>

            <!-- Auto-Dismiss Timer Bar -->
            <div class="w-full max-w-xs mt-6">
                <div class="flex items-center justify-between text-[11px] text-slate-400 mb-1.5 font-medium">
                    <span>Otomatis kembali ke layar QR</span>
                    <span id="modalTimerCountdown" class="font-mono text-emerald-400 font-bold">7s</span>
                </div>
                <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                    <div id="modalTimerBar" class="h-full bg-emerald-500 rounded-full transition-all duration-100 ease-linear" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kiosk Auto Refresh, Audio Synthesizer & Polling Script -->
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

        let lastEventId = null;
        let modalDismissTimer = null;
        let modalCountdownInterval = null;

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
        function playGreetingChime(isCheckIn = true) {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const audioCtx = new AudioContext();
                const now = audioCtx.currentTime;

                // Cheerful chord arpeggio for Check-in (C5, E5, G5, C6) or warm cadence for Check-out (G5, E5, C5)
                const notes = isCheckIn ? [523.25, 659.25, 783.99, 1046.50] : [783.99, 659.25, 523.25];
                
                notes.forEach((freq, idx) => {
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, now + idx * 0.12);

                    gain.gain.setValueAtTime(0.2, now + idx * 0.12);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.12 + 0.6);

                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now + idx * 0.12);
                    osc.stop(now + idx * 0.12 + 0.65);
                });
            } catch (err) {
                console.warn("Audio autoplay policy or error:", err);
            }
        }

        // Show Greeting Modal when teacher successfully scans
        function showGreetingModal(event) {
            const isCheckIn = event.type === 'check_in';

            // Configure Colors & Styling
            if (isCheckIn) {
                greetingCard.className = "relative w-full max-w-lg bg-slate-900 border-2 border-emerald-500/80 rounded-3xl p-8 shadow-2xl shadow-emerald-500/25 text-center overflow-hidden flex flex-col items-center animate-scale-up";
                modalIconRing.className = "w-24 h-24 rounded-3xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center shadow-xl shadow-emerald-500/30 border-4 border-white/20";
                modalBadgeType.className = "px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest bg-emerald-500/20 border border-emerald-400/40 text-emerald-300";
                modalBadgeType.textContent = "PRESENSI KEDATANGAN TERVERIFIKASI";
                modalGreetingTitle.textContent = "Selamat Datang!";
                modalGreetingTitle.className = "text-3xl sm:text-4xl font-black text-white heading-font mt-3 tracking-tight";
                modalTeacherName.className = "text-xl sm:text-2xl font-black text-emerald-300 heading-font drop-shadow-sm";
                modalTimerBar.className = "h-full bg-emerald-500 rounded-full transition-all duration-100 ease-linear";
            } else {
                greetingCard.className = "relative w-full max-w-lg bg-slate-900 border-2 border-sky-500/80 rounded-3xl p-8 shadow-2xl shadow-sky-500/25 text-center overflow-hidden flex flex-col items-center animate-scale-up";
                modalIconRing.className = "w-24 h-24 rounded-3xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white flex items-center justify-center shadow-xl shadow-sky-500/30 border-4 border-white/20";
                modalBadgeType.className = "px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest bg-sky-500/20 border border-sky-400/40 text-sky-300";
                modalBadgeType.textContent = "PRESENSI KEPULANGAN TERVERIFIKASI";
                modalGreetingTitle.textContent = "Selamat Pulang!";
                modalGreetingTitle.className = "text-3xl sm:text-4xl font-black text-white heading-font mt-3 tracking-tight";
                modalTeacherName.className = "text-xl sm:text-2xl font-black text-sky-300 heading-font drop-shadow-sm";
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
            let secondsLeft = durationSeconds;
            modalTimerCountdown.textContent = secondsLeft + 's';
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

        // Fetch fresh QR token
        async function fetchFreshQr() {
            try {
                const res = await fetch("{{ route('kiosk.token') }}");
                if (res.ok) {
                    const data = await res.json();
                    qrContainer.innerHTML = data.qr_svg;
                    remainingSeconds = data.remaining_seconds || 20;
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
            const pct = Math.max(0, (remainingSeconds / 20) * 100);
            countdownBar.style.width = pct + '%';
        }

        // Countdown interval for QR renewal (20s)
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
            let html = '';
            items.forEach(att => {
                const isPulang = !!att.check_out_time;
                const initial = att.name.charAt(0);
                const badgeColor = isPulang ? 'bg-sky-500/20 text-sky-300 border-sky-400/30' : 'bg-emerald-500/20 text-emerald-300 border-emerald-400/30';
                const avatarColor = isPulang ? 'bg-sky-500/20 text-sky-400 border-sky-400/30' : 'bg-emerald-500/20 text-emerald-400 border-emerald-400/30';
                const badgeText = isPulang ? 'SUDAH PULANG' : 'HADIR';
                const timeText = isPulang ? `Pulang: ${att.check_out_time} WIB` : `Masuk: ${att.check_in_time} WIB (${att.check_in_status})`;

                html += `
                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-950/60 border border-slate-800/60">
                        <div class="flex items-center space-x-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg ${avatarColor} border font-bold text-xs flex items-center justify-center shrink-0">
                                ${initial}
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-200 truncate">${att.name}</p>
                                <p class="text-[10px] text-slate-400 font-mono">${timeText}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 ${badgeColor} border">
                            ${badgeText}
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
