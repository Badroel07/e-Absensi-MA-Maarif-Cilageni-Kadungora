<!-- 4. MOBILE BOTTOM NAVIGATION BAR (Strictly hidden on md: and above) -->
@auth
    <nav id="mobile-bottom-nav" class="md:hidden fixed bottom-0 inset-x-0 z-50 flex justify-center pointer-events-none">
        <div id="mobile-bottom-nav-inner" class="pointer-events-auto w-full bg-gradient-to-r from-emerald-800 via-maarif-700 to-emerald-800 border-t border-emerald-900/80 shadow-[0_-4px_24px_rgba(0,0,0,0.25)] px-2 pt-2.5 pb-2 safe-bottom">
            <div aria-label="Sleek Minimal Navigation" class="flex items-center justify-around">
                @if(Auth::user()->role === 'siswa')
                    <!-- 1. Dashboard -->
                    <a href="{{ route('siswa.dashboard') }}" aria-current="{{ request()->routeIs('siswa.dashboard') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center py-1 group">
                        @if(request()->routeIs('siswa.dashboard'))
                            <span class="absolute -top-2.5 w-8 h-[2px] bg-gradient-to-r from-emerald-400 to-teal-300 shadow-[0_0_8px_#34d399] rounded-full"></span>
                            <div class="text-emerald-200 transition-colors">
                                <i data-lucide="layout-dashboard" class="w-5 h-5 drop-shadow-[0_0_6px_rgba(52,211,153,0.6)] stroke-[2.2]"></i>
                            </div>
                            <span class="text-[10px] font-bold text-white mt-1 tracking-tight">Dashboard</span>
                        @else
                            <i data-lucide="layout-dashboard" class="w-5 h-5 text-emerald-100/60 group-hover:text-white transition-colors stroke-[1.8]"></i>
                            <span class="text-[10px] font-medium text-emerald-100/70 mt-1 tracking-tight group-hover:text-white">Dashboard</span>
                        @endif
                    </a>

                    <!-- 2. Jadwal -->
                    <a href="{{ route('siswa.schedule') }}" aria-current="{{ request()->routeIs('siswa.schedule') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center py-1 group">
                        @if(request()->routeIs('siswa.schedule'))
                            <span class="absolute -top-2.5 w-8 h-[2px] bg-gradient-to-r from-emerald-400 to-teal-300 shadow-[0_0_8px_#34d399] rounded-full"></span>
                            <div class="text-emerald-200 transition-colors">
                                <i data-lucide="calendar" class="w-5 h-5 drop-shadow-[0_0_6px_rgba(52,211,153,0.6)] stroke-[2.2]"></i>
                            </div>
                            <span class="text-[10px] font-bold text-white mt-1 tracking-tight">Jadwal</span>
                        @else
                            <i data-lucide="calendar" class="w-5 h-5 text-emerald-100/60 group-hover:text-white transition-colors stroke-[1.8]"></i>
                            <span class="text-[10px] font-medium text-emerald-100/70 mt-1 tracking-tight group-hover:text-white">Jadwal</span>
                        @endif
                    </a>

                    <!-- 3. Riwayat -->
                    <a href="{{ route('siswa.history') }}" aria-current="{{ request()->routeIs('siswa.history') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center py-1 group">
                        @if(request()->routeIs('siswa.history'))
                            <span class="absolute -top-2.5 w-8 h-[2px] bg-gradient-to-r from-emerald-400 to-teal-300 shadow-[0_0_8px_#34d399] rounded-full"></span>
                            <div class="text-emerald-200 transition-colors">
                                <i data-lucide="history" class="w-5 h-5 drop-shadow-[0_0_6px_rgba(52,211,153,0.6)] stroke-[2.2]"></i>
                            </div>
                            <span class="text-[10px] font-bold text-white mt-1 tracking-tight">Riwayat</span>
                        @else
                            <i data-lucide="history" class="w-5 h-5 text-emerald-100/60 group-hover:text-white transition-colors stroke-[1.8]"></i>
                            <span class="text-[10px] font-medium text-emerald-100/70 mt-1 tracking-tight group-hover:text-white">Riwayat</span>
                        @endif
                    </a>

                    <!-- 4. Profil -->
                    <a href="{{ route('profile') }}" aria-current="{{ request()->routeIs('profile') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center py-1 group">
                        @if(request()->routeIs('profile'))
                            <span class="absolute -top-2.5 w-8 h-[2px] bg-gradient-to-r from-emerald-400 to-teal-300 shadow-[0_0_8px_#34d399] rounded-full"></span>
                            <div class="text-emerald-200 transition-colors">
                                <i data-lucide="user" class="w-5 h-5 drop-shadow-[0_0_6px_rgba(52,211,153,0.6)] stroke-[2.2]"></i>
                            </div>
                            <span class="text-[10px] font-bold text-white mt-1 tracking-tight">Profil</span>
                        @else
                            <i data-lucide="user" class="w-5 h-5 text-emerald-100/60 group-hover:text-white transition-colors stroke-[1.8]"></i>
                            <span class="text-[10px] font-medium text-emerald-100/70 mt-1 tracking-tight group-hover:text-white">Profil</span>
                        @endif
                    </a>

                @elseif(Auth::user()->role === 'guru')
                    <!-- Guru menu items -->
                    <!-- 1. Dashboard -->
                    <a href="{{ route('guru.dashboard') }}" aria-current="{{ request()->routeIs('guru.dashboard') && !request()->has('jadwal') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center py-1 group">
                        @if(request()->routeIs('guru.dashboard') && !request()->has('jadwal'))
                            <span class="absolute -top-2.5 w-8 h-[2px] bg-gradient-to-r from-emerald-400 to-teal-300 shadow-[0_0_8px_#34d399] rounded-full"></span>
                            <div class="text-emerald-200 transition-colors">
                                <i data-lucide="layout-dashboard" class="w-5 h-5 drop-shadow-[0_0_6px_rgba(52,211,153,0.6)] stroke-[2.2]"></i>
                            </div>
                            <span class="text-[10px] font-bold text-white mt-1 tracking-tight">Dashboard</span>
                        @else
                            <i data-lucide="layout-dashboard" class="w-5 h-5 text-emerald-100/60 group-hover:text-white transition-colors stroke-[1.8]"></i>
                            <span class="text-[10px] font-medium text-emerald-100/70 mt-1 tracking-tight group-hover:text-white">Dashboard</span>
                        @endif
                    </a>

                    <!-- 2. Jadwal -->
                    <a href="{{ route('guru.schedule') }}" aria-current="{{ request()->routeIs('guru.schedule') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center py-1 group">
                        @if(request()->routeIs('guru.schedule'))
                            <span class="absolute -top-2.5 w-8 h-[2px] bg-gradient-to-r from-emerald-400 to-teal-300 shadow-[0_0_8px_#34d399] rounded-full"></span>
                            <div class="text-emerald-200 transition-colors">
                                <i data-lucide="calendar" class="w-5 h-5 drop-shadow-[0_0_6px_rgba(52,211,153,0.6)] stroke-[2.2]"></i>
                            </div>
                            <span class="text-[10px] font-bold text-white mt-1 tracking-tight">Jadwal</span>
                        @else
                            <i data-lucide="calendar" class="w-5 h-5 text-emerald-100/60 group-hover:text-white transition-colors stroke-[1.8]"></i>
                            <span class="text-[10px] font-medium text-emerald-100/70 mt-1 tracking-tight group-hover:text-white">Jadwal</span>
                        @endif
                    </a>

                    <!-- 3. Pindai QR -->
                    <a href="{{ route('guru.scan') }}" aria-current="{{ request()->routeIs('guru.scan') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center py-1 group">
                        @if(request()->routeIs('guru.scan'))
                            <span class="absolute -top-2.5 w-8 h-[2px] bg-gradient-to-r from-emerald-400 to-teal-300 shadow-[0_0_8px_#34d399] rounded-full"></span>
                            <div class="text-emerald-200 transition-colors">
                                <i data-lucide="qr-code" class="w-5 h-5 drop-shadow-[0_0_6px_rgba(52,211,153,0.6)] stroke-[2.2]"></i>
                            </div>
                            <span class="text-[10px] font-bold text-white mt-1 tracking-tight">Pindai</span>
                        @else
                            <i data-lucide="qr-code" class="w-5 h-5 text-emerald-100/60 group-hover:text-white transition-colors stroke-[1.8]"></i>
                            <span class="text-[10px] font-medium text-emerald-100/70 mt-1 tracking-tight group-hover:text-white">Pindai</span>
                        @endif
                    </a>

                    <!-- 4. Riwayat -->
                    <a href="{{ route('guru.history') }}" aria-current="{{ request()->routeIs('guru.history') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center py-1 group">
                        @if(request()->routeIs('guru.history'))
                            <span class="absolute -top-2.5 w-8 h-[2px] bg-gradient-to-r from-emerald-400 to-teal-300 shadow-[0_0_8px_#34d399] rounded-full"></span>
                            <div class="text-emerald-200 transition-colors">
                                <i data-lucide="history" class="w-5 h-5 drop-shadow-[0_0_6px_rgba(52,211,153,0.6)] stroke-[2.2]"></i>
                            </div>
                            <span class="text-[10px] font-bold text-white mt-1 tracking-tight">Riwayat</span>
                        @else
                            <i data-lucide="history" class="w-5 h-5 text-emerald-100/60 group-hover:text-white transition-colors stroke-[1.8]"></i>
                            <span class="text-[10px] font-medium text-emerald-100/70 mt-1 tracking-tight group-hover:text-white">Riwayat</span>
                        @endif
                    </a>

                    <!-- 5. Profil -->
                    <a href="{{ route('profile') }}" aria-current="{{ request()->routeIs('profile') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center py-1 group">
                        @if(request()->routeIs('profile'))
                            <span class="absolute -top-2.5 w-8 h-[2px] bg-gradient-to-r from-emerald-400 to-teal-300 shadow-[0_0_8px_#34d399] rounded-full"></span>
                            <div class="text-emerald-200 transition-colors">
                                <i data-lucide="user" class="w-5 h-5 drop-shadow-[0_0_6px_rgba(52,211,153,0.6)] stroke-[2.2]"></i>
                            </div>
                            <span class="text-[10px] font-bold text-white mt-1 tracking-tight">Profil</span>
                        @else
                            <i data-lucide="user" class="w-5 h-5 text-emerald-100/60 group-hover:text-white transition-colors stroke-[1.8]"></i>
                            <span class="text-[10px] font-medium text-emerald-100/70 mt-1 tracking-tight group-hover:text-white">Profil</span>
                        @endif
                    </a>
                
                @elseif(Auth::user()->role === 'admin')
                    <!-- Admin menu items -->
                    <!-- 1. Dashboard Admin -->
                    <a href="{{ route('admin.dashboard') }}" aria-current="{{ request()->routeIs('admin.dashboard') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center py-1 group">
                        @if(request()->routeIs('admin.dashboard'))
                            <span class="absolute -top-2.5 w-8 h-[2px] bg-gradient-to-r from-emerald-400 to-teal-300 shadow-[0_0_8px_#34d399] rounded-full"></span>
                            <div class="text-emerald-200 transition-colors">
                                <i data-lucide="layout-dashboard" class="w-5 h-5 drop-shadow-[0_0_6px_rgba(52,211,153,0.6)] stroke-[2.2]"></i>
                            </div>
                            <span class="text-[10px] font-bold text-white mt-1 tracking-tight">Dashboard</span>
                        @else
                            <i data-lucide="layout-dashboard" class="w-5 h-5 text-emerald-100/60 group-hover:text-white transition-colors stroke-[1.8]"></i>
                            <span class="text-[10px] font-medium text-emerald-100/70 mt-1 tracking-tight group-hover:text-white">Dashboard</span>
                        @endif
                    </a>

                    <!-- 2. Siswa -->
                    <a href="{{ route('admin.siswa.index') }}" aria-current="{{ request()->routeIs('admin.siswa.*') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center py-1 group">
                        @if(request()->routeIs('admin.siswa.*'))
                            <span class="absolute -top-2.5 w-8 h-[2px] bg-gradient-to-r from-emerald-400 to-teal-300 shadow-[0_0_8px_#34d399] rounded-full"></span>
                            <div class="text-emerald-200 transition-colors">
                                <i data-lucide="users" class="w-5 h-5 drop-shadow-[0_0_6px_rgba(52,211,153,0.6)] stroke-[2.2]"></i>
                            </div>
                            <span class="text-[10px] font-bold text-white mt-1 tracking-tight">Siswa</span>
                        @else
                            <i data-lucide="users" class="w-5 h-5 text-emerald-100/60 group-hover:text-white transition-colors stroke-[1.8]"></i>
                            <span class="text-[10px] font-medium text-emerald-100/70 mt-1 tracking-tight group-hover:text-white">Siswa</span>
                        @endif
                    </a>

                    <!-- 3. Guru -->
                    <a href="{{ route('admin.guru.index') }}" aria-current="{{ request()->routeIs('admin.guru.*') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center py-1 group">
                        @if(request()->routeIs('admin.guru.*'))
                            <span class="absolute -top-2.5 w-8 h-[2px] bg-gradient-to-r from-emerald-400 to-teal-300 shadow-[0_0_8px_#34d399] rounded-full"></span>
                            <div class="text-emerald-200 transition-colors">
                                <i data-lucide="graduation-cap" class="w-5 h-5 drop-shadow-[0_0_6px_rgba(52,211,153,0.6)] stroke-[2.2]"></i>
                            </div>
                            <span class="text-[10px] font-bold text-white mt-1 tracking-tight">Guru</span>
                        @else
                            <i data-lucide="graduation-cap" class="w-5 h-5 text-emerald-100/60 group-hover:text-white transition-colors stroke-[1.8]"></i>
                            <span class="text-[10px] font-medium text-emerald-100/70 mt-1 tracking-tight group-hover:text-white">Guru</span>
                        @endif
                    </a>

                    <!-- 4. Profil -->
                    <a href="{{ route('profile') }}" aria-current="{{ request()->routeIs('profile') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center py-1 group">
                        @if(request()->routeIs('profile'))
                            <span class="absolute -top-2.5 w-8 h-[2px] bg-gradient-to-r from-emerald-400 to-teal-300 shadow-[0_0_8px_#34d399] rounded-full"></span>
                            <div class="text-emerald-200 transition-colors">
                                <i data-lucide="user" class="w-5 h-5 drop-shadow-[0_0_6px_rgba(52,211,153,0.6)] stroke-[2.2]"></i>
                            </div>
                            <span class="text-[10px] font-bold text-white mt-1 tracking-tight">Profil</span>
                        @else
                            <i data-lucide="user" class="w-5 h-5 text-emerald-100/60 group-hover:text-white transition-colors stroke-[1.8]"></i>
                            <span class="text-[10px] font-medium text-emerald-100/70 mt-1 tracking-tight group-hover:text-white">Profil</span>
                        @endif
                    </a>
                @endif
            </div>
        </div>
    </nav>
@endauth
