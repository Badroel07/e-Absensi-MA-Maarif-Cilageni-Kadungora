@extends('layouts.app')

@section('title', 'Profil Akun — MA Ma\'arif Cilageni')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 heading-font tracking-tight">Profil Akun</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Identitas madrasah, foto profil resmi, dan pengaturan keamanan kata sandi</p>
    </div>

    <!-- 1. Profile Hero & Identity Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-6">
        <!-- Avatar & Main Meta Row -->
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 pb-6 border-b border-slate-100 text-center sm:text-left">
            <!-- Avatar with Photo Input Trigger -->
            <div class="relative group shrink-0">
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl overflow-hidden shadow-md bg-slate-100 flex items-center justify-center relative">
                    <img id="preview-avatar-img" 
                         src="{{ $user->profile_photo_url ?? '' }}" 
                         alt="{{ $user->name }}" 
                         class="w-full h-full object-cover {{ $user->profile_photo_url ? '' : 'hidden' }}">
                    
                    <div id="fallback-avatar" 
                         class="w-full h-full bg-linear-to-br from-emerald-700 to-slate-900 text-white font-bold text-3xl sm:text-4xl flex items-center justify-center {{ $user->profile_photo_url ? 'hidden' : '' }}">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                </div>

                <label for="photo-input" 
                       title="Pilih foto profil"
                       class="absolute -bottom-1 -right-1 p-2.5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white rounded-2xl shadow-md shadow-emerald-900/25 cursor-pointer transition-all duration-150 active:scale-95 flex items-center justify-center focus-within:ring-2 focus-within:ring-emerald-600">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                </label>
            </div>

                <!-- Name & Roles -->
                <div class="flex-1 min-w-0 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900 heading-font break-words">{{ $user->name }}</h2>
                        <span class="text-xs font-medium text-slate-500">
                            Peran: <span class="font-semibold text-slate-700">{{ $user->role === 'guru' ? 'Guru' : ($user->role === 'admin' ? 'Administrator' : 'Siswa Madrasah') }}</span>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-mono font-medium">
                        Nomor Identitas: <span class="font-semibold text-slate-900">{{ $user->identity_number }}</span>
                    </p>
                    <p class="text-xs text-emerald-700 font-semibold flex items-center justify-center sm:justify-start gap-1.5">
                        <i data-lucide="mail" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span class="font-mono">{{ $user->email }}</span>
                    </p>
                </div>
            </div>

            <!-- Upload Form & Actions Controls (Appears on file select) -->
            <form id="photo-upload-form" action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" data-loading-form>
                @csrf
                <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden">

                <div id="photo-upload-controls" class="hidden p-4 bg-emerald-50/90 border border-emerald-200 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between text-xs text-emerald-900">
                        <div class="flex items-center space-x-2 min-w-0">
                            <i data-lucide="image" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                            <span class="truncate font-semibold" id="selected-file-name">Foto dipilih</span>
                        </div>
                        <span class="text-[11px] font-mono text-emerald-700 font-medium shrink-0">Pratinjau Siap</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <button type="submit" class="px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-semibold rounded-xl text-xs inline-flex items-center justify-center gap-1.5 shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                            <span>Simpan Foto Profil</span>
                        </button>
                        <button type="button" id="cancel-photo-btn" class="px-4 py-2.5 bg-white hover:bg-slate-100 active:bg-slate-200 text-slate-700 font-semibold border border-slate-200 rounded-xl text-xs inline-flex items-center justify-center gap-1 transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            <span>Batal</span>
                        </button>
                    </div>
                </div>
            </form>

            @if($user->profile_photo_path)
                <!-- Photo Management Bar -->
                <div class="flex flex-wrap items-center gap-3">
                    <form action="{{ route('profile.photo.destroy') }}" method="POST"
                        data-confirm="Apakah Anda yakin ingin menghapus foto profil Anda? Tampilan avatar akan dikembalikan ke inisial nama Anda."
                        data-confirm-title="Hapus Foto Profil"
                        data-confirm-type="danger"
                        data-confirm-btn="Ya, Hapus Foto"
                        data-confirm-icon="trash-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 active:bg-rose-200 text-rose-700 font-semibold border border-rose-200 rounded-xl text-xs inline-flex items-center justify-center gap-1.5 transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500" title="Hapus Foto Profil">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-600"></i>
                            <span>Hapus Foto</span>
                        </button>
                    </form>
                </div>
            @endif

            <!-- Detailed Identity Section -->
            <div class="pt-2">
                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/50 p-4 sm:p-5">
                    <div class="divide-y divide-slate-200/30 text-xs sm:text-sm">
                        @if($user->classroom)
                            <div class="flex items-center justify-between gap-3 py-3 first:pt-0">
                                <span class="text-xs font-semibold text-slate-500 flex items-center gap-1.5 shrink-0">
                                    <i data-lucide="graduation-cap" class="w-4 h-4 text-slate-400"></i>
                                    <span>Rombel / Kelas</span>
                                </span>
                                <span class="font-semibold text-emerald-900 text-right">
                                    Kelas {{ $user->classroom->name }}
                                </span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between gap-3 py-3 first:pt-0">
                            <span class="text-xs font-semibold text-slate-500 flex items-center gap-1.5 shrink-0">
                                <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                                <span>Tanggal Lahir</span>
                            </span>
                            <span class="font-medium text-slate-900 text-right whitespace-nowrap">
                                {{ $user->birth_date ? $user->birth_date->format('d F Y') : '-' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-3">
                            <span class="text-xs font-semibold text-slate-500 flex items-center gap-1.5 shrink-0">
                                <i data-lucide="shield-check" class="w-4 h-4 text-slate-400"></i>
                                <span>Status Akun</span>
                            </span>
                            <span class="font-medium text-emerald-700 text-right">
                                Aktif Terverifikasi
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-3 last:pb-0">
                            <span class="text-xs font-semibold text-slate-500 flex items-center gap-1.5 shrink-0">
                                <i data-lucide="phone" class="w-4 h-4 text-slate-400"></i>
                                <span>No. Handphone</span>
                            </span>
                            <span class="font-medium {{ $user->phone_number ? 'text-slate-900' : 'text-slate-400' }} font-mono text-right">
                                {{ $user->phone_number ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- 2. Security & Password Update Section -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-6">
        <div class="pb-4 border-b border-slate-100 flex items-center gap-3">
            <i data-lucide="key-round" class="w-5 h-5 text-emerald-700 shrink-0"></i>
            <div>
                <h3 class="text-base font-bold text-slate-900 heading-font">Perbarui Kata Sandi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Amankan akun Anda dengan mengganti kata sandi secara berkala (minimal 6 karakter)</p>
            </div>
        </div>
        
        <form action="{{ route('profile.password') }}" method="POST" data-loading-form class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Sandi Saat Ini <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <input type="password" name="current_password" required placeholder="Masukkan kata sandi saat ini"
                        class="w-full px-4 py-2.5 text-xs sm:text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/70 focus:bg-white transition-all font-mono">
                </div>
                @error('current_password')
                    <p class="text-rose-600 text-[11px] font-semibold mt-1.5 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Sandi Baru <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" required minlength="6"
                        placeholder="Minimal 6 karakter"
                        class="w-full px-4 py-2.5 text-xs sm:text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/70 focus:bg-white transition-all font-mono">
                    @error('password')
                        <p class="text-rose-600 text-[11px] font-semibold mt-1.5 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span></label>
                    <input type="password" name="password_confirmation" required minlength="6"
                        placeholder="Ulangi kata sandi baru"
                        class="w-full px-4 py-2.5 text-xs sm:text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/70 focus:bg-white transition-all font-mono">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-semibold rounded-xl text-xs sm:text-sm shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Perubahan Kata Sandi</span>
                </button>
            </div>
        </form>
    </div>

    <!-- 3. Account Session & Sign Out Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <i data-lucide="log-out" class="w-5 h-5 text-rose-600 shrink-0"></i>
            <div>
                <h3 class="text-base font-bold text-slate-900 heading-font">Keluar dari Sesi Aplikasi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Akhiri sesi aktif akun Anda pada perangkat ini dengan aman.</p>
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
            <button type="submit" class="w-full sm:w-auto bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-semibold py-2.5 px-5 rounded-xl text-xs inline-flex items-center justify-center gap-2 transition-all duration-150 shadow-xs active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                <i data-lucide="log-out" class="w-4 h-4 text-white"></i>
                <span>Keluar dari Akun</span>
            </button>
        </form>
    </div>
</div>

<script>
    function initProfilePhotoHandler() {
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
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProfilePhotoHandler, { once: true });
    } else {
        initProfilePhotoHandler();
    }
</script>
@endsection
