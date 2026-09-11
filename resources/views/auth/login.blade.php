<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#14532d">
    <title>Masuk - Presensi MA Ma'arif Cilageni</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        maarif: {
                            50:   '#f0fdf4',
                            100:  '#dcfce7',
                            200:  '#bbf7d0',
                            500:  '#22c55e',
                            600:  '#16a34a',
                            700:  '#15803D',
                            800:  '#166534',
                            900:  '#14532d',
                            950:  '#052e16',
                        }
                    },
                    fontFamily: {
                        sans:    ['Inter', 'system-ui', 'sans-serif'],
                        display: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        input[type="checkbox"]:checked { accent-color: #15803D; }
        input:focus { outline: none; }
    </style>
</head>
<body class="min-h-full bg-slate-50 antialiased selection:bg-maarif-100 selection:text-maarif-900 flex flex-col">
    <div class="min-h-screen flex-1 flex flex-col lg:flex-row">
        
        <!-- Sisi Kiri: Branding Madrasah (Desktop: Kolom Kiri Fullscreen, Mobile: Banner Header di Atas) -->
        <div class="w-full lg:w-[45%] xl:w-[42%] bg-gradient-to-br from-maarif-900 via-maarif-800 to-slate-950 text-white p-6 sm:p-10 lg:p-12 xl:p-16 flex flex-col justify-between relative overflow-hidden shrink-0">
            <!-- Ambient Lighting Lembut -->
            <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-maarif-600/20 blur-3xl pointer-events-none" aria-hidden="true"></div>
            <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-emerald-500/15 blur-3xl pointer-events-none" aria-hidden="true"></div>

            <!-- Konten Utama Panel Kiri (Terpusat Rapi Secara Vertikal di Desktop) -->
            <div class="relative z-10 lg:my-auto space-y-8">
                <!-- Header Identitas Madrasah -->
                <div>
                    <div class="flex items-center space-x-4 sm:space-x-5 mb-6 sm:mb-8">
                        <img src="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}" alt="Logo MA Ma'arif Cilageni" class="w-16 h-16 sm:w-20 sm:h-20 object-contain shrink-0 drop-shadow-xl">
                        <div>
                            <span class="text-[11px] font-semibold tracking-[0.2em] text-emerald-300 uppercase block mb-0.5">LP Ma'arif NU</span>
                            <h1 class="text-lg sm:text-xl lg:text-2xl font-bold tracking-tight text-white leading-tight font-display">MA Ma'arif Cilageni</h1>
                            <p class="text-xs text-slate-300 mt-0.5">Kadungora - Garut, Jawa Barat</p>
                        </div>
                    </div>

                    <div class="pt-1">
                        <h2 class="text-2xl sm:text-3xl lg:text-3xl font-bold text-white leading-tight font-display">
                            Presensi Harian Guru &amp; Siswa
                        </h2>
                        <p class="text-xs sm:text-sm text-emerald-100/80 leading-relaxed mt-3 max-w-md">
                            Catat kehadiran mengajar dan belajar di madrasah dengan verifikasi radius lokasi serta PIN sesi kelas.
                        </p>
                    </div>
                </div>

                <!-- Kartu Informasi Fitur (Hanya Tampil di Desktop, rapat dan menyatu) -->
                <div class="hidden lg:flex flex-col space-y-3 pt-2">
                    <div class="flex items-center space-x-3.5 bg-white/8 backdrop-blur-sm rounded-2xl p-4 border border-white/10 shadow-sm">
                        <div class="w-9 h-9 rounded-xl bg-amber-400/20 text-amber-300 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-white">Kode QR Kiosk</p>
                            <p class="text-[11px] text-slate-300">Pindai kode QR dinamis di layar madrasah saat tiba.</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3.5 bg-white/8 backdrop-blur-sm rounded-2xl p-4 border border-white/10 shadow-sm">
                        <div class="w-9 h-9 rounded-xl bg-emerald-400/20 text-emerald-300 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-white">Radius Lokasi Madrasah</p>
                            <p class="text-[11px] text-slate-300">Presensi hanya aktif saat Anda berada di area madrasah.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Sisi Kanan: Form Login (Fullscreen, Bersih, & Rata Tengah) -->
        <div class="w-full lg:w-[55%] xl:w-[60%] flex-1 bg-white flex flex-col justify-between p-6 sm:p-10 lg:p-16">
            <div class="w-full max-w-md mx-auto my-auto py-6">
                <!-- Title & Greeting -->
                <div class="mb-7">
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight font-display">
                        Masuk ke Akun Anda
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed">
                        Gunakan alamat email pribadi Anda yang telah terdaftar.
                    </p>
                </div>

                <!-- Notifikasi Error & Sukses -->
                @if($errors->any() || session('error'))
                    <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm rounded-2xl p-4 flex items-start space-x-3 shadow-xs" role="alert">
                        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium leading-relaxed">{{ session('error') ?? $errors->first() }}</span>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm rounded-2xl p-4 flex items-start space-x-3 shadow-xs" role="alert">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="font-medium leading-relaxed">{{ session('success') }}</span>
                    </div>
                @endif

                <!-- Form Login -->
                <form action="{{ route('login') }}" method="POST" data-loading-form class="space-y-5">
                    @csrf

                    <div>
                        <label for="login" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="email" id="login" name="login" value="{{ old('login') ?? old('email') }}" required autofocus autocomplete="email"
                                class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-slate-200 bg-slate-50/70 text-slate-900 placeholder-slate-400 text-sm font-medium transition focus:bg-white focus:border-maarif-600 focus:ring-2 focus:ring-maarif-600/20"
                                placeholder="nama@email.com">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" required autocomplete="current-password"
                                class="w-full pl-11 pr-12 py-3.5 rounded-xl border border-slate-200 bg-slate-50/70 text-slate-900 placeholder-slate-400 text-sm font-medium transition focus:bg-white focus:border-maarif-600 focus:ring-2 focus:ring-maarif-600/20"
                                placeholder="Masukkan kata sandi Anda">
                            
                            <!-- Toggle Show / Hide Password -->
                            <button type="button" id="togglePassword" aria-label="Tampilkan kata sandi" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors cursor-pointer focus:outline-none focus-visible:text-maarif-700">
                                <svg id="eyeIcon" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-600 pt-1">
                        <label class="flex items-center space-x-2.5 cursor-pointer select-none">
                            <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded border-slate-300 text-maarif-700 focus:ring-maarif-600 cursor-pointer">
                            <span class="font-medium text-slate-600">Ingat sesi masuk</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 py-4 px-6 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-sm sm:text-base shadow-lg shadow-maarif-700/25 transition-all duration-150 active:scale-[0.985] min-h-[50px] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-maarif-600">
                        <span>Masuk</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>

                    <div class="text-center pt-2">
                        <p class="text-xs text-slate-500">
                            Lupa kata sandi? <span class="font-semibold text-slate-700">Hubungi pihak sekolah</span>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Footer Mobile & Desktop -->
            <div class="pt-6 border-t border-slate-100 text-center text-xs text-slate-400">
                <p>&copy; {{ date('Y') }} MA Ma'arif Cilageni Kadungora</p>
            </div>
        </div>
    </div>

    <!-- Script Show/Hide Password -->
    <script>
        const btn = document.getElementById('togglePassword');
        const pwd = document.getElementById('password');
        const ico = document.getElementById('eyeIcon');

        const eyeOpen = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
        const eyeSlash = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;

        if (btn && pwd && ico) {
            btn.addEventListener('click', () => {
                const isHidden = pwd.type === 'password';
                pwd.type = isHidden ? 'text' : 'password';
                ico.innerHTML = isHidden ? eyeSlash : eyeOpen;
                btn.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            });
        }

        // Auto-loading on any form with [data-loading-form]
        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.hasAttribute('data-loading-form')) return;
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach((btn) => {
                btn.disabled = true;
                btn.style.opacity = '0.7';
                btn.style.cursor = 'wait';
            });
        }, true);
    </script>
</body>
</html>
