@extends('layouts.admin')

@section('title', 'Data Pokok Siswa — Admin')
@section('page-title', 'Data Siswa')

@section('content')
<div class="space-y-6">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 heading-font tracking-tight">Data Pokok Siswa</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola data induk siswa madrasah, rombel kelas, NISN, dan akun presensi.</p>
        </div>
        <button type="button" onclick="openAddModal()"
            class="inline-flex items-center justify-center gap-2 py-2.5 px-5 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs rounded-xl shadow-sm transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600 shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Siswa</span>
        </button>
    </div>

    {{-- ── FILTER BAR ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4">
        <form method="GET" action="{{ route('admin.siswa.index') }}" data-loading-form class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px] max-w-xs">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NISN atau nama siswa..."
                    class="w-full pl-10 pr-4 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50 focus:bg-white transition">
            </div>

            <select name="classroom_id"
                class="px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white font-medium transition cursor-pointer">
                <option value="">Semua Kelas</option>
                @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" {{ request('classroom_id') == $c->id ? 'selected' : '' }}>
                        Kelas {{ $c->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Filter</span>
            </button>

            @if(request()->hasAny(['search', 'classroom_id']))
                <a href="{{ route('admin.siswa.index') }}"
                   class="text-xs text-slate-500 hover:text-slate-800 font-semibold transition-colors duration-150 cursor-pointer">
                   Reset
                </a>
            @endif
        </form>
    </div>

    {{-- ── TABLE SISWA ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-semibold text-[10px] tracking-widest">
                        <th class="py-3 px-5">NISN</th>
                        <th class="py-3 px-5">Nama Siswa</th>
                        <th class="py-3 px-5">Kelas</th>
                        <th class="py-3 px-5">Tanggal Lahir</th>
                        <th class="py-3 px-5">No. HP</th>
                        <th class="py-3 px-5 text-center">Status</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $st)
                        <tr class="hover:bg-slate-50/60 transition-colors duration-100">

                            {{-- NISN --}}
                            <td class="py-3.5 px-5">
                                <span class="font-mono font-semibold text-xs text-slate-700 mono-font tracking-wide">
                                    {{ $st->identity_number }}
                                </span>
                            </td>

                            {{-- Nama + Email --}}
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    @if($st->profile_photo_url)
                                        <img src="{{ $st->profile_photo_url }}" loading="lazy" decoding="async" alt="{{ $st->name }}"
                                             class="w-8 h-8 rounded-lg object-cover shrink-0 border border-slate-200">
                                    @else
                                        <div class="w-8 h-8 rounded-lg bg-maarif-700 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ mb_substr($st->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900 text-sm truncate leading-snug">{{ $st->name }}</p>
                                        <p class="text-[11px] text-slate-400 mono-font truncate mt-0.5">{{ $st->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Kelas --}}
                            <td class="py-3.5 px-5">
                                <span class="px-2 py-0.5 rounded-md bg-maarif-50 text-maarif-800 border border-maarif-200/70 font-semibold text-[11px]">
                                    {{ $st->classroom->name ?? '-' }}
                                </span>
                            </td>

                            {{-- Tanggal Lahir --}}
                            <td class="py-3.5 px-5 text-slate-600 mono-font font-semibold text-xs">
                                {{ $st->birth_date ? $st->birth_date->format('d-m-Y') : '-' }}
                            </td>

                            {{-- No. HP --}}
                            <td class="py-3.5 px-5 text-slate-500 font-medium text-xs">
                                {{ $st->phone_number ?? '-' }}
                            </td>

                            {{-- Status --}}
                            <td class="py-3.5 px-5 text-center">
                                @if($st->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/70">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    {{-- Riwayat — primary action, labeled --}}
                                    <a href="{{ route('admin.siswa.riwayat', $st) }}"
                                       title="Lihat Riwayat Presensi"
                                       class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white rounded-lg text-xs font-semibold transition-all duration-150 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                                        <i data-lucide="history" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span>Riwayat</span>
                                    </a>

                                    {{-- Edit — icon only --}}
                                    <button type="button"
                                        onclick="openEditModal({{ json_encode([
                                            'id'               => $st->id,
                                            'identity_number'  => $st->identity_number,
                                            'name'             => $st->name,
                                            'email'            => $st->email,
                                            'classroom_id'     => $st->classroom_id,
                                            'birth_date'       => $st->birth_date ? $st->birth_date->format('Y-m-d') : '',
                                            'phone_number'     => $st->phone_number ?? '',
                                            'is_active'        => $st->is_active ? 1 : 0,
                                            'profile_photo_url'=> $st->profile_photo_url,
                                        ]) }})"
                                        title="Ubah Data Siswa"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-sky-50 hover:bg-sky-100 border border-sky-200 text-sky-700 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 shrink-0"></i>
                                    </button>

                                    {{-- Reset Password — icon only --}}
                                    <form action="{{ route('admin.users.reset-password', $st) }}" method="POST" class="inline-flex m-0 p-0"
                                        data-confirm="Reset kata sandi akun siswa <strong>{{ $st->name }}</strong> ke kata sandi bawaan (<code class='px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 font-mono font-bold text-xs'>{{ $st->getDefaultPassword() }}</code>)?"
                                        data-confirm-title="Reset Kata Sandi Siswa"
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
                                    <form action="{{ route('admin.siswa.destroy', $st) }}" method="POST" class="inline-flex m-0 p-0"
                                        data-confirm="Apakah Anda yakin ingin menghapus data siswa <strong>{{ $st->name }}</strong> (NISN: {{ $st->identity_number }})? Data dan riwayat presensi yang terkait akan dihapus secara permanen."
                                        data-confirm-title="Hapus Data Siswa"
                                        data-confirm-type="danger"
                                        data-confirm-btn="Ya, Hapus"
                                        data-confirm-icon="trash-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Data Siswa"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 shrink-0"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="inline-flex flex-col items-center gap-2">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i data-lucide="users" class="w-6 h-6 text-slate-400"></i>
                                    </span>
                                    <p class="text-sm font-bold text-slate-600">
                                        @if(request()->hasAny(['search', 'classroom_id']))
                                            Tidak ada siswa yang cocok dengan filter.
                                        @else
                                            Belum ada data siswa terdaftar.
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        @if(request()->hasAny(['search', 'classroom_id']))
                                            Coba ubah kata kunci atau <a href="{{ route('admin.siswa.index') }}" class="text-maarif-700 font-semibold hover:underline">reset filter</a>.
                                        @else
                                            Klik <button onclick="openAddModal()" class="text-maarif-700 font-semibold hover:underline">Tambah Siswa</button> untuk mendaftarkan siswa pertama.
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
            @if($students->hasPages())
                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $students->links() }}
                </div>
            @endif
    </div>

    {{-- ── MODAL TAMBAH SISWA ───────────────────────────────────────── --}}
    <div id="modalAddSiswa" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 hidden transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="modalAddTitle">
        <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-200/80 max-h-[92vh] flex flex-col overflow-hidden transform transition-all">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/60 shrink-0">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-maarif-50 text-maarif-700 border border-maarif-200/70 flex items-center justify-center shadow-xs">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 id="modalAddTitle" class="font-bold text-slate-900 heading-font text-base sm:text-lg">Tambah Siswa Baru</h3>
                        <p class="text-xs text-slate-400 font-medium">Registrasi profil, NISN, dan rombel siswa madrasah</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddModal()"
                    class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-all duration-150 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                    aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Form Body (Scrollable) --}}
            <form action="{{ route('admin.siswa.store') }}" method="POST" enctype="multipart/form-data" data-loading-form class="flex-1 overflow-y-auto p-6 space-y-6 text-xs">
                @csrf

                {{-- Group 1: Informasi Pokok Siswa --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                        <span class="w-1.5 h-3.5 bg-maarif-700 rounded-full"></span>
                        <span class="font-semibold text-slate-800 uppercase tracking-wider text-[11px]">Identitas Utama</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">
                                NISN <span class="text-rose-500">*</span>
                                <span class="text-[10px] font-normal text-slate-400 font-sans ml-1">(10 digit unik)</span>
                            </label>
                            <input type="text" name="identity_number" required maxlength="10" placeholder="0091234501"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none mono-font text-xs bg-slate-50/70 focus:bg-white transition placeholder:text-slate-400">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">
                                Nama Lengkap Siswa <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" required placeholder="Nama lengkap sesuai ijazah/akta"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none text-xs bg-slate-50/70 focus:bg-white transition placeholder:text-slate-400 font-medium">
                        </div>
                    </div>
                </div>

                {{-- Group 2: Penempatan Kelas & Autentikasi --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                        <span class="w-1.5 h-3.5 bg-maarif-700 rounded-full"></span>
                        <span class="font-semibold text-slate-800 uppercase tracking-wider text-[11px]">Rombel &amp; Akun</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">
                                Rombel / Kelas <span class="text-rose-500">*</span>
                            </label>
                            <select name="classroom_id" required
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50/70 focus:bg-white text-xs transition cursor-pointer font-medium">
                                <option value="">Pilih Rombongan Belajar</option>
                                @foreach($classrooms as $c)
                                    <option value="{{ $c->id }}">Kelas {{ $c->name }} (Tingkat {{ $c->grade_level }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">
                                Tanggal Lahir <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="birth_date" required
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none mono-font text-xs bg-slate-50/70 focus:bg-white transition">
                            <p class="text-[10.5px] text-slate-400 mt-1">Digunakan sebagai kata sandi login awal siswa.</p>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">
                                Alamat Email
                                <span class="text-[10px] font-normal text-slate-400 ml-1">(opsional)</span>
                            </label>
                            <input type="email" name="email" placeholder="nama@siswa.maarif.sch.id"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none text-xs bg-slate-50/70 focus:bg-white transition placeholder:text-slate-400">
                            <p class="text-[10.5px] text-slate-400 mt-1">Kosongkan untuk email sistem: <code class="mono-font text-slate-600">NISN@siswa...</code></p>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">
                                No. Handphone / WhatsApp
                                <span class="text-[10px] font-normal text-slate-400 ml-1">(opsional)</span>
                            </label>
                            <input type="text" name="phone_number" placeholder="08123456789"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none text-xs bg-slate-50/70 focus:bg-white transition placeholder:text-slate-400">
                            <p class="text-[10.5px] text-slate-400 mt-1">Nomor siswa atau kontak wali murid.</p>
                        </div>
                    </div>
                </div>

                {{-- Group 3: Foto Profil --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                        <span class="w-1.5 h-3.5 bg-maarif-700 rounded-full"></span>
                        <span class="font-semibold text-slate-800 uppercase tracking-wider text-[11px]">Foto Profil Siswa</span>
                    </div>

                    <div class="flex items-center gap-4 p-3.5 rounded-2xl bg-slate-50/70 border border-slate-200/80">
                        <div class="w-14 h-14 rounded-2xl bg-slate-200/80 border border-slate-300 flex items-center justify-center shrink-0 overflow-hidden text-slate-400">
                            <img id="addSiswaPreviewImg" src="" alt="Preview Foto" class="w-full h-full object-cover hidden">
                            <i id="addSiswaPlaceholder" data-lucide="user" class="w-6 h-6"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="block font-semibold text-slate-700 mb-1">Unggah Foto Siswa <span class="text-[10px] font-normal text-slate-400">(JPG, PNG, WebP maks 2MB)</span></label>
                            <input type="file" name="photo" id="addSiswaPhotoInput" accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-maarif-50 file:text-maarif-700 hover:file:bg-maarif-100 cursor-pointer">
                        </div>
                    </div>
                </div>

                {{-- Sticky Footer Inside Form --}}
                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" onclick="closeAddModal()"
                        class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-semibold text-xs border border-slate-200 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        Batal
                    </button>
                    <button type="submit"
                        class="py-2.5 px-6 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs shadow-xs transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Siswa</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── MODAL EDIT SISWA ─────────────────────────────────────────── --}}
    <div id="modalEditSiswa" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 hidden transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="modalEditTitle">
        <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-200/80 max-h-[92vh] flex flex-col overflow-hidden transform transition-all">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/60 shrink-0">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-700 border border-sky-200/70 flex items-center justify-center shadow-xs">
                        <i data-lucide="pencil" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 id="modalEditTitle" class="font-bold text-slate-900 heading-font text-base sm:text-lg">Perbarui Data Siswa</h3>
                        <p class="text-xs text-slate-400 font-medium">Ubah biodata, rombel, status aktif, atau foto siswa</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal()"
                    class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-all duration-150 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                    aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Form Body (Scrollable) --}}
            <form id="formEditSiswa" method="POST" enctype="multipart/form-data" data-loading-form class="flex-1 overflow-y-auto p-6 space-y-6 text-xs">
                @csrf
                @method('PUT')

                {{-- Photo block with Live Preview --}}
                <div class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl overflow-hidden shrink-0 border border-slate-300 flex items-center justify-center bg-slate-200 shadow-xs">
                            <img id="editSiswaPreviewImg" src="" alt="Foto Siswa" class="w-full h-full object-cover hidden">
                            <div id="editSiswaFallback" class="w-full h-full bg-maarif-700 text-white font-bold flex items-center justify-center text-lg">S</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="block font-semibold text-slate-700 mb-1">Ganti Foto Profil <span class="text-[10px] font-normal text-slate-400">(maks. 2MB)</span></label>
                            <input type="file" id="editSiswaPhoto" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">
                        </div>
                    </div>
                    <div id="editSiswaRemovePhotoBox" class="hidden pt-2.5 border-t border-slate-200/70">
                        <label class="inline-flex items-center text-xs text-rose-600 font-semibold cursor-pointer gap-2 select-none hover:text-rose-700">
                            <input type="checkbox" id="editSiswaRemovePhoto" name="remove_photo" value="1" class="rounded text-rose-600 focus:ring-rose-500 cursor-pointer w-3.5 h-3.5">
                            <span>Hapus foto saat ini (kembalikan ke inisial nama)</span>
                        </label>
                    </div>
                </div>

                {{-- Group 1: Data Pokok Siswa --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                        <span class="w-1.5 h-3.5 bg-sky-600 rounded-full"></span>
                        <span class="font-semibold text-slate-800 uppercase tracking-wider text-[11px]">Identitas Siswa</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">
                                NISN <span class="text-rose-500">*</span>
                                <span class="text-[10px] font-normal text-slate-400 font-sans ml-1">(10 digit)</span>
                            </label>
                            <input type="text" id="editSiswaNisn" name="identity_number" required maxlength="10"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none mono-font text-xs bg-slate-50/70 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">
                                Nama Lengkap Siswa <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="editSiswaName" name="name" required
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none text-xs bg-slate-50/70 focus:bg-white transition font-medium">
                        </div>
                    </div>
                </div>

                {{-- Group 2: Rombel, Tanggal Lahir, Kontak & Status --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                        <span class="w-1.5 h-3.5 bg-sky-600 rounded-full"></span>
                        <span class="font-semibold text-slate-800 uppercase tracking-wider text-[11px]">Akademik &amp; Akun</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">
                                Rombel / Kelas <span class="text-rose-500">*</span>
                            </label>
                            <select id="editSiswaClassroomId" name="classroom_id" required
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none bg-slate-50/70 focus:bg-white text-xs transition cursor-pointer font-medium">
                                <option value="">Pilih Kelas</option>
                                @foreach($classrooms as $c)
                                    <option value="{{ $c->id }}">Kelas {{ $c->name }} (Tingkat {{ $c->grade_level }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">
                                Tanggal Lahir <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" id="editSiswaBirthDate" name="birth_date" required
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none mono-font text-xs bg-slate-50/70 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" id="editSiswaEmail" name="email" required
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none text-xs bg-slate-50/70 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">
                                No. Handphone
                                <span class="text-[10px] font-normal text-slate-400 ml-1">(opsional)</span>
                            </label>
                            <input type="text" id="editSiswaPhone" name="phone_number"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none text-xs bg-slate-50/70 focus:bg-white transition">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 mb-1.5">Status Akun Siswa</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/60 cursor-pointer hover:bg-slate-100/60 transition">
                                    <input type="radio" name="is_active" id="editSiswaStatusActive" value="1" class="text-maarif-700 focus:ring-maarif-600">
                                    <div>
                                        <span class="font-semibold text-slate-800 text-xs block">Aktif</span>
                                        <span class="text-[10px] text-slate-400">Dapat presensi dan login sistem</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/60 cursor-pointer hover:bg-slate-100/60 transition">
                                    <input type="radio" name="is_active" id="editSiswaStatusInactive" value="0" class="text-rose-600 focus:ring-rose-500">
                                    <div>
                                        <span class="font-semibold text-slate-800 text-xs block">Nonaktif</span>
                                        <span class="text-[10px] text-slate-400">Akses login ditangguhkan</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sticky Footer Inside Form --}}
                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal()"
                        class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-semibold text-xs border border-slate-200 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        Batal
                    </button>
                    <button type="submit"
                        class="py-2.5 px-6 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs shadow-xs transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Perbarui Siswa</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Live preview for add photo
    const addPhotoInput = document.getElementById('addSiswaPhotoInput');
    const addPreviewImg = document.getElementById('addSiswaPreviewImg');
    const addPlaceholder = document.getElementById('addSiswaPlaceholder');
    if (addPhotoInput) {
        addPhotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    addPreviewImg.src = evt.target.result;
                    addPreviewImg.classList.remove('hidden');
                    if (addPlaceholder) addPlaceholder.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Live preview for edit photo
    const editPhotoInput = document.getElementById('editSiswaPhoto');
    const editPreviewImg = document.getElementById('editSiswaPreviewImg');
    const editFallback   = document.getElementById('editSiswaFallback');
    if (editPhotoInput) {
        editPhotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    editPreviewImg.src = evt.target.result;
                    editPreviewImg.classList.remove('hidden');
                    if (editFallback) editFallback.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    function openAddModal() {
        const modal = document.getElementById('modalAddSiswa');
        if (!modal) return;
        modal.classList.remove('hidden');
        const input = modal.querySelector('input[name="identity_number"]');
        if (input) input.focus();
    }
    function closeAddModal() {
        const modal = document.getElementById('modalAddSiswa');
        if (modal) modal.classList.add('hidden');
    }

    function openEditModal(student) {
        if (!student) return;
        const form = document.getElementById('formEditSiswa');
        if (form) form.action = '/admin/siswa/' + student.id;

        const nisn = document.getElementById('editSiswaNisn');
        if (nisn) nisn.value = student.identity_number || '';

        const name = document.getElementById('editSiswaName');
        if (name) name.value = student.name || '';

        const email = document.getElementById('editSiswaEmail');
        if (email) email.value = student.email || '';

        const cls = document.getElementById('editSiswaClassroomId');
        if (cls) cls.value = student.classroom_id || '';

        const bdate = document.getElementById('editSiswaBirthDate');
        if (bdate) bdate.value = student.birth_date || '';

        const phone = document.getElementById('editSiswaPhone');
        if (phone) phone.value = student.phone_number || '';
        
        const statusActive = document.getElementById('editSiswaStatusActive');
        const statusInactive = document.getElementById('editSiswaStatusInactive');
        if (student.is_active == 1) {
            if (statusActive) statusActive.checked = true;
        } else {
            if (statusInactive) statusInactive.checked = true;
        }

        const previewImg  = document.getElementById('editSiswaPreviewImg');
        const fallback    = document.getElementById('editSiswaFallback');
        const removeBox   = document.getElementById('editSiswaRemovePhotoBox');
        const removeCheck = document.getElementById('editSiswaRemovePhoto');
        const photoInput  = document.getElementById('editSiswaPhoto');

        if (photoInput)  photoInput.value = '';
        if (removeCheck) removeCheck.checked = false;

        if (student.profile_photo_url) {
            if (previewImg) {
                previewImg.src = student.profile_photo_url;
                previewImg.classList.remove('hidden');
            }
            if (fallback) fallback.classList.add('hidden');
            if (removeBox) removeBox.classList.remove('hidden');
        } else {
            if (previewImg) {
                previewImg.src = '';
                previewImg.classList.add('hidden');
            }
            if (fallback) {
                fallback.textContent = student.name ? student.name.charAt(0).toUpperCase() : 'S';
                fallback.classList.remove('hidden');
            }
            if (removeBox) removeBox.classList.add('hidden');
        }

        const modal = document.getElementById('modalEditSiswa');
        if (modal) modal.classList.remove('hidden');
        if (name) name.focus();
    }

    function closeEditModal() {
        const modal = document.getElementById('modalEditSiswa');
        if (modal) modal.classList.add('hidden');
    }

    // Close modal on backdrop click & ESC key
    ['modalAddSiswa', 'modalEditSiswa'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('click', function (e) {
                if (e.target === el) {
                    el.classList.add('hidden');
                }
            });
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddModal();
            closeEditModal();
        }
    });
</script>
@endpush
