@extends('layouts.app')

@section('title', 'Pindai QR Presensi — MA Ma\'arif Cilageni')

@push('styles')
<style>
    #reader video {
        border-radius: 1rem;
        object-fit: cover;
    }
</style>
@endpush

@section('content')
<div class="max-w-xl mx-auto space-y-4">
    <!-- Header Title -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
        <h1 class="text-lg sm:text-xl font-black text-slate-900 heading-font tracking-tight">Pemindai QR Presensi</h1>
        <p class="text-xs text-slate-500 mt-0.5">Arahkan kamera HP ke Layar Presensi Madrasah</p>
    </div>

    <!-- GPS Geofence Status Pod -->
    <div id="scanGeofenceCard" class="rounded-2xl bg-slate-900 text-white p-3.5 sm:p-4 border border-slate-800 shadow-md flex items-center justify-between gap-3 text-xs transition-all">
        <div class="flex items-center gap-3 min-w-0 flex-1">
            <div id="scanGeofenceIconBox" class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 text-white flex items-center justify-center shrink-0 shadow-xs transition-all">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <span id="scanGeofenceTitle" class="font-bold text-slate-100 uppercase tracking-wider text-xs heading-font">
                        Mendeteksi Lokasi GPS...
                    </span>
                    <span id="scanGeofenceDistance" class="font-mono font-black text-xs px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 shadow-xs border border-slate-700">
                        -- m
                    </span>
                </div>
                <p id="scanGeofenceDesc" class="text-[11px] text-slate-400 mt-0.5 leading-relaxed truncate">
                    Harap izinkan akses lokasi (GPS) pada peramban HP Anda.
                </p>
            </div>
        </div>
        <button type="button" onclick="initScanGeolocation(true)" class="p-2 sm:px-3 sm:py-2 rounded-xl bg-slate-800 hover:bg-slate-700 active:bg-slate-600 text-slate-200 text-xs font-bold border border-slate-700 shadow-xs transition-all active:scale-95 cursor-pointer shrink-0 inline-flex items-center gap-1.5" title="Perbarui Lokasi GPS">
            <svg id="scanGpsRefreshIcon" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="hidden sm:inline">Perbarui</span>
        </button>
    </div>

    <!-- Mode Selector: Datang vs Pulang -->
    <div class="bg-white p-1.5 rounded-2xl border border-slate-200 flex items-center shadow-xs">
        <button type="button" id="btnModeDatang" onclick="setMode('datang')"
            class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all duration-150 flex items-center justify-center gap-1.5 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white shadow-xs active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
            <span>Presensi Masuk</span>
        </button>
        <button type="button" id="btnModePulang" onclick="setMode('pulang')"
            class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all duration-150 flex items-center justify-center gap-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
            <span>Presensi Pulang</span>
        </button>
    </div>

    <!-- Scanner Box -->
    <div class="bg-white rounded-3xl p-4 border border-slate-200 shadow-md flex flex-col items-center">
        <div class="w-full aspect-square bg-slate-950 rounded-2xl overflow-hidden relative border-2 border-slate-800 shadow-inner flex items-center justify-center">
            <div id="reader" class="w-full h-full"></div>
            <div id="scanReticle" class="absolute inset-8 border-2 border-dashed border-emerald-400 rounded-2xl pointer-events-none animate-pulse"></div>
            <div id="scannerPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center bg-slate-900/90 text-slate-300">
                <svg class="w-10 h-10 mb-2 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-xs font-semibold text-white">Memuat Kamera Pemindai...</p>
                <p class="text-[11px] text-slate-400 mt-1">Izinkan akses kamera pada peramban HP Bapak/Ibu Guru</p>
                <button type="button" onclick="startCamera()" class="mt-3 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-600/25 transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                    Aktifkan Kamera
                </button>
            </div>
        </div>

        <p class="text-xs text-slate-500 text-center mt-3">
            Pindai Kode QR yang tampil di Layar Presensi Madrasah.
        </p>
    </div>

    <!-- Manual Token Input Fallback -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
        <h3 class="text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Masukkan Kode Presensi Manual (Cadangan)</h3>
        <p class="text-[11px] text-slate-500 mb-3">Jika kamera HP bermasalah, salin atau ketik kode dari Layar Presensi Madrasah:</p>
        <form id="formManual" onsubmit="submitManualToken(event)" class="space-y-2">
            <input type="text" id="manualToken" placeholder="Ketik kode presensi..."
                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none mono-font">
            <button type="submit" id="btnSubmitManual" class="w-full min-h-[44px] bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-bold py-2.5 rounded-xl text-xs shadow-md shadow-maarif-700/25 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-maarif-600">
                Kirim Presensi Manual
            </button>
        </form>
    </div>

    <!-- Modal Peringatan Teaching Completion Lock -->
    <div id="modalLock" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-4 hidden">
        <div class="w-full max-w-sm bg-white rounded-3xl p-6 shadow-2xl space-y-4 border border-rose-200">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <div class="text-center">
                <h3 class="text-base font-extrabold text-slate-900 heading-font">Presensi Pulang Belum Dapat Dilakukan</h3>
                <p id="modalLockMessage" class="text-xs text-slate-600 mt-1">
                    Masih ada kelas mengajar yang belum selesai dicatat kehadirannya.
                </p>
            </div>
            <div id="modalLockList" class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 space-y-1 max-h-36 overflow-y-auto">
                <!-- List of pending schedules -->
            </div>
            <div class="flex items-center gap-2.5 sm:gap-3 pt-2">
                <button type="button" onclick="closeLockModal()" class="flex-1 py-3 px-4 rounded-xl sm:rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-bold text-xs sm:text-sm border border-slate-200/60 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                    Tutup
                </button>
                <a href="{{ route('guru.dashboard') }}" class="flex-1 py-3 px-4 rounded-xl sm:rounded-2xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-bold text-xs sm:text-sm inline-flex items-center justify-center gap-2 shadow-md shadow-maarif-700/25 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-maarif-600">
                    <span>Kembali ke Beranda</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let currentMode = 'datang'; // 'datang' or 'pulang'
    let html5QrCode = null;
    let userCoords = { lat: null, lng: null };
    let isWithinGeofence = null;
    let geofenceDistance = null;

    const scanGeofenceIconBox = document.getElementById('scanGeofenceIconBox');
    const scanGeofenceTitle = document.getElementById('scanGeofenceTitle');
    const scanGeofenceDistance = document.getElementById('scanGeofenceDistance');
    const scanGeofenceDesc = document.getElementById('scanGeofenceDesc');

    function setMode(mode) {
        currentMode = mode;
        const btnDatang = document.getElementById('btnModeDatang');
        const btnPulang = document.getElementById('btnModePulang');

        if (mode === 'datang') {
            btnDatang.className = "flex-1 py-2.5 rounded-xl text-xs font-bold transition-all duration-150 flex items-center justify-center gap-1.5 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white shadow-xs active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600";
            btnPulang.className = "flex-1 py-2.5 rounded-xl text-xs font-bold transition-all duration-150 flex items-center justify-center gap-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400";
        } else {
            btnPulang.className = "flex-1 py-2.5 rounded-xl text-xs font-bold transition-all duration-150 flex items-center justify-center gap-1.5 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white shadow-xs active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600";
            btnDatang.className = "flex-1 py-2.5 rounded-xl text-xs font-bold transition-all duration-150 flex items-center justify-center gap-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400";
        }
    }

    function initScanGeolocation(isManual = false) {
        const refreshIcon = document.getElementById('scanGpsRefreshIcon');
        if (isManual && refreshIcon) {
            refreshIcon.classList.add('animate-spin');
            setTimeout(() => refreshIcon.classList.remove('animate-spin'), 1500);
        }

        if (!navigator.geolocation) {
            if (scanGeofenceTitle) scanGeofenceTitle.textContent = "GPS Tidak Didukung";
            if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Browser perangkat Anda tidak mendukung fitur lokasi.";
            return;
        }

        const handlePosition = (pos) => {
            userCoords.lat = pos.coords.latitude;
            userCoords.lng = pos.coords.longitude;
            checkGeofenceStatus();
        };

        const handleError = (err) => {
            if (err.code === 1) { // PERMISSION_DENIED
                if (scanGeofenceIconBox) scanGeofenceIconBox.className = "w-10 h-10 rounded-xl bg-red-600 border border-red-500 text-white flex items-center justify-center shrink-0 shadow-xs";
                if (scanGeofenceTitle) scanGeofenceTitle.textContent = "Izin Lokasi Ditolak";
                if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Harap izinkan akses lokasi (GPS) pada peramban HP Anda.";
            } else if (err.code === 2) { // POSITION_UNAVAILABLE
                if (scanGeofenceIconBox) scanGeofenceIconBox.className = "w-10 h-10 rounded-xl bg-amber-500 border border-amber-400 text-white flex items-center justify-center shrink-0 shadow-xs animate-pulse";
                if (scanGeofenceTitle) scanGeofenceTitle.textContent = "Lokasi Tidak Terdeteksi";
                if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Pastikan GPS aktif dan HP terhubung ke internet.";
            } else if (err.code === 3) { // TIMEOUT
                if (!userCoords.lat) {
                    if (scanGeofenceTitle) scanGeofenceTitle.textContent = "Mencari Lokasi GPS...";
                    if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Sedang memeriksa posisi Anda. Harap tunggu...";
                }
            }
        };

        navigator.geolocation.getCurrentPosition(
            handlePosition,
            () => {},
            { enableHighAccuracy: false, timeout: 6000, maximumAge: 60000 }
        );

        navigator.geolocation.watchPosition(
            handlePosition,
            handleError,
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 }
        );
    }

    async function checkGeofenceStatus() {
        if (!userCoords.lat || !userCoords.lng) return;

        try {
            const res = await fetch("{{ route('guru.check-status', [], false) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    latitude: userCoords.lat,
                    longitude: userCoords.lng
                })
            });

            if (res.ok) {
                const data = await res.json();
                isWithinGeofence = data.is_within_geofence;
                geofenceDistance = data.distance;

                if (data.is_within_geofence) {
                    if (scanGeofenceIconBox) scanGeofenceIconBox.className = "w-10 h-10 rounded-xl bg-emerald-500 border border-emerald-400 text-white flex items-center justify-center shrink-0 shadow-xs transition-all";
                    if (scanGeofenceTitle) scanGeofenceTitle.textContent = "Di Lingkungan Madrasah";
                    if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Posisi GPS valid. Anda siap melakukan presensi.";
                    if (scanGeofenceDistance) {
                        scanGeofenceDistance.textContent = Math.round(data.distance) + "m";
                        scanGeofenceDistance.className = "font-mono font-black text-xs px-2.5 py-0.5 rounded-full bg-emerald-500 text-white shadow-xs border border-emerald-400";
                    }
                } else {
                    if (scanGeofenceIconBox) scanGeofenceIconBox.className = "w-10 h-10 rounded-xl bg-red-600 border border-red-500 text-white flex items-center justify-center shrink-0 shadow-xs transition-all";
                    if (scanGeofenceTitle) scanGeofenceTitle.textContent = "Di Luar Area Madrasah";
                    if (scanGeofenceDesc) scanGeofenceDesc.textContent = "Presensi ditolak. Anda berada di luar area madrasah (maksimal " + data.radius + " meter).";
                    if (scanGeofenceDistance) {
                        scanGeofenceDistance.textContent = Math.round(data.distance) + "m";
                        scanGeofenceDistance.className = "font-mono font-black text-xs px-2.5 py-0.5 rounded-full bg-red-600 text-white shadow-xs border border-red-500";
                    }
                }
            }
        } catch(e) {
            console.warn("Gagal mengecek status lokasi ke server:", e);
        }
    }

    function startCamera() {
        document.getElementById('scannerPlaceholder').classList.add('hidden');
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }

        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        html5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText) => {
                html5QrCode.stop().then(() => {
                    sendScanToken(decodedText);
                });
            },
            (errorMessage) => {}
        ).catch(async (err) => {
            if (typeof window.showAlertDialog === 'function') {
                await window.showAlertDialog({
                    title: 'Akses Kamera Gagal',
                    message: 'Tidak dapat mengakses kamera: ' + err,
                    type: 'danger',
                    icon: 'alert-triangle'
                });
            } else {
                alert("Tidak dapat mengakses kamera: " + err);
            }
            document.getElementById('scannerPlaceholder').classList.remove('hidden');
        });
    }

    async function sendScanToken(token) {
        window.triggerHaptic([100, 50, 100]);

        // Pre-validation: Enforce GPS coordinates and Geofence before submission
        if (!userCoords.lat || !userCoords.lng) {
            if (typeof window.showAlertDialog === 'function') {
                await window.showAlertDialog({
                    title: 'Lokasi GPS Belum Terdeteksi',
                    message: 'Sistem sedang mencari posisi GPS Anda. Harap pastikan GPS HP aktif, izinkan akses lokasi, dan tunggu beberapa detik.',
                    type: 'warning',
                    icon: 'alert-triangle'
                });
            } else {
                alert('Lokasi GPS belum terdeteksi. Harap aktifkan GPS HP Anda.');
            }
            startCamera();
            return;
        }

        if (isWithinGeofence === false) {
            if (typeof window.showAlertDialog === 'function') {
                await window.showAlertDialog({
                    title: 'Di Luar Area Madrasah',
                    message: `Presensi ditolak. Posisi Anda terdeteksi berada di luar area madrasah (${Math.round(geofenceDistance || 0)} meter). Presensi wajib dilakukan di dalam area madrasah.`,
                    type: 'danger',
                    icon: 'map-pin'
                });
            } else {
                alert('Presensi ditolak. Anda berada di luar area madrasah.');
            }
            startCamera();
            return;
        }

        const endpoint = (currentMode === 'datang') ? "{{ route('guru.checkin', [], false) }}" : "{{ route('guru.checkout', [], false) }}";

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    qr_token: token.trim(),
                    latitude: userCoords.lat,
                    longitude: userCoords.lng
                })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                if (typeof window.showAlertDialog === 'function') {
                    await window.showAlertDialog({
                        title: 'Presensi Berhasil',
                        message: data.message,
                        type: 'success',
                        confirmText: 'Lanjutkan',
                        icon: 'check-circle-2'
                    });
                } else {
                    alert(data.message);
                }
                window.location.href = "{{ route('guru.dashboard') }}";
            } else {
                if (data.code === 'TEACHING_COMPLETION_LOCKED') {
                    showLockModal(data);
                } else {
                    if (typeof window.showAlertDialog === 'function') {
                        await window.showAlertDialog({
                            title: 'Presensi Gagal',
                            message: data.message || 'Kode QR tidak valid atau sudah berganti. Silakan pindai ulang.',
                            type: 'danger',
                            icon: 'alert-circle'
                        });
                    } else {
                        alert("Gagal: " + (data.message || 'Kode QR tidak valid.'));
                    }
                    startCamera(); // Restart camera
                }
            }
        } catch (err) {
            if (typeof window.showAlertDialog === 'function') {
                await window.showAlertDialog({
                    title: 'Kesalahan Jaringan',
                    message: 'Terjadi kesalahan jaringan atau server: ' + err,
                    type: 'danger',
                    icon: 'alert-circle'
                });
            } else {
                alert("Terjadi kesalahan jaringan: " + err);
            }
            startCamera();
        }
    }

    function submitManualToken(e) {
        e.preventDefault();
        const token = document.getElementById('manualToken').value;
        if (!token) return;
        sendScanToken(token);
    }

    function showLockModal(data) {
        document.getElementById('modalLockMessage').textContent = data.message;
        const listEl = document.getElementById('modalLockList');
        listEl.innerHTML = '';
        if (data.pending_schedules) {
            data.pending_schedules.forEach(item => {
                const p = document.createElement('p');
                p.className = 'font-semibold text-rose-700';
                p.textContent = '• ' + item;
                listEl.appendChild(p);
            });
        }
        document.getElementById('modalLock').classList.remove('hidden');
    }

    function closeLockModal() {
        document.getElementById('modalLock').classList.add('hidden');
        startCamera();
    }

    // Auto-start camera and geolocation on load
    window.addEventListener('DOMContentLoaded', () => {
        initScanGeolocation();
        startCamera();
    });
</script>
@endpush

