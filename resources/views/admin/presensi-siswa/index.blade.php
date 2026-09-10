@extends('layouts.admin')

@section('title', 'Kehadiran Siswa — Admin')
@section('page-title', 'Kehadiran Siswa')

@section('content')
<div class="space-y-6">
    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Presensi Siswa</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Rekapitulasi kehadiran harian siswa per rombongan belajar dan koreksi data presensi</p>
        </div>
    </div>

    <!-- Stat Overview for Filter Date -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm shrink-0 border border-slate-200/60">
                <i data-lucide="clipboard-list" class="w-5 h-5 text-slate-600"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Data</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 mono-font">{{ $summary['total'] }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200/80 flex items-center justify-center font-bold text-sm shrink-0">
                <i data-lucide="check" class="w-5 h-5 text-emerald-600"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-600">Hadir</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-700 mono-font">{{ $summary['hadir'] }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-700 border border-amber-200/80 flex items-center justify-center font-bold text-sm shrink-0">
                <i data-lucide="file-text" class="w-5 h-5 text-amber-600"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-wider text-amber-600">Izin</p>
                <p class="text-xl sm:text-2xl font-black text-amber-600 mono-font">{{ $summary['izin'] }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-2xl bg-sky-50 text-sky-700 border border-sky-200/80 flex items-center justify-center font-bold text-sm shrink-0">
                <i data-lucide="activity" class="w-5 h-5 text-sky-600"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-wider text-sky-600">Sakit</p>
                <p class="text-xl sm:text-2xl font-black text-sky-700 mono-font">{{ $summary['sakit'] }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center space-x-3.5 col-span-2 sm:col-span-1">
            <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-700 border border-rose-200/80 flex items-center justify-center font-bold text-sm shrink-0">
                <i data-lucide="x-circle" class="w-5 h-5 text-rose-600"></i>
            </div>
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-wider text-rose-600">Alpa</p>
                <p class="text-xl sm:text-2xl font-black text-rose-700 mono-font">{{ $summary['alpa'] }}</p>
            </div>
        </div>
    </div>

    <!-- Filter Bar & Quick Audit Link -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.presensi-siswa.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            <!-- Tanggal -->
            <input type="date" name="date" value="{{ $date }}"
                class="px-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">

            <!-- Kelas -->
            <select name="classroom_id" class="px-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
                <option value="">Semua Kelas</option>
                @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" {{ $classroomId == $c->id ? 'selected' : '' }}>
                        Kelas {{ $c->name }}
                    </option>
                @endforeach
            </select>

            <!-- Status -->
            <select name="status" class="px-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
                <option value="">Semua Status</option>
                <option value="HADIR" {{ $status === 'HADIR' ? 'selected' : '' }}>Hadir</option>
                <option value="IZIN" {{ $status === 'IZIN' ? 'selected' : '' }}>Izin</option>
                <option value="SAKIT" {{ $status === 'SAKIT' ? 'selected' : '' }}>Sakit</option>
                <option value="ALPA" {{ $status === 'ALPA' ? 'selected' : '' }}>Alpa</option>
            </select>

            <!-- Search Siswa -->
            <div class="relative flex-1 min-w-[180px] max-w-xs">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari Siswa / NISN..."
                    class="w-full pl-9 pr-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition-all">
            </div>

            <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white rounded-2xl text-xs font-extrabold transition-all duration-150 active:scale-95 shadow-md shadow-slate-800/20 cursor-pointer">
                <i data-lucide="filter" class="w-4 h-4"></i>
                <span>Terapkan</span>
            </button>

            @if(request()->hasAny(['classroom_id', 'status', 'search']) || $date !== \Carbon\Carbon::today()->format('Y-m-d'))
                <a href="{{ route('admin.presensi-siswa.index') }}" class="inline-flex items-center justify-center px-3.5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 active:text-slate-900 rounded-2xl hover:bg-slate-100 transition-all duration-150 cursor-pointer">Reset</a>
            @endif
        </form>

        <a href="{{ route('admin.audit.index') }}" class="inline-flex items-center gap-2 text-xs text-white font-extrabold bg-slate-800 hover:bg-slate-900 active:bg-slate-950 px-4 py-2.5 rounded-2xl transition-all duration-150 active:scale-95 shadow-xs cursor-pointer shrink-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700">
            <i data-lucide="history" class="w-4 h-4 text-white/90 shrink-0"></i>
            <span>Riwayat Perubahan Data &rarr;</span>
        </a>
    </div>

    <!-- Table Presensi Siswa -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="font-extrabold text-slate-900 heading-font text-sm sm:text-base">Daftar Presensi Jam Pelajaran Siswa</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Tanggal: <strong class="text-slate-800 font-mono">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</strong>
                </p>
            </div>
            <span class="text-xs font-bold px-3 py-1.5 bg-slate-100 text-slate-700 rounded-xl mono-font self-start sm:self-auto border border-slate-200">
                {{ $attendances->total() }} Baris Data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-500 uppercase font-extrabold text-[11px] tracking-wider">
                        <th class="py-3.5 px-5">Siswa & NISN</th>
                        <th class="py-3.5 px-5">Kelas & Mapel</th>
                        <th class="py-3.5 px-5">Guru & Jam</th>
                        <th class="py-3.5 px-5 text-center">Status Kehadiran</th>
                        <th class="py-3.5 px-5">Keterangan / Alasan</th>
                        <th class="py-3.5 px-5 text-center">Sesi Kelas</th>
                        <th class="py-3.5 px-5 text-right">Aksi Koreksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                        @php
                            $badgeClass = match($att->status) {
                                'HADIR' => 'bg-emerald-50 text-emerald-800 border-emerald-200/80',
                                'IZIN' => 'bg-amber-50 text-amber-800 border-amber-300',
                                'SAKIT' => 'bg-sky-50 text-sky-800 border-sky-300',
                                default => 'bg-rose-50 text-rose-800 border-rose-200/80',
                            };
                            $sessionLocked = $att->session?->status === 'LOCKED';
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Siswa -->
                            <td class="py-4 px-5">
                                <p class="font-bold text-slate-900">{{ $att->student->name ?? '-' }}</p>
                                <p class="text-[11px] font-mono text-slate-400 mt-0.5">NISN: {{ $att->student->identity_number ?? '-' }}</p>
                            </td>

                            <!-- Kelas & Mapel -->
                            <td class="py-4 px-5">
                                <span class="font-bold text-slate-900">Kelas {{ $att->student->classroom->name ?? ($att->schedule->classroom->name ?? '-') }}</span>
                                <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $att->schedule->subject->name ?? '-' }}</p>
                            </td>

                            <!-- Guru & Jam -->
                            <td class="py-4 px-5">
                                <p class="text-slate-800 font-medium">{{ $att->schedule->teacher->name ?? ($att->session->teacher->name ?? '-') }}</p>
                                <p class="text-[11px] font-mono text-slate-400 mt-0.5">
                                    {{ $att->schedule->start_time ?? '-' }} - {{ $att->schedule->end_time ?? '-' }}
                                </p>
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold border {{ $badgeClass }}">
                                    {{ $att->status }}
                                </span>
                            </td>

                            <!-- Keterangan -->
                            <td class="py-4 px-5">
                                @if($att->notes)
                                    <span class="text-slate-700 italic font-medium">{{ $att->notes }}</span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>

                            <!-- Status Sesi -->
                            <td class="py-4 px-5 text-center">
                                @if($sessionLocked)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-600 border border-slate-300" title="Sudah disimpan oleh Bapak/Ibu Guru">
                                        <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                        <span>Selesai</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                        Dibuka
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-5 text-right whitespace-nowrap">
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
                                     class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-xs transition-all duration-150 shadow-xs active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                                     <i data-lucide="clipboard-pen" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                     <span>Koreksi</span>
                                 </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="max-w-sm mx-auto space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto border border-slate-200">
                                        <i data-lucide="clipboard-list" class="w-6 h-6 text-slate-400"></i>
                                    </div>
                                    <p class="font-extrabold text-slate-700 text-sm">Tidak ada data kehadiran siswa</p>
                                    <p class="text-xs text-slate-400">Belum ada sesi presensi atau data tidak ditemukan dengan filter yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-slate-100">
            {{ $attendances->links() }}
        </div>
    </div>
</div>

<!-- Modal Koreksi Kehadiran Siswa -->
<div id="correctionModal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-200 space-y-5 animate-in fade-in zoom-in duration-200">
        <!-- Modal Header -->
        <div class="flex items-start justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-800 flex items-center justify-center border border-indigo-200/80 shrink-0">
                    <i data-lucide="clipboard-pen" class="w-5 h-5 text-indigo-700"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 heading-font">Koreksi Presensi Siswa (TU)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Perubahan ini akan dicatat ke riwayat audit madrasah.</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" aria-label="Tutup modal" class="p-2 -mr-1 -mt-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-150 active:scale-95 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Student & Session Info Box -->
        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/80 space-y-2 text-xs">
            <div class="flex justify-between">
                <span class="text-slate-500">Nama Siswa:</span>
                <span id="modalStudentName" class="font-bold text-slate-900">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">NISN:</span>
                <span id="modalNisn" class="font-mono font-bold text-slate-700">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Mata Pelajaran:</span>
                <span id="modalSubject" class="font-bold text-slate-800">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Status Saat Ini:</span>
                <span id="modalCurrentStatus" class="font-extrabold px-2.5 py-0.5 rounded-full text-xs bg-slate-200 text-slate-800">-</span>
            </div>
        </div>

        <!-- Form Edit -->
        <form id="correctionForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Status Selection -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Pilih Status Baru <span class="text-rose-600">*</span></label>
                <div class="grid grid-cols-4 gap-2.5">
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="HADIR" id="radioHadir" class="peer hidden">
                        <span class="block py-2.5 px-2 text-center text-xs font-extrabold rounded-2xl border border-emerald-300 bg-white text-slate-700 peer-checked:bg-emerald-700 peer-checked:text-white peer-checked:border-emerald-700 transition-all shadow-2xs">
                            <span class="inline-flex items-center justify-center gap-1">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                <span>Hadir</span>
                            </span>
                        </span>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="IZIN" id="radioIzin" class="peer hidden">
                        <span class="block py-2.5 px-2 text-center text-xs font-extrabold rounded-2xl border border-amber-300 bg-white text-slate-700 peer-checked:bg-amber-500 peer-checked:text-white peer-checked:border-amber-500 transition-all shadow-2xs">
                            <span class="inline-flex items-center justify-center gap-1">
                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                <span>Izin</span>
                            </span>
                        </span>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="SAKIT" id="radioSakit" class="peer hidden">
                        <span class="block py-2.5 px-2 text-center text-xs font-extrabold rounded-2xl border border-sky-300 bg-white text-slate-700 peer-checked:bg-sky-600 peer-checked:text-white peer-checked:border-sky-600 transition-all shadow-2xs">
                            <span class="inline-flex items-center justify-center gap-1">
                                <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                                <span>Sakit</span>
                            </span>
                        </span>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="ALPA" id="radioAlpa" class="peer hidden">
                        <span class="block py-2.5 px-2 text-center text-xs font-extrabold rounded-2xl border border-rose-300 bg-white text-slate-700 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 transition-all shadow-2xs">
                            <span class="inline-flex items-center justify-center gap-1">
                                <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                <span>Alpa</span>
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Reason / Notes (MANDATORY FOR AUDIT) -->
            <div class="space-y-1.5">
                <label for="modalReason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Alasan Perubahan / Bukti Dispensasi <span class="text-rose-600">*</span>
                </label>
                <textarea id="modalReason" name="reason" rows="3" required minlength="3" maxlength="255"
                    placeholder="Contoh: Surat dokter diserahkan wali murid ke TU / Koreksi keterangan kehadiran..."
                    class="w-full px-4 py-2.5 text-xs bg-slate-50/50 focus:bg-white border border-slate-300 rounded-2xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 transition-all"></textarea>
                <p class="text-[11px] text-slate-400 font-medium">Wajib diisi sebagai catatan pertanggungjawaban dalam riwayat perubahan data.</p>
            </div>

            <!-- Modal Action Buttons -->
            <div class="pt-3 border-t border-slate-100 flex items-center gap-3">
                <button type="button" onclick="closeEditModal()" class="flex-1 py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-bold text-xs sm:text-sm border border-slate-200/60 transition-all duration-150 active:scale-[0.98] cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-3 px-4 rounded-2xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openEditModal(data) {
        document.getElementById('modalStudentName').textContent = data.student_name;
        document.getElementById('modalNisn').textContent = data.nisn;
        document.getElementById('modalSubject').textContent = data.subject_name + ' (Kelas ' + data.class_name + ')';
        document.getElementById('modalCurrentStatus').textContent = data.current_status;
        document.getElementById('correctionForm').action = data.update_url;

        // Select the appropriate radio
        const status = data.current_status;
        const rHadir = document.getElementById('radioHadir');
        const rIzin = document.getElementById('radioIzin');
        const rSakit = document.getElementById('radioSakit');
        const rAlpa = document.getElementById('radioAlpa');

        rHadir.checked = (status === 'HADIR');
        rIzin.checked = (status === 'IZIN');
        rSakit.checked = (status === 'SAKIT');
        rAlpa.checked = (status === 'ALPA');

        // Pre-fill reason if existing
        document.getElementById('modalReason').value = data.notes || '';

        document.getElementById('correctionModal').classList.remove('hidden');
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function closeEditModal() {
        document.getElementById('correctionModal').classList.add('hidden');
    }

    // Close on backdrop click
    document.getElementById('correctionModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });
</script>
@endpush
@endsection
