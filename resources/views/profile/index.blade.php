@extends('layouts.app')

@section('title', 'Profil Akun — MA Ma\'arif Cilageni')

@section('content')
<div class="space-y-6">

    <!-- BEGIN: PageIntroSection -->
    <section class="space-y-1.5" data-purpose="heading-section">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 heading-font">Profil Akun</h2>
        <p class="text-[13px] leading-relaxed text-slate-600">
            Identitas madrasah, foto profil resmi, dan pengaturan keamanan kata sandi
        </p>
    </section>
    <!-- END: PageIntroSection -->

    <!-- Responsive 2-Column Grid Layout for Tablet and Desktop -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Column: Teacher Profile Card (5 cols on lg) -->
        <div class="lg:col-span-5">
            <!-- BEGIN: TeacherProfileCard -->
            <section class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 relative" data-purpose="teacher-identity">
                <!-- Avatar Block with Change Photo Button -->
                <div class="flex flex-col items-center">
                    <div class="relative mb-4">
                        <!-- Squircle Avatar Container -->
                        <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-[#065f46] to-[#044e3a] flex items-center justify-center text-white text-3xl font-extrabold shadow-md border-4 border-emerald-50/50 overflow-hidden relative">
                            <img id="preview-avatar-img"
                                 src="{{ $user->profile_photo_url ?? '' }}"
                                 alt="{{ $user->name }}"
                                 class="w-full h-full object-cover {{ $user->profile_photo_url ? '' : 'hidden' }}">
                            <span id="fallback-avatar" class="heading-font select-none {{ $user->profile_photo_url ? 'hidden' : '' }}">
                                {{ substr($user->name, 0, 1) }}
                            </span>
                        </div>
                        <!-- Action to change photo -->
                        <label for="photo-input" aria-label="Ubah foto profil" class="absolute -bottom-1.5 -right-1.5 w-8 h-8 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shadow-lg border-2 border-white active:scale-95 transition-transform cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                                <path d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                            </svg>
                        </label>
                    </div>

                    <!-- Name and Credentials -->
                    <h3 class="text-lg font-bold text-slate-900 text-center tracking-tight heading-font">{{ $user->name }}</h3>
                    <!-- Natural clean text info without overly boxy pills -->
                    <div class="mt-1 text-center text-xs text-slate-500 font-medium space-y-0.5">
                        <p>Peran: <span class="font-semibold text-slate-700">{{ $user->role === 'guru' ? 'Guru' : ($user->role === 'admin' ? 'Administrator' : 'Siswa') }}</span></p>
                        <p>Nomor Identitas: <span class="font-semibold text-slate-800 mono-font">{{ $user->identity_number }}</span></p>
                    </div>

                    <!-- Official Email Link -->
                    <a class="inline-flex items-center gap-1.5 mt-3 text-xs font-semibold text-emerald-700 hover:text-emerald-800 transition-colors" href="mailto:{{ $user->email }}">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                        </svg>
                        <span class="mono-font">{{ $user->email }}</span>
                    </a>
                </div>

                <!-- Hidden photo upload form & controls -->
                <form id="photo-upload-form" action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" data-loading-form>
                    @csrf
                    <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden">
                    <div id="photo-upload-controls" class="hidden mt-4 p-3.5 bg-emerald-50/90 border border-emerald-200 rounded-2xl space-y-3">
                        <div class="flex items-center justify-between text-xs text-emerald-900">
                            <span class="truncate font-semibold text-xs" id="selected-file-name">Foto dipilih</span>
                            <span class="text-[11px] font-mono text-emerald-700 font-medium shrink-0">Pratinjau Siap</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-xl text-xs inline-flex items-center justify-center gap-1 shadow-sm transition active:scale-[0.98] cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                                <span>Simpan Foto</span>
                            </button>
                            <button type="button" id="cancel-photo-btn" class="px-3.5 py-2 bg-white hover:bg-slate-100 text-slate-700 font-semibold border border-slate-200 rounded-xl text-xs inline-flex items-center justify-center gap-1 transition active:scale-95 cursor-pointer">
                                <span>Batal</span>
                            </button>
                        </div>
                    </div>
                </form>

                @if($user->profile_photo_path)
                    <div class="flex justify-center mt-3">
                        <form action="{{ route('profile.photo.destroy') }}" method="POST"
                            data-confirm="Hapus foto profil Anda? Tampilan avatar akan dikembalikan ke inisial nama Anda."
                            data-confirm-title="Hapus Foto Profil"
                            data-confirm-type="danger"
                            data-confirm-btn="Ya, Hapus Foto"
                            data-confirm-icon="trash-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold border border-rose-200 rounded-xl text-xs inline-flex items-center justify-center gap-1.5 transition active:scale-95 cursor-pointer" title="Hapus Foto Profil">
                                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                                <span>Hapus Foto</span>
                            </button>
                        </form>
                    </div>
                @endif

                <!-- Divider -->
                <hr class="border-slate-100 my-5">

                <!-- Detailed Teacher Metadata List -->
                <div class="space-y-3.5 text-xs">
                    @if($user->classroom)
                        <!-- Row: Rombel / Kelas -->
                        <div class="flex items-center justify-between py-0.5">
                            <div class="flex items-center gap-2.5 text-slate-500 font-medium">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                                </svg>
                                <span>Rombel / Kelas</span>
                            </div>
                            <span class="font-semibold text-slate-800">Kelas {{ $user->classroom->name }}</span>
                        </div>
                    @endif

                    <!-- Row 1: Tanggal Lahir -->
                    <div class="flex items-center justify-between py-0.5">
                        <div class="flex items-center gap-2.5 text-slate-500 font-medium">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                            </svg>
                            <span>Tanggal Lahir</span>
                        </div>
                        <span class="font-semibold text-slate-800 mono-font">{{ $user->birth_date ? $user->birth_date->format('d F Y') : '-' }}</span>
                    </div>

                    <!-- Row 2: Status Akun -->
                    <div class="flex items-center justify-between py-0.5">
                        <div class="flex items-center gap-2.5 text-slate-500 font-medium">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                            </svg>
                            <span>Status Akun</span>
                        </div>
                        <span class="font-semibold text-emerald-700 flex items-center gap-1.5">
                            Aktif Terverifikasi
                        </span>
                    </div>

                    <!-- Row 3: No. Handphone -->
                    <div class="flex items-center justify-between py-0.5">
                        <div class="flex items-center gap-2.5 text-slate-500 font-medium">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                            </svg>
                            <span>No. Handphone</span>
                        </div>
                        <span class="font-semibold {{ $user->phone_number ? 'text-slate-800' : 'text-slate-400' }} mono-font">{{ $user->phone_number ?? '-' }}</span>
                    </div>
                </div>
            </section>
            <!-- END: TeacherProfileCard -->
        </div>

        <!-- Right Column: Security & Logout Cards (7 cols on lg) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- BEGIN: SecurityPasswordCard -->
            <section class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100" data-purpose="security-settings">
                <!-- Section Header -->
                <div class="flex items-start gap-3 mb-5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight heading-font">Perbarui Kata Sandi</h3>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                            Amankan akun Anda dengan mengganti kata sandi secara berkala (minimal 6 karakter)
                        </p>
                    </div>
                </div>

                <!-- Form Elements -->
                <form action="{{ route('profile.password') }}" method="POST" data-loading-form class="space-y-4">
                    @csrf
                    <!-- Current Password -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input name="current_password" required placeholder="Masukkan kata sandi saat ini" type="password"
                                class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-3.5 pr-10 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                            <button aria-label="Lihat kata sandi" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer" type="button" onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                                </svg>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="text-rose-600 text-[11px] font-semibold mt-1.5 flex items-center gap-1">
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Kata Sandi Baru <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input name="password" required minlength="6" placeholder="Minimal 6 karakter" type="password"
                                class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-3.5 pr-10 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                            <button aria-label="Lihat kata sandi" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer" type="button" onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-rose-600 text-[11px] font-semibold mt-1.5 flex items-center gap-1">
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input name="password_confirmation" required minlength="6" placeholder="Ulangi kata sandi baru" type="password"
                                class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50/50 py-3 px-3.5 pr-10 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                            <button aria-label="Lihat kata sandi" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer" type="button" onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button class="w-full mt-2 py-3 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md transition-all heading-font cursor-pointer" type="submit">
                        <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
                        </svg>
                        <span>Simpan Perubahan Kata Sandi</span>
                    </button>
                </form>
            </section>
            <!-- END: SecurityPasswordCard -->

            <!-- BEGIN: LogoutSessionCard -->
            <section class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100" data-purpose="logout-session">
                <div class="flex items-start gap-3 mb-5">
                    <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight heading-font">Keluar dari Sesi Aplikasi</h3>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                            Akhiri sesi aktif akun Anda pada perangkat ini dengan aman.
                        </p>
                    </div>
                </div>
                <!-- Action Button Logout -->
                <form action="{{ route('logout') }}" method="POST"
                    data-confirm="Apakah Anda yakin ingin keluar dari sesi akun ini?"
                    data-confirm-title="Keluar dari Akun"
                    data-confirm-type="danger"
                    data-confirm-btn="Ya, Keluar">
                    @csrf
                    <button class="w-full py-3 px-4 rounded-xl bg-[#e11d48] hover:bg-[#be123c] active:scale-[0.99] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md transition-all cursor-pointer heading-font" type="submit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                        </svg>
                        <span>Keluar dari Akun</span>
                    </button>
                </form>
            </section>
            <!-- END: LogoutSessionCard -->
        </div>
    </div>

</div>

@push('scripts')
<script>
(function() {
    const input = document.getElementById('photo-input');
    const preview = document.getElementById('preview-avatar-img');
    const fallback = document.getElementById('fallback-avatar');
    const controls = document.getElementById('photo-upload-controls');
    const fileName = document.getElementById('selected-file-name');
    const cancelBtn = document.getElementById('cancel-photo-btn');
    let originalSrc = preview ? preview.src : '';

    if (input) {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran foto maksimal 2MB.');
                this.value = '';
                return;
            }
            const url = URL.createObjectURL(file);
            if (preview) {
                preview.src = url;
                preview.classList.remove('hidden');
            }
            if (fallback) fallback.classList.add('hidden');
            if (fileName) fileName.textContent = file.name;
            if (controls) controls.classList.remove('hidden');
            if (window.lucide) window.lucide.createIcons();
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            if (input) input.value = '';
            if (preview) {
                if (originalSrc && preview.src !== originalSrc) URL.revokeObjectURL(preview.src);
                if (!originalSrc) {
                    preview.classList.add('hidden');
                    if (fallback) fallback.classList.remove('hidden');
                } else {
                    preview.src = originalSrc;
                }
            }
            if (controls) controls.classList.add('hidden');
        });
    }
})();
</script>
@endpush
@endsection
