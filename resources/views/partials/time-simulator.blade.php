@php
    $isSimulated = \App\Services\TimeSimulatorService::isSimulated();
    $currentAppTime = \Carbon\Carbon::now();
@endphp

<!-- Floating Time Simulator Tool (Local Dev Only) — offset increased to avoid covering completion lock card -->
<div id="time-simulator-widget" class="fixed bottom-28 md:bottom-6 right-4 z-40 font-sans text-xs select-none">
    <!-- Trigger Pill Button -->
    <button type="button" onclick="toggleTimeSimulatorModal()" 
        class="inline-flex items-center space-x-2 px-3.5 py-2 rounded-full shadow-lg border transition-all duration-150 active:scale-95 cursor-pointer backdrop-blur-md focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 {{ $isSimulated ? 'bg-amber-500/95 hover:bg-amber-600 text-slate-950 border-amber-300 font-bold animate-pulse focus-visible:ring-amber-500' : 'bg-slate-900/85 hover:bg-slate-900 text-slate-200 border-slate-700 font-medium focus-visible:ring-slate-400' }}">
        <span class="w-2 h-2 rounded-full {{ $isSimulated ? 'bg-rose-600' : 'bg-emerald-400' }}"></span>
        <span class="inline-flex items-center space-x-1.5">
            @if($isSimulated)
                <i data-lucide="zap" class="w-3.5 h-3.5 text-slate-950"></i>
                <span>Simulasi:</span>
            @else
                <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-300"></i>
            @endif
            <span id="time-sim-pill-clock">{{ $currentAppTime->format('H:i') }}</span>
        </span>
        <i data-lucide="chevron-up" class="w-3.5 h-3.5 opacity-70"></i>
    </button>

    <!-- Modal Dialog Popover -->
    <div id="time-simulator-modal" class="hidden absolute bottom-12 right-0 w-80 bg-slate-900/95 backdrop-blur-xl border border-slate-700 text-white rounded-2xl shadow-2xl p-4 transition-all">
        <div class="flex items-center justify-between border-b border-slate-800 pb-2.5 mb-3">
            <div class="flex items-center space-x-2">
                <i data-lucide="timer" class="w-5 h-5 text-amber-400"></i>
                <div>
                    <h4 class="font-bold text-white text-xs leading-none">Simulasi Waktu Madrasah</h4>
                    <span class="text-[10px] text-slate-400">Uji Coba Waktu Jam Pelajaran</span>
                </div>
            </div>
            <button type="button" onclick="toggleTimeSimulatorModal()" class="text-slate-400 hover:text-white p-1 rounded-lg transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400" aria-label="Tutup simulasi">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Current Active Time Card -->
        <div class="p-2.5 rounded-xl {{ $isSimulated ? 'bg-amber-500/10 border border-amber-500/30 text-amber-200' : 'bg-slate-800/80 border border-slate-700 text-slate-300' }} mb-3">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Waktu Aplikasi Aktif:</div>
            <div class="text-base font-black font-mono mt-0.5 text-white">
                {{ $currentAppTime->translatedFormat('l, d M Y') }} &bull; <span id="time-sim-active-clock">{{ $currentAppTime->format('H:i:s') }}</span>
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">
                Status: <span class="font-semibold {{ $isSimulated ? 'text-amber-400' : 'text-emerald-400' }}">{{ $isSimulated ? 'Tergembok ke Waktu Tiruan' : 'Sinkron dengan Waktu Nyata' }}</span>
            </div>
        </div>

        <!-- Quick Jump Buttons -->
        <div class="space-y-1.5 mb-3">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Preset Jam Sekolah:</div>
            <div class="grid grid-cols-2 gap-1.5">
                <form action="{{ route('dev.time-simulator') }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="jump">
                    <input type="hidden" name="time" value="07:00">
                    <button type="submit" class="w-full text-left px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 active:bg-slate-900 text-slate-200 border border-slate-700/60 transition-all duration-150 active:scale-[0.98] text-[11px] font-semibold flex items-center justify-between cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500">
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="sunrise" class="w-3.5 h-3.5 text-amber-400"></i> 07:00</span>
                        <span class="text-[9px] text-slate-400">Presensi Masuk</span>
                    </button>
                </form>

                <form action="{{ route('dev.time-simulator') }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="jump">
                    <input type="hidden" name="time" value="07:30">
                    <button type="submit" class="w-full text-left px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 active:bg-slate-900 text-slate-200 border border-slate-700/60 transition-all duration-150 active:scale-[0.98] text-[11px] font-semibold flex items-center justify-between cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500">
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="book-open" class="w-3.5 h-3.5 text-blue-400"></i> 07:30</span>
                        <span class="text-[9px] text-slate-400">Mapel 1</span>
                    </button>
                </form>

                <form action="{{ route('dev.time-simulator') }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="minutes" value="3">
                    <button type="submit" class="w-full text-left px-2.5 py-1.5 rounded-lg bg-amber-950/40 hover:bg-amber-900/50 active:bg-amber-900 text-amber-200 border border-amber-800/40 transition-all duration-150 active:scale-[0.98] text-[11px] font-semibold flex items-center justify-between cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="hourglass" class="w-3.5 h-3.5 text-amber-300"></i> +3 Menit</span>
                        <span class="text-[9px] text-amber-300">PIN Berakhir</span>
                    </button>
                </form>

                <form action="{{ route('dev.time-simulator') }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="jump">
                    <input type="hidden" name="time" value="14:00">
                    <button type="submit" class="w-full text-left px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 active:bg-slate-900 text-slate-200 border border-slate-700/60 transition-all duration-150 active:scale-[0.98] text-[11px] font-semibold flex items-center justify-between cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500">
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="home" class="w-3.5 h-3.5 text-emerald-400"></i> 14:00</span>
                        <span class="text-[9px] text-slate-400">Presensi Pulang</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Custom Datetime Input -->
        <form action="{{ route('dev.time-simulator') }}" method="POST" class="mb-3">
            @csrf
            <input type="hidden" name="action" value="custom">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Setel Tanggal & Jam Bebas:</div>
            <div class="flex items-center space-x-1.5">
                <input type="datetime-local" name="datetime" value="{{ $currentAppTime->format('Y-m-d\TH:i') }}"
                    class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-2 py-1 text-[11px] text-white focus:outline-none focus:border-amber-400">
                <button type="submit" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-slate-950 font-bold rounded-lg text-[11px] transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                    Set
                </button>
            </div>
        </form>

        <!-- Reset Button -->
        @if($isSimulated)
            <form action="{{ route('dev.time-simulator') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="reset">
                <button type="submit" class="w-full py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold text-[11px] flex items-center justify-center space-x-1.5 shadow-md shadow-rose-600/25 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Kembalikan ke Waktu Nyata</span>
                </button>
            </form>
        @endif
    </div>
</div>

<script>
    function toggleTimeSimulatorModal() {
        const modal = document.getElementById('time-simulator-modal');
        if (modal) {
            modal.classList.toggle('hidden');
            if (!modal.classList.contains('hidden') && typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    }

    function updateSimulatorTicker() {
        const now = (typeof window.getServerNow === 'function') ? window.getServerNow() : new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');

        const pillClock = document.getElementById('time-sim-pill-clock');
        if (pillClock) {
            pillClock.textContent = `${h}:${m}`;
        }

        const activeClock = document.getElementById('time-sim-active-clock');
        if (activeClock) {
            activeClock.textContent = `${h}:${m}:${s}`;
        }
    }
    setInterval(updateSimulatorTicker, 1000);
    updateSimulatorTicker();
</script>
