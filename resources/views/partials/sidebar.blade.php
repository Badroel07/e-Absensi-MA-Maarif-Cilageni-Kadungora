<aside id="main-sidebar" class="hidden md:flex w-64 lg:w-72 bg-white text-slate-800 flex-col shrink-0 sticky top-0 h-screen z-40 select-none">
    <!-- Top Brand Section (Overlapping above topbar header) -->
    <div class="h-[72px] px-5 bg-emerald-800 border-b border-r border-emerald-900/20 flex items-center shrink-0" style="height: 72px; min-height: 72px;">
        <a href="{{ Auth::check() ? (Auth::user()->role === 'admin' ? route('admin.dashboard') : (Auth::user()->role === 'guru' ? route('guru.dashboard') : route('siswa.dashboard'))) : url('/') }}" class="flex items-center space-x-3.5 group min-w-0 w-full">
            <img src="{{ asset('img/d41a7486-229a-4c8a-9dc7-549fa8b467b0.png') }}" alt="Logo MA Ma'arif Cilageni" class="w-12 h-12 object-contain shrink-0 group-hover:scale-105 transition-transform drop-shadow-md" width="48" height="48" fetchpriority="high">
            <div class="min-w-0 flex-1">
                <h1 class="text-sm font-bold text-white heading-font tracking-tight truncate leading-snug">MA Ma'arif Cilageni</h1>
                @if(Auth::check() && Auth::user()->role === 'admin')
                    <p class="text-[11px] text-emerald-200/90 font-semibold uppercase tracking-wider truncate leading-tight">Panel Admin</p>
                @else
                    <p class="text-[11px] text-emerald-200/90 font-semibold uppercase tracking-wider truncate leading-tight">Sistem Presensi</p>
                @endif
            </div>
        </a>
    </div>

    <!-- Sidebar Lower Body -->
    <div class="flex-1 flex flex-col min-h-0 bg-white border-r border-slate-200">
        <!-- Navigation Links by Role -->
        <nav id="sidebar-nav" class="flex-1 px-4 py-5 space-y-5 overflow-y-auto custom-sidebar-scroll text-xs">
        @auth
            @if(Auth::user()->role === 'admin')
                <!-- 1. ADMIN NAVIGATION -->
                <!-- Dashboard -->
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                <!-- Data Pokok Madrasah -->
                <div class="space-y-1">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Data Pokok Madrasah</p>

                    <a href="{{ route('admin.siswa.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('admin.siswa.*') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="users" class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.siswa.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Data Siswa</span>
                    </a>

                    <a href="{{ route('admin.guru.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('admin.guru.*') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="graduation-cap" class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.guru.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Data Guru</span>
                    </a>

                    <a href="{{ route('admin.kelas.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('admin.kelas.*') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="school" class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.kelas.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Rombel / Kelas</span>
                    </a>

                    <a href="{{ route('admin.mapel.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('admin.mapel.*') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="book-open" class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.mapel.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Mata Pelajaran</span>
                    </a>

                    <a href="{{ route('admin.jadwal.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('admin.jadwal.*') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="calendar" class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.jadwal.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Jadwal Mingguan</span>
                    </a>
                </div>

                <!-- Presensi & Kehadiran -->
                <div class="space-y-1">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Presensi & Kehadiran</p>

                    <a href="{{ route('admin.presensi-guru.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('admin.presensi-guru.*') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="user-check" class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.presensi-guru.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Kehadiran Dewan Guru</span>
                    </a>

                    <a href="{{ route('admin.presensi-siswa.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('admin.presensi-siswa.*') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="clipboard-check" class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.presensi-siswa.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Kehadiran Siswa</span>
                    </a>
                </div>

                <!-- Pengaturan & Laporan -->
                <div class="space-y-1">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Pengaturan & Laporan</p>

                    <a href="{{ route('admin.lokasi.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('admin.lokasi.*') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="map-pin" class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.lokasi.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Pengaturan Lokasi</span>
                    </a>

                    <a href="{{ route('admin.laporan.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('admin.laporan.*') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="file-text" class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.laporan.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Laporan & Rekapitulasi</span>
                    </a>

                    <a href="{{ route('admin.audit.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('admin.audit.*') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="shield-alert" class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.audit.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Riwayat Perubahan Data</span>
                    </a>
                </div>

                <!-- Layanan Madrasah Kiosk -->
                <div class="space-y-1">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Layanan Madrasah</p>
                    <a href="{{ route('kiosk.index') }}" target="_blank" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-amber-800 bg-amber-50/70 hover:bg-amber-100/80 border border-amber-200/80 font-semibold transition-all duration-150 active:scale-[0.98] group">
                        <i data-lucide="qr-code" class="w-4 h-4 shrink-0 text-amber-600 group-hover:scale-110 transition-transform"></i>
                        <span class="flex-1">Layar Presensi</span>
                        <i data-lucide="external-link" class="w-3.5 h-3.5 text-amber-500"></i>
                    </a>
                </div>
            @elseif(Auth::user()->role === 'guru')
                <!-- 2. GURU NAVIGATION -->
                <div class="space-y-1">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Menu Guru</p>

                    <a href="{{ route('guru.dashboard') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('guru.dashboard') || request()->routeIs('guru.session.*') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0 {{ request()->routeIs('guru.dashboard') || request()->routeIs('guru.session.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('guru.schedule') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('guru.schedule') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="calendar" class="w-4 h-4 shrink-0 {{ request()->routeIs('guru.schedule') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Jadwal Mengajar</span>
                    </a>

                    <a href="{{ route('guru.scan') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('guru.scan') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="qr-code" class="w-4 h-4 shrink-0 {{ request()->routeIs('guru.scan') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Pindai QR Presensi</span>
                    </a>

                    <a href="{{ route('guru.history') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('guru.history') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="history" class="w-4 h-4 shrink-0 {{ request()->routeIs('guru.history') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Riwayat Presensi</span>
                    </a>
                </div>

                <div class="space-y-1">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Layanan Madrasah</p>
                    <a href="{{ route('kiosk.index') }}" target="_blank" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-amber-800 bg-amber-50/70 hover:bg-amber-100/80 border border-amber-200/80 font-semibold transition-all duration-150 active:scale-[0.98] group">
                        <i data-lucide="qr-code" class="w-4 h-4 shrink-0 text-amber-600 group-hover:scale-110 transition-transform"></i>
                        <span class="flex-1">Layar Presensi</span>
                        <i data-lucide="external-link" class="w-3.5 h-3.5 text-amber-500"></i>
                    </a>
                </div>
            @elseif(Auth::user()->role === 'siswa')
                <!-- 3. SISWA NAVIGATION -->
                <div class="space-y-1">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Menu Siswa</p>

                    <a href="{{ route('siswa.dashboard') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('siswa.dashboard') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0 {{ request()->routeIs('siswa.dashboard') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('siswa.schedule') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('siswa.schedule') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="calendar" class="w-4 h-4 shrink-0 {{ request()->routeIs('siswa.schedule') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Jadwal Pelajaran</span>
                    </a>

                    <a href="{{ route('siswa.history') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl {{ request()->routeIs('siswa.history') ? 'bg-emerald-700 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium' }} transition-all duration-150 active:scale-[0.98]">
                        <i data-lucide="history" class="w-4 h-4 shrink-0 {{ request()->routeIs('siswa.history') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Riwayat Kehadiran</span>
                    </a>
                </div>
            @endif
        @endauth
    </nav>

    <!-- User Profile Footer & Logout -->
    @auth
    <div class="p-4 border-t border-slate-100 bg-slate-50/60">
        <div class="flex items-center justify-between gap-2.5">
            <a href="{{ route('profile') }}" class="flex items-center space-x-2.5 min-w-0 flex-1 group">
                @if(Auth::user()->profile_photo_url)
                    <img src="{{ Auth::user()->profile_photo_url }}" loading="lazy" decoding="async" alt="{{ Auth::user()->name }}" class="w-9 h-9 rounded-xl object-cover shadow-2xs shrink-0 group-hover:scale-105 transition-transform border border-slate-200">
                @else
                    <div class="w-9 h-9 rounded-xl {{ Auth::user()->role === 'guru' ? 'bg-amber-100 text-amber-800 border-amber-200' : (Auth::user()->role === 'admin' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-maarif-100 text-maarif-800 border-maarif-200') }} font-bold text-xs flex items-center justify-center shrink-0 border shadow-2xs group-hover:scale-105 transition-transform">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
                <div class="truncate">
                    <p class="text-xs font-semibold text-slate-900 truncate leading-tight group-hover:text-emerald-700 transition">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5 capitalize">
                        {{ Auth::user()->role === 'admin' ? 'Administrator' : (Auth::user()->role === 'guru' ? 'Dewan Guru' : 'Siswa') }}
                    </p>
                </div>
            </a>

            <form action="{{ route('logout') }}" method="POST"
                data-confirm="Apakah Anda yakin ingin mengakhiri sesi dan keluar dari sistem absensi?"
                data-confirm-title="Konfirmasi Keluar Akun"
                data-confirm-type="warning"
                data-confirm-btn="Ya, Keluar"
                data-confirm-icon="log-out"
                class="shrink-0 m-0">
                @csrf
                <button type="submit" title="Keluar dari sistem" aria-label="Keluar dari sistem" class="p-2 rounded-xl bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-500 border border-slate-200/60 transition-all duration-150 active:scale-95 cursor-pointer shadow-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
    @endauth
    </div>
</aside>
