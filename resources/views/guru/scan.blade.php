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

@section('page-title', 'Pindai QR Presensi')

@section('content')
<div class="max-w-4xl mx-auto space-y-5 pb-10">

    {{-- ── 1. HEADER HALAMAN (Pure Typography, Unboxed) ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/70">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 heading-font tracking-tight leading-tight">
                Pemindai QR Presensi
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Arahkan kamera ke kode QR dinamis di Layar Presensi Madrasah
            </p>
        </div>

        {{-- Status Kehadiran Hari Ini (Clean Editorial, Anti-Pill, Pure Typography & Monospace) --}}
        @if(isset($hasCheckedIn))
            <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap text-xs">
                <div class="inline-flex items-center gap-1.5 font-semibold {{ $hasCheckedIn ? 'text-emerald-700' : 'text-amber-700' }}">
                    <span class="w-2 h-2 rounded-full {{ $hasCheckedIn ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    <span>{{ $hasCheckedIn ? 'Sudah Presensi Masuk' : 'Belum Presensi Masuk' }}</span>
                </div>

                @if(isset($dailyAttendance) && $dailyAttendance?->check_in_time)
                    <span class="text-slate-300">/</span>
                    <span class="mono-font text-slate-600 font-medium">
                        {{ substr($dailyAttendance->check_in_time, 0, 5) }} WIB
                        @if($dailyAttendance->check_out_time)
                            &rarr; {{ substr($dailyAttendance->check_out_time, 0, 5) }} WIB
                        @endif
                    </span>
                @endif

                @if(isset($pendingSchedules) && $pendingSchedules->count() > 0)
                    <span class="text-slate-300">/</span>
                    <span class="mono-font text-amber-700 font-semibold">
                        {{ $pendingSchedules->count() }} Kelas Mengajar Aktif
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- ── 2. KONDISI SUDAH PULANG VS GRID PEMINDAI ── --}}
    @if(isset($dailyAttendance) && $dailyAttendance?->check_out_time)
        {{-- Tampilan saat Guru Sudah Selesai Presensi Pulang --}}
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-xs text-center space-y-5 max-w-2xl mx-auto my-4">
            <i data-lucide="check-circle-2" class="w-12 h-12 sm:w-14 sm:h-14 text-emerald-600 mx-auto"></i>
            
            <div class="space-y-2">
                <h2 class="text-lg sm:text-2xl font-bold text-slate-900 heading-font tracking-tight">
                    Anda Sudah Boleh Pulang
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed max-w-lg mx-auto">
                    Alhamdulillah, seluruh rangkaian tugas mengajar dan presensi harian Bapak/Ibu Guru hari ini telah selesai dan tercatat lengkap di sistem. Silakan beristirahat dan hati-hati di perjalanan.
                </p>
            </div>

            {{-- Ringkasan Waktu Hadir & Pulang --}}
            <div class="grid grid-cols-2 gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-200/70 max-w-md mx-auto text-left text-xs">
                <div>
                    <span class="text-slate-500 font-medium text-[11px] block">Presensi Masuk:</span>
                    <span class="mono-font font-bold text-emerald-700 text-sm mt-0.5 block">
                        {{ substr($dailyAttendance->check_in_time ?? '--:--', 0, 5) }} WIB
                    </span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium text-[11px] block">Presensi Pulang:</span>
                    <span class="mono-font font-bold text-emerald-700 text-sm mt-0.5 block">
                        {{ substr($dailyAttendance->check_out_time, 0, 5) }} WIB
                    </span>
                </div>
            </div>

            <div class="pt-3 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('guru.dashboard') }}"
                    class="w-full sm:w-auto min-h-[44px] px-6 py-3 rounded-2xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white text-xs sm:text-sm font-semibold inline-flex items-center justify-center gap-2 shadow-md shadow-maarif-700/25 transition-all duration-150 active:scale-[0.98] cursor-pointer">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
                <a href="{{ route('guru.history') }}"
                    class="w-full sm:w-auto min-h-[44px] px-6 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 text-xs sm:text-sm font-semibold inline-flex items-center justify-center gap-2 transition-all duration-150 active:scale-[0.98] cursor-pointer">
                    <i data-lucide="history" class="w-4 h-4"></i>
                    <span>Lihat Riwayat Presensi</span>
                </a>
            </div>
        </div>
    @else
        {{-- ── GRID UTAMA PEMINDAI QR (Kamera Viewfinder + Info GPS & Manual Token) ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

            {{-- KOLOM KIRI: Scanner Viewfinder (lg:col-span-7) --}}
            <div class="lg:col-span-7 bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
                
                {{-- Mode Info Banner (Otomatis Masuk/Pulang) --}}
                <div class="flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-2xl bg-slate-50 border border-slate-200/70 text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="relative flex h-2 w-2 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="font-semibold text-slate-800 truncate">Deteksi Mode Otomatis</span>
                    </div>
                    <span class="mono-font text-[11px] text-slate-500 shrink-0 font-medium">
                        {{ $hasCheckedIn ? 'Siap Pulang' : 'Siap Masuk' }}
                    </span>
                </div>

                {{-- Camera Viewfinder Container --}}
                <div class="relative w-full aspect-[4/3] sm:aspect-square max-h-[380px] sm:max-h-[420px] bg-slate-950 rounded-2xl overflow-hidden shadow-inner flex items-center justify-center">
                    <div id="reader" class="absolute inset-0"></div>
                    
                    {{-- Border Rim --}}
                    <div class="pointer-events-none absolute inset-0 ring-1 ring-white/10 rounded-2xl"></div>

                    {{-- Targeting Corner Brackets (Maarif Brand Emerald) --}}
                    <div class="pointer-events-none absolute inset-6 sm:inset-8">
                        <span class="absolute left-0 top-0 w-7 h-7 sm:w-8 sm:h-8 border-l-[3px] border-t-[3px] border-emerald-400 rounded-tl-xl"></span>
                        <span class="absolute right-0 top-0 w-7 h-7 sm:w-8 sm:h-8 border-r-[3px] border-t-[3px] border-emerald-400 rounded-tr-xl"></span>
                        <span class="absolute left-0 bottom-0 w-7 h-7 sm:w-8 sm:h-8 border-l-[3px] border-b-[3px] border-emerald-400 rounded-bl-xl"></span>
                        <span class="absolute right-0 bottom-0 w-7 h-7 sm:w-8 sm:h-8 border-r-[3px] border-b-[3px] border-emerald-400 rounded-br-xl"></span>
                    </div>

                    {{-- Center Reticle --}}
                    <div id="scanReticle" class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center opacity-60">
                        <span class="absolute w-px h-4 bg-white/40 left-1/2 -translate-x-1/2"></span>
                        <span class="absolute h-px w-4 bg-white/40 top-1/2 -translate-y-1/2"></span>
                    </div>

                    {{-- Laser Scan Line --}}
                    <div id="scanLine" class="pointer-events-none absolute left-[15%] right-[15%] top-[18%] h-0.5 bg-gradient-to-r from-transparent via-emerald-400 to-transparent opacity-0"></div>

                    {{-- Inactive Placeholder State --}}
                    <div id="scannerPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-slate-950/95 p-6 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 text-white/70 flex items-center justify-center shadow-inner">
                            <i data-lucide="camera" class="w-7 h-7"></i>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-white heading-font">Kamera Pemindai Belum Aktif</p>
                            <p class="text-xs text-slate-400 max-w-xs">Berikan izin akses kamera dan lokasi untuk memindai QR code</p>
                        </div>
                        <button type="button" onclick="startCamera()"
                            class="mt-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white text-xs sm:text-sm font-semibold shadow-md shadow-maarif-700/30 transition-all duration-150 active:scale-[0.98] cursor-pointer min-h-[44px]">
                            <i data-lucide="video" class="w-4 h-4"></i>
                            <span>Aktifkan Kamera</span>
                        </button>
                    </div>
                </div>

                {{-- Bottom Viewfinder Control Bar --}}
                <div class="flex items-center justify-between gap-3 text-xs pt-1">
                    <span class="text-slate-400 flex items-center gap-1.5">
                        <i data-lucide="scan" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Tahan QR di tengah bingkai</span>
                    </span>
                    <button type="button" onclick="startCamera()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200/80 text-slate-600 hover:text-slate-900 hover:bg-slate-50 active:scale-[0.98] font-semibold text-xs transition-all cursor-pointer">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span>Mulai Ulang Kamera</span>
                    </button>
                </div>
            </div>

            {{-- KOLOM KANAN: Status Geofence & Input Manual (lg:col-span-5) --}}
            <div class="lg:col-span-5 space-y-4">
                
                {{-- 1. Kartu Evaluasi Lokasi GPS / Geofence --}}
                <div id="scanGeofenceCard" class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs space-y-3.5">
                    <div class="flex items-start gap-3.5">
                        <div id="scanGeofenceIconBox" class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5">
                            <i data-lucide="map-pin" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span id="scanGeofenceTitle" class="text-xs font-semibold text-slate-700">Mendeteksi koordinat GPS...</span>
                                <span id="scanGeofenceDistance" class="mono-font text-xs font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">-- m</span>
                            </div>
                            <p id="scanGeofenceDesc" class="text-xs text-slate-400 leading-relaxed">
                                Pastikan GPS aktif & berada di dalam area madrasah.
                            </p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <button type="button" onclick="initScanGeolocation(true)"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200/80 text-slate-700 hover:text-slate-900 hover:bg-slate-50 active:scale-[0.98] text-xs font-semibold transition-all cursor-pointer min-h-[40px]">
                            <svg id="scanGpsRefreshIcon" class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Perbarui Akurasi Lokasi</span>
                        </button>
                    </div>
                </div>

                {{-- 2. Input Manual Kode QR (Fallback Cadangan) --}}
                <details class="group bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <summary class="list-none flex items-center justify-between p-4 sm:p-5 cursor-pointer select-none hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <i data-lucide="key-round" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <span class="text-xs font-semibold text-slate-800">Input Manual Token (Cadangan)</span>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="px-4 pb-5 pt-1 sm:px-5 border-t border-slate-100 space-y-3">
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Gunakan kode teks acak yang tertera di bawah QR Layar Presensi Madrasah jika kamera terkendala.
                        </p>
                        <form id="formManual" onsubmit="submitManualToken(event)" class="flex gap-2">
                            <input type="text" id="manualToken" placeholder="Tempel token QR..." autocomplete="off" inputmode="text"
                                class="flex-1 min-w-0 px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-transparent focus:outline-none mono-font placeholder:text-slate-300 bg-slate-50 focus:bg-white transition-colors">
                            <button type="submit" id="btnSubmitManual"
                                class="shrink-0 px-4 py-2.5 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs shadow-xs transition-all active:scale-[0.98] cursor-pointer min-h-[42px]">
                                <span>Kirim</span>
                            </button>
                        </form>
                    </div>
                </details>

                {{-- 3. Panduan Alur Singkat --}}
                <div class="bg-slate-50 border border-slate-200/70 rounded-3xl p-4 sm:p-5 text-xs text-slate-500 space-y-2">
                    <p class="font-semibold text-slate-700 flex items-center gap-1.5">
                        <i data-lucide="info" class="w-4 h-4 text-slate-400"></i>
                        <span>Informasi Presensi Guru:</span>
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-slate-500 pl-1 leading-relaxed">
                        <li>Scan pertama hari ini otomatis mencatat <strong>Presensi Masuk</strong>.</li>
                        <li>Selesaikan semua kelas mengajar di jadwal harian sebelum melakukan <strong>Presensi Pulang</strong>.</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ── 3. MODAL: Presensi Pulang Tertahan (Kelas Belum Selesai) ── --}}
        <div id="modalLock" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-end sm:items-center justify-center p-4 hidden">
            <div class="w-full max-w-sm bg-white rounded-3xl p-6 shadow-2xl space-y-4 border border-rose-200">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto border border-rose-200/80">
                    <i data-lucide="lock" class="w-6 h-6"></i>
                </div>
                <div class="text-center space-y-1">
                    <h3 class="text-base font-bold text-slate-900 heading-font">Presensi Pulang Tertahan</h3>
                    <p id="modalLockMessage" class="text-xs text-slate-500 leading-relaxed">Masih ada kelas mengajar yang belum disimpan dan ditutup permanen.</p>
                </div>
                <div id="modalLockList" class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 text-xs text-slate-700 space-y-1.5 max-h-36 overflow-y-auto"></div>
                <div class="flex items-center gap-2.5 pt-1">
                    <button type="button" onclick="closeLockModal()"
                        class="flex-1 py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-semibold text-xs transition-all active:scale-[0.98] cursor-pointer min-h-[44px]">
                        Tutup
                    </button>
                    <a href="{{ route('guru.dashboard') }}"
                        class="flex-1 py-3 px-4 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs inline-flex items-center justify-center gap-2 shadow-sm transition-all active:scale-[0.98] cursor-pointer min-h-[44px]">
                        <span>Ke Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    @endif
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
            if (scanGeofenceIconBox) scanGeofenceIconBox.className = "w-10 h-10 rounded-xl bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5";
            if (scanGeofenceTitle) { scanGeofenceTitle.textContent = "Di Lingkungan Madrasah"; scanGeofenceTitle.className = "text-xs font-semibold text-emerald-700"; }
            if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Radius " + (data.radius || 75) + " m — lokasi terverifikasi siap presensi.";
            if (scanGeofenceDistance) { scanGeofenceDistance.textContent = Math.round(data.distance || 0) + " m"; scanGeofenceDistance.className = "mono-font text-xs font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/80"; }
        } else {
            if (scanGeofenceIconBox) scanGeofenceIconBox.className = "w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5";
            if (scanGeofenceTitle) { scanGeofenceTitle.textContent = "Di Luar Area Madrasah"; scanGeofenceTitle.className = "text-xs font-semibold text-rose-700"; }
            if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Berada di luar " + (data.radius || 75) + " m dari madrasah. Presensi ditolak.";
            if (scanGeofenceDistance) { scanGeofenceDistance.textContent = Math.round(data.distance || 0) + " m"; scanGeofenceDistance.className = "mono-font text-xs font-bold px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200/80"; }
        }
        if (window.lucide) window.lucide.createIcons();
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
        const readerEl = document.getElementById('reader');
        if (!readerEl) return;
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
