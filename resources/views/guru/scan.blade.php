@extends('layouts.app')

@section('title', 'Pindai QR Presensi — MA Ma\'arif Cilageni')

@push('styles')
<style>
    #reader video { border-radius: 0.5rem; object-fit: cover; width:100%; height:100%; }
    #reader { width:100%; height:100%; }
    @keyframes scanSweep { 0%{transform:translateY(-100%);opacity:0}12%{opacity:1}88%{opacity:1}100%{transform:translateY(340%);opacity:0} }
    .scan-line { animation: scanSweep 2.2s cubic-bezier(.4,0,.2,1) infinite; }
    @media (prefers-reduced-motion: reduce){ .scan-line{animation:none!important} }
</style>
@endpush

@section('content')
<div class="max-w-[860px] mx-auto space-y-4">

    {{-- Header: sederhana --}}
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 heading-font tracking-tight leading-none">Pemindai QR Presensi</h1>
        <p class="text-xs text-slate-500 mt-1">Arahkan kamera ke QR di Layar Presensi Madrasah.</p>
    </div>

    {{-- Status presensi hari ini --}}
    @if(isset($hasCheckedIn))
    <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-xs font-semibold {{ $hasCheckedIn ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-50 border-amber-200 text-amber-800' }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $hasCheckedIn ? 'bg-emerald-500' : 'bg-amber-400' }}"></span>
            {{ $hasCheckedIn ? 'Sudah masuk' : 'Belum masuk' }}
        </span>
        @if(isset($pendingSchedules) && $pendingSchedules->count() > 0)
            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-600 text-xs font-semibold">{{ $pendingSchedules->count() }} kelas belum disimpan</span>
        @endif
        @if(isset($dailyAttendance) && $dailyAttendance)
            <span class="mono-font text-xs text-slate-400">{{ substr($dailyAttendance->check_in_time ?? '--:--',0,5) }} WIB @if($dailyAttendance->check_out_time) &middot; {{ substr($dailyAttendance->check_out_time,0,5) }} WIB @endif</span>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4 items-start">

        {{-- Kartu utama: mode toggle + viewfinder --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">

            {{-- Auto mode — no manual toggle --}}
            <div class="px-4 pt-4 pb-3">
                <div class="flex items-center gap-2 text-xs font-semibold text-emerald-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Mode Otomatis — Masuk / Pulang menyesuaikan sendiri
                </div>
                <p class="text-xs text-slate-400 mt-1">Scan pertama hari ini = Masuk. Scan setelah semua kelas selesai = Pulang. Tidak perlu pilih manual.</p>
            </div>

            <div class="border-t border-slate-100 mx-4"></div>

            {{-- Viewfinder --}}
            <div class="p-4">
                <div class="relative aspect-square w-full bg-slate-900 rounded-xl overflow-hidden">
                    <div id="reader" class="absolute inset-0"></div>
                    <div class="pointer-events-none absolute inset-0 rounded-xl ring-1 ring-white/10"></div>
                    {{-- Maarif-green corner brackets --}}
                    <div class="pointer-events-none absolute inset-4">
                        <span class="absolute left-0 top-0 w-8 h-8 border-l-[3px] border-t-[3px] border-maarif-500 rounded-tl-lg"></span>
                        <span class="absolute right-0 top-0 w-8 h-8 border-r-[3px] border-t-[3px] border-maarif-500 rounded-tr-lg"></span>
                        <span class="absolute left-0 bottom-0 w-8 h-8 border-l-[3px] border-b-[3px] border-maarif-500 rounded-bl-lg"></span>
                        <span class="absolute right-0 bottom-0 w-8 h-8 border-r-[3px] border-b-[3px] border-maarif-500 rounded-br-lg"></span>
                    </div>
                    <div id="scanReticle" class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[52%] h-[52%] flex items-center justify-center">
                        <span class="absolute w-px h-3 bg-white/25 left-1/2 -translate-x-1/2"></span>
                        <span class="absolute h-px w-3 bg-white/25 top-1/2 -translate-y-1/2"></span>
                    </div>
                    <div id="scanLine" class="pointer-events-none absolute left-[12%] right-[12%] top-[14%] h-px bg-gradient-to-r from-transparent via-maarif-500 to-transparent opacity-0"></div>
                    {{-- Placeholder saat kamera belum aktif --}}
                    <div id="scannerPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-slate-900">
                        <div class="w-14 h-14 rounded-xl bg-white/10 flex items-center justify-center text-white/60">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="text-center px-6">
                            <p class="text-sm font-semibold text-white heading-font">Kamera belum aktif</p>
                            <p class="text-xs text-slate-400 mt-1">Izinkan akses kamera &amp; lokasi</p>
                        </div>
                        <button type="button" onclick="startCamera()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white text-sm font-semibold shadow-md shadow-maarif-700/40 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 min-h-[44px]">
                            Aktifkan Kamera
                        </button>
                    </div>
                </div>
                {{-- Bawah viewfinder --}}
                <div class="mt-3 flex items-center justify-between gap-3">
                    <span class="text-xs text-slate-400">Tahan QR di dalam bingkai 1–2 detik</span>
                    <button type="button" onclick="startCamera()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition-all active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600 min-h-[34px]">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Mulai ulang
                    </button>
                </div>
            </div>
        </div>

        {{-- Kolom kanan: GPS + manual --}}
        <div class="space-y-3">

            {{-- GPS / Verifikasi Posisi --}}
            <div id="scanGeofenceCard" class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-4 flex gap-3 items-start">
                    <div id="scanGeofenceIconBox" class="w-9 h-9 rounded-xl bg-slate-800 text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span id="scanGeofenceTitle" class="text-xs font-semibold text-slate-700">Mendeteksi lokasi...</span>
                            <span id="scanGeofenceDistance" class="mono-font text-xs px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500">-- m</span>
                        </div>
                        <p id="scanGeofenceDesc" class="text-xs text-slate-400 leading-relaxed mt-0.5">Izinkan GPS di peramban.</p>
                    </div>
                </div>
                <div class="px-4 pb-4">
                    <button type="button" onclick="initScanGeolocation(true)"
                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition-all active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 min-h-[38px]">
                        <svg id="scanGpsRefreshIcon" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Perbarui GPS
                    </button>
                </div>
            </div>

            {{-- Kode manual (cadangan) --}}
            <details class="group bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <summary class="list-none flex items-center justify-between px-4 py-3 cursor-pointer select-none hover:bg-slate-50 transition-colors">
                    <span class="text-xs font-semibold text-slate-700">Kode manual (cadangan)</span>
                    <svg class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="px-4 pb-4 pt-1 border-t border-slate-100">
                    <p class="text-xs text-slate-400 mb-3">Salin kode dari Layar Presensi. Token berganti tiap 20 detik.</p>
                    <form id="formManual" onsubmit="submitManualToken(event)" class="flex gap-2">
                        <input type="text" id="manualToken" placeholder="Tempel kode..." autocomplete="off" inputmode="text"
                            class="flex-1 min-w-0 px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none mono-font placeholder:text-slate-300 bg-slate-50 focus:bg-white transition-colors">
                        <button type="submit" id="btnSubmitManual"
                            class="shrink-0 px-4 py-2.5 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs shadow-sm transition-all active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600 min-h-[42px]">
                            Kirim
                        </button>
                    </form>
                </div>
            </details>

        </div>
    </div>

    {{-- Modal: presensi pulang tertahan --}}
    <div id="modalLock" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-end sm:items-center justify-center p-4 hidden">
        <div class="w-full max-w-sm bg-white rounded-2xl p-6 shadow-xl space-y-4 border border-rose-200">
            <div class="w-11 h-11 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center mx-auto">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div class="text-center">
                <h3 class="text-base font-semibold text-slate-900 heading-font">Presensi pulang tertahan</h3>
                <p id="modalLockMessage" class="text-xs text-slate-500 mt-1.5 leading-relaxed">Masih ada kelas mengajar yang belum dicatat kehadirannya.</p>
            </div>
            <div id="modalLockList" class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 space-y-1 max-h-36 overflow-y-auto"></div>
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="closeLockModal()"
                    class="flex-1 py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-semibold text-xs transition-all active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 min-h-[44px]">
                    Tutup
                </button>
                <a href="{{ route('guru.dashboard') }}"
                    class="flex-1 py-3 px-4 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs inline-flex items-center justify-center gap-2 shadow-sm transition-all active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600 min-h-[44px]">
                    Ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
(function() {
    'use strict';

    const GEO_CACHE_KEY = 'maarif_geo_cache';
    const GEO_CACHE_TTL = 120000; // 2 minutes

    function getCachedGeofence() {
        try {
            const raw = sessionStorage.getItem(GEO_CACHE_KEY);
            if (!raw) return null;
            const data = JSON.parse(raw);
            if (Date.now() - data.timestamp < GEO_CACHE_TTL) {
                return data;
            }
            sessionStorage.removeItem(GEO_CACHE_KEY);
        } catch(e) {}
        return null;
    }

    function setCachedGeofence(coords, serverData) {
        try {
            sessionStorage.setItem(GEO_CACHE_KEY, JSON.stringify({
                coords: { lat: coords.lat, lng: coords.lng },
                serverData: serverData,
                timestamp: Date.now()
            }));
        } catch(e) {}
    }

    window.html5QrCode = window.html5QrCode || null;
    let userCoords = { lat: null, lng: null };
    let isWithinGeofence = null;
    let geofenceDistance = null;
    let activeScanWatchId = null;
    let scanGpsRetryTimeout = null;
    let lastScanGeoAttempt = 0;

    const scanGeofenceIconBox = document.getElementById('scanGeofenceIconBox');
    const scanGeofenceTitle = document.getElementById('scanGeofenceTitle');
    const scanGeofenceDistance = document.getElementById('scanGeofenceDistance');
    const scanGeofenceDesc = document.getElementById('scanGeofenceDesc');

    function renderScanGeofence(data) {
        isWithinGeofence = (data.is_within_geofence !== undefined) ? data.is_within_geofence : Boolean(data.within);
        geofenceDistance = data.distance;
        if (isWithinGeofence) {
            if (scanGeofenceIconBox) scanGeofenceIconBox.className = "w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0";
            if (scanGeofenceTitle) { scanGeofenceTitle.textContent = "Di lingkungan madrasah"; scanGeofenceTitle.className = "text-xs font-semibold text-emerald-700"; }
            if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Radius " + (data.radius || 75) + " m — siap presensi.";
            if (scanGeofenceDistance) { scanGeofenceDistance.textContent = Math.round(data.distance || 0) + " m"; scanGeofenceDistance.className = "mono-font text-xs px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700"; }
        } else {
            if (scanGeofenceIconBox) scanGeofenceIconBox.className = "w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0";
            if (scanGeofenceTitle) { scanGeofenceTitle.textContent = "Di luar area madrasah"; scanGeofenceTitle.className = "text-xs font-semibold text-rose-700"; }
            if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Di luar " + (data.radius || 75) + " m — presensi ditolak.";
            if (scanGeofenceDistance) { scanGeofenceDistance.textContent = Math.round(data.distance || 0) + " m"; scanGeofenceDistance.className = "mono-font text-xs px-1.5 py-0.5 rounded-md bg-rose-50 text-rose-700"; }
        }
    }

    function initScanGeolocation(isManual = false) {
        const now = Date.now();
        if (!isManual && (now - lastScanGeoAttempt < 1500)) {
            return;
        }
        lastScanGeoAttempt = now;

        const refreshIcon = document.getElementById('scanGpsRefreshIcon');
        if (isManual && refreshIcon) {
            refreshIcon.classList.add('animate-spin');
            setTimeout(() => refreshIcon.classList.remove('animate-spin'), 1500);
        }

        // Check sessionStorage cache for instant display
        const cached = getCachedGeofence();
        if (cached && cached.coords && cached.coords.lat) {
            userCoords.lat = cached.coords.lat;
            userCoords.lng = cached.coords.lng;
            renderScanGeofence(cached.serverData);
        }

        if (!navigator.geolocation) {
            if (scanGeofenceTitle) scanGeofenceTitle.textContent = "Geolocation tidak didukung";
            if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Peramban tidak mendukung akses lokasi.";
            return;
        }

        if (scanGpsRetryTimeout) {
            clearTimeout(scanGpsRetryTimeout);
            scanGpsRetryTimeout = null;
        }

        if (!cached) {
            isWithinGeofence = null;
            if (scanGeofenceTitle && !userCoords.lat) scanGeofenceTitle.textContent = "Mendeteksi lokasi...";
            if (scanGeofenceDesc && !userCoords.lat) scanGeofenceDesc.textContent = "Sedang mencari sinyal GPS...";
        }

        const handleScanPosition = async (pos) => {
            if (scanGpsRetryTimeout) {
                clearTimeout(scanGpsRetryTimeout);
                scanGpsRetryTimeout = null;
            }
            userCoords.lat = pos.coords.latitude;
            userCoords.lng = pos.coords.longitude;
            try {
                const res = await fetch("{{ route('guru.check-status', [], false) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ latitude: userCoords.lat, longitude: userCoords.lng })
                });
                const data = await res.json();
                setCachedGeofence(userCoords, data);
                renderScanGeofence(data);
            } catch (e) {
                console.warn("Gagal cek geofence:", e);
            }
        };

        const scheduleScanRetry = (delay = 3500) => {
            if (!scanGpsRetryTimeout && (!userCoords.lat || !userCoords.lng)) {
                scanGpsRetryTimeout = setTimeout(() => {
                    scanGpsRetryTimeout = null;
                    if (document.visibilityState !== 'hidden') {
                        initScanGeolocation(false);
                    }
                }, delay);
            }
        };

        const handleScanError = (err) => {
            if (err.code === 1) { // PERMISSION_DENIED
                if (scanGeofenceTitle) { scanGeofenceTitle.textContent = "Izin lokasi ditolak"; scanGeofenceTitle.className = "text-xs font-semibold text-rose-700"; }
                if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Izinkan akses lokasi pada peramban.";
                if (scanGeofenceDistance) scanGeofenceDistance.textContent = "-- m";
            } else if (err.code === 2) { // POSITION_UNAVAILABLE
                if (scanGeofenceTitle) { scanGeofenceTitle.textContent = "Mencari sinyal GPS..."; scanGeofenceTitle.className = "text-xs font-semibold text-amber-700"; }
                if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Aktifkan GPS & tunggu sinyal terkunci.";
                if (scanGeofenceDistance) scanGeofenceDistance.textContent = "-- m";
                scheduleScanRetry(3500);
            } else if (err.code === 3) { // TIMEOUT
                if (!userCoords.lat) {
                    if (scanGeofenceTitle) { scanGeofenceTitle.textContent = "Mencari lokasi GPS..."; scanGeofenceTitle.className = "text-xs font-semibold text-amber-700"; }
                    if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Menghubungkan ke satelit...";
                    scheduleScanRetry(3000);
                }
            }
        };

        if (activeScanWatchId !== null) {
            navigator.geolocation.clearWatch(activeScanWatchId);
            activeScanWatchId = null;
        }

        // Tier 1: Fast initial fix (allow cached coordinates up to 60s for instant scanning)
        navigator.geolocation.getCurrentPosition(
            handleScanPosition,
            () => {},
            { enableHighAccuracy: false, timeout: 6000, maximumAge: 60000 }
        );

        // Tier 2: Continuous high-accuracy refinement
        try {
            activeScanWatchId = navigator.geolocation.watchPosition(
                handleScanPosition,
                handleScanError,
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 }
            );
        } catch (e) {
            console.warn("Gagal memulai watchPosition:", e);
        }
    }

    function setScanLineActive(active){ const line=document.getElementById('scanLine'); if(!line) return; if(active){line.classList.remove('opacity-0');line.classList.add('scan-line')} else {line.classList.add('opacity-0');line.classList.remove('scan-line')} }

    function stopScannerCleanly() {
        setScanLineActive(false);
        if (window.html5QrCode) {
            try {
                if (window.html5QrCode.isScanning) {
                    window.html5QrCode.stop().catch(() => {});
                }
                if (typeof window.html5QrCode.clear === 'function') {
                    window.html5QrCode.clear();
                }
            } catch(e) {}
            window.html5QrCode = null;
        }
        if (activeScanWatchId !== null) {
            navigator.geolocation.clearWatch(activeScanWatchId);
            activeScanWatchId = null;
        }
        if (scanGpsRetryTimeout) {
            clearTimeout(scanGpsRetryTimeout);
            scanGpsRetryTimeout = null;
        }
    }

    function startCamera() {
        const placeholder = document.getElementById('scannerPlaceholder');
        if (placeholder) placeholder.classList.add('hidden');
        setScanLineActive(true);

        const launchScanner = () => {
            const readerEl = document.getElementById('reader');
            if (!readerEl) return;

            // If an active scanner instance already exists, clear it first
            if (window.html5QrCode) {
                try {
                    if (window.html5QrCode.isScanning) {
                        window.html5QrCode.stop().catch(() => {}).finally(() => {
                            try { window.html5QrCode.clear(); } catch(e) {}
                            window.html5QrCode = null;
                            mountNewScanner();
                        });
                        return;
                    } else {
                        try { window.html5QrCode.clear(); } catch(e) {}
                        window.html5QrCode = null;
                    }
                } catch(e) {
                    window.html5QrCode = null;
                }
            }
            mountNewScanner();
        };

        const mountNewScanner = () => {
            const readerEl = document.getElementById('reader');
            if (!readerEl) return;

            try {
                window.html5QrCode = new Html5Qrcode("reader");
                const config = { fps: 10, qrbox: { width: 250, height: 250 } };
                window.html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    (decodedText) => {
                        setScanLineActive(false);
                        stopScannerCleanly();
                        sendScanToken(decodedText);
                    },
                    () => {}
                ).catch(async (err) => {
                    setScanLineActive(false);
                    if (placeholder) placeholder.classList.remove('hidden');
                    const errMsg = (err && err.message) ? err.message : String(err);
                    if (typeof window.showAlertDialog === 'function') {
                        await window.showAlertDialog({
                            title: 'Akses Kamera Gagal',
                            message: 'Tidak dapat mengakses kamera: ' + errMsg + '. Pastikan izin kamera telah diberikan pada peramban.',
                            type: 'danger',
                            icon: 'alert-triangle'
                        });
                    } else {
                        alert("Tidak dapat mengakses kamera: " + errMsg);
                    }
                });
            } catch(err) {
                setScanLineActive(false);
                if (placeholder) placeholder.classList.remove('hidden');
                console.error('[Scanner] Inisialisasi gagal:', err);
            }
        };

        if (typeof Html5Qrcode === 'undefined') {
            let waitTries = 0;
            const waitTimer = setInterval(() => {
                waitTries++;
                if (typeof Html5Qrcode !== 'undefined') {
                    clearInterval(waitTimer);
                    launchScanner();
                } else if (waitTries > 30) {
                    clearInterval(waitTimer);
                    setScanLineActive(false);
                    if (placeholder) placeholder.classList.remove('hidden');
                    if (typeof window.showAlertDialog === 'function') {
                        window.showAlertDialog({
                            title: 'Pustaka Kamera Belum Siap',
                            message: 'Modul pemindai QR memerlukan koneksi internet untuk memuat komponen. Silakan segarkan halaman atau periksa jaringan Anda.',
                            type: 'danger',
                            icon: 'alert-circle'
                        });
                    }
                }
            }, 100);
        } else {
            launchScanner();
        }
    }

    async function sendScanToken(token){
        window.triggerHaptic([100,50,100]);
        if(!userCoords.lat || !userCoords.lng || isWithinGeofence !== true){
            if(typeof window.showAlertDialog==='function'){
                await window.showAlertDialog({
                    title: (isWithinGeofence === false) ? 'Di Luar Area Madrasah' : 'Lokasi GPS Belum Terkunci',
                    message: (isWithinGeofence === false)
                        ? "Presensi ditolak. Posisi Anda "+Math.round(geofenceDistance||0)+" m dari madrasah — wajib di dalam area."
                        : "Sistem sedang mencari posisi GPS. Pastikan GPS aktif dan tunggu hingga lokasi terverifikasi.",
                    type: (isWithinGeofence === false) ? 'danger' : 'warning',
                    icon: (isWithinGeofence === false) ? 'map-pin' : 'alert-triangle'
                });
            } else {
                alert((isWithinGeofence === false) ? 'Di luar area madrasah.' : 'Lokasi GPS belum terkunci.');
            }
            startCamera();
            return;
        }
        const endpoint="{{ route('guru.auto', [], false) }}";
        let _csrfRetry = false;
        const doFetch = async () => {
            const res=await fetch(endpoint,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:JSON.stringify({qr_token:token.trim(),latitude:userCoords.lat,longitude:userCoords.lng}), credentials:'same-origin'});
            const data=await res.json().catch(()=>({}));
            return {res, data};
        };
        try{
            let {res, data} = await doFetch();
            // Auto-handle CSRF mismatch (stale token after long idle) — refresh meta and retry once
            if(res.status===419 && data.code==='CSRF_MISMATCH' && !_csrfRetry){
                _csrfRetry = true;
                try{
                    const fresh = await fetch(window.location.href, {headers:{'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin'});
                    const html = await fresh.text();
                    const m = html.match(/name="_token"\s+value="([^"]+)"|name="csrf-token"\s+content="([^"]+)"/);
                    const newToken = m ? (m[1]||m[2]) : null;
                    if(newToken){
                        document.querySelectorAll('meta[name="csrf-token"]').forEach(m=>m.content=newToken);
                        document.querySelectorAll('input[name="_token"]').forEach(i=>i.value=newToken);
                    }
                }catch(e){}
                const retry = await doFetch();
                res = retry.res; data = retry.data;
            }
            if(res.ok&&data.success){
                stopScannerCleanly();
                if(typeof window.showAlertDialog==='function'){
                    await window.showAlertDialog({title:'Presensi Berhasil',message:data.message,type:'success',confirmText:'Lanjutkan',icon:'check-circle-2'});
                } else alert(data.message);
                if (window.MaarifSPA && typeof window.MaarifSPA.navigate === 'function') {
                    window.MaarifSPA.navigate("{{ route('guru.dashboard') }}");
                } else {
                    window.location.href="{{ route('guru.dashboard') }}";
                }
            } else {
                if(data.code==='TEACHING_COMPLETION_LOCKED') showLockModal(data);
                else if(data.code==='ALREADY_CHECKED_IN' || data.code==='ALREADY_CHECKED_OUT'){
                    if(typeof window.showAlertDialog==='function'){
                        await window.showAlertDialog({title:'Sudah Tercatat',message:data.message,type:'warning',icon:'check-circle-2'});
                    } else alert(data.message);
                } else if(data.code==='CSRF_MISMATCH'){
                    if(typeof window.showAlertDialog==='function'){
                        await window.showAlertDialog({title:'Sesi Diperbarui',message:'Sesi keamanan diperbarui. Halaman akan dimuat ulang.',type:'warning',icon:'refresh-cw'});
                    }
                    window.location.reload();
                    return;
                } else {
                    if(typeof window.showAlertDialog==='function'){
                        await window.showAlertDialog({title:'Presensi Gagal',message:data.message||'Kode QR tidak valid atau sudah berganti. Pindai ulang.',type:'danger',icon:'alert-circle'});
                    } else alert("Gagal: "+(data.message||'Kode QR tidak valid.'));
                    startCamera();
                }
            }
        }catch(err){
            if(typeof window.showAlertDialog==='function'){
                await window.showAlertDialog({title:'Kesalahan Jaringan',message:'Terjadi kesalahan jaringan: '+err,type:'danger',icon:'alert-circle'});
            } else alert("Kesalahan jaringan: "+err);
            startCamera();
        }
    }

    function submitManualToken(e){
        e.preventDefault();
        const token=document.getElementById('manualToken').value;
        if(!token) return;
        if(!userCoords.lat || !userCoords.lng || isWithinGeofence !== true){
            if(typeof window.showAlertDialog==='function'){
                window.showAlertDialog({
                    title: (isWithinGeofence === false) ? 'Di Luar Area Madrasah' : 'Lokasi GPS Belum Terkunci',
                    message: (isWithinGeofence === false)
                        ? "Presensi ditolak. Posisi Anda "+Math.round(geofenceDistance||0)+" m dari madrasah — wajib di dalam area."
                        : "Sistem sedang mencari posisi GPS. Pastikan GPS aktif dan tunggu hingga lokasi terverifikasi.",
                    type: (isWithinGeofence === false) ? 'danger' : 'warning',
                    icon: (isWithinGeofence === false) ? 'map-pin' : 'alert-triangle'
                });
            } else {
                alert((isWithinGeofence === false) ? 'Di luar area madrasah.' : 'Lokasi GPS belum terkunci.');
            }
            return;
        }
        document.getElementById('scannerPlaceholder').classList.add('hidden');
        setScanLineActive(false);
        stopScannerCleanly();
        sendScanToken(token);
    }

    function showLockModal(data){
        document.getElementById('modalLockMessage').textContent=data.message;
        const listEl=document.getElementById('modalLockList');
        listEl.innerHTML='';
        if(data.pending_schedules) data.pending_schedules.forEach(item=>{
            const p=document.createElement('p');
            p.className='font-semibold text-rose-700';
            p.textContent='• '+item;
            listEl.appendChild(p);
        });
        document.getElementById('modalLock').classList.remove('hidden');
    }

    function closeLockModal() {
        const m = document.getElementById('modalLock');
        if (m) m.classList.add('hidden');
    }

    function initScanView() {
        initScanGeolocation();
        startCamera();
    }

    // Initialize immediately if DOM is already ready (SPA navigation), or wait for DOMContentLoaded (initial load)
    if (document.readyState === 'loading') {
        window.addEventListener('DOMContentLoaded', initScanView, { once: true });
    } else {
        initScanView();
    }

    // Auto re-acquire GPS when user returns to scanner
    const onVisibilityChange = () => {
        if (document.visibilityState === 'visible') {
            initScanGeolocation(false);
        }
    };
    const onFocus = () => {
        initScanGeolocation(false);
    };
    const onDevLocationChanged = () => {
        initScanGeolocation(true);
    };

    document.addEventListener('visibilitychange', onVisibilityChange);
    window.addEventListener('focus', onFocus);
    window.addEventListener('dev-location-changed', onDevLocationChanged);

    // Permissions API: immediately re-run when user grants permission in browser prompt
    if (navigator.permissions && navigator.permissions.query) {
        navigator.permissions.query({ name: 'geolocation' }).then((permissionStatus) => {
            permissionStatus.onchange = () => {
                if (permissionStatus.state === 'granted') {
                    initScanGeolocation(true);
                }
            };
        }).catch(() => {});
    }

    // Export functions to window for inline HTML handlers
    window.initScanGeolocation = initScanGeolocation;
    window.startCamera = startCamera;
    window.submitManualToken = submitManualToken;
    window.closeLockModal = closeLockModal;

    // Clean up scanner and GPS listeners on SPA unload
    const cleanupScan = () => {
        stopScannerCleanly();
        document.removeEventListener('visibilitychange', onVisibilityChange);
        window.removeEventListener('focus', onFocus);
        window.removeEventListener('dev-location-changed', onDevLocationChanged);
    };

    if (window.MaarifSPA && typeof window.MaarifSPA.onPageUnload === 'function') {
        window.MaarifSPA.onPageUnload(cleanupScan);
    }
    window.addEventListener('app:before-page-unload', cleanupScan, { once: true });
})();
</script>
@endpush
