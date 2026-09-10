<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#15803D">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sistem Absensi MA Ma\'arif Cilageni')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}">

    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800;900&display=swap" rel="stylesheet">

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
                            200: '#bbf7d0',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803D', // Primary Ma'arif Green
                            800: '#166534', // Deep Forest
                            900: '#14532d',
                            gold: '#EAB308',
                        }
                    },
                    spacing: {
                        '13': '3.25rem', // 52px
                    },
                    boxShadow: {
                        '2xs': '0 1px 2px 0 rgba(0, 0, 0, 0.03)',
                        'xs': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    fontSize: {
                        '3xs': ['0.5625rem', { lineHeight: '0.75rem', letterSpacing: '0.05em' }], // 9px
                        '2xs': ['0.625rem', { lineHeight: '0.875rem', letterSpacing: '0.025em' }], // 10px
                        'xs+': ['0.6875rem', { lineHeight: '1rem', letterSpacing: '0.015em' }],     // 11px
                    }
                }
            }
        }
    </script>
    <script>
        window.__serverTimeMs = {{ \Carbon\Carbon::now()->getTimestampMs() }};
        window.__clientInitMs = Date.now();
        window.getServerNow = function() {
            return new Date(window.__serverTimeMs + (Date.now() - window.__clientInitMs));
        };
    </script>
    <style>
        body {
            -webkit-tap-highlight-color: transparent;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .heading-font {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        }
        .mono-font {
            font-family: 'JetBrains Mono', monospace;
        }
        .safe-bottom {
            padding-bottom: max(env(safe-area-inset-bottom, 0px), 8px);
        }
        .touch-btn {
            min-height: 48px;
            min-width: 48px;
        }

        /* Prevent ugly glitch outline on mouse click while preserving accessible keyboard navigation */
        button:focus:not(:focus-visible),
        a:focus:not(:focus-visible),
        [role="button"]:focus:not(:focus-visible),
        input[type="button"]:focus:not(:focus-visible),
        input[type="submit"]:focus:not(:focus-visible),
        input[type="reset"]:focus:not(:focus-visible) {
            outline: none !important;
            box-shadow: none !important;
        }

        button, a, input, select, textarea, [role="button"] {
            -webkit-tap-highlight-color: transparent;
        }
    </style>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <div id="page-styles-container" class="contents">
        @stack('styles')
    </div>
</head>
<body class="min-h-full bg-slate-50 text-slate-900 flex flex-col antialiased selection:bg-maarif-100 selection:text-maarif-800">
    <!-- Main Application Container (Full Width Responsive) -->
    <!-- Main Application Wrapper -->
    <div class="w-full min-h-screen bg-slate-50 flex flex-col md:flex-row relative">
        
        <!-- 1. MODULAR UNIFIED DESKTOP SIDEBAR (Visible on screens >= 768px) -->
        @include('partials.sidebar')

        <!-- Right Content Area Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-slate-50">

            <!-- 1. DESKTOP TOP HEADER BAR (Synchronized h-[72px] with Sidebar Brand & Crisp Elevated Bottom Shadow) -->
            <header class="hidden md:flex sticky top-0 z-20 h-[72px] px-6 sm:px-8 bg-emerald-800 border-b border-emerald-900/20 items-center justify-between text-white shrink-0" style="height: 72px; min-height: 72px;">
                <!-- Left: Page Title with Category Hierarchy -->
                <div id="desktop-header-title" class="flex flex-col justify-center">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-200/90 leading-none mb-1">
                        @if(Auth::check() && Auth::user()->role === 'guru')
                            Portal Dewan Guru
                        @elseif(Auth::check() && Auth::user()->role === 'siswa')
                            Portal Siswa
                        @elseif(Auth::check() && Auth::user()->role === 'admin')
                            Panel Administrator
                        @else
                            Sistem Presensi
                        @endif
                    </span>
                    <h2 class="text-base sm:text-lg font-black text-white heading-font tracking-tight leading-none">
                        @hasSection('page-title')
                            @yield('page-title')
                        @elseif(\Illuminate\Support\Facades\View::hasSection('title'))
                            @php
                                $cleanTitle = trim(explode('—', \Illuminate\Support\Facades\View::yieldContent('title'))[0]);
                            @endphp
                            {{ $cleanTitle }}
                        @else
                            @if(request()->routeIs('siswa.dashboard'))
                                Dashboard
                            @elseif(request()->routeIs('siswa.schedule'))
                                Jadwal Pelajaran
                            @elseif(request()->routeIs('siswa.history'))
                                Riwayat Presensi
                            @elseif(request()->routeIs('siswa.leaves.*'))
                                Pengajuan Izin
                            @elseif(request()->routeIs('profile*'))
                                Profil Pengguna
                            @elseif(request()->routeIs('guru.dashboard'))
                                Dashboard
                            @elseif(request()->routeIs('guru.scan'))
                                Pindai QR Presensi
                            @elseif(request()->routeIs('guru.session'))
                                Presensi Kelas
                            @elseif(request()->routeIs('guru.reconcile'))
                                Konfirmasi Keterangan Siswa
                            @elseif(request()->routeIs('guru.history'))
                                Riwayat Kelas Mengajar
                            @else
                                Dashboard
                            @endif
                        @endif
                    </h2>
                </div>
            </header>

            <!-- 2. MOBILE TOP BAR (Visible only on screens < 768px) -->
            <header class="md:hidden sticky top-0 z-40 h-16 px-4 bg-gradient-to-r from-emerald-800 via-maarif-700 to-emerald-800 border-b border-emerald-900/80 flex items-center justify-between text-white">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}" alt="Logo MA Ma'arif Cilageni" class="w-11 h-11 object-contain shrink-0 drop-shadow-md">
                    <div id="mobile-header-title">
                        <h1 class="text-[10px] font-extrabold tracking-wider text-emerald-200 uppercase">MA Ma'arif Cilageni</h1>
                        <p class="text-sm font-extrabold text-white heading-font leading-tight">
                            @hasSection('page-title')
                                @yield('page-title')
                            @elseif(\Illuminate\Support\Facades\View::hasSection('title'))
                                @php
                                    $cleanMobileTitle = trim(explode('—', \Illuminate\Support\Facades\View::yieldContent('title'))[0]);
                                @endphp
                                {{ $cleanMobileTitle }}
                            @else
                                Dashboard
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    @auth
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ Auth::user()->role === 'guru' ? 'bg-amber-300 text-slate-900 border border-amber-400 shadow-2xs' : 'bg-emerald-600 text-white border border-emerald-500 shadow-2xs' }}">
                            {{ strtoupper(Auth::user()->role) }}
                        </span>
                        <form action="{{ route('logout') }}" method="POST" class="inline"
                            data-confirm="Apakah Anda yakin ingin mengakhiri sesi dan keluar dari sistem absensi?"
                            data-confirm-title="Konfirmasi Keluar"
                            data-confirm-type="warning"
                            data-confirm-btn="Ya, Keluar"
                            data-confirm-icon="log-out">
                            @csrf
                            <button type="submit" title="Keluar dari sistem" aria-label="Keluar dari sistem" class="p-1.5 rounded-lg text-emerald-100 hover:text-white hover:bg-rose-600/80 transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                                <i data-lucide="log-out" class="w-5 h-5"></i>
                            </button>
                        </form>
                    @endauth
                </div>
            </header>



            <!-- 3. MAIN CONTENT (Generous top padding/margin for comfortable spacing below elevated header) -->
            <main id="main-content" class="flex-1 w-full px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8 md:pt-8 pb-36 md:pb-12">
                @yield('content')
            </main>
        </div>

        <!-- 4. MOBILE BOTTOM NAVIGATION BAR (Strictly hidden on md: and above) -->
        @include('partials.bottom-nav')
    </div>

    <!-- Haptic feedback and service worker registration -->
    <script>
        // Initialize Lucide Icons
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        window.triggerHaptic = function(pattern = [50]) {
            if (navigator.vibrate) {
                try {
                    navigator.vibrate(pattern);
                } catch(e) {}
            }
        };

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js').catch(() => {});
            });
        }
    </script>
    @if(app()->environment('local'))
        @include('partials.time-simulator')
    @endif
    @include('partials.confirm-dialog')
    @include('partials.toast-notification')
    @include('partials.partial-nav')

    <div id="page-scripts-container" class="contents">
        @stack('scripts')
    </div>
</body>
</html>
