@extends('layouts.app')

@section('title', 'Pindai QR Presensi — MA Ma\'arif Cilageni')
@section('page-title', 'Pindai QR Presensi')

@push('styles')
<style>
    #reader {
        width: 100% !important;
        height: 100% !important;
        position: absolute !important;
        inset: 0 !important;
        overflow: hidden !important;
        border-radius: 1rem !important;
    }
    #reader > div {
        width: 100% !important;
        height: 100% !important;
        position: relative !important;
    }
    #reader video {
        width: 100% !important;
        height: 100% !important;
        min-width: 100% !important;
        min-height: 100% !important;
        max-width: none !important;
        object-fit: cover !important;
        border-radius: 1rem !important;
        display: block !important;
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
    }
    #reader canvas, #qr-canvas, #reader__scan_region, #qr-shaded-region {
        display: none !important;
    }
    #reader div[style*="Scanner paused"] {
        display: none !important;
    }
    @keyframes scanLineMove {
        0% { transform: translateY(0); opacity: 0.8; }
        50% { transform: translateY(180px); opacity: 0.3; }
        100% { transform: translateY(0); opacity: 0.8; }
    }
    .scanner-beam { animation: scanLineMove 2.8s ease-in-out infinite; }
    @media(prefers-reduced-motion:reduce){ .scanner-beam{animation:none!important} }
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Header Intro -->
    <section class="space-y-1.5" data-purpose="header-intro">
        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight heading-font">Pemindai QR Presensi</h2>
        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
            Arahkan kamera ke kode QR dinamis di Layar Presensi Madrasah
        </p>
        @if(isset($hasCheckedIn) && isset($dailyAttendance))
            <p class="text-xs sm:text-sm font-medium text-emerald-700 pt-0.5 flex items-center gap-1.5">
                <span>{{ $hasCheckedIn ? 'Sudah Presensi Masuk' : 'Belum Presensi Masuk' }}</span>
                @if($dailyAttendance?->check_in_time)
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-600 font-semibold mono-font">
                        {{ substr($dailyAttendance->check_in_time, 0, 5) }} WIB
                        @if($dailyAttendance?->check_out_time)
                            → {{ substr($dailyAttendance->check_out_time, 0, 5) }} WIB
                        @endif
                    </span>
                @endif
            </p>
        @endif
    </section>

    {{-- Sudah Pulang State --}}
    @if(isset($dailyAttendance) && $dailyAttendance?->check_out_time)
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-sm text-center space-y-5 max-w-2xl mx-auto">
            <svg class="w-12 h-12 text-emerald-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
            </svg>
            <div class="space-y-2">
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 heading-font">Anda Sudah Boleh Pulang</h2>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed max-w-lg mx-auto">
                    Seluruh rangkaian tugas mengajar dan presensi harian telah selesai. Silakan beristirahat dan hati-hati di perjalanan.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-200/70 text-left text-xs">
                <div><span class="text-slate-500 block text-[11px]">Presensi Masuk:</span><span class="mono-font font-bold text-emerald-700 text-sm block">{{ substr($dailyAttendance->check_in_time ?? '--:--', 0, 5) }} WIB</span></div>
                <div><span class="text-slate-500 block text-[11px]">Presensi Pulang:</span><span class="mono-font font-bold text-emerald-700 text-sm block">{{ substr($dailyAttendance->check_out_time, 0, 5) }} WIB</span></div>
            </div>
            <div class="pt-3 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('guru.dashboard') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 12l2-2m2 2l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>
    @else
        <!-- Responsive 2-column layout for tablet & desktop -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Column: Scanner Card (7 columns on desktop) -->
            <div class="lg:col-span-7 space-y-4">
                <section class="bg-white rounded-3xl p-4 sm:p-5 shadow-sm border border-slate-200/70 space-y-4" data-purpose="qr-scanner-card">
                    <!-- Header Info Mode Presensi -->
                    <div class="flex items-center justify-between text-xs sm:text-sm px-1">
                        <span class="font-semibold text-slate-700 heading-font">Deteksi Mode Otomatis</span>
                        <span class="font-bold text-emerald-700">{{ $hasCheckedIn ? 'Siap Pulang' : 'Siap Masuk' }}</span>
                    </div>

                    <!-- Viewfinder Viewport -->
                    <div class="relative w-full aspect-[4/3] rounded-2xl overflow-hidden bg-slate-900 shadow-inner flex items-center justify-center">
                        <div id="reader" class="absolute inset-0 flex items-stretch [&_video]:w-full [&_video]:h-full [&_video]:object-cover [&_canvas]:hidden" style="position: absolute;"></div>
                        <div class="pointer-events-none absolute inset-0 ring-1 ring-white/10 rounded-2xl"></div>

                        <!-- Animated Scanning Beam -->
                        <div id="scanLine" class="absolute inset-x-8 top-10 h-0.5 bg-gradient-to-r from-transparent via-emerald-400 to-transparent scanner-beam z-10 opacity-0"></div>

                        <!-- Outer Target Reticle -->
                        <div class="relative w-52 sm:w-64 h-44 sm:h-52 z-10 flex items-center justify-center pointer-events-none">
                            <div class="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-emerald-400 rounded-tl-xl"></div>
                            <div class="absolute top-0 right-0 w-8 h-8 border-t-2 border-r-2 border-emerald-400 rounded-tr-xl"></div>
                            <div class="absolute bottom-0 left-0 w-8 h-8 border-b-2 border-l-2 border-emerald-400 rounded-bl-xl"></div>
                            <div class="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-emerald-400 rounded-br-xl"></div>
                            <!-- Inner Focus Reticle -->
                            <div id="scanReticle" class="w-32 sm:w-40 h-24 sm:h-28 border border-white/60 rounded-lg flex items-center justify-center bg-white/5 backdrop-blur-[1px]">
                                <svg class="w-6 h-6 text-white/70" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <line x1="12" x2="12" y1="8" y2="16"></line>
                                    <line x1="8" x2="16" y1="12" y2="12"></line>
                                </svg>
                            </div>
                        </div>

                        <!-- Subtle bottom guide overlay -->
                        <div class="absolute bottom-2.5 inset-x-0 text-center z-10">
                            <span class="text-[11px] sm:text-xs text-white/75 font-medium tracking-wide bg-black/40 px-3 py-1 rounded-full backdrop-blur-sm">
                                Sejajarkan QR di dalam bingkai
                            </span>
                        </div>

                        <!-- Inactive Placeholder State -->
                        <div id="scannerPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-slate-950/95 p-6 text-center z-20">
                            <div class="w-14 h-14 rounded-2xl bg-white/10 text-white/70 flex items-center justify-center">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path><circle cx="12" cy="13" r="4"></circle></svg>
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-white heading-font">Kamera Pemindai Belum Aktif</p>
                                <p class="text-xs text-slate-400 max-w-xs">Berikan izin akses kamera dan lokasi untuk memindai QR</p>
                            </div>
                            <button type="button" onclick="startCamera()" class="mt-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white text-xs font-semibold shadow-md transition active:scale-[0.98] min-h-[44px] cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="23 7 13 7 8 3 1 3 1 17 23 17"></polygon><circle cx="12" cy="13" r="4"></circle></svg>
                                <span>Aktifkan Kamera</span>
                            </button>
                        </div>
                    </div>

                    <!-- Camera Controls & Instruction Bar -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                        <div class="flex items-center gap-2 text-slate-500 pl-1">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                                <rect height="18" rx="2" ry="2" width="18" x="3" y="3"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <span class="text-xs sm:text-sm text-slate-600 font-medium">Tahan QR di tengah bingkai</span>
                        </div>
                        <button aria-label="Mulai ulang kamera" onclick="startCamera()" class="flex items-center gap-1.5 px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 active:scale-95 border border-slate-200 rounded-xl transition-all shadow-xs cursor-pointer" type="button">
                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                            </svg>
                            <span>Mulai Ulang Kamera</span>
                        </button>
                    </div>
                </section>
            </div>

            <!-- Right Column: Verification & Fallbacks (5 columns on desktop) -->
            <div class="lg:col-span-5 space-y-4">
                <!-- Location Verification Card -->
                <section id="scanGeofenceCard" class="bg-white rounded-3xl p-4 sm:p-5 shadow-sm border border-slate-200/70 space-y-3.5" data-purpose="location-card">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div id="scanGeofenceIconBox" class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div class="space-y-0.5">
                                <h3 id="scanGeofenceTitle" class="text-sm sm:text-base font-bold text-slate-900 leading-tight heading-font">Di Lingkungan Madrasah</h3>
                                <p id="scanGeofenceDesc" class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                                    Radius {{ $location->radius_meters ?? 75 }} m — lokasi terverifikasi siap presensi.
                                </p>
                            </div>
                        </div>
                        <div id="scanGeofenceDistance" class="text-xs sm:text-sm font-bold text-emerald-700 bg-emerald-50/80 px-2.5 py-1 rounded-lg border border-emerald-200/60 shrink-0 mono-font heading-font">
                            -- m
                        </div>
                    </div>
                    <button type="button" onclick="initScanGeolocation(true)" class="w-full py-2.5 px-4 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 flex items-center justify-center gap-2 active:scale-[0.99] transition-all shadow-xs cursor-pointer">
                        <svg id="scanGpsRefreshIcon" class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                            <path d="M3 3v5h5"></path>
                            <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"></path>
                            <path d="M16 21h5v-5"></path>
                        </svg>
                        <span>Perbarui Akurasi Lokasi</span>
                    </button>
                </section>

                <!-- Manual Token Accordion -->
                <section data-purpose="manual-token-section">
                    <details class="group bg-white rounded-2xl shadow-sm border border-slate-200/70 overflow-hidden">
                        <summary class="w-full p-4 flex items-center justify-between text-left cursor-pointer list-none">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center group-open:text-emerald-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                                    </svg>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-800">Input Manual Token (Cadangan)</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </summary>
                        <div class="px-4 pb-4 pt-2 border-t border-slate-100 space-y-3">
                            <p class="text-xs text-slate-500 leading-relaxed">Gunakan kode teks di bawah QR Layar Madrasah jika kamera terkendala.</p>
                            <form id="formManual" onsubmit="submitManualToken(event)" class="flex gap-2">
                                <input type="text" id="manualToken" placeholder="Tempel token QR..." autocomplete="off" class="flex-1 min-w-0 px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-600 focus:outline-none mono-font placeholder:text-slate-300 bg-slate-50 focus:bg-white transition">
                                <button type="submit" id="btnSubmitManual" class="shrink-0 px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs shadow-sm min-h-[42px] cursor-pointer">Kirim</button>
                            </form>
                        </div>
                    </details>
                </section>

                <!-- Guidance Notes Card -->
                <section class="bg-slate-50/90 rounded-3xl p-4 sm:p-5 border border-slate-200/60 space-y-2.5" data-purpose="attendance-info">
                    <div class="flex items-center gap-2 text-slate-800">
                        <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" x2="12" y1="16" y2="12"></line>
                            <line x1="12" x2="12.01" y1="8" y2="8"></line>
                        </svg>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 heading-font">Informasi Presensi Guru</h4>
                    </div>
                    <ul class="text-[11px] sm:text-xs text-slate-600 space-y-1.5 pl-6 list-disc leading-relaxed marker:text-slate-400">
                        <li>Scan pertama hari ini otomatis mencatat <strong class="text-slate-800 font-semibold">Presensi Masuk</strong>.</li>
                        <li>Selesaikan semua kelas mengajar di jadwal harian sebelum melakukan <strong class="text-slate-800 font-semibold">Presensi Pulang</strong>.</li>
                    </ul>
                </section>
            </div>
        </div>

        <!-- Modal: Presensi Pulang Tertahan -->
        <div id="modalLock" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-end sm:items-center justify-center p-4 hidden">
            <div class="w-full max-w-sm bg-white rounded-3xl p-6 shadow-2xl space-y-4 border border-rose-200">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto border border-rose-200/80">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                </div>
                <div class="text-center space-y-1">
                    <h3 class="text-base font-bold text-slate-900 heading-font">Presensi Pulang Tertahan</h3>
                    <p id="modalLockMessage" class="text-xs text-slate-500 leading-relaxed">Masih ada kelas yang belum disimpan.</p>
                </div>
                <div id="modalLockList" class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 text-xs text-slate-700 space-y-1.5 max-h-36 overflow-y-auto"></div>
                <div class="flex items-center gap-2.5 pt-1">
                    <button type="button" onclick="closeLockModal()" class="flex-1 py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs min-h-[44px] cursor-pointer">Tutup</button>
                    <a href="{{ route('guru.dashboard') }}" class="flex-1 py-3 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs inline-flex items-center justify-center gap-2 min-h-[44px]">Ke Dashboard</a>
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
    const GEO_CACHE_TTL = 120000;

    function getCachedGeofence() {
        try {
            const raw = sessionStorage.getItem(GEO_CACHE_KEY);
            if (!raw) return null;
            const data = JSON.parse(raw);
            if (Date.now() - data.timestamp < GEO_CACHE_TTL) return data;
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
            if (scanGeofenceIconBox) scanGeofenceIconBox.className = "w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100";
            if (scanGeofenceTitle) { scanGeofenceTitle.textContent = "Di Lingkungan Madrasah"; scanGeofenceTitle.className = "text-sm sm:text-base font-bold text-slate-900 heading-font leading-tight"; }
            if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Radius " + (data.radius || 75) + " m — lokasi terverifikasi siap presensi.";
            if (scanGeofenceDistance) { scanGeofenceDistance.textContent = Math.round(data.distance || 0) + " m"; scanGeofenceDistance.className = "text-xs sm:text-sm font-bold text-emerald-700 bg-emerald-50/80 px-2.5 py-1 rounded-lg border border-emerald-200/60 shrink-0 mono-font heading-font"; }
        } else {
            if (scanGeofenceIconBox) scanGeofenceIconBox.className = "w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100";
            if (scanGeofenceTitle) { scanGeofenceTitle.textContent = "Di Luar Area Madrasah"; scanGeofenceTitle.className = "text-sm sm:text-base font-bold text-rose-900 heading-font leading-tight"; }
            if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Berada di luar " + (data.radius || 75) + " m dari madrasah. Presensi ditolak.";
            if (scanGeofenceDistance) { scanGeofenceDistance.textContent = Math.round(data.distance || 0) + " m"; scanGeofenceDistance.className = "text-xs sm:text-sm font-bold text-rose-600 bg-rose-50/80 px-2.5 py-1 rounded-lg border border-rose-100 shrink-0 mono-font heading-font"; }
        }
    }

    function initScanGeolocation(isManual = false) {
        const now = Date.now();
        if (!isManual && (now - lastScanGeoAttempt < 1500)) return;
        lastScanGeoAttempt = now;

        const refreshIcon = document.getElementById('scanGpsRefreshIcon');
        if (isManual && refreshIcon) {
            refreshIcon.classList.add('animate-spin');
            setTimeout(() => refreshIcon.classList.remove('animate-spin'), 1500);
        }

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
                    if (document.visibilityState !== 'hidden') initScanGeolocation(false);
                }, delay);
            }
        };

        const handleScanError = (err) => {
            if (err.code === 1) {
                if (scanGeofenceTitle) { scanGeofenceTitle.textContent = "Izin lokasi ditolak"; scanGeofenceTitle.className = "text-sm sm:text-base font-bold text-rose-700 heading-font leading-tight"; }
                if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Izinkan akses lokasi pada peramban.";
                if (scanGeofenceDistance) scanGeofenceDistance.textContent = "-- m";
            } else if (err.code === 2) {
                if (scanGeofenceTitle) { scanGeofenceTitle.textContent = "Mencari sinyal GPS..."; scanGeofenceTitle.className = "text-sm sm:text-base font-bold text-amber-700 heading-font leading-tight"; }
                if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Aktifkan GPS & tunggu sinyal terkunci.";
                if (scanGeofenceDistance) scanGeofenceDistance.textContent = "-- m";
                scheduleScanRetry(3500);
            } else if (err.code === 3) {
                if (!userCoords.lat) {
                    if (scanGeofenceTitle) { scanGeofenceTitle.textContent = "Mencari lokasi GPS..."; scanGeofenceTitle.className = "text-sm sm:text-base font-bold text-amber-700 heading-font leading-tight"; }
                    if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Menghubungkan ke satelit...";
                    scheduleScanRetry(3000);
                }
            }
        };

        if (activeScanWatchId !== null) {
            navigator.geolocation.clearWatch(activeScanWatchId);
            activeScanWatchId = null;
        }

        navigator.geolocation.getCurrentPosition(handleScanPosition, () => {}, { enableHighAccuracy: false, timeout: 6000, maximumAge: 60000 });
        try {
            activeScanWatchId = navigator.geolocation.watchPosition(handleScanPosition, handleScanError, { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 });
        } catch (e) {
            console.warn("Gagal memulai watchPosition:", e);
        }
    }

    function setScanLineActive(active){ const line=document.getElementById('scanLine'); if(!line) return; if(active){line.classList.remove('opacity-0');line.classList.add('scanner-beam')} else {line.classList.add('opacity-0');line.classList.remove('scanner-beam')} }

    function stopScannerCleanly() {
        setScanLineActive(false);
        if (window.html5QrCode) {
            try {
                if (window.html5QrCode.isScanning) window.html5QrCode.stop().catch(() => {});
                if (typeof window.html5QrCode.clear === 'function') window.html5QrCode.clear();
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
                const config = { fps: 15, qrbox: (viewfinderWidth, viewfinderHeight) => {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    const qrboxSize = Math.floor(minEdge * 0.75);
                    return { width: qrboxSize, height: qrboxSize };
                }};
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
                            message: 'Tidak dapat mengakses kamera: ' + errMsg + '. Pastikan izin kamera telah diberikan.',
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
                            message: 'Modul pemindai QR memerlukan koneksi internet untuk memuat komponen.',
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

    if (document.readyState === 'loading') {
        window.addEventListener('DOMContentLoaded', initScanView, { once: true });
    } else {
        initScanView();
    }

    const onVisibilityChange = () => {
        if (document.visibilityState === 'visible') initScanGeolocation(false);
    };
    const onFocus = () => initScanGeolocation(false);
    const onDevLocationChanged = () => initScanGeolocation(true);

    document.addEventListener('visibilitychange', onVisibilityChange);
    window.addEventListener('focus', onFocus);
    window.addEventListener('dev-location-changed', onDevLocationChanged);

    if (navigator.permissions && navigator.permissions.query) {
        navigator.permissions.query({ name: 'geolocation' }).then((permissionStatus) => {
            permissionStatus.onchange = () => {
                if (permissionStatus.state === 'granted') initScanGeolocation(true);
            };
        }).catch(() => {});
    }

    window.initScanGeolocation = initScanGeolocation;
    window.startCamera = startCamera;
    window.submitManualToken = submitManualToken;
    window.closeLockModal = closeLockModal;

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
