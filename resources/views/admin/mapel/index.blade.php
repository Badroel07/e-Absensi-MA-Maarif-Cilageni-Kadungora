@extends('layouts.admin')

@section('title', 'Data Pokok Mata Pelajaran — Admin')
@section('page-title', 'Data Mata Pelajaran')

@section('content')
<div class="space-y-6">
    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Data Mata Pelajaran</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola kurikulum, kode mata pelajaran, dan keterkaitan jadwal belajar mengajar</p>
        </div>
    </div>

    <!-- Header Banner / Action Bar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 shadow-2xs">
                <i data-lucide="book-open" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base sm:text-lg font-black text-slate-900 heading-font">Mata Pelajaran</h2>
                    <span class="text-xs font-extrabold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                        {{ $subjects->count() }} Mapel
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Daftar kurikulum dan kode mata pelajaran MA Ma'arif Cilageni</p>
            </div>
        </div>
        <button type="button" onclick="openAddModal()" class="inline-flex items-center justify-center gap-2 py-3 px-5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs rounded-2xl shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Mata Pelajaran</span>
        </button>
    </div>

    <!-- Table Mapel -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-700 uppercase font-bold text-[11px] tracking-wider">
                        <th class="py-3.5 px-5">Kode Mapel</th>
                        <th class="py-3.5 px-5">Nama Mata Pelajaran</th>
                        <th class="py-3.5 px-5 text-center">Jumlah Jadwal Terkait</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subjects as $s)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-mono font-extrabold text-xs">
                                    {{ $s->code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 font-bold text-slate-900 text-sm">{{ $s->name }}</td>
                            <td class="py-3.5 px-5 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 mono-font">
                                    {{ $s->schedules_count }} Jadwal
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <button type="button" onclick="openEditModal({{ json_encode([
                                        'id' => $s->id,
                                        'code' => $s->code,
                                        'name' => $s->name,
                                    ]) }})" title="Ubah Mata Pelajaran" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 shadow-xs">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                        <span>Edit</span>
                                    </button>

                                    <form action="{{ route('admin.mapel.destroy', $s) }}" method="POST" class="inline-flex m-0 p-0"
                                        data-confirm="Apakah Anda yakin ingin menghapus mata pelajaran <strong>{{ $s->name }}</strong> (Kode: {{ $s->code }})? Jadwal pelajaran yang terkait akan terpengaruh."
                                        data-confirm-title="Hapus Mata Pelajaran"
                                        data-confirm-type="danger"
                                        data-confirm-btn="Ya, Hapus"
                                        data-confirm-icon="trash-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Mata Pelajaran" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 shadow-xs">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-slate-400">
                                <p class="text-xs font-medium">Belum ada mata pelajaran.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Mapel -->
    <div id="modalAddMapel" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-md bg-white rounded-3xl p-6 shadow-2xl space-y-5 border border-slate-200">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-900 heading-font text-base">Tambah Mata Pelajaran</h3>
                </div>
                <button type="button" onclick="closeAddModal()" class="p-2 -mr-1 -mt-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400" aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.mapel.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Kode Mapel (Unik)</label>
                    <input type="text" name="code" required placeholder="Contoh: PAI-FKH, MTK, IPA"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-mono uppercase font-bold text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Nama Mata Pelajaran</label>
                    <input type="text" name="name" required placeholder="Contoh: Fikih, Matematika"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-medium text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div class="pt-3 flex items-center gap-3">
                    <button type="button" onclick="closeAddModal()" class="flex-1 py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-bold text-xs border border-slate-200/80 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-3 px-4 rounded-2xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-bold text-xs shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-600">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Mapel -->
    <div id="modalEditMapel" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-md bg-white rounded-3xl p-6 shadow-2xl space-y-5 border border-slate-200">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                        <i data-lucide="pencil" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-900 heading-font text-base">Ubah Mata Pelajaran</h3>
                </div>
                <button type="button" onclick="closeEditModal()" class="p-2 -mr-1 -mt-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400" aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEditMapel" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Kode Mapel (Unik)</label>
                    <input type="text" id="editMapelCode" name="code" required placeholder="Contoh: PAI-FKH, MTK, IPA"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-mono uppercase font-bold text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Nama Mata Pelajaran</label>
                    <input type="text" id="editMapelName" name="name" required placeholder="Contoh: Fikih, Matematika"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-medium text-xs bg-slate-50/50 focus:bg-white transition">
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
    function openAddModal() { document.getElementById('modalAddMapel').classList.remove('hidden'); }
    function closeAddModal() { document.getElementById('modalAddMapel').classList.add('hidden'); }

    function openEditModal(subject) {
        document.getElementById('formEditMapel').action = '/admin/mapel/' + subject.id;
        document.getElementById('editMapelCode').value = subject.code;
        document.getElementById('editMapelName').value = subject.name;

        document.getElementById('modalEditMapel').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('modalEditMapel').classList.add('hidden');
    }
</script>
@endpush
