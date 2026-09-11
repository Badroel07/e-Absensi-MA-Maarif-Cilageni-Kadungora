@extends('layouts.admin')

@section('title', 'Laporan Kehadiran — Admin')
@section('page-title', 'Laporan Kehadiran Siswa')

@section('content')
<div class="space-y-6">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 heading-font tracking-tight">Laporan Kehadiran Siswa</h1>
            <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi persentase kehadiran berkala dan ekspor dokumen resmi madrasah.</p>
        </div>
        {{-- Export Buttons di Header --}}
        <div class="flex items-center gap-2">
            <button type="button" onclick="openPdfModal()"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-semibold text-xs shadow-sm transition-all duration-150 active:scale-95 cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak PDF</span>
            </button>

            <a href="{{ route('admin.laporan.excel', request()->all()) }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs shadow-sm transition-all duration-150 active:scale-95 cursor-pointer">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Unduh Excel</span>
            </a>
        </div>
    </div>

    {{-- ── FILTER CARD ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4">
        <form method="GET" action="{{ route('admin.laporan.index') }}" data-loading-form class="flex flex-wrap items-center gap-3 text-xs">
            <div class="flex items-center gap-2">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white mono-font font-medium transition">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Sampai</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white mono-font font-medium transition">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Kelas</label>
                <select name="classroom_id"
                    class="px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white font-medium transition cursor-pointer">
                    <option value="">Semua Kelas</option>
                    @foreach($classrooms as $c)
                        <option value="{{ $c->id }}" {{ $classroomId == $c->id ? 'selected' : '' }}>
                            Kelas {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="self-end pt-1">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Tampilkan</span>
                </button>
            </div>
        </form>
    </div>

    {{-- ── SUMMARY KPI OVERVIEW ─────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Card Utama: Tingkat Kehadiran --}}
        <div class="bg-gradient-to-br from-maarif-800 to-maarif-900 rounded-2xl p-5 text-white flex flex-col justify-between">
            <div>
                <p class="text-[11px] font-semibold text-emerald-300 uppercase tracking-widest">Rata-rata Kehadiran</p>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-4xl font-bold mono-font tracking-tight leading-none">{{ $stats['percentage_hadir'] }}%</span>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-white/15 rounded-full h-1.5">
                    <div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ min($stats['percentage_hadir'], 100) }}%"></div>
                </div>
                <p class="text-[11px] text-emerald-200/80 font-medium mt-1.5">Presensi hadir terverifikasi</p>
            </div>
        </div>

        {{-- Hadir --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Hadir (JP)</p>
            <div class="mt-2">
                <span class="text-3xl font-bold text-emerald-700 mono-font">{{ number_format($stats['total_hadir'], 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Total kehadiran jam pelajaran</p>
            </div>
        </div>

        {{-- Izin --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Izin (JP)</p>
            <div class="mt-2">
                <span class="text-3xl font-bold text-amber-600 mono-font">{{ number_format($stats['total_izin'], 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Total izin terkonfirmasi</p>
            </div>
        </div>

        {{-- Sakit --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Sakit (JP)</p>
            <div class="mt-2">
                <span class="text-3xl font-bold text-sky-600 mono-font">{{ number_format($stats['total_sakit'], 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Total keterangan sakit</p>
            </div>
        </div>

        {{-- Alpa --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Alpa (JP)</p>
            <div class="mt-2">
                <span class="text-3xl font-bold text-rose-600 mono-font">{{ number_format($stats['total_alpa'], 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-1">Tanpa keterangan</p>
            </div>
        </div>
    </div>

    {{-- ── TABLE REKAPITULASI ───────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-900 heading-font text-sm leading-tight">Rekapitulasi Kehadiran Siswa</h2>
                <p class="text-[11px] text-slate-500 mt-0.5">Daftar agregat kehadiran seluruh siswa per kelas terpilih.</p>
            </div>
            <span class="text-xs font-semibold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200 mono-font">
                {{ count($rows) }} Siswa
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-semibold text-[10px] tracking-widest">
                        <th class="py-3 px-4 text-center">No</th>
                        <th class="py-3 px-5">NISN</th>
                        <th class="py-3 px-5">Nama Siswa</th>
                        <th class="py-3 px-5">Kelas</th>
                        <th class="py-3 px-4 text-center text-emerald-700">Hadir</th>
                        <th class="py-3 px-4 text-center text-amber-700">Izin</th>
                        <th class="py-3 px-4 text-center text-sky-700">Sakit</th>
                        <th class="py-3 px-4 text-center text-rose-700">Alpa</th>
                        <th class="py-3 px-5 text-center">Total JP</th>
                        <th class="py-3 px-5 text-right">% Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $idx => $r)
                        <tr class="hover:bg-slate-50/60 transition-colors duration-100">
                            <td class="py-3 px-4 text-slate-400 font-mono text-center">{{ $idx + 1 }}</td>
                            <td class="py-3 px-5 font-mono font-medium text-slate-700">{{ $r['nisn'] }}</td>
                            <td class="py-3 px-5 font-semibold text-slate-900 text-sm">{{ $r['name'] }}</td>
                            <td class="py-3 px-5">
                                <span class="px-2 py-0.5 rounded-md bg-maarif-50 text-maarif-800 border border-maarif-200/70 font-semibold text-[11px]">
                                    Kelas {{ $r['class_name'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-semibold text-emerald-700">{{ $r['hadir'] }}</td>
                            <td class="py-3 px-4 text-center font-mono font-semibold text-amber-600">{{ $r['izin'] }}</td>
                            <td class="py-3 px-4 text-center font-mono font-semibold text-sky-600">{{ $r['sakit'] }}</td>
                            <td class="py-3 px-4 text-center font-mono font-semibold text-rose-600">{{ $r['alpa'] }}</td>
                            <td class="py-3 px-5 text-center font-mono text-slate-600 font-medium">{{ $r['total'] }}</td>
                            <td class="py-3 px-5 text-right font-mono font-semibold text-slate-900 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold {{ $r['persentase'] >= 80 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/70' : 'bg-rose-50 text-rose-800 border border-rose-200/70' }}">
                                    {{ $r['persentase'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-16 text-center">
                                <div class="inline-flex flex-col items-center gap-2">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i data-lucide="file-text" class="w-6 h-6 text-slate-400"></i>
                                    </span>
                                    <p class="text-sm font-semibold text-slate-600">Tidak ada data presensi pada rentang tanggal ini</p>
                                    <p class="text-xs text-slate-400">Coba ubah rentang tanggal atau pilih kelas lain.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── MODAL LEMBAR PENGESAHAN CETAK PDF ────────────────────────── --}}
<div id="pdfSignatoryModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="modalPdfTitle">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[92vh] flex flex-col overflow-hidden shadow-2xl border border-slate-200/80 transform transition-all">
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/60 shrink-0">
            <div class="flex items-center gap-3.5">
                <span class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-700 border border-rose-200 flex items-center justify-center shadow-xs">
                    <i data-lucide="printer" class="w-5 h-5"></i>
                </span>
                <div>
                    <h3 id="modalPdfTitle" class="text-base sm:text-lg font-bold text-slate-900 heading-font">Lembar Pengesahan Laporan PDF</h3>
                    <p class="text-xs text-slate-400 font-medium">Konfigurasi nama pejabat dan tanda tangan resmi pada cetak dokumen</p>
                </div>
            </div>
            <button type="button" onclick="closePdfModal()"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-all duration-150 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                aria-label="Tutup modal">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="pdfExportForm" method="GET" action="{{ route('admin.laporan.pdf') }}" data-loading-form target="_blank" class="flex-1 overflow-y-auto p-6 space-y-5 text-xs">
            <input type="hidden" name="start_date" value="{{ $startDate }}">
            <input type="hidden" name="end_date" value="{{ $endDate }}">
            <input type="hidden" name="classroom_id" value="{{ $classroomId }}">

            {{-- Pihak Pemeriksa --}}
            <div class="space-y-3.5 bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <span class="font-semibold text-slate-900 uppercase tracking-wider text-[11px] flex items-center gap-2">
                        <span class="w-1.5 h-3.5 bg-maarif-700 rounded-full"></span>
                        <span>Pemeriksa / Waka (Sebelah Kiri)</span>
                    </span>
                    <div class="flex flex-wrap gap-1 text-[10.5px]">
                        <button type="button" onclick="setSignerPreset('Waka. Kesiswaan', 'Ahmad Subandi, S.AP', '198501012010011001')" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 font-semibold text-slate-700 cursor-pointer shadow-xs transition">Waka Kesiswaan</button>
                        <button type="button" onclick="setSignerPreset('Kepala Tata Usaha', 'Dra. Hj. Siti Aminah', '197905122008012003')" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 font-semibold text-slate-700 cursor-pointer shadow-xs transition">Kepala TU</button>
                        <button type="button" onclick="setSignerPreset('Waka. Kurikulum', 'Drs. H. M. Zainuri, M.Pd', '197508142000031002')" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 font-semibold text-slate-700 cursor-pointer shadow-xs transition">Waka Kurikulum</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jabatan Pemeriksa</label>
                        <input type="text" id="signer_title" name="signer_title" value="Waka. Kesiswaan"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-white font-medium text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap &amp; Gelar</label>
                        <input type="text" id="signer_name" name="signer_name" value="Ahmad Subandi, S.AP"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-white font-medium text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">NIP / NUPTK</label>
                        <input type="text" id="signer_nip" name="signer_nip" value="198501012010011001"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-white mono-font font-medium text-xs">
                    </div>
                </div>
            </div>

            {{-- Kepala Madrasah --}}
            <div class="space-y-3.5 bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80">
                <span class="font-semibold text-slate-900 uppercase tracking-wider text-[11px] flex items-center gap-2">
                    <span class="w-1.5 h-3.5 bg-rose-600 rounded-full"></span>
                    <span>Kepala Madrasah (Sebelah Kanan)</span>
                </span>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jabatan Pimpinan</label>
                        <input type="text" id="headmaster_title" name="headmaster_title" value="Kepala MA Ma'arif Cilageni"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-white font-medium text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Kepala Madrasah</label>
                        <input type="text" id="headmaster_name" name="headmaster_name" value="Drs. H. M. Syamsuddin, M.M.Pd"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-white font-medium text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">NIP / NUPTK</label>
                        <input type="text" id="headmaster_nip" name="headmaster_nip" value="196803151994031003"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-white mono-font font-medium text-xs">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kota Pengesahan</label>
                    <input type="text" name="signature_city" value="Kadungora"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50/70 focus:bg-white font-medium text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tanggal Dokumen</label>
                    <input type="date" name="signature_date" value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50/70 focus:bg-white mono-font font-medium text-xs">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                <button type="button" onclick="closePdfModal()"
                    class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-semibold text-xs border border-slate-200 cursor-pointer transition-all active:scale-[0.98]">
                    Batal
                </button>
                <button type="submit" onclick="setTimeout(closePdfModal, 500)"
                    class="py-2.5 px-6 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-semibold text-xs shadow-xs flex items-center gap-2 cursor-pointer transition-all active:scale-[0.98]">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak PDF Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openPdfModal() {
        const modal = document.getElementById('pdfSignatoryModal');
        if (modal) {
            modal.classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    function closePdfModal() {
        const modal = document.getElementById('pdfSignatoryModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function setSignerPreset(title, name, nip) {
        document.getElementById('signer_title').value = title;
        document.getElementById('signer_name').value = name;
        document.getElementById('signer_nip').value = nip;
    }

    const modalPdfEl = document.getElementById('pdfSignatoryModal');
    if (modalPdfEl) {
        modalPdfEl.addEventListener('click', function(e) {
            if (e.target === this) {
                closePdfModal();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePdfModal();
        }
    });
</script>
@endpush
