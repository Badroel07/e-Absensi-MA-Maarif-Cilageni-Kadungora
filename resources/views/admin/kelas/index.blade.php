@extends('layouts.admin')

@section('title', 'Data Pokok Kelas — Admin')
@section('page-title', 'Data Kelas & Rombongan Belajar')

@section('content')
<div class="space-y-6">
    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Data Rombongan Belajar</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola pembagian kelas, tingkat pendidikan, dan tahun ajaran aktif madrasah</p>
        </div>
    </div>

    <!-- Header Action Bar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 shadow-2xs">
                <i data-lucide="school" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base sm:text-lg font-black text-slate-900 heading-font">Rombongan Belajar</h2>
                    <span class="text-xs font-extrabold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                        {{ $classrooms->count() }} Kelas
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Daftar kelas aktif untuk tahun ajaran madrasah saat ini</p>
            </div>
        </div>
        <button type="button" onclick="openAddModal()" class="inline-flex items-center justify-center gap-2 py-3 px-5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs rounded-2xl shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Data Kelas</span>
        </button>
    </div>

    <!-- Table Kelas -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-700 uppercase font-bold text-[11px] tracking-wider">
                        <th class="py-3.5 px-5">Nama Kelas</th>
                        <th class="py-3.5 px-5">Tingkat Pendidikan</th>
                        <th class="py-3.5 px-5">Tahun Ajaran</th>
                        <th class="py-3.5 px-5 text-center">Jumlah Siswa</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($classrooms as $c)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-5">
                                <span class="px-3 py-1 rounded-xl bg-emerald-700 text-white font-black text-xs shadow-2xs">
                                    {{ $c->name }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-slate-800 font-bold text-sm">Tingkat Kelas {{ $c->grade_level }}</td>
                            <td class="py-3.5 px-5 text-slate-600 font-mono font-semibold">{{ $c->academic_year }}</td>
                            <td class="py-3.5 px-5 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 mono-font">
                                    {{ $c->students_count }} Siswa
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <button type="button" onclick="openEditModal({{ json_encode([
                                        'id' => $c->id,
                                        'name' => $c->name,
                                        'grade_level' => $c->grade_level,
                                        'academic_year' => $c->academic_year,
                                    ]) }})" title="Ubah Data Kelas" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 shadow-xs">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                        <span>Edit</span>
                                    </button>

                                    <form action="{{ route('admin.kelas.destroy', $c) }}" method="POST" class="inline-flex m-0 p-0"
                                        data-confirm="Apakah Anda yakin ingin menghapus kelas <strong>{{ $c->name }}</strong> (Tingkat {{ $c->grade_level }})? Tindakan ini tidak dapat dibatalkan jika terdapat siswa atau jadwal terkait."
                                        data-confirm-title="Hapus Data Kelas"
                                        data-confirm-type="danger"
                                        data-confirm-btn="Ya, Hapus"
                                        data-confirm-icon="trash-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Data Kelas" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 shadow-xs">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400">
                                <p class="text-xs font-medium">Belum ada kelas yang terdaftar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Kelas -->
    <div id="modalAddKelas" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-md bg-white rounded-3xl p-6 shadow-2xl space-y-5 border border-slate-200">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-900 heading-font text-base">Tambah Data Kelas</h3>
                </div>
                <button type="button" onclick="closeAddModal()" class="p-2 -mr-1 -mt-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400" aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.kelas.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Nama Kelas (Contoh: 10A, 11 IPA, 12 IPS)</label>
                    <input type="text" name="name" required placeholder="10A"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-bold text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Tingkat Kelas (MA / Setingkat SMA)</label>
                    <select name="grade_level" required class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium text-xs transition">
                        <option value="10">Tingkat 10 (Kelas X)</option>
                        <option value="11">Tingkat 11 (Kelas XI)</option>
                        <option value="12">Tingkat 12 (Kelas XII)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Tahun Ajaran</label>
                    <input type="text" name="academic_year" value="2026/2027" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-mono text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div class="pt-3 flex items-center gap-3">
                    <button type="button" onclick="closeAddModal()" class="flex-1 py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-bold text-xs border border-slate-200/80 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-3 px-4 rounded-2xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-bold text-xs shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-600">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Kelas</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Kelas -->
    <div id="modalEditKelas" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-md bg-white rounded-3xl p-6 shadow-2xl space-y-5 border border-slate-200">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                        <i data-lucide="pencil" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-900 heading-font text-base">Ubah Data Kelas</h3>
                </div>
                <button type="button" onclick="closeEditModal()" class="p-2 -mr-1 -mt-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400" aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEditKelas" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Nama Kelas (Contoh: 10A, 11 IPA, 12 IPS)</label>
                    <input type="text" id="editKelasName" name="name" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-bold text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Tingkat Kelas (MA / Setingkat SMA)</label>
                    <select id="editKelasGradeLevel" name="grade_level" required class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium text-xs transition">
                        <option value="10">Tingkat 10 (Kelas X)</option>
                        <option value="11">Tingkat 11 (Kelas XI)</option>
                        <option value="12">Tingkat 12 (Kelas XII)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Tahun Ajaran</label>
                    <input type="text" id="editKelasAcademicYear" name="academic_year" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-mono text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div class="pt-3 flex items-center gap-3">
                    <button type="button" onclick="closeEditModal()" class="flex-1 py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-bold text-xs border border-slate-200/80 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-3 px-4 rounded-2xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-bold text-xs shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-600">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Perbarui</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAddModal() { document.getElementById('modalAddKelas').classList.remove('hidden'); }
    function closeAddModal() { document.getElementById('modalAddKelas').classList.add('hidden'); }

    function openEditModal(classroom) {
        document.getElementById('formEditKelas').action = '/admin/kelas/' + classroom.id;
        document.getElementById('editKelasName').value = classroom.name;
        document.getElementById('editKelasGradeLevel').value = classroom.grade_level;
        document.getElementById('editKelasAcademicYear').value = classroom.academic_year;

        document.getElementById('modalEditKelas').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('modalEditKelas').classList.add('hidden');
    }
</script>
@endpush
