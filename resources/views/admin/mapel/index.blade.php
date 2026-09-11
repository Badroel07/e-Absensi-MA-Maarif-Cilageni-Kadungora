@extends('layouts.admin')

@section('title', 'Data Pokok Mata Pelajaran — Admin')
@section('page-title', 'Data Mata Pelajaran')

@section('content')
<div class="space-y-6">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 heading-font tracking-tight">Data Mata Pelajaran</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola kurikulum madrasah, kode unik mata pelajaran, dan jadwal kegiatan belajar.</p>
        </div>
        <button type="button" onclick="openAddModal()"
            class="inline-flex items-center justify-center gap-2 py-2.5 px-5 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs rounded-xl shadow-sm transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600 shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Mapel</span>
        </button>
    </div>

    {{-- ── TABLE MAPEL ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden"
         x-data="{ ready: false }"
         x-init="$nextTick(() => { setTimeout(() => { ready = true; }, window.__isLiveSearching ? 0 : 450); })">

        {{-- Skeleton placeholder — visible immediately on page load (NO x-cloak) --}}
        <div x-show="!ready" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true">
            <x-skeleton :count="6" :columns="4" :avatar="false" />
        </div>

        {{-- Real Table Content --}}
        <div x-show="ready" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-semibold text-[10px] tracking-widest">
                        <th class="py-3 px-5">Kode Mapel</th>
                        <th class="py-3 px-5">Nama Mata Pelajaran</th>
                        <th class="py-3 px-5 text-center">Jadwal Terkait</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subjects as $s)
                        <tr class="hover:bg-slate-50/60 transition-colors duration-100">
                            {{-- Kode Mapel --}}
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-1 rounded-md bg-maarif-50 text-maarif-800 border border-maarif-200/70 font-mono font-semibold text-xs tracking-wider">
                                    {{ $s->code }}
                                </span>
                            </td>

                            {{-- Nama Mapel --}}
                            <td class="py-3.5 px-5">
                                <span class="font-semibold text-slate-900 text-sm">{{ $s->name }}</span>
                            </td>

                            {{-- Jadwal Terkait --}}
                            <td class="py-3.5 px-5 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 mono-font">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                                    {{ $s->schedules_count }} Jadwal
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    {{-- Edit --}}
                                    <button type="button"
                                        onclick="openEditModal({{ json_encode([
                                             'id' => $s->id,
                                             'code' => $s->code,
                                             'name' => $s->name,
                                         ]) }})"
                                        title="Ubah Mata Pelajaran"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-sky-50 hover:bg-sky-100 border border-sky-200 text-sky-700 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 shrink-0"></i>
                                    </button>

                                    {{-- Hapus --}}
                                    <form action="{{ route('admin.mapel.destroy', $s) }}" method="POST" class="inline-flex m-0 p-0"
                                        data-confirm="Apakah Anda yakin ingin menghapus mata pelajaran <strong>{{ $s->name }}</strong> (Kode: {{ $s->code }})? Jadwal pelajaran yang terkait akan terpengaruh."
                                        data-confirm-title="Hapus Mata Pelajaran"
                                        data-confirm-type="danger"
                                        data-confirm-btn="Ya, Hapus"
                                        data-confirm-icon="trash-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Mata Pelajaran"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 shrink-0"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-16 text-center">
                                <div class="inline-flex flex-col items-center gap-2">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i data-lucide="book-open" class="w-6 h-6 text-slate-400"></i>
                                    </span>
                                    <p class="text-sm font-semibold text-slate-600">Belum ada mata pelajaran terdaftar</p>
                                    <p class="text-xs text-slate-400">
                                        Klik <button onclick="openAddModal()" class="text-maarif-700 font-semibold hover:underline">Tambah Mapel</button> untuk mendaftarkan mata pelajaran pertama.
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

    {{-- ── MODAL TAMBAH MAPEL ───────────────────────────────────────── --}}
    <div id="modalAddMapel" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 hidden transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="modalAddTitle">
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200/80 overflow-hidden transform transition-all">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/60">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-maarif-50 text-maarif-700 border border-maarif-200/70 flex items-center justify-center shadow-xs">
                        <i data-lucide="book-plus" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 id="modalAddTitle" class="font-bold text-slate-900 heading-font text-base sm:text-lg">Tambah Mata Pelajaran</h3>
                        <p class="text-xs text-slate-400 font-medium">Registrasi kode dan nama mata pelajaran madrasah</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddModal()"
                    class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-all duration-150 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                    aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.mapel.store') }}" method="POST" data-loading-form class="p-6 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">
                        Kode Mapel <span class="text-rose-500">*</span>
                        <span class="text-[10px] font-normal text-slate-400 ml-1">(kode singkat unik, huruf kapital)</span>
                    </label>
                    <input type="text" name="code" required placeholder="Contoh: PAI-FKH, MTK, BIO, ARB"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none mono-font uppercase font-semibold text-xs bg-slate-50/70 focus:bg-white transition placeholder:normal-case placeholder:font-normal placeholder:text-slate-400">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">
                        Nama Mata Pelajaran <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" required placeholder="Contoh: Fikih, Matematika Wajib, Bahasa Arab"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none text-xs bg-slate-50/70 focus:bg-white transition font-medium placeholder:text-slate-400">
                </div>

                <div class="p-3 bg-slate-50/80 rounded-2xl border border-slate-200/70 text-[11px] text-slate-500 flex items-start gap-2.5">
                    <i data-lucide="info" class="w-4 h-4 text-maarif-700 shrink-0 mt-0.5"></i>
                    <span>Mata pelajaran yang dibuat dapat langsung dijadwalkan pada menu Jadwal Mingguan dan dipetakan ke guru pengampu.</span>
                </div>

                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" onclick="closeAddModal()"
                        class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-semibold text-xs border border-slate-200 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        Batal
                    </button>
                    <button type="submit"
                        class="py-2.5 px-6 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs shadow-xs transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Mapel</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── MODAL EDIT MAPEL ─────────────────────────────────────────── --}}
    <div id="modalEditMapel" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 hidden transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="modalEditTitle">
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200/80 overflow-hidden transform transition-all">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/60">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-700 border border-sky-200/70 flex items-center justify-center shadow-xs">
                        <i data-lucide="pencil" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 id="modalEditTitle" class="font-bold text-slate-900 heading-font text-base sm:text-lg">Perbarui Mata Pelajaran</h3>
                        <p class="text-xs text-slate-400 font-medium">Ubah rincian kode atau nama mata pelajaran</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal()"
                    class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-all duration-150 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                    aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEditMapel" method="POST" data-loading-form class="p-6 space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">
                        Kode Mapel <span class="text-rose-500">*</span>
                        <span class="text-[10px] font-normal text-slate-400 ml-1">(kode singkat unik)</span>
                    </label>
                    <input type="text" id="editMapelCode" name="code" required placeholder="Contoh: PAI-FKH"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none mono-font uppercase font-semibold text-xs bg-slate-50/70 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">
                        Nama Mata Pelajaran <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="editMapelName" name="name" required placeholder="Contoh: Fikih"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none text-xs bg-slate-50/70 focus:bg-white transition font-medium">
                </div>

                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal()"
                        class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-semibold text-xs border border-slate-200 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        Batal
                    </button>
                    <button type="submit"
                        class="py-2.5 px-6 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs shadow-xs transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Perbarui Mapel</span>
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
        document.getElementById('modalAddMapel').classList.remove('hidden'); 
        document.getElementById('modalAddMapel').querySelector('input[name="code"]').focus();
    }
    function closeAddModal() { 
        document.getElementById('modalAddMapel').classList.add('hidden'); 
    }

    function openEditModal(subject) {
        document.getElementById('formEditMapel').action = '/admin/mapel/' + subject.id;
        document.getElementById('editMapelCode').value = subject.code;
        document.getElementById('editMapelName').value = subject.name;

        document.getElementById('modalEditMapel').classList.remove('hidden');
        document.getElementById('editMapelName').focus();
    }
    function closeEditModal() {
        document.getElementById('modalEditMapel').classList.add('hidden');
    }

    // Close modal on backdrop click & ESC key
    ['modalAddMapel', 'modalEditMapel'].forEach(id => {
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
