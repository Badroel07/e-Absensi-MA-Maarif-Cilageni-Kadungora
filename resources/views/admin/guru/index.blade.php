@extends('layouts.admin')

@section('title', 'Data Pokok Guru — Admin')
@section('page-title', 'Data Bapak/Ibu Guru')

@section('content')
<div class="space-y-6">
    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Data Dewan Guru</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola data pokok dewan guru, NIP/NUPTK, nomor kontak, dan kredensial akun</p>
        </div>
    </div>

    <!-- Action Bar & Search -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.guru.index') }}" class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 flex-1">
            <div class="relative flex-1 sm:max-w-xs">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIP, Nama, Email..."
                    class="w-full pl-9 pr-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition-all">
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white rounded-2xl text-xs font-extrabold transition-all duration-150 active:scale-95 shadow-md shadow-slate-800/20 cursor-pointer">
                <i data-lucide="search" class="w-4 h-4"></i>
                <span>Cari</span>
            </button>
            @if(request('search'))
                <a href="{{ route('admin.guru.index') }}" class="inline-flex items-center justify-center px-3.5 py-2.5 text-xs text-slate-500 hover:text-slate-800 font-bold transition-colors duration-150 cursor-pointer">Reset</a>
            @endif
        </form>

        <button type="button" onclick="openAddModal()" class="inline-flex items-center justify-center gap-2 py-3 px-5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs sm:text-sm rounded-2xl shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] cursor-pointer shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Data Guru</span>
        </button>
    </div>

    <!-- Table Guru -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-500 uppercase font-extrabold text-[11px] tracking-wider">
                        <th class="py-3.5 px-5">NIP / NUPTK</th>
                        <th class="py-3.5 px-5">Nama Bapak/Ibu Guru</th>
                        <th class="py-3.5 px-5">Email Madrasah</th>
                        <th class="py-3.5 px-5">Alamat Email</th>
                        <th class="py-3.5 px-5">Tanggal Lahir</th>
                        <th class="py-3.5 px-5">No. HP</th>
                        <th class="py-3.5 px-5 text-center">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi & Reset</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($teachers as $g)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-5 font-mono font-bold text-slate-900">{{ $g->identity_number }}</td>
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-3">
                                    @if($g->profile_photo_url)
                                        <img src="{{ $g->profile_photo_url }}" alt="{{ $g->name }}" class="w-8 h-8 rounded-xl object-cover shrink-0 border border-slate-200 shadow-2xs">
                                    @else
                                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 font-extrabold text-xs flex items-center justify-center shrink-0 border border-amber-200">
                                            {{ substr($g->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <span class="font-bold text-slate-900">{{ $g->name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-5 text-slate-600 font-medium">{{ $g->email ?? '-' }}</td>
                            <td class="py-4 px-5 text-slate-700 font-mono font-medium">
                                {{ $g->birth_date ? $g->birth_date->format('d-m-Y') : '-' }}
                            </td>
                            <td class="py-4 px-5 text-slate-600">{{ $g->phone_number ?? '-' }}</td>
                            <td class="py-4 px-5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold {{ $g->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/80' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                    {{ $g->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <!-- Riwayat Absen Guru -->
                                    <a href="{{ route('admin.guru.riwayat', $g) }}" title="Lihat Riwayat Presensi Bapak/Ibu Guru" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer shadow-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                        <i data-lucide="history" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                        <span>Riwayat</span>
                                    </a>

                                    <!-- Edit Guru -->
                                    <button type="button" onclick="openEditModal({{ json_encode([
                                        'id' => $g->id,
                                        'identity_number' => $g->identity_number,
                                        'name' => $g->name,
                                        'email' => $g->email ?? '',
                                        'birth_date' => $g->birth_date ? $g->birth_date->format('Y-m-d') : '',
                                        'phone_number' => $g->phone_number ?? '',
                                        'is_active' => $g->is_active ? 1 : 0,
                                        'profile_photo_url' => $g->profile_photo_url,
                                    ]) }})" title="Ubah Data Bapak/Ibu Guru" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer shadow-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                        <span>Edit</span>
                                    </button>

                                    <!-- 1-Klik Reset Password -->
                                    <form action="{{ route('admin.users.reset-password', $g) }}" method="POST" class="inline-flex m-0 p-0"
                                        data-confirm="Reset kata sandi akun guru <strong>{{ $g->name }}</strong> ke format bawaan tanggal lahir (<code class='px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 font-mono font-bold text-xs'>{{ $g->getDefaultPassword() }}</code>)?"
                                        data-confirm="Reset kata sandi akun guru <strong>{{ $g->name }}</strong> ke kata sandi bawaan (<code class='px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 font-mono font-bold text-xs'>{{ $g->getDefaultPassword() }}</code>)?"
                                        data-confirm-title="Reset Kata Sandi Guru"
                                        data-confirm-type="warning"
                                        data-confirm-btn="Ya, Reset Sandi"
                                        data-confirm-icon="key-round">
                                        @csrf
                                        <button type="submit" title="Reset Kata Sandi ke Format Tanggal Lahir" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer shadow-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                                        <button type="submit" title="Reset Kata Sandi ke Format Bawaan" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer shadow-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                                            <i data-lucide="key-round" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                            <span>Reset Sandi</span>
                                        </button>
                                    </form>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.guru.destroy', $g) }}" method="POST" class="inline-flex m-0 p-0"
                                        data-confirm="Apakah Anda yakin ingin menghapus data guru <strong>{{ $g->name }}</strong>? Riwayat penugasan dan akun guru ini akan dinonaktifkan."
                                        data-confirm-title="Hapus Data Guru"
                                        data-confirm-type="danger"
                                        data-confirm-btn="Ya, Hapus"
                                        data-confirm-icon="trash-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Data Guru" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer shadow-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 font-medium">Tidak ada data guru ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-slate-100">
            {{ $teachers->links() }}
        </div>
    </div>

    <!-- Modal Tambah Guru -->
    <div id="modalAddGuru" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-md bg-white rounded-3xl p-6 sm:p-7 shadow-2xl space-y-5 border border-slate-200 animate-in fade-in zoom-in duration-200 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center border border-emerald-200/80">
                        <i data-lucide="user-plus" class="w-5 h-5 text-emerald-700"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-900 heading-font text-base">Tambah Guru Baru</h3>
                </div>
                <button type="button" onclick="closeAddModal()" class="p-2 -mr-1 -mt-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-150 active:scale-95 cursor-pointer" aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.guru.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">NIP / NUPTK / No. Pegawai <span class="text-rose-500">*</span></label>
                    <input type="text" name="identity_number" required placeholder="Contoh: 198001012005011001"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-mono transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Ust. H. Ahmad Dahlan, S.Pd.I"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Email Madrasah (Opsional)</label>
                    <input type="email" name="email" placeholder="Contoh: ahmad@maarif.sch.id"
                    <label class="block font-bold text-slate-700 mb-1.5">Email Madrasah <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" required placeholder="Contoh: ahmad@maarif.sch.id"
                    <label class="block font-bold text-slate-700 mb-1.5">Alamat Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" required placeholder="Contoh: guru@email.com"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Tanggal Lahir <span class="text-rose-500">*</span></label>
                    <input type="date" name="birth_date" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">No. Handphone (Opsional)</label>
                    <input type="text" name="phone_number" placeholder="Contoh: 08123456789"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Foto Profil Guru (Opsional, Maks. 2MB)</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp"
                        class="w-full px-4 py-2 border border-slate-300 rounded-2xl text-xs bg-slate-50/50 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200 cursor-pointer transition-all">
                </div>

                <div class="pt-3 flex items-center gap-3">
                    <button type="button" onclick="closeAddModal()" class="flex-1 py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-bold text-xs sm:text-sm border border-slate-200/60 transition-all duration-150 active:scale-[0.98] cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-3 px-4 rounded-2xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Guru</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Guru -->
    <div id="modalEditGuru" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-md bg-white rounded-3xl p-6 sm:p-7 shadow-2xl space-y-5 border border-slate-200 animate-in fade-in zoom-in duration-200 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-800 flex items-center justify-center border border-sky-200/80">
                        <i data-lucide="user-cog" class="w-5 h-5 text-sky-700"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-900 heading-font text-base">Ubah Data Guru</h3>
                </div>
                <button type="button" onclick="closeEditModal()" class="p-2 -mr-1 -mt-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-150 active:scale-95 cursor-pointer" aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEditGuru" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <!-- Photo Management Block -->
                <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200 space-y-3">
                    <div class="flex items-center space-x-3.5">
                        <div class="w-13 h-13 rounded-2xl overflow-hidden bg-slate-200 shrink-0 relative flex items-center justify-center border border-slate-300 shadow-2xs">
                            <img id="editGuruPreviewImg" src="" alt="Foto Guru" class="w-full h-full object-cover hidden">
                            <div id="editGuruFallback" class="w-full h-full bg-emerald-700 text-white font-black flex items-center justify-center text-lg">G</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="block font-bold text-slate-700 mb-1">Ganti Foto Profil (Maks. 2MB)</label>
                            <input type="file" id="editGuruPhoto" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="w-full text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 cursor-pointer">
                        </div>
                    </div>
                    <div id="editGuruRemovePhotoBox" class="hidden pt-2 border-t border-slate-200 flex items-center">
                        <label class="inline-flex items-center text-xs text-rose-600 font-bold cursor-pointer">
                            <input type="checkbox" id="editGuruRemovePhoto" name="remove_photo" value="1" class="rounded-lg text-rose-600 mr-2 focus:ring-rose-500 cursor-pointer">
                            Hapus foto profil saat ini (kembali ke inisial)
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">NIP / NUPTK / No. Pegawai <span class="text-rose-500">*</span></label>
                    <input type="text" id="editGuruNip" name="identity_number" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-mono transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                    <input type="text" id="editGuruName" name="name" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Alamat Email (Opsional)</label>
                    <input type="email" id="editGuruEmail" name="email"
                    <label class="block font-bold text-slate-700 mb-1.5">Alamat Email <span class="text-rose-500">*</span></label>
                    <input type="email" id="editGuruEmail" name="email" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Tanggal Lahir <span class="text-rose-500">*</span></label>
                    <input type="date" id="editGuruBirthDate" name="birth_date" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">No. Handphone (WhatsApp)</label>
                    <input type="text" id="editGuruPhone" name="phone_number"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Status Akun</label>
                    <select id="editGuruIsActive" name="is_active" class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-white font-bold transition-all">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>

                <div class="pt-3 flex items-center gap-3">
                    <button type="button" onclick="closeEditModal()" class="flex-1 py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-bold text-xs sm:text-sm border border-slate-200/60 transition-all duration-150 active:scale-[0.98] cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-3 px-4 rounded-2xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer">
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
    function openAddModal() {
        document.getElementById('modalAddGuru').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('modalAddGuru').classList.add('hidden');
    }

    function openEditModal(teacher) {
        document.getElementById('formEditGuru').action = '/admin/guru/' + teacher.id;
        document.getElementById('editGuruNip').value = teacher.identity_number;
        document.getElementById('editGuruName').value = teacher.name;
        document.getElementById('editGuruEmail').value = teacher.email || '';
        document.getElementById('editGuruBirthDate').value = teacher.birth_date;
        document.getElementById('editGuruPhone').value = teacher.phone_number || '';
        document.getElementById('editGuruIsActive').value = teacher.is_active;

        const previewImg = document.getElementById('editGuruPreviewImg');
        const fallback = document.getElementById('editGuruFallback');
        const removeBox = document.getElementById('editGuruRemovePhotoBox');
        const removeCheck = document.getElementById('editGuruRemovePhoto');
        const photoInput = document.getElementById('editGuruPhoto');
        
        if (photoInput) photoInput.value = '';
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
    }
    function closeEditModal() {
        document.getElementById('modalEditGuru').classList.add('hidden');
    }
</script>
@endpush
