@extends('layouts.admin')

@section('title', 'Data Pokok Siswa — Admin')
@section('page-title', 'Data Siswa')

@section('content')
<div class="space-y-6">
    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Data Pokok Siswa</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola data induk siswa madrasah, rombel kelas, NISN, dan akun presensi</p>
        </div>
    </div>

    <!-- Action Bar & Filters -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.siswa.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            <div class="relative w-full sm:w-72">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NISN atau Nama Siswa..."
                    class="w-full pl-10 pr-4 py-2.5 text-xs border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition">
            </div>
            
            <select name="classroom_id" class="px-4 py-2.5 text-xs border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition cursor-pointer">
                <option value="">Semua Kelas</option>
                @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" {{ request('classroom_id') == $c->id ? 'selected' : '' }}>
                        Kelas {{ $c->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white rounded-2xl text-xs font-extrabold transition-all duration-150 active:scale-95 shadow-md shadow-slate-800/20 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['search', 'classroom_id']))
                <a href="{{ route('admin.siswa.index') }}" class="inline-flex items-center justify-center px-3 py-2 text-xs text-slate-500 hover:text-slate-800 font-bold transition-colors duration-150 cursor-pointer">Reset</a>
            @endif
        </form>

        <!-- Tambah Siswa Button -->
        <button type="button" onclick="openAddModal()" class="inline-flex items-center justify-center gap-2 py-3 px-5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs rounded-2xl shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Data Siswa</span>
        </button>
    </div>

    <!-- Table Siswa -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-700 uppercase font-bold text-[11px] tracking-wider">
                        <th class="py-3.5 px-5">NISN (10-Digit)</th>
                        <th class="py-3.5 px-5">Nama Lengkap</th>
                        <th class="py-3.5 px-5">Kelas</th>
                        <th class="py-3.5 px-5">Tanggal Lahir</th>
                        <th class="py-3.5 px-5">No. HP</th>
                        <th class="py-3.5 px-5 text-center">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi & Reset</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $st)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-800 font-mono font-bold text-xs border border-slate-200">
                                    {{ $st->identity_number }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5">
                                <div class="flex items-center space-x-3">
                                    @if($st->profile_photo_url)
                                        <img src="{{ $st->profile_photo_url }}" alt="{{ $st->name }}" class="w-8 h-8 rounded-xl object-cover shrink-0 border border-slate-200 shadow-2xs">
                                    @else
                                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 font-black text-xs flex items-center justify-center shrink-0 border border-emerald-200">
                                            {{ substr($st->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <span class="font-bold text-slate-900 text-sm truncate">{{ $st->name }}</span>
                                    <div class="min-w-0">
                                        <span class="font-bold text-slate-900 text-sm truncate block">{{ $st->name }}</span>
                                        <span class="text-[11px] text-slate-400 font-mono truncate block">{{ $st->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-extrabold text-xs">
                                    {{ $st->classroom->name ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-slate-700 font-mono font-semibold">
                                {{ $st->birth_date ? $st->birth_date->format('d-m-Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-5 text-slate-500 font-medium">{{ $st->phone_number ?? '-' }}</td>
                            <td class="py-3.5 px-5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold {{ $st->is_active ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                    {{ $st->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <!-- Riwayat Absen Siswa -->
                                    <a href="{{ route('admin.siswa.riwayat', $st) }}" title="Lihat Riwayat Presensi" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 shadow-xs">
                                        <i data-lucide="history" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                        <span>Riwayat</span>
                                    </a>

                                    <!-- Edit Siswa -->
                                    <button type="button" onclick="openEditModal({{ json_encode([
                                        'id' => $st->id,
                                        'identity_number' => $st->identity_number,
                                        'name' => $st->name,
                                        'email' => $st->email,
                                        'classroom_id' => $st->classroom_id,
                                        'birth_date' => $st->birth_date ? $st->birth_date->format('Y-m-d') : '',
                                        'phone_number' => $st->phone_number ?? '',
                                        'is_active' => $st->is_active ? 1 : 0,
                                        'profile_photo_url' => $st->profile_photo_url,
                                    ]) }})" title="Ubah Data Siswa" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 shadow-xs">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                        <span>Edit</span>
                                    </button>

                                    <!-- 1-Klik Reset Password -->
                                    <form action="{{ route('admin.users.reset-password', $st) }}" method="POST" class="inline-flex m-0 p-0"
                                        data-confirm="Reset kata sandi akun siswa <strong>{{ $st->name }}</strong> ke format bawaan tanggal lahir (<code class='px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 font-mono font-bold text-xs'>{{ $st->getDefaultPassword() }}</code>)?"
                                        data-confirm="Reset kata sandi akun siswa <strong>{{ $st->name }}</strong> ke kata sandi bawaan (<code class='px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 font-mono font-bold text-xs'>{{ $st->getDefaultPassword() }}</code>)?"
                                        data-confirm-title="Reset Kata Sandi Siswa"
                                        data-confirm-type="warning"
                                        data-confirm-btn="Ya, Reset Sandi"
                                        data-confirm-icon="key-round">
                                        @csrf
                                        <button type="submit" title="Reset Kata Sandi ke Format Tanggal Lahir" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 shadow-xs">
                                        <button type="submit" title="Reset Kata Sandi ke Format Bawaan" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 shadow-xs">
                                            <i data-lucide="key-round" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                            <span>Reset</span>
                                        </button>
                                    </form>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.siswa.destroy', $st) }}" method="POST" class="inline-flex m-0 p-0"
                                        data-confirm="Apakah Anda yakin ingin menghapus data siswa <strong>{{ $st->name }}</strong> (NISN: {{ $st->identity_number }})? Data dan riwayat presensi yang terkait akan dihapus secara permanen."
                                        data-confirm-title="Hapus Data Siswa"
                                        data-confirm-type="danger"
                                        data-confirm-btn="Ya, Hapus"
                                        data-confirm-icon="trash-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Data Siswa" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 shadow-xs">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                <p class="text-xs font-medium">Tidak ada data siswa ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $students->links() }}
        </div>
    </div>

    <!-- Modal Tambah Siswa -->
    <div id="modalAddSiswa" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-md bg-white rounded-3xl p-6 shadow-2xl space-y-4 border border-slate-200 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-900 heading-font text-base">Tambah Data Siswa</h3>
                </div>
                <button type="button" onclick="closeAddModal()" class="p-2 -mr-1 -mt-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400" aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.siswa.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">NISN (10 Digit Angka)</label>
                    <input type="text" name="identity_number" required maxlength="10" placeholder="Contoh: 0091234501"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-mono text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Siswa</label>
                    <input type="text" name="name" required placeholder="Contoh: Muhammad Al-Fatih"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-medium text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Alamat Email Siswa <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" placeholder="Contoh: 0091234501@siswa.maarif.sch.id"
                    <input type="email" name="email" required placeholder="Contoh: siswa@gmail.com"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-medium text-xs bg-slate-50/50 focus:bg-white transition">
                    <p class="text-[10px] text-slate-400 mt-1">Kosongkan untuk membuat otomatis: <code>NISN@siswa.maarif.sch.id</code></p>
                    <p class="text-[10px] text-slate-400 mt-1">Gunakan alamat email pribadi siswa yang aktif untuk login.</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kelas</label>
                    <select name="classroom_id" required class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium text-xs transition">
                        <option value="">Pilih Kelas</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}">Kelas {{ $c->name }} (Tingkat {{ $c->grade_level }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Lahir <span class="text-rose-500">*</span></label>
                    <input type="date" name="birth_date" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-mono text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">No. Handphone (Opsional)</label>
                    <input type="text" name="phone_number" placeholder="Contoh: 08123456789"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Foto Profil Siswa (Opsional, Maks. 2MB)</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp"
                        class="w-full px-3 py-1.5 border border-slate-300 rounded-2xl text-xs bg-slate-50/50 file:mr-2 file:py-1 file:px-2.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 cursor-pointer">
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

    <!-- Modal Edit Siswa -->
    <div id="modalEditSiswa" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-md bg-white rounded-3xl p-6 shadow-2xl space-y-4 border border-slate-200 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                        <i data-lucide="pencil" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-900 heading-font text-base">Ubah Data Siswa</h3>
                </div>
                <button type="button" onclick="closeEditModal()" class="p-2 -mr-1 -mt-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400" aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEditSiswa" method="POST" enctype="multipart/form-data" class="space-y-3.5 text-xs">
                @csrf
                @method('PUT')

                <!-- Photo Management Block -->
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-2xl overflow-hidden bg-slate-200 shrink-0 relative flex items-center justify-center border border-slate-300">
                            <img id="editSiswaPreviewImg" src="" alt="Foto Siswa" class="w-full h-full object-cover hidden">
                            <div id="editSiswaFallback" class="w-full h-full bg-emerald-700 text-white font-bold flex items-center justify-center text-base">S</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="block font-bold text-slate-700 mb-0.5">Ganti Foto Profil (Maks. 2MB)</label>
                            <input type="file" id="editSiswaPhoto" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="w-full text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 cursor-pointer">
                        </div>
                    </div>
                    <div id="editSiswaRemovePhotoBox" class="hidden pt-1.5 border-t border-slate-200/70 flex items-center">
                        <label class="inline-flex items-center text-[11px] text-rose-600 font-semibold cursor-pointer">
                            <input type="checkbox" id="editSiswaRemovePhoto" name="remove_photo" value="1" class="rounded text-rose-600 mr-1.5 focus:ring-rose-500 cursor-pointer">
                            Hapus foto profil saat ini (kembali ke inisial)
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">NISN (10 Digit Angka)</label>
                    <input type="text" id="editSiswaNisn" name="identity_number" required maxlength="10"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-mono text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Siswa</label>
                    <input type="text" id="editSiswaName" name="name" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-medium text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Alamat Email Siswa <span class="text-rose-500">*</span></label>
                    <input type="email" id="editSiswaEmail" name="email" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-medium text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kelas / Rombel</label>
                    <select id="editSiswaClassroomId" name="classroom_id" required class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium text-xs transition">
                        <option value="">Pilih Kelas</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}">Kelas {{ $c->name }} (Tingkat {{ $c->grade_level }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Lahir</label>
                    <input type="date" id="editSiswaBirthDate" name="birth_date" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none font-mono text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">No. Handphone</label>
                    <input type="text" id="editSiswaPhone" name="phone_number"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none text-xs bg-slate-50/50 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status Akun</label>
                    <select id="editSiswaIsActive" name="is_active" class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium text-xs transition">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
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
    function openAddModal() {
        document.getElementById('modalAddSiswa').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('modalAddSiswa').classList.add('hidden');
    }

    function openEditModal(student) {
        document.getElementById('formEditSiswa').action = '/admin/siswa/' + student.id;
        document.getElementById('editSiswaNisn').value = student.identity_number;
        document.getElementById('editSiswaName').value = student.name;
        document.getElementById('editSiswaEmail').value = student.email || '';
        document.getElementById('editSiswaClassroomId').value = student.classroom_id;
        document.getElementById('editSiswaBirthDate').value = student.birth_date;
        document.getElementById('editSiswaPhone').value = student.phone_number || '';
        document.getElementById('editSiswaIsActive').value = student.is_active;

        const previewImg = document.getElementById('editSiswaPreviewImg');
        const fallback = document.getElementById('editSiswaFallback');
        const removeBox = document.getElementById('editSiswaRemovePhotoBox');
        const removeCheck = document.getElementById('editSiswaRemovePhoto');
        const photoInput = document.getElementById('editSiswaPhoto');
        
        if (photoInput) photoInput.value = '';
        if (removeCheck) removeCheck.checked = false;

        if (student.profile_photo_url) {
            previewImg.src = student.profile_photo_url;
            previewImg.classList.remove('hidden');
            fallback.classList.add('hidden');
            removeBox.classList.remove('hidden');
        } else {
            previewImg.src = '';
            previewImg.classList.add('hidden');
            fallback.textContent = student.name ? student.name.charAt(0).toUpperCase() : 'S';
            fallback.classList.remove('hidden');
            removeBox.classList.add('hidden');
        }

        document.getElementById('modalEditSiswa').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('modalEditSiswa').classList.add('hidden');
    }
</script>
@endpush
