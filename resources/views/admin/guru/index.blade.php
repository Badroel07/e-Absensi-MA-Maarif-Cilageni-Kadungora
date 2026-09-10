@extends('layouts.admin')

@section('title', 'Data Pokok Guru — Admin')
@section('page-title', 'Data Dewan Guru')

@section('content')
<div class="space-y-6">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-black text-slate-900 heading-font tracking-tight">Data Dewan Guru</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola data pokok dewan guru, NIP/NUPTK, kontak, dan kredensial akun presensi.</p>
        </div>
        <button type="button" onclick="openAddModal()"
            class="inline-flex items-center justify-center gap-2 py-2.5 px-5 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-extrabold text-xs rounded-xl shadow-sm transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600 shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Guru</span>
        </button>
    </div>

    {{-- ── FILTER & SEARCH BAR ───────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4">
        <form method="GET" action="{{ route('admin.guru.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[220px] max-w-sm">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIP, nama guru, email..."
                    class="w-full pl-10 pr-4 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50 focus:bg-white transition">
            </div>

            <button type="submit"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                <span>Cari</span>
            </button>

            @if(request('search'))
                <a href="{{ route('admin.guru.index') }}"
                   class="text-xs text-slate-500 hover:text-slate-800 font-semibold transition-colors duration-150 cursor-pointer">
                   Reset
                </a>
            @endif
        </form>
    </div>

    {{-- ── TABLE GURU ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-bold text-[10px] tracking-widest">
                        <th class="py-3 px-5">NIP / NUPTK</th>
                        <th class="py-3 px-5">Nama Guru</th>
                        <th class="py-3 px-5">Tanggal Lahir</th>
                        <th class="py-3 px-5">No. HP</th>
                        <th class="py-3 px-5 text-center">Status</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($teachers as $g)
                        <tr class="hover:bg-slate-50/60 transition-colors duration-100">

                            {{-- NIP --}}
                            <td class="py-3.5 px-5">
                                <span class="font-mono font-bold text-xs text-slate-700 mono-font tracking-wide">
                                    {{ $g->identity_number }}
                                </span>
                            </td>

                            {{-- Nama + Email --}}
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    @if($g->profile_photo_url)
                                        <img src="{{ $g->profile_photo_url }}" alt="{{ $g->name }}"
                                             class="w-8 h-8 rounded-lg object-cover shrink-0 border border-slate-200">
                                    @else
                                        <div class="w-8 h-8 rounded-lg bg-maarif-700 text-white font-black text-xs flex items-center justify-center shrink-0">
                                            {{ mb_substr($g->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 text-sm truncate leading-snug">{{ $g->name }}</p>
                                        <p class="text-[11px] text-slate-400 mono-font truncate mt-0.5">{{ $g->email ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Tanggal Lahir --}}
                            <td class="py-3.5 px-5 text-slate-600 mono-font font-semibold text-xs">
                                {{ $g->birth_date ? $g->birth_date->format('d-m-Y') : '-' }}
                            </td>

                            {{-- No. HP --}}
                            <td class="py-3.5 px-5 text-slate-500 font-medium text-xs">
                                {{ $g->phone_number ?? '-' }}
                            </td>

                            {{-- Status --}}
                            <td class="py-3.5 px-5 text-center">
                                @if($g->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/70">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-extrabold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    {{-- Riwayat — primary action, labeled --}}
                                    <a href="{{ route('admin.guru.riwayat', $g) }}"
                                       title="Lihat Riwayat Presensi Guru"
                                       class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white rounded-lg text-xs font-bold transition-all duration-150 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                                        <i data-lucide="history" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span>Riwayat</span>
                                    </a>

                                    {{-- Edit — icon only --}}
                                    <button type="button"
                                        onclick="openEditModal({{ json_encode([
                                            'id'               => $g->id,
                                            'identity_number'  => $g->identity_number,
                                            'name'             => $g->name,
                                            'email'            => $g->email ?? '',
                                            'birth_date'       => $g->birth_date ? $g->birth_date->format('Y-m-d') : '',
                                            'phone_number'     => $g->phone_number ?? '',
                                            'is_active'        => $g->is_active ? 1 : 0,
                                            'profile_photo_url'=> $g->profile_photo_url,
                                        ]) }})"
                                        title="Ubah Data Guru"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-sky-50 hover:bg-sky-100 border border-sky-200 text-sky-700 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 shrink-0"></i>
                                    </button>

                                    {{-- Reset Password — icon only --}}
                                    <form action="{{ route('admin.users.reset-password', $g) }}" method="POST" class="inline-flex m-0 p-0"
                                        data-confirm="Reset kata sandi akun guru <strong>{{ $g->name }}</strong> ke kata sandi bawaan (<code class='px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 font-mono font-bold text-xs'>{{ $g->getDefaultPassword() }}</code>)?"
                                        data-confirm-title="Reset Kata Sandi Guru"
                                        data-confirm-type="warning"
                                        data-confirm-btn="Ya, Reset Sandi"
                                        data-confirm-icon="key-round">
                                        @csrf
                                        <button type="submit" title="Reset Kata Sandi ke Bawaan"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-700 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                                            <i data-lucide="key-round" class="w-3.5 h-3.5 shrink-0"></i>
                                        </button>
                                    </form>

                                    {{-- Hapus — icon only --}}
                                    <form action="{{ route('admin.guru.destroy', $g) }}" method="POST" class="inline-flex m-0 p-0"
                                        data-confirm="Apakah Anda yakin ingin menghapus data guru <strong>{{ $g->name }}</strong>? Riwayat penugasan dan akun guru ini akan dinonaktifkan."
                                        data-confirm-title="Hapus Data Guru"
                                        data-confirm-type="danger"
                                        data-confirm-btn="Ya, Hapus"
                                        data-confirm-icon="trash-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Data Guru"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 shrink-0"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="inline-flex flex-col items-center gap-2">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i data-lucide="users" class="w-6 h-6 text-slate-400"></i>
                                    </span>
                                    <p class="text-sm font-bold text-slate-600">
                                        @if(request('search'))
                                            Tidak ada data guru yang cocok dengan pencarian.
                                        @else
                                            Belum ada data dewan guru terdaftar.
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        @if(request('search'))
                                            Coba ubah kata kunci atau <a href="{{ route('admin.guru.index') }}" class="text-maarif-700 font-semibold hover:underline">reset pencarian</a>.
                                        @else
                                            Klik <button onclick="openAddModal()" class="text-maarif-700 font-semibold hover:underline">Tambah Guru</button> untuk mendaftarkan dewan guru pertama.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($teachers->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $teachers->links() }}
            </div>
        @endif
    </div>

    {{-- ── MODAL TAMBAH GURU ────────────────────────────────────────── --}}
    <div id="modalAddGuru" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 hidden transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="modalAddTitle">
        <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-200/80 max-h-[92vh] flex flex-col overflow-hidden transform transition-all">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/60 shrink-0">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-maarif-50 text-maarif-700 border border-maarif-200/70 flex items-center justify-center shadow-xs">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 id="modalAddTitle" class="font-extrabold text-slate-900 heading-font text-base sm:text-lg">Tambah Pendidik / Guru</h3>
                        <p class="text-xs text-slate-400 font-medium">Registrasi profil, NIP/NUPTK, akun login dan data guru madrasah</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddModal()"
                    class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-all duration-150 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                    aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Form Body (Scrollable) --}}
            <form action="{{ route('admin.guru.store') }}" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-6 space-y-6 text-xs">
                @csrf

                {{-- Group 1: Identitas Kepegawaian --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                        <span class="w-1.5 h-3.5 bg-maarif-700 rounded-full"></span>
                        <span class="font-extrabold text-slate-800 uppercase tracking-wider text-[11px]">Identitas Kepegawaian</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1.5">
                                NIP / NUPTK / No. Pegawai <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="identity_number" required placeholder="198001012005011001"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none mono-font text-xs bg-slate-50/70 focus:bg-white transition placeholder:text-slate-400">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1.5">
                                Nama Lengkap &amp; Gelar <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" required placeholder="Ust. Ahmad Dahlan, S.Pd.I"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none text-xs bg-slate-50/70 focus:bg-white transition placeholder:text-slate-400 font-medium">
                        </div>
                    </div>
                </div>

                {{-- Group 2: Akun & Kontak --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                        <span class="w-1.5 h-3.5 bg-maarif-700 rounded-full"></span>
                        <span class="font-extrabold text-slate-800 uppercase tracking-wider text-[11px]">Akun &amp; Kontak Resmi</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1.5">
                                Alamat Email Login <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" name="email" required placeholder="guru@maarif.sch.id"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none text-xs bg-slate-50/70 focus:bg-white transition placeholder:text-slate-400">
                            <p class="text-[10.5px] text-slate-400 mt-1">Digunakan untuk login dan notifikasi jadwal madrasah.</p>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1.5">
                                Tanggal Lahir <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="birth_date" required
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none mono-font text-xs bg-slate-50/70 focus:bg-white transition">
                            <p class="text-[10.5px] text-slate-400 mt-1">Basis kata sandi bawaan guru saat pertama kali dibuat.</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1.5">
                                No. Handphone / WhatsApp
                                <span class="text-[10px] font-normal text-slate-400 ml-1">(opsional)</span>
                            </label>
                            <input type="text" name="phone_number" placeholder="08123456789"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none text-xs bg-slate-50/70 focus:bg-white transition placeholder:text-slate-400">
                        </div>
                    </div>
                </div>

                {{-- Group 3: Foto Profil Guru --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                        <span class="w-1.5 h-3.5 bg-maarif-700 rounded-full"></span>
                        <span class="font-extrabold text-slate-800 uppercase tracking-wider text-[11px]">Foto Profil Guru</span>
                    </div>

                    <div class="flex items-center gap-4 p-3.5 rounded-2xl bg-slate-50/70 border border-slate-200/80">
                        <div class="w-14 h-14 rounded-2xl bg-slate-200/80 border border-slate-300 flex items-center justify-center shrink-0 overflow-hidden text-slate-400">
                            <img id="addGuruPreviewImg" src="" alt="Preview Foto" class="w-full h-full object-cover hidden">
                            <i id="addGuruPlaceholder" data-lucide="user" class="w-6 h-6"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="block font-bold text-slate-700 mb-1">Unggah Foto Pendidik <span class="text-[10px] font-normal text-slate-400">(JPG, PNG, WebP maks 2MB)</span></label>
                            <input type="file" name="photo" id="addGuruPhotoInput" accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-maarif-50 file:text-maarif-700 hover:file:bg-maarif-100 cursor-pointer">
                        </div>
                    </div>
                </div>

                {{-- Sticky Footer Inside Form --}}
                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" onclick="closeAddModal()"
                        class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-bold text-xs border border-slate-200 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        Batal
                    </button>
                    <button type="submit"
                        class="py-2.5 px-6 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-bold text-xs shadow-xs transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Guru</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── MODAL EDIT GURU ─────────────────────────────────────────── --}}
    <div id="modalEditGuru" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 hidden transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="modalEditTitle">
        <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-200/80 max-h-[92vh] flex flex-col overflow-hidden transform transition-all">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/60 shrink-0">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-700 border border-sky-200/70 flex items-center justify-center shadow-xs">
                        <i data-lucide="pencil" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 id="modalEditTitle" class="font-extrabold text-slate-900 heading-font text-base sm:text-lg">Perbarui Data Guru</h3>
                        <p class="text-xs text-slate-400 font-medium">Ubah profil kepegawaian, kontak, status aktif, atau foto pendidik</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal()"
                    class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-all duration-150 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                    aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Form Body (Scrollable) --}}
            <form id="formEditGuru" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-6 space-y-6 text-xs">
                @csrf
                @method('PUT')

                {{-- Photo block with Live Preview --}}
                <div class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl overflow-hidden shrink-0 border border-slate-300 flex items-center justify-center bg-slate-200 shadow-xs">
                            <img id="editGuruPreviewImg" src="" alt="Foto Guru" class="w-full h-full object-cover hidden">
                            <div id="editGuruFallback" class="w-full h-full bg-maarif-700 text-white font-black flex items-center justify-center text-lg">G</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="block font-bold text-slate-700 mb-1">Ganti Foto Profil <span class="text-[10px] font-normal text-slate-400">(maks. 2MB)</span></label>
                            <input type="file" id="editGuruPhoto" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">
                        </div>
                    </div>
                    <div id="editGuruRemovePhotoBox" class="hidden pt-2.5 border-t border-slate-200/70">
                        <label class="inline-flex items-center text-xs text-rose-600 font-bold cursor-pointer gap-2 select-none hover:text-rose-700">
                            <input type="checkbox" id="editGuruRemovePhoto" name="remove_photo" value="1" class="rounded text-rose-600 focus:ring-rose-500 cursor-pointer w-3.5 h-3.5">
                            <span>Hapus foto saat ini (kembalikan ke inisial nama)</span>
                        </label>
                    </div>
                </div>

                {{-- Group 1: Data Pokok Guru --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                        <span class="w-1.5 h-3.5 bg-sky-600 rounded-full"></span>
                        <span class="font-extrabold text-slate-800 uppercase tracking-wider text-[11px]">Identitas Kepegawaian</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1.5">
                                NIP / NUPTK / No. Pegawai <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="editGuruNip" name="identity_number" required
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none mono-font text-xs bg-slate-50/70 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1.5">
                                Nama Lengkap &amp; Gelar <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="editGuruName" name="name" required
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none text-xs bg-slate-50/70 focus:bg-white transition font-medium">
                        </div>
                    </div>
                </div>

                {{-- Group 2: Kontak & Akun --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                        <span class="w-1.5 h-3.5 bg-sky-600 rounded-full"></span>
                        <span class="font-extrabold text-slate-800 uppercase tracking-wider text-[11px]">Akun, Kontak &amp; Status</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1.5">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" id="editGuruEmail" name="email" required
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none text-xs bg-slate-50/70 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1.5">
                                Tanggal Lahir <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" id="editGuruBirthDate" name="birth_date" required
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none mono-font text-xs bg-slate-50/70 focus:bg-white transition">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1.5">
                                No. Handphone / WhatsApp
                                <span class="text-[10px] font-normal text-slate-400 ml-1">(opsional)</span>
                            </label>
                            <input type="text" id="editGuruPhone" name="phone_number"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none text-xs bg-slate-50/70 focus:bg-white transition">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1.5">Status Akun Guru</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/60 cursor-pointer hover:bg-slate-100/60 transition">
                                    <input type="radio" name="is_active" id="editGuruStatusActive" value="1" class="text-maarif-700 focus:ring-maarif-600">
                                    <div>
                                        <span class="font-bold text-slate-800 text-xs block">Aktif</span>
                                        <span class="text-[10px] text-slate-400">Dapat presensi dan buka sesi mengajar</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/60 cursor-pointer hover:bg-slate-100/60 transition">
                                    <input type="radio" name="is_active" id="editGuruStatusInactive" value="0" class="text-rose-600 focus:ring-rose-500">
                                    <div>
                                        <span class="font-bold text-slate-800 text-xs block">Nonaktif</span>
                                        <span class="text-[10px] text-slate-400">Akses akun sementara dibekukan</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sticky Footer Inside Form --}}
                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal()"
                        class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-bold text-xs border border-slate-200 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        Batal
                    </button>
                    <button type="submit"
                        class="py-2.5 px-6 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-bold text-xs shadow-xs transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Perbarui Guru</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Live preview for add teacher photo
    const addGuruPhotoInput = document.getElementById('addGuruPhotoInput');
    const addGuruPreviewImg = document.getElementById('addGuruPreviewImg');
    const addGuruPlaceholder = document.getElementById('addGuruPlaceholder');
    if (addGuruPhotoInput) {
        addGuruPhotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    addGuruPreviewImg.src = evt.target.result;
                    addGuruPreviewImg.classList.remove('hidden');
                    if (addGuruPlaceholder) addGuruPlaceholder.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Live preview for edit teacher photo
    const editGuruPhotoInput = document.getElementById('editGuruPhoto');
    const editGuruPreviewImg = document.getElementById('editGuruPreviewImg');
    const editGuruFallback   = document.getElementById('editGuruFallback');
    if (editGuruPhotoInput) {
        editGuruPhotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    editGuruPreviewImg.src = evt.target.result;
                    editGuruPreviewImg.classList.remove('hidden');
                    if (editGuruFallback) editGuruFallback.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    function openAddModal() {
        document.getElementById('modalAddGuru').classList.remove('hidden');
        document.getElementById('modalAddGuru').querySelector('input[name="identity_number"]').focus();
    }
    function closeAddModal() {
        document.getElementById('modalAddGuru').classList.add('hidden');
    }

    function openEditModal(teacher) {
        const form = document.getElementById('formEditGuru');
        form.action = '/admin/guru/' + teacher.id;

        document.getElementById('editGuruNip').value        = teacher.identity_number;
        document.getElementById('editGuruName').value       = teacher.name;
        document.getElementById('editGuruEmail').value      = teacher.email || '';
        document.getElementById('editGuruBirthDate').value  = teacher.birth_date;
        document.getElementById('editGuruPhone').value      = teacher.phone_number || '';
        
        if (teacher.is_active == 1) {
            document.getElementById('editGuruStatusActive').checked = true;
        } else {
            document.getElementById('editGuruStatusInactive').checked = true;
        }

        const previewImg  = document.getElementById('editGuruPreviewImg');
        const fallback    = document.getElementById('editGuruFallback');
        const removeBox   = document.getElementById('editGuruRemovePhotoBox');
        const removeCheck = document.getElementById('editGuruRemovePhoto');
        const photoInput  = document.getElementById('editGuruPhoto');

        if (photoInput)  photoInput.value = '';
        if (removeCheck) removeCheck.checked = false;

        if (teacher.profile_photo_url) {
            previewImg.src = teacher.profile_photo_url;
            previewImg.classList.remove('hidden');
            fallback.classList.add('hidden');
            removeBox.classList.remove('hidden');
        } else {
            previewImg.src = '';
            previewImg.classList.add('hidden');
            fallback.textContent = teacher.name ? teacher.name.charAt(0).toUpperCase() : 'G';
            fallback.classList.remove('hidden');
            removeBox.classList.add('hidden');
        }

        document.getElementById('modalEditGuru').classList.remove('hidden');
        document.getElementById('editGuruName').focus();
    }

    function closeEditModal() {
        document.getElementById('modalEditGuru').classList.add('hidden');
    }

    // Close modal on backdrop click & ESC key
    ['modalAddGuru', 'modalEditGuru'].forEach(id => {
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
