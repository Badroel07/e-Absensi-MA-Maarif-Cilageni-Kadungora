@extends('layouts.admin')

@section('title', 'Kehadiran Siswa — Admin')
@section('page-title', 'Kehadiran Siswa')

@section('content')
@php
    $totalPresensi = $summary['total'] ?? 0;
    $hadirTotal = $summary['hadir'] ?? 0;
    $persenKehadiran = $totalPresensi > 0 ? round(($hadirTotal / $totalPresensi) * 100, 1) : 0;
@endphp

<div class="space-y-6">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 heading-font tracking-tight">Presensi Siswa</h1>
            <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi kehadiran harian siswa per sesi kelas dan koreksi data presensi.</p>
        </div>
        <a href="{{ route('admin.audit.index') }}"
           class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 active:bg-slate-100 border border-slate-200/80 px-3.5 py-2 rounded-xl transition-all duration-150 active:scale-95 shadow-2xs">
            <i data-lucide="history" class="w-4 h-4 text-slate-500 shrink-0"></i>
            <span>Riwayat Perubahan Data</span>
        </a>
    </div>

    {{-- ── STAT OVERVIEW (HIERARCHY-FIRST) ───────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card Utama --}}
        <div class="bg-gradient-to-br from-maarif-800 to-maarif-900 rounded-2xl p-5 text-white flex flex-col justify-between">
            <div>
                <p class="text-[11px] font-semibold text-emerald-300 uppercase tracking-widest">Siswa Hadir</p>
                <div class="mt-2.5 flex items-baseline gap-2">
                    <span class="text-4xl font-bold mono-font tracking-tight leading-none">{{ $hadirTotal }}</span>
                    <span class="text-xs font-semibold text-emerald-200/80">/ {{ $totalPresensi }} Siswa</span>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between items-center mb-1 text-[11px]">
                    <span class="font-semibold text-emerald-200">Tingkat Kehadiran</span>
                    <span class="font-bold mono-font text-white">{{ $persenKehadiran }}%</span>
                </div>
                <div class="w-full bg-white/15 rounded-full h-1.5">
                    <div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ min($persenKehadiran, 100) }}%"></div>
                </div>
            </div>
        </div>

        {{-- Izin --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Izin</p>
                <span class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-200/70 text-amber-600 flex items-center justify-center">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-bold text-amber-600 mono-font">{{ $summary['izin'] }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Siswa dengan surat izin</p>
            </div>
        </div>

        {{-- Sakit --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Sakit</p>
                <span class="w-8 h-8 rounded-xl bg-sky-50 border border-sky-200/70 text-sky-600 flex items-center justify-center">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-bold text-sky-600 mono-font">{{ $summary['sakit'] }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Siswa sakit / istirahat</p>
            </div>
        </div>

        {{-- Alpa --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Tanpa Keterangan</p>
                <span class="w-8 h-8 rounded-xl bg-rose-50 border border-rose-200/70 text-rose-600 flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-bold text-rose-600 mono-font">{{ $summary['alpa'] }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Belum ada konfirmasi</p>
            </div>
        </div>
    </div>

    {{-- ── FILTER BAR ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4">
        <form method="GET" action="{{ route('admin.presensi-siswa.index') }}" data-loading-form class="flex flex-wrap items-center gap-3">
            <div class="w-40">
                <input type="date" name="date" value="{{ $date }}"
                    class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white mono-font font-medium transition">
            </div>

            <select name="classroom_id"
                class="px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white font-medium transition cursor-pointer">
                <option value="">Semua Kelas</option>
                @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" {{ $classroomId == $c->id ? 'selected' : '' }}>
                        Kelas {{ $c->name }}
                    </option>
                @endforeach
            </select>

            <select name="status"
                class="px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white font-medium transition cursor-pointer">
                <option value="">Semua Status</option>
                <option value="HADIR" {{ $status === 'HADIR' ? 'selected' : '' }}>Hadir</option>
                <option value="IZIN" {{ $status === 'IZIN' ? 'selected' : '' }}>Izin</option>
                <option value="SAKIT" {{ $status === 'SAKIT' ? 'selected' : '' }}>Sakit</option>
                <option value="ALPA" {{ $status === 'ALPA' ? 'selected' : '' }}>Alpa</option>
            </select>

            <div class="relative flex-1 min-w-[180px] max-w-xs">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari Siswa / NISN..."
                    class="w-full pl-10 pr-4 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white transition">
            </div>

            <button type="submit"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Terapkan</span>
            </button>

            @if(request()->hasAny(['classroom_id', 'status', 'search']) || $date !== \Carbon\Carbon::today()->format('Y-m-d'))
                <a href="{{ route('admin.presensi-siswa.index') }}"
                   class="text-xs text-slate-500 hover:text-slate-800 font-semibold transition-colors duration-150 cursor-pointer">
                   Reset
                </a>
            @endif
        </form>
    </div>

    {{-- ── TABLE PRESENSI SISWA ─────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden"
         x-data="{ ready: false }"
         x-init="$nextTick(() => { setTimeout(() => { ready = true; }, window.__isLiveSearching ? 0 : 450); })">

        {{-- Skeleton placeholder — visible immediately on page load (NO x-cloak) --}}
        <div x-show="!ready" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true">
            <x-skeleton :count="10" :columns="7" :avatar="true" />
        </div>

        {{-- Real Table Content --}}
        <div x-show="ready" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-semibold text-[10px] tracking-widest">
                        <th class="py-3 px-5">Siswa / NISN</th>
                        <th class="py-3 px-5">Kelas &amp; Mapel</th>
                        <th class="py-3 px-5">Guru Pengampu</th>
                        <th class="py-3 px-5 text-center">Status Kehadiran</th>
                        <th class="py-3 px-5">Keterangan</th>
                        <th class="py-3 px-5 text-center">Sesi</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                        @php
                            $sessionLocked = $att->session?->status === 'LOCKED';
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors duration-100">
                            {{-- Siswa --}}
                            <td class="py-3.5 px-5">
                                <p class="font-semibold text-slate-900 text-sm leading-snug">{{ $att->student->name ?? '-' }}</p>
                                <p class="text-[11px] font-mono text-slate-400 mt-0.5">NISN: {{ $att->student->identity_number ?? '-' }}</p>
                            </td>

                            {{-- Kelas & Mapel --}}
                            <td class="py-3.5 px-5">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-md bg-maarif-50 text-maarif-800 border border-maarif-200/70 font-semibold text-[11px]">
                                        Kelas {{ $att->student->classroom->name ?? ($att->schedule->classroom->name ?? '-') }}
                                    </span>
                                    <span class="font-medium text-slate-800">{{ $att->schedule->subject->name ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- Guru --}}
                            <td class="py-3.5 px-5">
                                <p class="text-slate-800 font-medium text-xs">{{ $att->schedule->teacher->name ?? ($att->session->teacher->name ?? '-') }}</p>
                                <p class="text-[11px] font-mono text-slate-400 mt-0.5">
                                    {{ substr($att->schedule->start_time ?? '', 0, 5) }} – {{ substr($att->schedule->end_time ?? '', 0, 5) }}
                                </p>
                            </td>

                            {{-- Status --}}
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                @if($att->status === 'HADIR')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/70">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                        Hadir
                                    </span>
                                @elseif($att->status === 'IZIN')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        Izin
                                    </span>
                                @elseif($att->status === 'SAKIT')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-sky-50 text-sky-800 border border-sky-200">
                                        Sakit
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-800 border border-rose-200/70">
                                        Alpa
                                    </span>
                                @endif
                            </td>

                            {{-- Keterangan --}}
                            <td class="py-3.5 px-5">
                                @if($att->notes)
                                    <span class="text-slate-700 italic text-xs">{{ $att->notes }}</span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>

                            {{-- Status Sesi --}}
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                @if($sessionLocked)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200" title="Sesi ditutup & disimpan oleh guru">
                                        <i data-lucide="lock" class="w-3 h-3 text-slate-500"></i>
                                        <span>Tersimpan</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/70">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0 animate-pulse"></span>
                                        Aktif
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <button type="button"
                                    onclick="openEditModal({
                                        id: '{{ $att->id }}',
                                        student_name: '{{ addslashes($att->student->name ?? 'Siswa') }}',
                                        nisn: '{{ $att->student->identity_number ?? '-' }}',
                                        subject_name: '{{ addslashes($att->schedule->subject->name ?? '-') }}',
                                        class_name: '{{ addslashes($att->student->classroom->name ?? '-') }}',
                                        current_status: '{{ $att->status }}',
                                        notes: '{{ addslashes($att->notes ?? '') }}',
                                        update_url: '{{ route('admin.presensi-siswa.update', $att) }}'
                                    })"
                                    title="Koreksi Status Presensi Siswa"
                                    class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white rounded-lg text-xs font-semibold transition-all duration-150 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                                    <i data-lucide="clipboard-pen" class="w-3.5 h-3.5 shrink-0"></i>
                                    <span>Koreksi</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="inline-flex flex-col items-center gap-2">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i data-lucide="clipboard-list" class="w-6 h-6 text-slate-400"></i>
                                    </span>
                                    <p class="text-sm font-semibold text-slate-600">Tidak ada data kehadiran siswa yang ditemukan</p>
                                    <p class="text-xs text-slate-400">
                                        Coba sesuaikan filter pencarian atau <a href="{{ route('admin.presensi-siswa.index') }}" class="text-maarif-700 font-semibold hover:underline">reset filter</a>.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

            {{-- Pagination --}}
            @if($attendances->hasPages())
                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ── MODAL KOREKSI PRESENSI SISWA ─────────────────────────────── --}}
    <div id="correctionModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 hidden transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="modalKoreksiSiswaTitle">
        <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200/80 max-h-[92vh] flex flex-col overflow-hidden transform transition-all">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/60 shrink-0">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-maarif-50 text-maarif-700 border border-maarif-200/70 flex items-center justify-center shadow-xs">
                        <i data-lucide="clipboard-pen" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 id="modalKoreksiSiswaTitle" class="text-base sm:text-lg font-bold text-slate-900 heading-font">Koreksi Presensi Siswa</h3>
                        <p class="text-xs text-slate-400 font-medium">Perubahan status dicatat dalam audit trail sistem madrasah</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal()" aria-label="Tutup modal"
                    class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-all duration-150 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-5 text-xs">
                {{-- Student Details Box --}}
                <div class="bg-slate-50/80 rounded-2xl p-4 border border-slate-200/80 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">Nama Lengkap:</span>
                        <span id="modalStudentName" class="font-semibold text-slate-900 text-sm">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">NISN:</span>
                        <span id="modalNisn" class="mono-font font-medium text-slate-700 px-2 py-0.5 rounded-md bg-white border border-slate-200/70">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">Mata Pelajaran:</span>
                        <span id="modalSubject" class="font-medium text-slate-800">-</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-slate-200/70">
                        <span class="text-slate-500 font-medium">Status Saat Ini:</span>
                        <span id="modalCurrentStatus" class="font-semibold px-2.5 py-0.5 rounded-lg text-xs bg-slate-200/80 text-slate-800">-</span>
                    </div>
                </div>

                <form id="correctionForm" method="POST" data-loading-form action="" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-semibold text-slate-700 mb-2 uppercase tracking-wider text-[11px]">Pilih Status Baru <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-4 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="HADIR" id="radioHadir" class="peer hidden">
                                <span class="block py-2.5 px-1 text-center font-semibold rounded-xl border border-slate-200/90 bg-white text-slate-700 peer-checked:bg-maarif-700 peer-checked:text-white peer-checked:border-maarif-700 shadow-xs transition hover:bg-slate-50">
                                    Hadir
                                </span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="IZIN" id="radioIzin" class="peer hidden">
                                <span class="block py-2.5 px-1 text-center font-semibold rounded-xl border border-slate-200/90 bg-white text-slate-700 peer-checked:bg-amber-600 peer-checked:text-white peer-checked:border-amber-600 shadow-xs transition hover:bg-slate-50">
                                    Izin
                                </span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="SAKIT" id="radioSakit" class="peer hidden">
                                <span class="block py-2.5 px-1 text-center font-semibold rounded-xl border border-slate-200/90 bg-white text-slate-700 peer-checked:bg-sky-600 peer-checked:text-white peer-checked:border-sky-600 shadow-xs transition hover:bg-slate-50">
                                    Sakit
                                </span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="ALPA" id="radioAlpa" class="peer hidden">
                                <span class="block py-2.5 px-1 text-center font-semibold rounded-xl border border-slate-200/90 bg-white text-slate-700 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 shadow-xs transition hover:bg-slate-50">
                                    Alpa
                                </span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="modalReason" class="block font-semibold text-slate-700 mb-1.5 uppercase tracking-wider text-[11px]">
                            Alasan Koreksi / Keterangan Dispensasi <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="modalReason" name="reason" rows="3" required minlength="3" maxlength="255"
                            placeholder="Contoh: Surat dokter diserahkan orang tua siswa / penyesuaian izin resmi TU..."
                            class="w-full px-3.5 py-2.5 bg-slate-50/70 focus:bg-white border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 transition text-xs"></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" onclick="closeEditModal()"
                            class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-semibold text-xs border border-slate-200 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                            Batal
                        </button>
                        <button type="submit"
                            class="py-2.5 px-6 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs shadow-xs transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Koreksi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function openEditModal(data) {
        document.getElementById('modalStudentName').textContent = data.student_name;
        document.getElementById('modalNisn').textContent = data.nisn;
        document.getElementById('modalSubject').textContent = data.subject_name + ' (Kelas ' + data.class_name + ')';
        document.getElementById('modalCurrentStatus').textContent = data.current_status;
        document.getElementById('correctionForm').action = data.update_url;

        const status = data.current_status;
        document.getElementById('radioHadir').checked = (status === 'HADIR');
        document.getElementById('radioIzin').checked = (status === 'IZIN');
        document.getElementById('radioSakit').checked = (status === 'SAKIT');
        document.getElementById('radioAlpa').checked = (status === 'ALPA');

        document.getElementById('modalReason').value = data.notes || '';
        document.getElementById('correctionModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('correctionModal').classList.add('hidden');
    }

    const modalSiswa = document.getElementById('correctionModal');
    if (modalSiswa) {
        modalSiswa.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditModal();
        }
    });
</script>
@endpush
