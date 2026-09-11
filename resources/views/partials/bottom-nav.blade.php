<!-- 4. MOBILE BOTTOM NAVIGATION BAR (Strictly hidden on md: and above) -->
@auth
    <nav id="mobile-bottom-nav" class="md:hidden fixed bottom-0 inset-x-0 z-50 flex justify-center pointer-events-none">
        <div id="mobile-bottom-nav-inner" class="w-full bg-gradient-to-r from-emerald-800 via-maarif-700 to-emerald-800 border-t border-emerald-900/80 shadow-[0_-4px_24px_rgba(0,0,0,0.25)] px-3 pt-2 safe-bottom pointer-events-auto">
            @if(Auth::user()->role === 'siswa')
                <div class="grid grid-cols-4 items-center max-w-md mx-auto">
                    <!-- 1. Dashboard -->
                    <a href="{{ route('siswa.dashboard') }}" class="group flex flex-col items-center justify-center py-1 transition-all duration-150 {{ request()->routeIs('siswa.dashboard') ? 'text-white font-semibold' : 'text-emerald-200/75 hover:text-white font-medium' }}">
                        <div class="relative p-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('siswa.dashboard') ? 'bg-white/20 text-white shadow-xs backdrop-blur-xs ring-1 ring-white/25' : 'text-emerald-200/75 group-hover:text-white group-hover:bg-white/10' }}">
                            <i data-lucide="layout-dashboard" class="w-5 h-5 {{ request()->routeIs('siswa.dashboard') ? 'stroke-[2.4]' : 'stroke-[1.8]' }}"></i>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('siswa.dashboard') ? 'text-white font-semibold' : 'text-emerald-200/75 group-hover:text-white' }}">Dashboard</span>
                    </a>

                    <!-- 2. Jadwal -->
                    <a href="{{ route('siswa.schedule') }}" class="group flex flex-col items-center justify-center py-1 transition-all duration-150 {{ request()->routeIs('siswa.schedule') ? 'text-white font-semibold' : 'text-emerald-200/75 hover:text-white font-medium' }}">
                        <div class="relative p-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('siswa.schedule') ? 'bg-white/20 text-white shadow-xs backdrop-blur-xs ring-1 ring-white/25' : 'text-emerald-200/75 group-hover:text-white group-hover:bg-white/10' }}">
                            <i data-lucide="calendar" class="w-5 h-5 {{ request()->routeIs('siswa.schedule') ? 'stroke-[2.4]' : 'stroke-[1.8]' }}"></i>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('siswa.schedule') ? 'text-white font-semibold' : 'text-emerald-200/75 group-hover:text-white' }}">Jadwal</span>
                    </a>

                    <!-- 3. Riwayat -->
                    <a href="{{ route('siswa.history') }}" class="group flex flex-col items-center justify-center py-1 transition-all duration-150 {{ request()->routeIs('siswa.history') ? 'text-white font-semibold' : 'text-emerald-200/75 hover:text-white font-medium' }}">
                        <div class="relative p-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('siswa.history') ? 'bg-white/20 text-white shadow-xs backdrop-blur-xs ring-1 ring-white/25' : 'text-emerald-200/75 group-hover:text-white group-hover:bg-white/10' }}">
                            <i data-lucide="history" class="w-5 h-5 {{ request()->routeIs('siswa.history') ? 'stroke-[2.4]' : 'stroke-[1.8]' }}"></i>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('siswa.history') ? 'text-white font-semibold' : 'text-emerald-200/75 group-hover:text-white' }}">Riwayat</span>
                    </a>

                    <!-- 4. Profil -->
                    <a href="{{ route('profile') }}" class="group flex flex-col items-center justify-center py-1 transition-all duration-150 {{ request()->routeIs('profile') ? 'text-white font-semibold' : 'text-emerald-200/75 hover:text-white font-medium' }}">
                        <div class="relative p-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('profile') ? 'bg-white/20 text-white shadow-xs backdrop-blur-xs ring-1 ring-white/25' : 'text-emerald-200/75 group-hover:text-white group-hover:bg-white/10' }}">
                            <i data-lucide="user" class="w-5 h-5 {{ request()->routeIs('profile') ? 'stroke-[2.4]' : 'stroke-[1.8]' }}"></i>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('profile') ? 'text-white font-semibold' : 'text-emerald-200/75 group-hover:text-white' }}">Profil</span>
                    </a>
                </div>
            @elseif(Auth::user()->role === 'guru')
                <div class="grid grid-cols-5 items-center max-w-md mx-auto">
                    <!-- 1. Dashboard -->
                    <a href="{{ route('guru.dashboard') }}" class="group flex flex-col items-center justify-center py-1 transition-all duration-150 {{ request()->routeIs('guru.dashboard') && !request()->has('jadwal') ? 'text-white font-semibold' : 'text-emerald-200/75 hover:text-white font-medium' }}">
                        <div class="relative p-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('guru.dashboard') && !request()->has('jadwal') ? 'bg-white/20 text-white shadow-xs backdrop-blur-xs ring-1 ring-white/25' : 'text-emerald-200/75 group-hover:text-white group-hover:bg-white/10' }}">
                            <i data-lucide="layout-dashboard" class="w-5 h-5 {{ request()->routeIs('guru.dashboard') && !request()->has('jadwal') ? 'stroke-[2.4]' : 'stroke-[1.8]' }}"></i>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('guru.dashboard') && !request()->has('jadwal') ? 'text-white font-semibold' : 'text-emerald-200/75 group-hover:text-white' }}">Dashboard</span>
                    </a>

                    <!-- 2. Jadwal Mengajar -->
                    <a href="{{ route('guru.schedule') }}" class="group flex flex-col items-center justify-center py-1 transition-all duration-150 {{ request()->routeIs('guru.schedule') ? 'text-white font-semibold' : 'text-emerald-200/75 hover:text-white font-medium' }}">
                        <div class="relative p-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('guru.schedule') ? 'bg-white/20 text-white shadow-xs backdrop-blur-xs ring-1 ring-white/25' : 'text-emerald-200/75 group-hover:text-white group-hover:bg-white/10' }}">
                            <i data-lucide="calendar" class="w-5 h-5 {{ request()->routeIs('guru.schedule') ? 'stroke-[2.4]' : 'stroke-[1.8]' }}"></i>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('guru.schedule') ? 'text-white font-semibold' : 'text-emerald-200/75 group-hover:text-white' }}">Jadwal</span>
                    </a>
                    
                    <!-- 3. Pindai QR (True Center Elevated Button) -->
                    <a href="{{ route('guru.scan') }}" class="group relative flex flex-col items-center justify-center -mt-6">
                        <div class="w-12 h-12 rounded-full {{ request()->routeIs('guru.scan') ? 'bg-amber-300 text-slate-900 ring-4 ring-emerald-800 shadow-xl shadow-amber-950/40' : 'bg-white text-emerald-800 ring-4 ring-emerald-800 shadow-lg shadow-black/30' }} flex items-center justify-center group-active:scale-95 group-hover:scale-105 transition-all duration-150">
                            <i data-lucide="qr-code" class="w-5 h-5 {{ request()->routeIs('guru.scan') ? 'stroke-[2.5]' : 'stroke-[2.2]' }}"></i>
                        </div>
                        <span class="text-[10px] font-semibold mt-1 tracking-tight {{ request()->routeIs('guru.scan') ? 'text-amber-300' : 'text-emerald-200/80 group-hover:text-white' }}">Pindai QR</span>
                    </a>

                    <!-- 4. Riwayat -->
                    <a href="{{ route('guru.history') }}" class="group flex flex-col items-center justify-center py-1 transition-all duration-150 {{ request()->routeIs('guru.history') ? 'text-white font-semibold' : 'text-emerald-200/75 hover:text-white font-medium' }}">
                        <div class="relative p-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('guru.history') ? 'bg-white/20 text-white shadow-xs backdrop-blur-xs ring-1 ring-white/25' : 'text-emerald-200/75 group-hover:text-white group-hover:bg-white/10' }}">
                            <i data-lucide="history" class="w-5 h-5 {{ request()->routeIs('guru.history') ? 'stroke-[2.4]' : 'stroke-[1.8]' }}"></i>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('guru.history') ? 'text-white font-semibold' : 'text-emerald-200/75 group-hover:text-white' }}">Riwayat</span>
                    </a>
                    
                    <!-- 5. Profil -->
                    <a href="{{ route('profile') }}" class="group flex flex-col items-center justify-center py-1 transition-all duration-150 {{ request()->routeIs('profile') ? 'text-white font-semibold' : 'text-emerald-200/75 hover:text-white font-medium' }}">
                        <div class="relative p-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('profile') ? 'bg-white/20 text-white shadow-xs backdrop-blur-xs ring-1 ring-white/25' : 'text-emerald-200/75 group-hover:text-white group-hover:bg-white/10' }}">
                            <i data-lucide="user" class="w-5 h-5 {{ request()->routeIs('profile') ? 'stroke-[2.4]' : 'stroke-[1.8]' }}"></i>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('profile') ? 'text-white font-semibold' : 'text-emerald-200/75 group-hover:text-white' }}">Profil</span>
                    </a>
                </div>
            @elseif(Auth::user()->role === 'admin')
                <div class="grid grid-cols-4 items-center max-w-md mx-auto">
                    <!-- 1. Dashboard Admin -->
                    <a href="{{ route('admin.dashboard') }}" class="group flex flex-col items-center justify-center py-1 transition-all duration-150 {{ request()->routeIs('admin.dashboard') ? 'text-white font-semibold' : 'text-emerald-200/75 hover:text-white font-medium' }}">
                        <div class="relative p-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-white/20 text-white shadow-xs backdrop-blur-xs ring-1 ring-white/25' : 'text-emerald-200/75 group-hover:text-white group-hover:bg-white/10' }}">
                            <i data-lucide="layout-dashboard" class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'stroke-[2.4]' : 'stroke-[1.8]' }}"></i>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('admin.dashboard') ? 'text-white font-semibold' : 'text-emerald-200/75 group-hover:text-white' }}">Dashboard</span>
                    </a>

                    <!-- 2. Siswa -->
                    <a href="{{ route('admin.siswa.index') }}" class="group flex flex-col items-center justify-center py-1 transition-all duration-150 {{ request()->routeIs('admin.siswa.*') ? 'text-white font-semibold' : 'text-emerald-200/75 hover:text-white font-medium' }}">
                        <div class="relative p-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.siswa.*') ? 'bg-white/20 text-white shadow-xs backdrop-blur-xs ring-1 ring-white/25' : 'text-emerald-200/75 group-hover:text-white group-hover:bg-white/10' }}">
                            <i data-lucide="users" class="w-5 h-5 {{ request()->routeIs('admin.siswa.*') ? 'stroke-[2.4]' : 'stroke-[1.8]' }}"></i>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('admin.siswa.*') ? 'text-white font-semibold' : 'text-emerald-200/75 group-hover:text-white' }}">Siswa</span>
                    </a>

                    <!-- 3. Guru -->
                    <a href="{{ route('admin.guru.index') }}" class="group flex flex-col items-center justify-center py-1 transition-all duration-150 {{ request()->routeIs('admin.guru.*') ? 'text-white font-semibold' : 'text-emerald-200/75 hover:text-white font-medium' }}">
                        <div class="relative p-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.guru.*') ? 'bg-white/20 text-white shadow-xs backdrop-blur-xs ring-1 ring-white/25' : 'text-emerald-200/75 group-hover:text-white group-hover:bg-white/10' }}">
                            <i data-lucide="graduation-cap" class="w-5 h-5 {{ request()->routeIs('admin.guru.*') ? 'stroke-[2.4]' : 'stroke-[1.8]' }}"></i>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('admin.guru.*') ? 'text-white font-semibold' : 'text-emerald-200/75 group-hover:text-white' }}">Guru</span>
                    </a>

                    <!-- 4. Profil -->
                    <a href="{{ route('profile') }}" class="group flex flex-col items-center justify-center py-1 transition-all duration-150 {{ request()->routeIs('profile') ? 'text-white font-semibold' : 'text-emerald-200/75 hover:text-white font-medium' }}">
                        <div class="relative p-1.5 rounded-xl transition-all duration-200 {{ request()->routeIs('profile') ? 'bg-white/20 text-white shadow-xs backdrop-blur-xs ring-1 ring-white/25' : 'text-emerald-200/75 group-hover:text-white group-hover:bg-white/10' }}">
                            <i data-lucide="user" class="w-5 h-5 {{ request()->routeIs('profile') ? 'stroke-[2.4]' : 'stroke-[1.8]' }}"></i>
                        </div>
                        <span class="text-[10px] tracking-tight mt-0.5 {{ request()->routeIs('profile') ? 'text-white font-semibold' : 'text-emerald-200/75 group-hover:text-white' }}">Profil</span>
                    </a>
                </div>
            @endif
        </div>
    </nav>
@endauth
