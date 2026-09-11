@extends('layouts.admin')

@section('title', 'Presensi Dewan Guru — Admin')
@section('page-title', 'Pemantauan & Rekap Presensi Dewan Guru')

@section('content')
@php
    $totalGuru = $summary['totalGuru'] ?? 0;
    $hadirTotal = ($summary['hadir'] ?? 0) + ($summary['terlambat'] ?? 0);
    $persenKehadiran = $totalGuru > 0 ? round(($hadirTotal / $totalGuru) * 100, 1) : 0;
@endphp

<div class="space-y-6">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 heading-font tracking-tight">Presensi Dewan Guru</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pemantauan status kehadiran harian, ketepatan waktu, dan riwayat presensi dewan guru.</p>
        </div>
        <div class="text-xs font-semibold text-slate-500 bg-white border border-slate-200/80 px-3.5 py-2 rounded-xl">
            Tanggal: <span class="font-semibold text-slate-900">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</span>
        </div>
    </div>

    {{-- ── KPI SUMMARY CARDS ────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Card Utama --}}
        <div class="bg-gradient-to-br from-maarif-800 to-maarif-900 rounded-2xl p-5 text-white flex flex-col justify-between">
            <div>
                <p class="text-[11px] font-semibold text-emerald-300 uppercase tracking-widest">Total Guru Hadir</p>
                <div class="mt-2.5 flex items-baseline gap-2">
                    <span class="text-4xl font-bold mono-font tracking-tight leading-none">{{ $hadirTotal }}</span>
                    <span class="text-xs font-semibold text-emerald-200/80">/ {{ $totalGuru }} Guru</span>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between items-center mb-1 text-[11px]">
                    <span class="font-semibold text-emerald-200">Kehadiran Hari Ini</span>
                    <span class="font-bold mono-font text-white">{{ $persenKehadiran }}%</span>
                </div>
                <div class="w-full bg-white/15 rounded-full h-1.5">
                    <div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ min($persenKehadiran, 100) }}%"></div>
                </div>
            </div>
        </div>

        {{-- Tepat Waktu --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Tepat Waktu</p>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-200/70 text-emerald-700 flex items-center justify-center">
                    <i data-lucide="clock-check" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-bold text-emerald-700 mono-font">{{ $summary['hadir'] }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Hadir &le; 07:15 WIB</p>
            </div>
        </div>

        {{-- Terlambat --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Terlambat</p>
                <span class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-200/70 text-amber-600 flex items-center justify-center">
                    <i data-lucide="clock-alert" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-bold text-amber-600 mono-font">{{ $summary['terlambat'] }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Hadir &gt; 07:15 WIB</p>
            </div>
        </div>

        {{-- Belum Hadir / Belum Presensi --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Belum Hadir</p>
                <span class="w-8 h-8 rounded-xl bg-rose-50 border border-rose-200/70 text-rose-600 flex items-center justify-center">
                    <i data-lucide="user-x" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-bold text-rose-600 mono-font">{{ $summary['belumHadir'] }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Belum tercatat check-in</p>
            </div>
        </div>
    </div>

    {{-- ── FILTER & SEARCH BAR ───────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4">
        <form method="GET" action="{{ route('admin.presensi-guru.index') }}" data-loading-form class="flex flex-wrap items-center gap-3">
            <div class="w-40">
                <input type="date" name="date" value="{{ $date }}"
                    class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white mono-font font-medium transition">
            </div>

            <select name="status"
                class="px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white font-medium transition cursor-pointer">
                <option value="">Semua Status</option>
                <option value="HADIR" {{ request('status') === 'HADIR' ? 'selected' : '' }}>Hadir (Tepat Waktu)</option>
                <option value="TERLAMBAT" {{ request('status') === 'TERLAMBAT' ? 'selected' : '' }}>Terlambat</option>
                <option value="BELUM_HADIR" {{ request('status') === 'BELUM_HADIR' ? 'selected' : '' }}>Belum Hadir</option>
                <option value="IZIN" {{ request('status') === 'IZIN' ? 'selected' : '' }}>Izin</option>
                <option value="SAKIT" {{ request('status') === 'SAKIT' ? 'selected' : '' }}>Sakit</option>
                <option value="ALPA" {{ request('status') === 'ALPA' ? 'selected' : '' }}>Alpa</option>
            </select>

            <div class="relative flex-1 min-w-[200px] max-w-xs">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIP guru..."
                    class="w-full pl-10 pr-4 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white transition">
            </div>

            <button type="submit"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Terapkan</span>
            </button>

            @if(request()->hasAny(['date', 'status', 'search']))
                <a href="{{ route('admin.presensi-guru.index') }}"
                   class="text-xs text-slate-500 hover:text-slate-800 font-semibold transition-colors duration-150 cursor-pointer">
                   Reset
                </a>
            @endif
        </form>
    </div>

    {{-- ── TABLE PRESENSI GURU ───────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden"
         x-data="{ ready: false }"
         x-init="$nextTick(() => { setTimeout(() => { ready = true; }, window.__isLiveSearching ? 0 : 450); })">

        {{-- Skeleton placeholder — visible immediately on page load (NO x-cloak) --}}
        <div x-show="!ready" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true">
            <x-skeleton :count="10" :columns="6" :avatar="true" />
        </div>

        {{-- Real Table Content --}}
        <div x-show="ready" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-semibold text-[10px] tracking-widest">
                        <th class="py-3 px-5">Guru / NIP</th>
                        <th class="py-3 px-5 text-center">Status Kehadiran</th>
                        <th class="py-3 px-5 text-center">Jam Masuk</th>
                        <th class="py-3 px-5 text-center">Jam Pulang</th>
                        <th class="py-3 px-5">Jadwal Kelas Hari Ini</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $row)
                        @php
                            $t = $row['teacher'];
                            $st = $row['status'];
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors duration-100">
                            {{-- Nama & NIP --}}
                            <td class="py-3.5 px-5">
                                <p class="font-semibold text-slate-900 text-sm leading-snug">{{ $t->name }}</p>
                                <p class="font-mono text-slate-400 text-[11px] mt-0.5">NIP: {{ $t->identity_number }}</p>
                            </td>

                            {{-- Status Masuk --}}
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                @if($st === 'HADIR')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/70">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                        Tepat Waktu
                                    </span>
                                @elseif($st === 'TERLAMBAT')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                        Terlambat
                                    </span>
                                @elseif($st === 'IZIN')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        Izin
                                    </span>
                                @elseif($st === 'SAKIT')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-sky-50 text-sky-800 border border-sky-200">
                                        Sakit
                                    </span>
                                @elseif($st === 'ALPA')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-800 border border-rose-200/70">
                                        Alpa
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
                                        Belum Hadir
                                    </span>
                                @endif
                            </td>

                            {{-- Jam Masuk --}}
                            <td class="py-3.5 px-5 text-center font-mono">
                                @if($row['check_in_time'])
                                    <span class="inline-block font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/70 text-xs">
                                        {{ $row['check_in_time'] }} WIB
                                    </span>
                                @else
                                    <span class="text-slate-300 font-sans">—</span>
                                @endif
                            </td>

                            {{-- Jam Pulang --}}
                            <td class="py-3.5 px-5 text-center font-mono">
                                @if($row['check_out_time'])
                                    <span class="inline-block font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/70 text-xs">
                                        {{ $row['check_out_time'] }} WIB
                                    </span>
                                @else
                                    <span class="text-slate-400 font-sans italic text-xs">Belum</span>
                                @endif
                            </td>

                            {{-- Jadwal Hari Ini --}}
                            <td class="py-3.5 px-5">
                                @if($row['total_schedules_today'] === 0)
                                    <span class="text-slate-400 text-xs italic">Tidak ada jadwal</span>
                                @elseif($row['pending_schedules_count'] === 0)
                                    <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold text-xs bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200/70">
                                        <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>{{ $row['total_schedules_today'] }} Kelas Tuntas</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-amber-700 font-semibold text-xs bg-amber-50 px-2.5 py-1 rounded-md border border-amber-200">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600"></i>
                                        <span>{{ $row['pending_schedules_count'] }} dari {{ $row['total_schedules_today'] }} Belum</span>
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.guru.riwayat', $t) }}"
                                       title="Lihat Riwayat Presensi Guru"
                                       class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white rounded-lg text-xs font-semibold transition-all duration-150 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                                        <i data-lucide="history" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span>Riwayat</span>
                                    </a>

                                    <button type="button"
                                        onclick="openEditGuruModal({{ json_encode([
                                            'id' => $t->id,
                                            'name' => $t->name,
                                            'status' => in_array($st, ['HADIR', 'TERLAMBAT', 'IZIN', 'SAKIT', 'ALPA']) ? $st : 'HADIR',
                                            'check_in_time' => $row['check_in_time'] ?? '',
                                            'check_out_time' => $row['check_out_time'] ?? '',
                                        ]) }})"
                                        title="Koreksi Presensi Guru"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-700 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                                        <i data-lucide="clipboard-pen" class="w-3.5 h-3.5 shrink-0"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="inline-flex flex-col items-center gap-2">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i data-lucide="user-x" class="w-6 h-6 text-slate-400"></i>
                                    </span>
                                    <p class="text-sm font-semibold text-slate-600">Tidak ada data kehadiran yang cocok dengan filter.</p>
                                    <p class="text-xs text-slate-400">
                                        Coba sesuaikan tanggal atau <a href="{{ route('admin.presensi-guru.index') }}" class="text-maarif-700 font-semibold hover:underline">reset filter</a>.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    </div>

    {{-- ── MODAL KOREKSI GURU ────────────────────────────────────────── --}}
    <div id="modalEditGuruPresensi" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 hidden transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="modalKoreksiGuruTitle">
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200/80 max-h-[92vh] flex flex-col overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/60 shrink-0">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 border border-amber-200/70 flex items-center justify-center shadow-xs">
                        <i data-lucide="clipboard-pen" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 id="modalKoreksiGuruTitle" class="font-bold text-slate-900 heading-font text-base sm:text-lg">Koreksi Presensi Guru</h3>
                        <p id="modalGuruName" class="text-xs text-slate-500 font-semibold mt-0.5"></p>
                    </div>
                </div>
                <button type="button" onclick="closeEditGuruModal()"
                    class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-all duration-150 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                    aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEditGuruPresensi" method="POST" data-loading-form class="flex-1 overflow-y-auto p-6 space-y-5 text-xs">
                @csrf
                @method('PUT')
                <input type="hidden" name="date" value="{{ $date }}">

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5 uppercase tracking-wider text-[11px]">
                        Pilih Status Kehadiran <span class="text-rose-500">*</span>
                    </label>
                    <select id="editGuruStatus" name="status" required
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50/70 focus:bg-white text-xs transition font-semibold cursor-pointer text-slate-800">
                        <option value="HADIR">HADIR (Tepat Waktu)</option>
                        <option value="TERLAMBAT">TERLAMBAT</option>
                        <option value="IZIN">IZIN (Kedinasan / Izin Resmi)</option>
                        <option value="SAKIT">SAKIT (Surat Dokter)</option>
                        <option value="ALPA">ALPA (Tanpa Keterangan)</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Jam Masuk (Check-in)</label>
                        <input type="time" id="editGuruCheckIn" name="check_in_time"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50/70 focus:bg-white mono-font font-medium text-xs transition">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Jam Pulang (Check-out)</label>
                        <input type="time" id="editGuruCheckOut" name="check_out_time"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50/70 focus:bg-white mono-font font-medium text-xs transition">
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-200/80 text-[11px] text-slate-500 flex items-start gap-2.5">
                    <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                    <span>Koreksi presensi guru dicatat dalam log audit madrasah dan langsung memperbarui rekapitulasi kehadiran harian serta bulanan.</span>
                </div>

                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" onclick="closeEditGuruModal()"
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
@endsection

@push('scripts')
<script>
    function openEditGuruModal(data) {
        document.getElementById('modalGuruName').innerText = data.name;
        document.getElementById('formEditGuruPresensi').action = '/admin/presensi-guru/' + data.id;
        document.getElementById('editGuruStatus').value = data.status;
        document.getElementById('editGuruCheckIn').value = data.check_in_time;
        document.getElementById('editGuruCheckOut').value = data.check_out_time;

        document.getElementById('modalEditGuruPresensi').classList.remove('hidden');
    }

    function closeEditGuruModal() {
        document.getElementById('modalEditGuruPresensi').classList.add('hidden');
    }

    // Backdrop dismissal & ESC key
    const modalEl = document.getElementById('modalEditGuruPresensi');
    if (modalEl) {
        modalEl.addEventListener('click', function (e) {
            if (e.target === modalEl) {
                closeEditGuruModal();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditGuruModal();
        }
    });
</script>
@endpush
