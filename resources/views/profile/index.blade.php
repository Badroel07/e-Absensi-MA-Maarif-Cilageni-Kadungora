@extends('layouts.app')

@section('title', 'Profil Akun — MA Ma\'arif Cilageni')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Profil Pengguna</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Informasi identitas akun, foto profil madrasah, dan manajemen keamanan kata sandi</p>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center space-x-3">
            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <div class="flex items-center space-x-2 font-bold">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                <span>Terjadi kendala pada pengisian data:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-rose-700 text-[11px] ml-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6">
        <!-- 1. Profile Photo & Identity Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 pb-6 border-b border-slate-100 text-center sm:text-left">
                <!-- Photo Display & Instant Preview Area -->
                <div class="relative group shrink-0">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl overflow-hidden ring-4 ring-slate-100 shadow-md bg-slate-100 flex items-center justify-center relative">
                        <img id="preview-avatar-img" 
                             src="{{ $user->profile_photo_url ?? '' }}" 
                             alt="{{ $user->name }}" 
                             class="w-full h-full object-cover {{ $user->profile_photo_url ? '' : 'hidden' }}">
                        
                        <div id="fallback-avatar" 
                             class="w-full h-full bg-linear-to-br from-maarif-700 to-maarif-900 text-white font-black text-3xl sm:text-4xl flex items-center justify-center {{ $user->profile_photo_url ? 'hidden' : '' }}">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    </div>

                    <!-- Trigger Button overlay -->
                    <label for="photo-input" 
                           title="Pilih foto profil"
                           class="absolute -bottom-2 -right-2 p-2 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white rounded-2xl shadow-md shadow-emerald-900/25 cursor-pointer transition-all duration-150 active:scale-95 flex items-center justify-center border-2 border-white focus-within:ring-2 focus-within:ring-emerald-600">
                        <i data-lucide="camera" class="w-4 h-4"></i>
                    </label>
                </div>

                <div class="flex-1 min-w-0 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 heading-font break-words">{{ $user->name }}</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold tracking-wide {{ $user->role === 'guru' ? 'bg-amber-50 text-amber-800 border border-amber-300' : ($user->role === 'admin' ? 'bg-purple-50 text-purple-800 border border-purple-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200/80') }}">
                            {{ $user->role === 'guru' ? 'Bapak/Ibu Guru' : ($user->role === 'admin' ? 'Admin' : 'Siswa Madrasah') }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-mono font-medium">Nomor Identitas (NISN/NIP): <span class="font-bold text-slate-800">{{ $user->identity_number }}</span></p>
                    <p class="text-xs text-slate-400">Terdaftar di sistem presensi MA Ma'arif Cilageni Kadungora.</p>
                </div>
            </div>

            <!-- Upload Form & Actions -->
            <form id="photo-upload-form" action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden">

                <!-- Actions when a photo is newly selected -->
                <div id="photo-upload-controls" class="hidden p-4 bg-emerald-50/80 border border-emerald-200 rounded-2xl space-y-3">
                    <div class="flex items-center space-x-2 text-xs text-emerald-900">
                        <i data-lucide="image" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                        <span class="truncate font-bold" id="selected-file-name">Foto dipilih</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <button type="submit" class="px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold rounded-xl text-xs inline-flex items-center justify-center gap-1.5 shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                            <span>Simpan Foto Baru</span>
                        </button>
                        <button type="button" id="cancel-photo-btn" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-bold border border-slate-200/80 rounded-xl text-xs inline-flex items-center justify-center gap-1 transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            <span>Batal</span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Photo Management Buttons -->
            <div class="flex flex-wrap items-center gap-2.5">
                <label for="photo-input" class="cursor-pointer px-4 py-2.5 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white font-extrabold rounded-2xl text-xs inline-flex items-center justify-center gap-2 transition-all duration-150 active:scale-95 shadow-xs focus-within:ring-2 focus-within:ring-slate-700">
                    <i data-lucide="camera" class="w-4 h-4 text-white/90"></i>
                    <span>{{ $user->profile_photo_path ? 'Ganti Foto Profil' : 'Unggah Foto Profil' }}</span>
                </label>

                @if($user->profile_photo_path)
                    <form action="{{ route('profile.photo.destroy') }}" method="POST"
                        data-confirm="Apakah Anda yakin ingin menghapus foto profil Anda? Tampilan avatar akan dikembalikan ke inisial default nama Anda."
                        data-confirm-title="Hapus Foto Profil"
                        data-confirm-type="danger"
                        data-confirm-btn="Ya, Hapus Foto"
                        data-confirm-icon="trash-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-extrabold rounded-2xl text-xs inline-flex items-center justify-center gap-1.5 transition-all duration-150 shadow-xs active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500" title="Hapus Foto Profil">
                            <i data-lucide="trash-2" class="w-4 h-4 text-white/90"></i>
                            <span>Hapus Foto</span>
                        </button>
                    </form>
                @endif
                <span class="text-[11px] text-slate-400">Maks. <strong>2 MB</strong> (JPG, PNG, JPEG, WEBP)</span>
            </div>

            <!-- Detailed Identity Attributes -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs pt-2">
                <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 flex flex-col justify-center">
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Tanggal Lahir</p>
                    <p class="font-bold text-slate-800 mt-1">{{ $user->birth_date ? $user->birth_date->format('d F Y') : '-' }}</p>
                </div>
                @if($user->classroom)
                    <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 flex flex-col justify-center">
                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Rombel / Kelas</p>
                        <p class="font-bold text-emerald-800 mt-1">Kelas {{ $user->classroom->name }}</p>
                    </div>
                @endif
                @if($user->email)
                    <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 flex flex-col justify-center min-w-0">
                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Alamat Email</p>
                        <p class="font-bold text-slate-800 mt-1 break-all select-all leading-snug">{{ $user->email }}</p>
                    </div>
                @endif
                @if($user->phone_number)
                    <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 flex flex-col justify-center">
                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">No. Handphone</p>
                        <p class="font-bold text-slate-800 mt-1 font-mono">{{ $user->phone_number }}</p>
                    </div>
                @endif
                <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 flex flex-col justify-center">
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Status Akun</p>
                    <p class="font-bold text-emerald-700 mt-1 inline-flex items-center gap-1.5">
                        <i data-lucide="badge-check" class="w-4 h-4 shrink-0 text-emerald-600"></i>
                        <span class="whitespace-nowrap">Aktif Terverifikasi</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- 2. Password Change Form Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-6">
            <div class="pb-4 border-b border-slate-100 flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center border border-emerald-200/80 shrink-0">
                    <i data-lucide="shield-check" class="w-5 h-5 text-emerald-700"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900 heading-font">Perbarui Kata Sandi Akun</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pastikan menggunakan kombinasi kata sandi yang aman dan tidak mudah ditebak.</p>
                </div>
            </div>
            
            <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi Saat Ini <span class="text-rose-500">*</span></label>
                    <input type="password" name="current_password" required placeholder="Masukkan kata sandi saat ini"
                        class="w-full px-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition-all">
                    @error('current_password')
                        <p class="text-rose-600 text-[11px] font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi Baru <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" required minlength="6"
                            placeholder="Minimal 6 karakter"
                            class="w-full px-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition-all">
                        @error('password')
                            <p class="text-rose-600 text-[11px] font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span></label>
                        <input type="password" name="password_confirmation" required minlength="6"
                            placeholder="Ulangi kata sandi baru"
                            class="w-full px-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition-all">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold rounded-2xl text-xs sm:text-sm shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Perubahan Kata Sandi</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- 3. Account Session & Logout Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-700 flex items-center justify-center border border-rose-200/80 shrink-0">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900 heading-font">Keluar dari Sesi Aplikasi</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Akhiri sesi aktif Anda di perangkat ini dengan aman.</p>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST"
                data-confirm="Apakah Anda yakin ingin mengakhiri sesi dan keluar dari sistem absensi?"
                data-confirm-title="Konfirmasi Keluar"
                data-confirm-type="warning"
                data-confirm-btn="Ya, Keluar"
                data-confirm-icon="log-out"
                class="m-0">
                @csrf
                <button type="submit" class="w-full sm:w-auto bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-extrabold py-2.5 px-5 rounded-2xl text-xs inline-flex items-center justify-center gap-2 transition-all duration-150 shadow-xs active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                    <i data-lucide="log-out" class="w-4 h-4 text-white/90"></i>
                    <span>Keluar dari Akun</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const photoInput = document.getElementById('photo-input');
        const previewImg = document.getElementById('preview-avatar-img');
        const fallbackAvatar = document.getElementById('fallback-avatar');
        const uploadControls = document.getElementById('photo-upload-controls');
        const cancelBtn = document.getElementById('cancel-photo-btn');
        const fileNameText = document.getElementById('selected-file-name');

        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        if (typeof window.showAlertDialog === 'function') {
                            window.showAlertDialog({
                                title: 'Ukuran Berkas Terlalu Besar',
                                message: 'Ukuran foto yang dipilih melebihi batas maksimal <strong>2 MB</strong>. Silakan pilih foto dengan resolusi atau ukuran lebih kecil.',
                                type: 'warning',
                                icon: 'alert-triangle'
                            });
                        } else {
                            alert('Ukuran berkas melebihi batas maksimal 2 MB.');
                        }
                        photoInput.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        if (previewImg) {
                            previewImg.src = event.target.result;
                            previewImg.classList.remove('hidden');
                        }
                        if (fallbackAvatar) {
                            fallbackAvatar.classList.add('hidden');
                        }
                        if (uploadControls) {
                            uploadControls.classList.remove('hidden');
                        }
                        if (fileNameText) {
                            fileNameText.textContent = file.name;
                        }
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });

            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    photoInput.value = '';
                    if (uploadControls) {
                        uploadControls.classList.add('hidden');
                    }
                    @if($user->profile_photo_url)
                        if (previewImg) {
                            previewImg.src = "{{ $user->profile_photo_url }}";
                            previewImg.classList.remove('hidden');
                        }
                        if (fallbackAvatar) {
                            fallbackAvatar.classList.add('hidden');
                        }
                    @else
                        if (previewImg) {
                            previewImg.src = '';
                            previewImg.classList.add('hidden');
                        }
                        if (fallbackAvatar) {
                            fallbackAvatar.classList.remove('hidden');
                        }
                    @endif
                });
            }
        }
    });
</script>
@endsection
