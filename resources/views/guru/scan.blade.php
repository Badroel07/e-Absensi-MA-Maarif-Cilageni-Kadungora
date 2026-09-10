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
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight leading-none">Pemindai QR Presensi</h1>
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

            {{-- Mode toggle di atas viewfinder --}}
            <div class="px-4 pt-4 pb-3">
                <p class="text-xs font-semibold text-slate-500 mb-2">Pilih mode presensi</p>
                <div class="p-1 rounded-xl bg-slate-100 flex gap-1">
                    <button type="button" id="btnModeDatang" onclick="setMode('datang')"
                        class="flex-1 py-2.5 rounded-lg text-sm font-bold transition-all duration-150 bg-maarif-700 text-white shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600 cursor-pointer min-h-[44px]"
                        aria-pressed="true">
                        Masuk
                    </button>
                    <button type="button" id="btnModePulang" onclick="setMode('pulang')"
                        class="flex-1 py-2.5 rounded-lg text-sm font-bold transition-all duration-150 text-slate-500 hover:text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 cursor-pointer min-h-[44px]"
                        aria-pressed="false">
                        Pulang
                    </button>
                </div>
                <p id="modeHint" class="text-xs text-slate-400 mt-2">Masuk: catat kedatangan. Pulang: hanya setelah kelas hari ini disimpan.</p>
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
                            <p class="text-sm font-bold text-white heading-font">Kamera belum aktif</p>
                            <p class="text-xs text-slate-400 mt-1">Izinkan akses kamera &amp; lokasi</p>
                        </div>
                        <button type="button" onclick="startCamera()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white text-sm font-bold shadow-md shadow-maarif-700/40 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 min-h-[44px]">
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
                            <span id="scanGeofenceTitle" class="text-xs font-bold text-slate-700">Mendeteksi lokasi...</span>
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
                            class="shrink-0 px-4 py-2.5 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-bold text-xs shadow-sm transition-all active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600 min-h-[42px]">
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
                <h3 class="text-base font-bold text-slate-900 heading-font">Presensi pulang tertahan</h3>
                <p id="modalLockMessage" class="text-xs text-slate-500 mt-1.5 leading-relaxed">Masih ada kelas mengajar yang belum dicatat kehadirannya.</p>
            </div>
            <div id="modalLockList" class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 space-y-1 max-h-36 overflow-y-auto"></div>
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="closeLockModal()"
                    class="flex-1 py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-bold text-xs transition-all active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 min-h-[44px]">
                    Tutup
                </button>
                <a href="{{ route('guru.dashboard') }}"
                    class="flex-1 py-3 px-4 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-bold text-xs inline-flex items-center justify-center gap-2 shadow-sm transition-all active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600 min-h-[44px]">
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
    let currentMode = 'datang';
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
        const hint = document.getElementById('modeHint');
        if (mode === 'datang') {
            btnDatang.className = "flex-1 py-2.5 rounded-lg text-sm font-bold transition-all duration-150 bg-maarif-700 text-white shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600 cursor-pointer min-h-[44px]";
            btnDatang.setAttribute('aria-pressed','true');
            btnPulang.className = "flex-1 py-2.5 rounded-lg text-sm font-bold transition-all duration-150 text-slate-500 hover:text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 cursor-pointer min-h-[44px]";
            btnPulang.setAttribute('aria-pressed','false');
            if (hint) hint.textContent = "Masuk: catat kedatangan. Pulang: hanya setelah kelas hari ini disimpan.";
        } else {
            btnPulang.className = "flex-1 py-2.5 rounded-lg text-sm font-bold transition-all duration-150 bg-slate-800 text-white shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700 cursor-pointer min-h-[44px]";
            btnPulang.setAttribute('aria-pressed','true');
            btnDatang.className = "flex-1 py-2.5 rounded-lg text-sm font-bold transition-all duration-150 text-slate-500 hover:text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 cursor-pointer min-h-[44px]";
            btnDatang.setAttribute('aria-pressed','false');
            if (hint) hint.textContent = "Pulang: sistem kunci jika masih ada sesi kelas belum disimpan.";
        }
    }
    function initScanGeolocation(isManual = false) {
        const refreshIcon = document.getElementById('scanGpsRefreshIcon');
        if (isManual && refreshIcon) { refreshIcon.classList.add('animate-spin'); setTimeout(()=>refreshIcon.classList.remove('animate-spin'),1500); }
        if (!navigator.geolocation) { if(scanGeofenceTitle) scanGeofenceTitle.textContent="Geolocation tidak didukung"; if(scanGeofenceDesc) scanGeofenceDesc.textContent="Peramban tidak mendukung akses lokasi."; return; }
        if(scanGeofenceTitle) scanGeofenceTitle.textContent="Mendeteksi lokasi...";
        if(scanGeofenceDesc) scanGeofenceDesc.textContent="Izinkan GPS di peramban.";
        navigator.geolocation.getCurrentPosition(async(pos)=>{
            userCoords.lat=pos.coords.latitude; userCoords.lng=pos.coords.longitude;
            try{
                const res=await fetch("{{ route('guru.check-status', [], false) }}",{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:JSON.stringify({latitude:userCoords.lat,longitude:userCoords.lng})});
                const data=await res.json();
                isWithinGeofence=data.within; geofenceDistance=data.distance;
                if(data.within){
                    if(scanGeofenceIconBox) scanGeofenceIconBox.className="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0";
                    if(scanGeofenceTitle){scanGeofenceTitle.textContent="Di lingkungan madrasah";scanGeofenceTitle.className="text-xs font-bold text-emerald-700";}
                    if(scanGeofenceDesc) scanGeofenceDesc.textContent="Radius "+data.radius+" m — siap presensi.";
                    if(scanGeofenceDistance){scanGeofenceDistance.textContent=Math.round(data.distance)+" m";scanGeofenceDistance.className="mono-font text-xs px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700";}
                } else {
                    if(scanGeofenceIconBox) scanGeofenceIconBox.className="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0";
                    if(scanGeofenceTitle){scanGeofenceTitle.textContent="Di luar area madrasah";scanGeofenceTitle.className="text-xs font-bold text-rose-700";}
                    if(scanGeofenceDesc) scanGeofenceDesc.textContent="Di luar "+data.radius+" m — presensi ditolak.";
                    if(scanGeofenceDistance){scanGeofenceDistance.textContent=Math.round(data.distance)+" m";scanGeofenceDistance.className="mono-font text-xs px-1.5 py-0.5 rounded-md bg-rose-50 text-rose-700";}
                }
            }catch(e){ console.warn("Gagal cek geofence:",e); }
        },(err)=>{
            if(scanGeofenceTitle) scanGeofenceTitle.textContent="Lokasi belum aktif";
            if(scanGeofenceDesc) scanGeofenceDesc.textContent="Aktifkan GPS & izinkan akses lokasi.";
            if(scanGeofenceDistance) scanGeofenceDistance.textContent="-- m";
        },{enableHighAccuracy:true,timeout:10000,maximumAge:0});
    }
    function setScanLineActive(active){ const line=document.getElementById('scanLine'); if(!line) return; if(active){line.classList.remove('opacity-0');line.classList.add('scan-line')} else {line.classList.add('opacity-0');line.classList.remove('scan-line')} }
    function startCamera(){
        document.getElementById('scannerPlaceholder').classList.add('hidden');
        setScanLineActive(true);
        if(!html5QrCode) html5QrCode=new Html5Qrcode("reader");
        const config={fps:10,qrbox:{width:250,height:250}};
        html5QrCode.start({facingMode:"environment"},config,(decodedText)=>{ setScanLineActive(false); html5QrCode.stop().then(()=>sendScanToken(decodedText)); },()=>{}).catch(async(err)=>{ setScanLineActive(false); if(typeof window.showAlertDialog==='function'){ await window.showAlertDialog({title:'Akses Kamera Gagal',message:'Tidak dapat mengakses kamera: '+err,type:'danger',icon:'alert-triangle'});} else alert("Tidak dapat mengakses kamera: "+err); document.getElementById('scannerPlaceholder').classList.remove('hidden'); });
    }
    async function sendScanToken(token){
        window.triggerHaptic([100,50,100]);
        if(!userCoords.lat||!userCoords.lng){ if(typeof window.showAlertDialog==='function'){ await window.showAlertDialog({title:'Lokasi GPS Belum Terdeteksi',message:'Sistem sedang mencari posisi GPS. Pastikan GPS aktif, izinkan akses lokasi, dan tunggu beberapa detik.',type:'warning',icon:'alert-triangle'});} else alert('Lokasi GPS belum terdeteksi.'); startCamera(); return; }
        if(isWithinGeofence===false){ if(typeof window.showAlertDialog==='function'){ await window.showAlertDialog({title:'Di Luar Area Madrasah',message:"Presensi ditolak. Posisi Anda "+Math.round(geofenceDistance||0)+" m dari madrasah — wajib di dalam area.",type:'danger',icon:'map-pin'});} else alert('Di luar area madrasah.'); startCamera(); return; }
        const endpoint=(currentMode==='datang')?"{{ route('guru.checkin', [], false) }}":"{{ route('guru.checkout', [], false) }}";
        try{
            const res=await fetch(endpoint,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:JSON.stringify({qr_token:token.trim(),latitude:userCoords.lat,longitude:userCoords.lng})});
            const data=await res.json();
            if(res.ok&&data.success){ if(typeof window.showAlertDialog==='function'){ await window.showAlertDialog({title:'Presensi Berhasil',message:data.message,type:'success',confirmText:'Lanjutkan',icon:'check-circle-2'});} else alert(data.message); window.location.href="{{ route('guru.dashboard') }}"; }
            else { if(data.code==='TEACHING_COMPLETION_LOCKED') showLockModal(data); else { if(typeof window.showAlertDialog==='function'){ await window.showAlertDialog({title:'Presensi Gagal',message:data.message||'Kode QR tidak valid atau sudah berganti. Pindai ulang.',type:'danger',icon:'alert-circle'});} else alert("Gagal: "+(data.message||'Kode QR tidak valid.')); startCamera(); } }
        }catch(err){ if(typeof window.showAlertDialog==='function'){ await window.showAlertDialog({title:'Kesalahan Jaringan',message:'Terjadi kesalahan jaringan: '+err,type:'danger',icon:'alert-circle'});} else alert("Kesalahan jaringan: "+err); startCamera(); }
    }
    function submitManualToken(e){ e.preventDefault(); const token=document.getElementById('manualToken').value; if(!token) return; document.getElementById('scannerPlaceholder').classList.add('hidden'); setScanLineActive(false); if(html5QrCode){ try{html5QrCode.stop()}catch(_){} } sendScanToken(token); }
    function showLockModal(data){ document.getElementById('modalLockMessage').textContent=data.message; const listEl=document.getElementById('modalLockList'); listEl.innerHTML=''; if(data.pending_schedules) data.pending_schedules.forEach(item=>{ const p=document.createElement('p'); p.className='font-semibold text-rose-700'; p.textContent='• '+item; listEl.appendChild(p); }); document.getElementById('modalLock').classList.remove('hidden'); }
    function closeLockModal(){ document.getElementById('modalLock').classList.add('hidden'); startCamera(); }
    window.addEventListener('DOMContentLoaded',()=>{ initScanGeolocation(); startCamera(); });
</script>
@endpush
