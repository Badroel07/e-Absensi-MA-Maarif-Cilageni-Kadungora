@extends('layouts.admin')

@section('title', 'Pengaturan Lokasi Madrasah — Admin')
@section('page-title', 'Pengaturan Lokasi & Geofence')

@section('content')
<div class="max-w-3xl space-y-6">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-black text-slate-900 heading-font tracking-tight">Pengaturan Lokasi Madrasah</h1>
            <p class="text-xs text-slate-500 mt-0.5">Konfigurasi koordinat GPS dan radius batas validasi presensi madrasah.</p>
        </div>
    </div>

    {{-- ── FORM CARD ────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/80 space-y-6">
        <div class="flex items-start gap-3.5 pb-4 border-b border-slate-100">
            <span class="w-10 h-10 rounded-xl bg-maarif-50 border border-maarif-200/70 text-maarif-700 flex items-center justify-center shrink-0">
                <i data-lucide="map-pin" class="w-5 h-5"></i>
            </span>
            <div>
                <h2 class="font-extrabold text-slate-900 heading-font text-base">Titik Koordinat Resmi Madrasah</h2>
                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                    Pengaturan titik pusat koordinat MA Ma'arif Cilageni Kadungora untuk memastikan kehadiran berada dalam radius sah.
                </p>
            </div>
        </div>

        <form action="{{ route('admin.lokasi.update') }}" method="POST" class="space-y-5 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-slate-700 mb-1.5">Nama Lokasi Madrasah <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $location->name ?? "MA Ma'arif Cilageni Kadungora") }}" required
                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50 focus:bg-white text-xs font-semibold transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Titik Lintang (Latitude) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $location->latitude ?? -7.1147000) }}" required
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50 focus:bg-white mono-font font-bold text-xs transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Titik Bujur (Longitude) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $location->longitude ?? 107.8845000) }}" required
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50 focus:bg-white mono-font font-bold text-xs transition">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1.5">
                    Batas Jarak / Radius Presensi (Meter) <span class="text-rose-500">*</span>
                    <span class="text-slate-400 font-normal ml-1">(Rekomendasi 75 meter, rentang: 30m – 500m)</span>
                </label>
                <div class="flex items-center gap-3">
                    <input type="number" name="radius_meters" min="30" max="500" value="{{ old('radius_meters', $location->radius_meters ?? 75) }}" required
                        class="w-32 px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50 focus:bg-white mono-font text-center font-extrabold text-sm transition">
                    <span class="text-slate-500 font-medium">Meter dari titik pusat madrasah</span>
                </div>
            </div>

            {{-- Info Callout --}}
            <div class="p-4 rounded-xl bg-maarif-50 border border-maarif-200/70 text-slate-700 text-xs space-y-1">
                <p class="font-extrabold text-maarif-800 flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-4 h-4 text-maarif-700 shrink-0"></i>
                    <span>Perlindungan Presensi Geofencing Server-Side</span>
                </p>
                <p class="leading-relaxed text-slate-600">
                    Sistem memvalidasi koordinat GPS perangkat secara server-side saat melakukan check-in. Presensi di luar radius akan ditolak demi menjaga integritas kehadiran.
                </p>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 py-2.5 px-6 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-extrabold text-xs rounded-xl shadow-sm transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
