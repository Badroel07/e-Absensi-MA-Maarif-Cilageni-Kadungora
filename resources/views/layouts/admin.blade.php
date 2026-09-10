<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Panel Admin — Sistem Presensi MA Ma\'arif Cilageni')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}">

    <script>
        window.__serverTimeMs = {{ \Carbon\Carbon::now()->getTimestampMs() }};
        window.__clientInitMs = Date.now();
        window.getServerNow = function() {
            return new Date(window.__serverTimeMs + (Date.now() - window.__clientInitMs));
        };
    </script>

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
                            700: '#15803D',
                            800: '#166534',
                            900: '#14532d',
                            gold: '#EAB308',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    fontSize: {
                        '3xs': ['0.5625rem', { lineHeight: '0.75rem', letterSpacing: '0.05em' }],
                        '2xs': ['0.625rem', { lineHeight: '0.875rem', letterSpacing: '0.025em' }],
                        'xs+': ['0.6875rem', { lineHeight: '1rem', letterSpacing: '0.015em' }],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .heading-font { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
        .mono-font { font-family: 'JetBrains Mono', monospace; }
        
        /* Custom slim & elegant scrollbar for sidebar */
        .custom-sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }
        .custom-sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Disable transitions during initial render to prevent flickering */
        .preload-transitions * {
            -webkit-transition: none !important;
            -moz-transition: none !important;
            -ms-transition: none !important;
            -o-transition: none !important;
            transition: none !important;
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

        .safe-bottom {
            padding-bottom: max(env(safe-area-inset-bottom, 0px), 8px);
        }
    </style>

    <!-- Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800;900&family=JetBrains+Mono:wght@500;600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800;900&family=JetBrains+Mono:wght@500;600;700;800&display=swap" rel="stylesheet">
    </noscript>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    @stack('styles')
</head>
<body class="min-h-full bg-slate-50 text-slate-900 flex flex-col antialiased selection:bg-emerald-100 selection:text-emerald-800">
    <!-- Main Application Wrapper -->
    <div class="w-full min-h-screen bg-slate-50 flex flex-col md:flex-row relative">
        
        <!-- 1. MODULAR UNIFIED DESKTOP SIDEBAR (Visible on screens >= 768px) -->
        @include('partials.sidebar')

        <!-- Right Content Area Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-slate-50">

            <!-- 1. DESKTOP TOP HEADER BAR (Synchronized h-[72px] with Sidebar Brand) -->
            <header class="hidden md:flex sticky top-0 z-20 h-[72px] px-6 sm:px-8 bg-emerald-800 border-b border-emerald-900/20 items-center justify-between text-white shrink-0" style="height: 72px; min-height: 72px;">
                <div class="flex flex-col justify-center">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-200/90 leading-none mb-1">Panel Administrator</span>
                    <h2 class="text-base sm:text-lg font-black text-white heading-font tracking-tight leading-none">@yield('page-title', 'Dashboard')</h2>
                </div>
            </header>

            <!-- 2. MOBILE TOP BAR (Visible only on screens < 768px) -->
            <header class="md:hidden sticky top-0 z-40 h-16 px-4 bg-gradient-to-r from-emerald-800 via-maarif-700 to-emerald-800 border-b border-emerald-900/80 flex items-center justify-between text-white">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}" alt="Logo MA Ma'arif Cilageni" class="w-11 h-11 object-contain shrink-0 drop-shadow-md">
                    <div>
                        <h1 class="text-[10px] font-extrabold tracking-wider text-emerald-200 uppercase">MA Ma'arif Cilageni</h1>
                        <p class="text-sm font-extrabold text-white heading-font leading-tight">
                            @yield('page-title', 'Dashboard')
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-600 text-white border border-emerald-500 shadow-2xs">
                        ADMIN
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
                </div>
            </header>

            <!-- 3. MAIN CONTENT -->
            <main class="flex-1 w-full px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8 md:pt-8 pb-36 md:pb-12">
                @yield('content')
            </main>
        </div>

        <!-- 4. MOBILE BOTTOM NAVIGATION BAR (Strictly hidden on md: and above) -->
        @include('partials.bottom-nav')
    </div>

    @if(app()->environment('local'))
        @include('partials.time-simulator')
    @endif

    @include('partials.confirm-dialog')
    @include('partials.toast-notification')

    @stack('scripts')
    <script>
        // Initialize Lucide Icons
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        // Remove preload transition lock right after initial DOM render
        window.addEventListener('DOMContentLoaded', () => {
            requestAnimationFrame(() => {
                document.body.classList.remove('preload-transitions');
            });
        });
    </script>
</body>
</html>
