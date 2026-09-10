<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#15803D">
    <title>Masuk — Sistem Absensi MA Ma'arif Cilageni</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        maarif: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803D',
                            800: '#166534',
                            900: '#14532d',
                            gold: '#EAB308',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4 sm:p-6 lg:p-8 antialiased selection:bg-maarif-100 selection:text-maarif-800">
    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-2xl border border-slate-200/80 overflow-hidden grid grid-cols-1 lg:grid-cols-12">
        
        <!-- Left Banner: Visual & Madrasah Brand (Visible on Desktop / Large Tablet) -->
        <div class="hidden lg:flex lg:col-span-6 bg-gradient-to-br from-maarif-900 via-maarif-800 to-slate-950 text-white p-10 flex-col justify-between relative overflow-hidden">
            <!-- Background Decorative Rings -->
            <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-maarif-600/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -left-16 -bottom-16 w-64 h-64 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>

            <!-- Top Header in Banner -->
            <div class="relative z-10">
                <div class="flex items-center space-x-3 mb-6">
                    <img src="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}" alt="Logo MA Ma'arif Cilageni" class="w-12 h-12 object-contain shrink-0">
                    <div>
                        <span class="text-[11px] font-bold tracking-widest text-emerald-300 uppercase">LP Ma'arif NU</span>
                        <h1 class="text-lg font-black tracking-tight text-white leading-tight">MA Ma'arif Cilageni</h1>
                        <p class="text-xs text-slate-300">Kadungora - Garut, Jawa Barat</p>
                    </div>
                </div>

                <div class="space-y-4 pt-4 border-t border-maarif-700/60">
                    <h2 class="text-2xl font-black text-white leading-snug heading-font">
                        Presensi Digital Terpadu & Terintegrasi
                    </h2>
                    <p class="text-xs text-emerald-100/80 leading-relaxed">
                        Sistem presensi mandiri Bapak/Ibu Guru dan siswa di lingkungan madrasah (radius 75 meter), Layar Presensi Madrasah QR Code, serta konfirmasi kehadiran siswa yang tertib dan transparan.
                    </p>
                </div>
            </div>

            <!-- Middle Highlights -->
            <div class="space-y-3 my-6 relative z-10">
                <div class="flex items-center space-x-3 bg-white/10 backdrop-blur rounded-2xl p-3.5 border border-white/10">
                    <div class="w-8 h-8 rounded-xl bg-amber-400/20 text-amber-300 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white">Layar Presensi Madrasah</p>
                        <p class="text-[11px] text-slate-300">Kode QR otomatis diperbarui setiap 20 detik untuk keakuratan kehadiran.</p>
                    </div>
                </div>

                <div class="flex items-center space-x-3 bg-white/10 backdrop-blur rounded-2xl p-3.5 border border-white/10">
                    <div class="w-8 h-8 rounded-xl bg-emerald-400/20 text-emerald-300 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white">Batas Area Madrasah & PIN Kelas</p>
                        <p class="text-[11px] text-slate-300">Siswa wajib berada di area madrasah (radius 75 meter) saat presensi.</p>
                    </div>
                </div>
            </div>

            <!-- Bottom Banner Action -->
            <div class="relative z-10 pt-4 border-t border-maarif-700/60 flex items-center justify-between">
                <span class="text-[11px] text-emerald-200">Layar Presensi Bersama:</span>
                <a href="{{ route('kiosk.index') }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 py-1.5 px-3 rounded-xl bg-white/20 hover:bg-white/30 active:bg-white/40 text-white text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer backdrop-blur-sm border border-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                    <span>Buka Layar Presensi</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="col-span-12 lg:col-span-6 p-6 sm:p-10 flex flex-col justify-between">
            <div>
                <!-- Mobile Brand Header (Hidden on Desktop) -->
                <div class="lg:hidden text-center mb-6">
                    <img src="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}" alt="Logo MA Ma'arif Cilageni" class="w-14 h-14 object-contain shrink-0 mx-auto mb-3">
                    <h1 class="text-xs uppercase font-bold tracking-wider text-slate-500">MA Ma'arif Cilageni</h1>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight heading-font">
                        Sistem Presensi Kehadiran
                    </h2>
                </div>

                <!-- Desktop Title Header -->
                <div class="hidden lg:block mb-6">
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight heading-font">
                        Selamat Datang
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Masukkan nomor identitas madrasah atau email Anda untuk masuk ke sistem presensi.
                    </p>
                </div>

                <!-- Error & Success Notifications -->
                @if($errors->any() || session('error'))
                    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-2xl p-3.5 flex items-start space-x-2.5">
                        <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') ?? $errors->first() }}</span>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-2xl p-3.5 flex items-start space-x-2.5">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <!-- Login Form -->
                <form action="{{ route('login') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="login" class="block text-xs font-bold text-slate-700 mb-1.5">
                            NISN / NIP / Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-maarif-600 focus:border-transparent text-sm placeholder-slate-400 font-medium transition bg-slate-50/50 focus:bg-white"
                                placeholder="NISN Siswa, NIP Bapak/Ibu Guru, atau Email">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" required
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-maarif-600 focus:border-transparent text-sm placeholder-slate-400 font-medium transition bg-slate-50/50 focus:bg-white"
                                placeholder="Kata Sandi Bawaan: Tanggal Lahir (HHBBTTTT)">
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-600 pt-1">
                        <label class="flex items-center space-x-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" value="1" checked class="rounded border-slate-300 text-maarif-700 focus:ring-maarif-600 cursor-pointer">
                            <span>Ingat sesi masuk</span>
                        </label>
                        <span class="text-[11px] text-slate-400">Format: Tanggal Lahir (HHBBTTTT)</span>
                    </div>

                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-5 sm:px-6 rounded-xl sm:rounded-2xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-bold text-sm sm:text-base shadow-md shadow-maarif-700/25 transition-all duration-150 active:scale-[0.98] min-h-[48px] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-maarif-600">
                        <span>Masuk ke Sistem</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Footer / Mobile Kiosk Link -->
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="text-[11px]">MA Ma'arif Cilageni &copy; {{ date('Y') }}</span>
                <a href="{{ route('kiosk.index') }}" target="_blank" class="font-bold text-maarif-700 hover:text-maarif-800 inline-flex items-center gap-1 transition-colors duration-150">
                    <span>Layar Presensi</span>
                    <svg class="w-3.5 h-3.5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
