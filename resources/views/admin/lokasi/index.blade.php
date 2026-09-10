@extends('layouts.admin')

@section('title', 'Pengaturan Lokasi Madrasah — Admin')
@section('page-title', 'Pengaturan Lokasi & Batas Area Madrasah')

@section('content')
<div class="max-w-3xl space-y-6">
    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Pengaturan Lokasi Madrasah</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Konfigurasi titik koordinat GPS dan radius validasi absensi madrasah</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
        <div class="flex items-start space-x-3.5 pb-4 border-b border-slate-100">
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center border border-emerald-200/80 shrink-0">
                <i data-lucide="map-pin" class="w-5 h-5 text-emerald-700"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-slate-900 heading-font text-base sm:text-lg">Titik Koordinat Resmi Madrasah</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Pengaturan titik pusat lokasi MA Ma'arif Cilageni Kadungora untuk memastikan siswa berada di lingkungan madrasah saat memasukkan PIN kehadiran.
                </p>
            </div>
        </div>

        <form action="{{ route('admin.lokasi.update') }}" method="POST" class="space-y-5 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-slate-700 mb-1.5">Nama Lokasi Madrasah <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $location->name ?? "MA Ma'arif Cilageni Kadungora") }}" required
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Titik Lintang (Latitude) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $location->latitude ?? -7.1147000) }}" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-mono font-bold transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Titik Bujur (Longitude) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $location->longitude ?? 107.8845000) }}" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-mono font-bold transition-all">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1.5">
                    Batas Jarak / Radius Presensi (Meter) <span class="text-rose-500">*</span>
                    <span class="text-slate-400 font-normal ml-1">(Standar 75 meter, rentang aman 50m – 100m)</span>
                </label>
                <div class="flex items-center space-x-3">
                    <input type="number" name="radius_meters" min="30" max="500" value="{{ old('radius_meters', $location->radius_meters ?? 75) }}" required
                        class="w-32 px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-mono text-center font-extrabold text-sm transition-all">
                    <span class="text-slate-600 font-medium">Meter dari titik pusat madrasah</span>
                </div>
            </div>

            <!-- Notice card -->
            <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-900 text-xs space-y-1.5">
                <p class="font-extrabold flex items-center text-emerald-800">
                    <i data-lucide="shield-check" class="w-4 h-4 mr-2 text-emerald-700 shrink-0"></i>
                    Perlindungan Kehadiran di Lingkungan Madrasah
                </p>
                <p class="leading-relaxed text-emerald-800/90">
                    Setiap kali siswa memasukkan kode PIN, sistem secara otomatis memastikan posisi siswa berada di lingkungan madrasah. Jika siswa berada di luar batas jarak (radius), presensi tidak dapat dilakukan demi memastikan kejujuran kehadiran siswa.
                </p>
            </div>

            <div class="pt-2">
                <button type="submit" class="inline-flex items-center justify-center gap-2 py-3 px-6 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs sm:text-sm rounded-2xl shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Pengaturan Lokasi</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
