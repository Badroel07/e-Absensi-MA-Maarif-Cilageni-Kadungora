@php
    $isSimulated = \App\Services\TimeSimulatorService::isSimulated();
    $currentAppTime = \Carbon\Carbon::now();
    $activeSchool = \App\Models\SchoolLocation::getActiveLocation();
    $schoolLat = $activeSchool?->latitude ?? -7.1147;
    $schoolLng = $activeSchool?->longitude ?? 107.8845;
    $schoolRadius = $activeSchool?->radius_meters ?? 75;
@endphp

<!-- Developer Controls Root Container (Local Environment Only) -->
<div id="dev-toolbar-root" class="select-none">
    <style>
        #dev-toolbar-root {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #e4e4e7;
        }
        #dev-toolbar-root .mono {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-feature-settings: 'tnum', 'zero';
        }
        /* Floating Trigger Pill */
        #dev-pill-trigger {
            position: fixed;
            bottom: 5.5rem; /* above mobile nav */
            right: 1rem;
            z-index: 9999;
            background: #09090b !important;
            border: 1px solid #27272a;
            color: #e4e4e7;
            padding: 7px 13px;
            border-radius: 9999px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.05);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        @media (min-width: 768px) {
            #dev-pill-trigger {
                bottom: 1.25rem;
                right: 1.25rem;
            }
        }
        #dev-pill-trigger:hover {
            background: #18181b !important;
            border-color: #3f3f46;
            transform: translateY(-1px);
        }
        #dev-pill-trigger:active {
            transform: scale(0.97);
        }

        /* Modal Overlay & Card */
        #dev-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.72);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.18s ease, visibility 0.18s ease;
        }
        #dev-modal-overlay.open {
            opacity: 1;
            visibility: visible;
        }
        #dev-modal-card {
            background: #121316 !important;
            border: 1px solid #27272a;
            border-radius: 18px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 25px 60px -15px rgba(0,0,0,0.9), 0 0 0 1px rgba(255,255,255,0.06);
            overflow: hidden;
            transform: scale(0.96) translateY(8px);
            transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        }
        #dev-modal-overlay.open #dev-modal-card {
            transform: scale(1) translateY(0);
        }

        /* Inner Sections */
        .dev-section {
            background: #18191e;
            border: 1px solid #23252d;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 12px;
        }
        .dev-section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #a1a1aa;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Segmented Button Group */
        .dev-segmented {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 4px;
            background: #0f1013;
            padding: 4px;
            border-radius: 10px;
            border: 1px solid #23252d;
        }
        .dev-seg-btn {
            background: transparent;
            border: 1px solid transparent;
            color: #a1a1aa;
            padding: 7px 6px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.12s ease;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        .dev-seg-btn:hover {
            color: #ffffff;
            background: rgba(255,255,255,0.04);
        }
        /* Active States */
        .dev-seg-btn.active-inside {
            background: #15803d !important;
            border-color: #22c55e !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(34,197,94,0.3);
        }
        .dev-seg-btn.active-outside {
            background: #b91c1c !important;
            border-color: #ef4444 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(239,68,68,0.3);
        }
        .dev-seg-btn.active-real {
            background: #0369a1 !important;
            border-color: #38bdf8 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(56,189,248,0.3);
        }

        /* Time Presets */
        .dev-time-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 8px;
        }
        .dev-time-btn {
            background: #0f1013;
            border: 1px solid #27272a;
            color: #f4f4f5;
            padding: 6px 4px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            width: 100%;
            transition: all 0.12s ease;
        }
        .dev-time-btn:hover {
            background: #27272a;
            border-color: #3f3f46;
            color: #ffffff;
        }

        /* Custom Input Row */
        .dev-input-row {
            display: flex;
            gap: 6px;
        }
        .dev-input-dt {
            flex: 1;
            background: #0f1013;
            border: 1px solid #27272a;
            border-radius: 8px;
            color: #ffffff;
            padding: 6px 8px;
            font-size: 11px;
            outline: none;
            color-scheme: dark;
        }
        .dev-input-dt:focus {
            border-color: #3b82f6;
        }
        .dev-btn-submit {
            background: #2563eb;
            border: 1px solid #3b82f6;
            color: #ffffff;
            font-weight: 600;
            font-size: 11px;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.12s ease;
        }
        .dev-btn-submit:hover {
            background: #1d4ed8;
        }
    </style>

    <!-- Trigger Button -->
    <button type="button" onclick="openDevModal()" id="dev-pill-trigger" title="Buka Kontrol Developer">
        <span style="display:flex; align-items:center; gap:5px;">
            <span style="width:6px; height:6px; border-radius:9999px; background:{{ $isSimulated ? '#fbbf24' : '#71717a' }};"></span>
            <span id="dev-pill-time" class="mono" style="font-size:11px; font-weight:600; color:#f4f4f5;">{{ $currentAppTime->format('H:i') }}</span>
        </span>
        <span style="color:#52525b; font-size:10px;">•</span>
        <span style="display:flex; align-items:center; gap:5px;">
            <span id="dev-pill-loc-dot" style="width:6px; height:6px; border-radius:9999px; background:#22c55e;"></span>
            <span id="dev-pill-loc-label" style="font-size:11px; font-weight:600; color:#f4f4f5;">Di Dalam</span>
        </span>
        <i data-lucide="sliders-horizontal" style="width:12px; height:12px; color:#a1a1aa; margin-left:2px;"></i>
    </button>

    <!-- Modal Backdrop & Dialog -->
    <div id="dev-modal-overlay" onclick="handleDevOverlayClick(event)">
        <div id="dev-modal-card">
            <!-- Modal Header -->
            <div style="padding: 14px 16px; border-bottom: 1px solid #23252d; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 7px; height: 7px; border-radius: 9999px; background: #22c55e;"></div>
                    <span style="font-weight: 700; font-size: 13px; color: #ffffff; letter-spacing: -0.01em;">Dev Control</span>
                    <span style="background: #23252d; color: #a1a1aa; padding: 2px 6px; border-radius: 5px; font-size: 9px; font-weight: 700; letter-spacing: 0.05em;" class="mono">LOCAL</span>
                </div>
                <button type="button" onclick="closeDevModal()" style="background: transparent; border: none; color: #71717a; cursor: pointer; padding: 4px; border-radius: 6px; display: flex;" aria-label="Tutup">
                    <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                </button>
            </div>

            <div style="padding: 14px 16px;">
                <!-- 1. GEOFENCE GPS TOGGLE -->
                <div class="dev-section">
                    <div class="dev-section-title">
                        <span>Lokasi Geofence</span>
                        <span id="dev-modal-status-badge" class="mono" style="color: #22c55e; font-size: 10px;">±8m (Lolos)</span>
                    </div>

                    <!-- 3-Way Segmented Control -->
                    <div class="dev-segmented">
                        <button type="button" id="seg-btn-inside" onclick="setDevLocationMode('inside')" class="dev-seg-btn active-inside">
                            <i data-lucide="map-pin-check" style="width: 12px; height: 12px;"></i>
                            Di Dalam
                        </button>
                        <button type="button" id="seg-btn-outside" onclick="setDevLocationMode('outside')" class="dev-seg-btn">
                            <i data-lucide="map-pin-off" style="width: 12px; height: 12px;"></i>
                            Di Luar
                        </button>
                        <button type="button" id="seg-btn-real" onclick="setDevLocationMode('real')" class="dev-seg-btn">
                            <i data-lucide="navigation" style="width: 12px; height: 12px;"></i>
                            GPS Asli
                        </button>
                    </div>
                </div>

                <!-- 2. TIME SIMULATION SECTION -->
                <div class="dev-section" style="margin-bottom: 0;">
                    <div class="dev-section-title">
                        <span>Waktu Sistem</span>
                        <span id="dev-modal-clock-ticker" class="mono" style="color: #f4f4f5; font-weight: 700; font-size: 11px;">{{ $currentAppTime->format('H:i:s') }} WIB</span>
                    </div>

                    <!-- Preset Buttons -->
                    <div class="dev-time-grid">
                        <form action="{{ route('dev.time-simulator') }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="jump">
                            <input type="hidden" name="time" value="07:00">
                            <button type="submit" class="dev-time-btn mono" title="07:00 Pagi">07:00</button>
                        </form>

                        <form action="{{ route('dev.time-simulator') }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="jump">
                            <input type="hidden" name="time" value="07:30">
                            <button type="submit" class="dev-time-btn mono" title="07:30 Masuk Mapel">07:30</button>
                        </form>

                        <form action="{{ route('dev.time-simulator') }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="minutes" value="5">
                            <button type="submit" class="dev-time-btn mono" title="Maju 5 Menit">+5m</button>
                        </form>

                        <form action="{{ route('dev.time-simulator') }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="jump">
                            <input type="hidden" name="time" value="14:00">
                            <button type="submit" class="dev-time-btn mono" title="14:00 Pulang">14:00</button>
                        </form>
                    </div>

                    <!-- Custom Datetime Input -->
                    <form action="{{ route('dev.time-simulator') }}" method="POST" class="dev-input-row">
                        @csrf
                        <input type="hidden" name="action" value="custom">
                        <input type="datetime-local" name="datetime" value="{{ $currentAppTime->format('Y-m-d\TH:i') }}" class="dev-input-dt mono">
                        <button type="submit" class="dev-btn-submit">Set</button>
                    </form>

                    @if($isSimulated)
                        <form action="{{ route('dev.time-simulator') }}" method="POST" style="margin-top: 10px; text-align: center;">
                            @csrf
                            <input type="hidden" name="action" value="reset">
                            <button type="submit" style="background: transparent; border: none; color: #f87171; font-size: 11px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; padding: 2px 6px;">
                                <i data-lucide="rotate-ccw" style="width: 11px; height: 11px;"></i>
                                Reset ke Waktu Nyata
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const DEV_SCHOOL_COORDS = {
        lat: {{ (float) $schoolLat }},
        lng: {{ (float) $schoolLng }}
    };

    // Global simulated coords provider
    window.getDevSimulatedCoords = function() {
        const mode = localStorage.getItem('dev_mock_location_mode') || 'inside';
        if (mode === 'inside') {
            return {
                lat: DEV_SCHOOL_COORDS.lat - 0.00005,
                lng: DEV_SCHOOL_COORDS.lng + 0.00005,
                mode: 'inside'
            };
        } else if (mode === 'outside') {
            return {
                lat: DEV_SCHOOL_COORDS.lat - 0.035,
                lng: DEV_SCHOOL_COORDS.lng + 0.032,
                mode: 'outside'
            };
        }
        return null;
    };

    // Geolocation Interceptor for local dev environment
    (function() {
        if (typeof window === 'undefined' || !navigator.geolocation) return;

        const origGetPos = navigator.geolocation.getCurrentPosition.bind(navigator.geolocation);
        const origWatchPos = navigator.geolocation.watchPosition.bind(navigator.geolocation);

        navigator.geolocation.getCurrentPosition = function(success, error, options) {
            const sim = window.getDevSimulatedCoords ? window.getDevSimulatedCoords() : null;
            if (sim) {
                success({
                    coords: {
                        latitude: sim.lat,
                        longitude: sim.lng,
                        accuracy: 10,
                        altitude: null,
                        altitudeAccuracy: null,
                        heading: null,
                        speed: null
                    },
                    timestamp: Date.now()
                });
                return;
            }
            return origGetPos(success, error, options);
        };

        navigator.geolocation.watchPosition = function(success, error, options) {
            const sim = window.getDevSimulatedCoords ? window.getDevSimulatedCoords() : null;
            if (sim) {
                success({
                    coords: {
                        latitude: sim.lat,
                        longitude: sim.lng,
                        accuracy: 10
                    },
                    timestamp: Date.now()
                });
                return 999999;
            }
            return origWatchPos(success, error, options);
        };
    })();

    // Modal Visibility Functions
    function openDevModal() {
        const overlay = document.getElementById('dev-modal-overlay');
        if (overlay) {
            overlay.classList.add('open');
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    }

    function closeDevModal() {
        const overlay = document.getElementById('dev-modal-overlay');
        if (overlay) {
            overlay.classList.remove('open');
        }
    }

    function handleDevOverlayClick(e) {
        if (e.target.id === 'dev-modal-overlay') {
            closeDevModal();
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDevModal();
        }
    });

    // Location Mode Switcher
    function setDevLocationMode(mode) {
        localStorage.setItem('dev_mock_location_mode', mode);
        try {
            sessionStorage.removeItem('maarif_geo_cache');
        } catch (e) {}
        updateDevLocationUI();

        // Dispatch reactive event for dashboards
        window.dispatchEvent(new CustomEvent('dev-location-changed', { detail: { mode } }));

        if (typeof window.initGeolocation === 'function') {
            window.initGeolocation(true);
        }
        if (typeof window.initScanGeolocation === 'function') {
            window.initScanGeolocation(true);
        }
    }

    function updateDevLocationUI() {
        const mode = localStorage.getItem('dev_mock_location_mode') || 'inside';
        const pillDot = document.getElementById('dev-pill-loc-dot');
        const pillLabel = document.getElementById('dev-pill-loc-label');
        const statusBadge = document.getElementById('dev-modal-status-badge');

        const btnInside = document.getElementById('seg-btn-inside');
        const btnOutside = document.getElementById('seg-btn-outside');
        const btnReal = document.getElementById('seg-btn-real');

        if (!btnInside || !btnOutside || !btnReal) return;

        btnInside.className = 'dev-seg-btn';
        btnOutside.className = 'dev-seg-btn';
        btnReal.className = 'dev-seg-btn';

        if (mode === 'inside') {
            btnInside.className = 'dev-seg-btn active-inside';
            if (pillDot) pillDot.style.background = '#22c55e';
            if (pillLabel) pillLabel.textContent = 'Di Dalam';
            if (statusBadge) {
                statusBadge.textContent = '±8m (Lolos)';
                statusBadge.style.color = '#22c55e';
            }
        } else if (mode === 'outside') {
            btnOutside.className = 'dev-seg-btn active-outside';
            if (pillDot) pillDot.style.background = '#ef4444';
            if (pillLabel) pillLabel.textContent = 'Di Luar';
            if (statusBadge) {
                statusBadge.textContent = '±5.2km (Terkunci)';
                statusBadge.style.color = '#ef4444';
            }
        } else {
            btnReal.className = 'dev-seg-btn active-real';
            if (pillDot) pillDot.style.background = '#38bdf8';
            if (pillLabel) pillLabel.textContent = 'GPS Asli';
            if (statusBadge) {
                statusBadge.textContent = 'Sensor Real';
                statusBadge.style.color = '#38bdf8';
            }
        }

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // Ticker Updater
    function updateDevSimTicker() {
        const now = (typeof window.getServerNow === 'function') ? window.getServerNow() : new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');

        const pillClock = document.getElementById('dev-pill-time');
        if (pillClock) pillClock.textContent = `${h}:${m}`;

        const modalClock = document.getElementById('dev-modal-clock-ticker');
        if (modalClock) modalClock.textContent = `${h}:${m}:${s} WIB`;
    }

    setInterval(updateDevSimTicker, 1000);
    updateDevSimTicker();

    window.addEventListener('DOMContentLoaded', () => {
        updateDevLocationUI();
    });
</script>
