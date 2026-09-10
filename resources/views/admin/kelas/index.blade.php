@extends('layouts.admin')

@section('title', 'Data Pokok Kelas — Admin')
@section('page-title', 'Data Rombongan Belajar')

@section('content')
<div class="space-y-6">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-black text-slate-900 heading-font tracking-tight">Data Rombongan Belajar</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola pembagian kelas, tingkat pendidikan, dan tahun ajaran aktif madrasah.</p>
        </div>
        <button type="button" onclick="openAddModal()"
            class="inline-flex items-center justify-center gap-2 py-2.5 px-5 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-extrabold text-xs rounded-xl shadow-sm transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600 shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Kelas</span>
        </button>
    </div>

    {{-- ── TABLE KELAS ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-bold text-[10px] tracking-widest">
                        <th class="py-3 px-5">Rombel / Nama Kelas</th>
                        <th class="py-3 px-5">Tingkat Pendidikan</th>
                        <th class="py-3 px-5">Tahun Ajaran</th>
                        <th class="py-3 px-5 text-center">Jumlah Siswa</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($classrooms as $c)
                        <tr class="hover:bg-slate-50/60 transition-colors duration-100">
                            {{-- Nama Kelas --}}
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-1 rounded-md bg-maarif-50 text-maarif-800 border border-maarif-200/70 font-extrabold text-xs">
                                    Kelas {{ $c->name }}
                                </span>
                            </td>

                            {{-- Tingkat --}}
                            <td class="py-3.5 px-5 text-slate-800 font-bold text-xs">
                                @if($c->grade_level == '10')
                                    Tingkat X (Sepuluh)
                                @elseif($c->grade_level == '11')
                                    Tingkat XI (Sebelas)
                                @elseif($c->grade_level == '12')
                                    Tingkat XII (Dua Belas)
                                @else
                                    Tingkat {{ $c->grade_level }}
                                @endif
                            </td>

                            {{-- Tahun Ajaran --}}
                            <td class="py-3.5 px-5 text-slate-600 mono-font font-semibold text-xs">
                                {{ $c->academic_year }}
                            </td>

                            {{-- Jumlah Siswa --}}
                            <td class="py-3.5 px-5 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 mono-font">
                                    <i data-lucide="users" class="w-3.5 h-3.5 text-slate-400"></i>
                                    {{ $c->students_count }} Siswa
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    {{-- Edit --}}
                                    <button type="button"
                                        onclick="openEditModal({{ json_encode([
                                            'id' => $c->id,
                                            'name' => $c->name,
                                            'grade_level' => $c->grade_level,
                                            'academic_year' => $c->academic_year,
                                        ]) }})"
                                        title="Ubah Data Kelas"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-sky-50 hover:bg-sky-100 border border-sky-200 text-sky-700 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 shrink-0"></i>
                                    </button>

                                    {{-- Hapus --}}
                                    <form action="{{ route('admin.kelas.destroy', $c) }}" method="POST" class="inline-flex m-0 p-0"
                                        data-confirm="Apakah Anda yakin ingin menghapus kelas <strong>{{ $c->name }}</strong> (Tingkat {{ $c->grade_level }})? Tindakan ini tidak dapat dibatalkan jika terdapat siswa atau jadwal terkait."
                                        data-confirm-title="Hapus Data Kelas"
                                        data-confirm-type="danger"
                                        data-confirm-btn="Ya, Hapus"
                                        data-confirm-icon="trash-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Data Kelas"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 shrink-0"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center">
                                <div class="inline-flex flex-col items-center gap-2">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i data-lucide="school" class="w-6 h-6 text-slate-400"></i>
                                    </span>
                                    <p class="text-sm font-bold text-slate-600">Belum ada kelas yang terdaftar</p>
                                    <p class="text-xs text-slate-400">
                                        Klik <button onclick="openAddModal()" class="text-maarif-700 font-semibold hover:underline">Tambah Kelas</button> untuk mulai menyusun rombongan belajar.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── MODAL TAMBAH KELAS ───────────────────────────────────────── --}}
    {{-- ── MODAL TAMBAH KELAS ───────────────────────────────────────── --}}
    <div id="modalAddKelas" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 hidden transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="modalAddTitle">
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200/80 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/60">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-maarif-50 text-maarif-700 border border-maarif-200/70 flex items-center justify-center shadow-xs">
                        <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 id="modalAddTitle" class="font-extrabold text-slate-900 heading-font text-base sm:text-lg">Tambah Rombel / Kelas</h3>
                        <p class="text-xs text-slate-400 font-medium">Buka rombongan belajar baru untuk tahun ajaran aktif</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddModal()"
                    class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-all duration-150 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                    aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.kelas.store') }}" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Nama Kelas / Rombel <span class="text-rose-500">*</span>
                        <span class="text-[10px] font-normal text-slate-400 ml-1">(contoh: 10 IPA 1, 11 IPS, 12 Keagamaan)</span>
                    </label>
                    <input type="text" name="name" required placeholder="Contoh: 10 IPA 1"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none font-bold text-xs bg-slate-50/70 focus:bg-white transition placeholder:font-normal placeholder:text-slate-400">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Tingkat Pendidikan <span class="text-rose-500">*</span></label>
                        <select name="grade_level" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50/70 focus:bg-white text-xs transition cursor-pointer font-medium">
                            <option value="10">Tingkat 10 (Kelas X)</option>
                            <option value="11">Tingkat 11 (Kelas XI)</option>
                            <option value="12">Tingkat 12 (Kelas XII)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input type="text" name="academic_year" value="2026/2027" required placeholder="2026/2027"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none mono-font text-xs bg-slate-50/70 focus:bg-white transition">
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" onclick="closeAddModal()"
                        class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-bold text-xs border border-slate-200 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        Batal
                    </button>
                    <button type="submit"
                        class="py-2.5 px-6 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-bold text-xs shadow-xs transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Kelas</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── MODAL EDIT KELAS ─────────────────────────────────────────── --}}
    <div id="modalEditKelas" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 hidden transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="modalEditTitle">
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200/80 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/60">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-700 border border-sky-200/70 flex items-center justify-center shadow-xs">
                        <i data-lucide="pencil" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 id="modalEditTitle" class="font-extrabold text-slate-900 heading-font text-base sm:text-lg">Perbarui Data Kelas</h3>
                        <p class="text-xs text-slate-400 font-medium">Ubah nama rombel, tingkat kelas, atau tahun ajaran</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal()"
                    class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-all duration-150 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                    aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEditKelas" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Nama Kelas <span class="text-rose-500">*</span></label>
                    <input type="text" id="editKelasName" name="name" required
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none font-bold text-xs bg-slate-50/70 focus:bg-white transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Tingkat Pendidikan <span class="text-rose-500">*</span></label>
                        <select id="editKelasGradeLevel" name="grade_level" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none bg-slate-50/70 focus:bg-white text-xs transition cursor-pointer font-medium">
                            <option value="10">Tingkat 10 (Kelas X)</option>
                            <option value="11">Tingkat 11 (Kelas XI)</option>
                            <option value="12">Tingkat 12 (Kelas XII)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input type="text" id="editKelasAcademicYear" name="academic_year" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none mono-font text-xs bg-slate-50/70 focus:bg-white transition">
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal()"
                        class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-bold text-xs border border-slate-200 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        Batal
                    </button>
                    <button type="submit"
                        class="py-2.5 px-6 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-bold text-xs shadow-xs transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Perbarui Kelas</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function openAddModal() {
        document.getElementById('modalAddKelas').classList.remove('hidden');
        document.getElementById('modalAddKelas').querySelector('input[name="name"]').focus();
    }
    function closeAddModal() {
        document.getElementById('modalAddKelas').classList.add('hidden');
    }

    function openEditModal(classroom) {
        document.getElementById('formEditKelas').action = '/admin/kelas/' + classroom.id;
        document.getElementById('editKelasName').value = classroom.name;
        document.getElementById('editKelasGradeLevel').value = classroom.grade_level;
        document.getElementById('editKelasAcademicYear').value = classroom.academic_year;

        document.getElementById('modalEditKelas').classList.remove('hidden');
        document.getElementById('editKelasName').focus();
    }
    function closeEditModal() {
        document.getElementById('modalEditKelas').classList.add('hidden');
    }

    // Close modal on backdrop click & ESC key
    ['modalAddKelas', 'modalEditKelas'].forEach(id => {
        const el = document.getElementById(id);
        el.addEventListener('click', function (e) {
            if (e.target === el) {
                el.classList.add('hidden');
            }
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddModal();
            closeEditModal();
        }
    });
</script>
@endpush
