@extends('layouts.admin')

@section('title', 'Laporan Kehadiran — Admin')
@section('page-title', 'Laporan Kehadiran Siswa')

@section('content')
<div class="space-y-6">
    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Laporan Kehadiran</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Rekapitulasi komprehensif dan ekspor rekap presensi dalam format PDF atau Excel</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.laporan.index') }}" class="flex flex-wrap items-end gap-3.5 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1.5">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="px-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1.5">Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="px-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1.5">Pilih Kelas</label>
                <select name="classroom_id" class="px-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
                    <option value="">Semua Kelas</option>
                    @foreach($classrooms as $c)
                        <option value="{{ $c->id }}" {{ $classroomId == $c->id ? 'selected' : '' }}>
                            Kelas {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white rounded-2xl font-extrabold text-xs transition-all duration-150 active:scale-95 shadow-md shadow-slate-800/20 cursor-pointer">
                <i data-lucide="filter" class="w-4 h-4"></i>
                <span>Tampilkan Data</span>
            </button>

            <!-- Export Buttons -->
            <div class="ml-auto flex flex-wrap items-center gap-2.5">
                <button type="button" onclick="openPdfModal()"
                    class="px-4 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-extrabold text-xs inline-flex items-center gap-2 shadow-md shadow-rose-600/25 transition-all duration-150 active:scale-95 cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak PDF Laporan</span>
                </button>

                <a href="{{ route('admin.laporan.excel', request()->all()) }}"
                    class="px-4 py-2.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs inline-flex items-center gap-2 shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-95 cursor-pointer">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Unduh File Excel (.csv)</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs text-center">
            <p class="text-[11px] font-extrabold text-emerald-600 uppercase tracking-wider">Hadir</p>
            <p class="text-2xl sm:text-3xl font-black text-emerald-700 mono-font mt-1">{{ $stats['total_hadir'] }}</p>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs text-center">
            <p class="text-[11px] font-extrabold text-amber-600 uppercase tracking-wider">Izin</p>
            <p class="text-2xl sm:text-3xl font-black text-amber-600 mono-font mt-1">{{ $stats['total_izin'] }}</p>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs text-center">
            <p class="text-[11px] font-extrabold text-sky-600 uppercase tracking-wider">Sakit</p>
            <p class="text-2xl sm:text-3xl font-black text-sky-600 mono-font mt-1">{{ $stats['total_sakit'] }}</p>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs text-center">
            <p class="text-[11px] font-extrabold text-rose-600 uppercase tracking-wider">Alpa</p>
            <p class="text-2xl sm:text-3xl font-black text-rose-700 mono-font mt-1">{{ $stats['total_alpa'] }}</p>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs text-center col-span-2 md:col-span-1">
            <p class="text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Tingkat Kehadiran</p>
            <p class="text-2xl sm:text-3xl font-black text-emerald-800 mono-font mt-1">{{ $stats['percentage_hadir'] }}%</p>
        </div>
    </div>

    <!-- Table Siswa Rekapitulasi -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-900 heading-font text-sm sm:text-base">Rekapitulasi Kehadiran Siswa</h3>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200">{{ count($rows) }} Siswa Terdata</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-500 uppercase font-extrabold text-[11px] tracking-wider">
                        <th class="py-3.5 px-4 text-center">No</th>
                        <th class="py-3.5 px-5">NISN</th>
                        <th class="py-3.5 px-5">Nama Siswa</th>
                        <th class="py-3.5 px-5">Kelas</th>
                        <th class="py-3.5 px-4 text-center text-emerald-700">Hadir</th>
                        <th class="py-3.5 px-4 text-center text-amber-700">Izin</th>
                        <th class="py-3.5 px-4 text-center text-sky-700">Sakit</th>
                        <th class="py-3.5 px-4 text-center text-rose-700">Alpa</th>
                        <th class="py-3.5 px-5 text-center">Total JP</th>
                        <th class="py-3.5 px-5 text-right">% Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $idx => $r)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 text-slate-400 font-mono text-center">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-5 font-mono font-bold text-slate-900">{{ $r['nisn'] }}</td>
                            <td class="py-3.5 px-5 font-bold text-slate-900">{{ $r['name'] }}</td>
                            <td class="py-3.5 px-5 text-slate-700 font-medium">Kelas {{ $r['class_name'] }}</td>
                            <td class="py-3.5 px-4 text-center font-mono font-extrabold text-emerald-700">{{ $r['hadir'] }}</td>
                            <td class="py-3.5 px-4 text-center font-mono font-extrabold text-amber-600">{{ $r['izin'] }}</td>
                            <td class="py-3.5 px-4 text-center font-mono font-extrabold text-sky-600">{{ $r['sakit'] }}</td>
                            <td class="py-3.5 px-4 text-center font-mono font-black text-rose-600">{{ $r['alpa'] }}</td>
                            <td class="py-3.5 px-5 text-center font-mono text-slate-500 font-medium">{{ $r['total'] }}</td>
                            <td class="py-3.5 px-5 text-right font-mono font-black text-slate-900">
                                <span class="px-2.5 py-1 rounded-full text-xs {{ $r['persentase'] >= 80 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/80' : 'bg-rose-50 text-rose-800 border border-rose-200/80' }}">
                                    {{ $r['persentase'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400 font-medium">Tidak ada rekaman presensi pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Pengaturan Lembar Pengesahan / Cetak PDF -->
<div id="pdfSignatoryModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200 p-6 sm:p-8 space-y-6 animate-in fade-in zoom-in duration-150">
        
        <!-- Modal Header -->
        <div class="flex items-start justify-between border-b border-slate-100 pb-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 font-extrabold text-[11px] mb-2">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    <span>Lembar Pengesahan Laporan</span>
                </div>
                <h3 class="text-lg sm:text-xl font-black text-slate-900 heading-font">Penyesuaian Penandatangan Dokumen</h3>
                <p class="text-xs text-slate-500 mt-0.5">Atur nama dan jabatan penandatangan laporan sebelum mencetak PDF resmi.</p>
            </div>
            <button type="button" onclick="closePdfModal()" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Form Cetak PDF -->
        <form id="pdfExportForm" method="GET" action="{{ route('admin.laporan.pdf') }}" target="_blank" class="space-y-6">
            <!-- Hidden Filter Parameters -->
            <input type="hidden" name="start_date" value="{{ $startDate }}">
            <input type="hidden" name="end_date" value="{{ $endDate }}">
            <input type="hidden" name="classroom_id" value="{{ $classroomId }}">

            <!-- Section 1: Pihak Pertama (Pemeriksa / Penandatangan) -->
            <div class="space-y-3 bg-slate-50/70 p-4 rounded-2xl border border-slate-200/80">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <span class="font-extrabold text-xs text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="user-check" class="w-4 h-4 text-emerald-600"></i>
                        <span>Pihak Pemeriksa (Kiri)</span>
                    </span>
                    <!-- Quick Presets -->
                    <div class="flex flex-wrap gap-1 text-[10px]">
                        <button type="button" onclick="setSignerPreset('Waka. Kesiswaan', 'Ahmad Subandi, S.AP', '198501012010011001')" class="px-2 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 font-bold text-slate-700 cursor-pointer">Waka Kesiswaan</button>
                        <button type="button" onclick="setSignerPreset('Kepala Tata Usaha', 'Dra. Hj. Siti Aminah', '197905122008012003')" class="px-2 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 font-bold text-slate-700 cursor-pointer">Kepala TU</button>
                        <button type="button" onclick="setSignerPreset('Waka. Kurikulum', 'Drs. H. M. Zainuri, M.Pd', '197508142000031002')" class="px-2 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 font-bold text-slate-700 cursor-pointer">Waka Kurikulum</button>
                        <button type="button" onclick="setSignerPreset('Wali Kelas', 'Ust. H. Ahmad Dahlan', '198012012010011001')" class="px-2 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 font-bold text-slate-700 cursor-pointer">Wali Kelas</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jabatan Pemeriksa</label>
                        <input type="text" id="signer_title" name="signer_title" value="Waka. Kesiswaan"
                            class="w-full px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-white font-medium">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap & Gelar</label>
                        <input type="text" id="signer_name" name="signer_name" value="Ahmad Subandi, S.AP"
                            class="w-full px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-white font-medium">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">NIP / NUPTK</label>
                        <input type="text" id="signer_nip" name="signer_nip" value="198501012010011001"
                            class="w-full px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-white font-medium">
                    </div>
                </div>
            </div>

            <!-- Section 2: Pihak Kedua (Kepala Madrasah / Pimpinan) -->
            <div class="space-y-3 bg-slate-50/70 p-4 rounded-2xl border border-slate-200/80">
                <span class="font-extrabold text-xs text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="award" class="w-4 h-4 text-emerald-600"></i>
                    <span>Kepala Madrasah / Pimpinan (Kanan)</span>
                </span>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jabatan Pimpinan</label>
                        <input type="text" id="headmaster_title" name="headmaster_title" value="Kepala MA Ma'arif Cilageni"
                            class="w-full px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-white font-medium">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Kepala Madrasah</label>
                        <input type="text" id="headmaster_name" name="headmaster_name" value="Drs. H. M. Syamsuddin, M.M.Pd"
                            class="w-full px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-white font-medium">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">NIP / NUPTK</label>
                        <input type="text" id="headmaster_nip" name="headmaster_nip" value="196803151994031003"
                            class="w-full px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-white font-medium">
                    </div>
                </div>
            </div>

            <!-- Section 3: Tempat & Tanggal Penandatanganan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kota Tempat Pengesahan</label>
                    <input type="text" name="signature_city" value="Kadungora"
                        class="w-full px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Dokumen</label>
                    <input type="date" name="signature_date" value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                        class="w-full px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium">
                </div>
            </div>

            <!-- Modal Footer / Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closePdfModal()"
                    class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer transition-all">
                    Batal
                </button>
                <button type="submit" onclick="setTimeout(closePdfModal, 500)"
                    class="px-6 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-extrabold text-xs shadow-lg shadow-rose-600/30 flex items-center gap-2 cursor-pointer transition-all active:scale-95">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak PDF Laporan Sekarang</span>
                </button>
            </div>
        </form>

    </div>
</div>

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
</script>
@endpush
@endsection
