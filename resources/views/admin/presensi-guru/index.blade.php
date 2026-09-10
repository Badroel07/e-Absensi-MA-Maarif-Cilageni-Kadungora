@extends('layouts.admin')

@section('title', 'Presensi Dewan Guru — Admin')
@section('page-title', 'Pemantauan & Rekap Presensi Dewan Guru')

@section('content')
<div class="space-y-6">
    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Presensi Dewan Guru</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pemantauan status kehadiran harian, ketepatan waktu, dan riwayat presensi dewan guru</p>
        </div>
    </div>

    <!-- Top Summary KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Total Guru</p>
            <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mono-font mt-1.5">{{ $summary['totalGuru'] }}</h3>
            <p class="text-[11px] font-medium text-slate-400 mt-1">Dewan Guru Aktif</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-extrabold text-emerald-600 uppercase tracking-wider">Tepat Waktu</p>
            <h3 class="text-2xl sm:text-3xl font-black text-emerald-700 mono-font mt-1.5">{{ $summary['hadir'] }}</h3>
            <p class="text-[11px] font-medium text-slate-400 mt-1">&le; 07:15 WIB</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-extrabold text-amber-600 uppercase tracking-wider">Terlambat</p>
            <h3 class="text-2xl sm:text-3xl font-black text-amber-600 mono-font mt-1.5">{{ $summary['terlambat'] }}</h3>
            <p class="text-[11px] font-medium text-slate-400 mt-1">&gt; 07:15 WIB</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <p class="text-[11px] font-extrabold text-rose-600 uppercase tracking-wider">Belum Hadir</p>
            <h3 class="text-2xl sm:text-3xl font-black text-rose-600 mono-font mt-1.5">{{ $summary['belumHadir'] }}</h3>
            <p class="text-[11px] font-medium text-slate-400 mt-1">Belum Presensi Masuk</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs col-span-2 md:col-span-1">
            <p class="text-[11px] font-extrabold text-emerald-800 uppercase tracking-wider">Sudah Pulang</p>
            <h3 class="text-2xl sm:text-3xl font-black text-emerald-800 mono-font mt-1.5">{{ $summary['checkoutTuntas'] }}</h3>
            <p class="text-[11px] font-medium text-slate-400 mt-1">Tuntas Presensi Pulang</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.presensi-guru.index') }}" class="flex flex-wrap items-end gap-3.5">
            <div>
                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}"
                    class="px-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
            </div>

            <div>
                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Status Kehadiran</label>
                <select name="status" class="px-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
                    <option value="">Semua Status</option>
                    <option value="HADIR" {{ request('status') === 'HADIR' ? 'selected' : '' }}>Hadir (Tepat Waktu)</option>
                    <option value="TERLAMBAT" {{ request('status') === 'TERLAMBAT' ? 'selected' : '' }}>Terlambat</option>
                    <option value="BELUM_HADIR" {{ request('status') === 'BELUM_HADIR' ? 'selected' : '' }}>Belum Hadir</option>
                    <option value="IZIN" {{ request('status') === 'IZIN' ? 'selected' : '' }}>Izin</option>
                    <option value="SAKIT" {{ request('status') === 'SAKIT' ? 'selected' : '' }}>Sakit</option>
                    <option value="ALPA" {{ request('status') === 'ALPA' ? 'selected' : '' }}>Alpa</option>
                </select>
            </div>

            <div class="flex-1 min-w-[220px]">
                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Cari Guru</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama atau NIP Guru..."
                        class="w-full pl-9 pr-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white transition-all">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white rounded-2xl text-xs font-extrabold transition-all duration-150 active:scale-95 shadow-md shadow-slate-800/20 cursor-pointer">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>Terapkan</span>
                </button>
                @if(request()->hasAny(['date', 'status', 'search']))
                    <a href="{{ route('admin.presensi-guru.index') }}" class="px-3.5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 active:text-slate-900 rounded-2xl hover:bg-slate-100 transition-all duration-150 cursor-pointer">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Presensi Guru -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="font-extrabold text-slate-900 heading-font text-sm sm:text-base">Daftar Kehadiran Harian Guru</h3>
                <p class="text-xs text-slate-500 mt-0.5">Tanggal: {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</p>
            </div>
            <span class="text-xs text-slate-500 font-bold bg-slate-100 px-3 py-1.5 rounded-xl self-start sm:self-auto">Total: {{ $attendances->count() }} Guru</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-500 uppercase font-extrabold text-[11px] tracking-wider">
                        <th class="py-3.5 px-5">Guru / NIP</th>
                        <th class="py-3.5 px-5 text-center">Status Masuk</th>
                        <th class="py-3.5 px-5">Jam Masuk</th>
                        <th class="py-3.5 px-5">Jam Pulang</th>
                        <th class="py-3.5 px-5">Status Jadwal Hari Ini</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $row)
                        @php
                            $t = $row['teacher'];
                            $att = $row['attendance'];
                            $st = $row['status'];
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-5">
                                <p class="font-bold text-slate-900 text-sm">{{ $t->name }}</p>
                                <p class="font-mono text-slate-400 text-xs mt-0.5">{{ $t->identity_number }}</p>
                            </td>

                            <td class="py-4 px-5 text-center">
                                @if($st === 'HADIR')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                        TEPAT WAKTU
                                    </span>
                                @elseif($st === 'TERLAMBAT')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-200/80">
                                        TERLAMBAT
                                    </span>
                                @elseif($st === 'IZIN')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-300">
                                        IZIN
                                    </span>
                                @elseif($st === 'SAKIT')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-sky-50 text-sky-800 border border-sky-300">
                                        SAKIT
                                    </span>
                                @elseif($st === 'ALPA')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-rose-50 text-rose-800 border border-rose-200/80">
                                        ALPA
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-600 border border-slate-200">
                                        BELUM HADIR
                                    </span>
                                @endif
                            </td>

                            <td class="py-4 px-5 font-mono">
                                @if($row['check_in_time'])
                                    <span class="font-bold text-slate-900">{{ $row['check_in_time'] }} WIB</span>
                                @else
                                    <span class="text-slate-400 font-sans">-</span>
                                @endif
                            </td>

                            <td class="py-4 px-5 font-mono">
                                @if($row['check_out_time'])
                                    <span class="font-bold text-emerald-700">{{ $row['check_out_time'] }} WIB</span>
                                 @else
                                     <span class="text-slate-400 font-sans italic text-xs">Belum presensi pulang</span>
                                 @endif
                            </td>

                            <td class="py-4 px-5">
                                @if($row['total_schedules_today'] === 0)
                                    <span class="text-slate-400 italic">Tidak ada jadwal</span>
                                @elseif($row['pending_schedules_count'] === 0)
                                    <span class="inline-flex items-center text-emerald-700 font-bold text-xs bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/80">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5 mr-1 text-emerald-600"></i>
                                        {{ $row['total_schedules_today'] }} Kelas Tuntas
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-amber-700 font-bold text-xs bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200/80">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 mr-1 text-amber-600"></i>
                                        {{ $row['pending_schedules_count'] }} dari {{ $row['total_schedules_today'] }} Belum Selesai
                                    </span>
                                @endif
                            </td>

                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <!-- Tombol Lihat Riwayat -->
                                    <a href="{{ route('admin.guru.riwayat', $t) }}" title="Lihat Riwayat Presensi Bapak/Ibu Guru" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-bold text-xs transition-all duration-150 shadow-xs active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                        <i data-lucide="history" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                        <span>Riwayat</span>
                                    </a>

                                    <!-- Tombol Koreksi Kehadiran Guru -->
                                    <button type="button"
                                        onclick="openEditGuruModal({{ json_encode([
                                            'id' => $t->id,
                                            'name' => $t->name,
                                            'status' => in_array($st, ['HADIR', 'TERLAMBAT', 'IZIN', 'SAKIT', 'ALPA']) ? $st : 'HADIR',
                                            'check_in_time' => $row['check_in_time'] ?? '',
                                            'check_out_time' => $row['check_out_time'] ?? '',
                                        ]) }})"
                                        title="Koreksi Kehadiran Bapak/Ibu Guru oleh TU"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-xs transition-all duration-150 shadow-xs active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                                        <i data-lucide="clipboard-pen" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                        <span>Koreksi TU</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 font-medium">Tidak ada data kehadiran yang sesuai dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Koreksi Kehadiran Guru -->
    <div id="modalEditGuruPresensi" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-md bg-white rounded-3xl p-6 sm:p-7 shadow-2xl space-y-5 border border-slate-200 animate-in fade-in zoom-in duration-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-800 flex items-center justify-center border border-indigo-200/80">
                        <i data-lucide="clipboard-pen" class="w-5 h-5 text-indigo-700"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 heading-font text-base">Koreksi Presensi Guru</h3>
                        <p id="modalGuruName" class="text-xs text-emerald-700 font-bold"></p>
                    </div>
                </div>
                <button type="button" onclick="closeEditGuruModal()" aria-label="Tutup modal" class="p-2 -mr-1 -mt-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-150 active:scale-95 cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEditGuruPresensi" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <input type="hidden" name="date" value="{{ $date }}">

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Status Kehadiran <span class="text-rose-500">*</span></label>
                    <select id="editGuruStatus" name="status" required class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-bold transition-all">
                        <option value="HADIR">HADIR (Tepat Waktu)</option>
                        <option value="TERLAMBAT">TERLAMBAT</option>
                        <option value="IZIN">IZIN</option>
                        <option value="SAKIT">SAKIT</option>
                        <option value="ALPA">ALPA (Tanpa Keterangan)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Jam Masuk (Format Jam:Menit)</label>
                        <input type="time" id="editGuruCheckIn" name="check_in_time"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-mono transition-all">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Jam Pulang (Format Jam:Menit)</label>
                        <input type="time" id="editGuruCheckOut" name="check_out_time"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-mono transition-all">
                    </div>
                </div>

                <p class="text-[11px] text-slate-500 bg-slate-50 p-3 rounded-2xl border border-slate-200 font-medium">
                    Koreksi ini dicatat oleh Admin untuk mencatat izin kedinasan, surat sakit, atau kendala presensi Bapak/Ibu Guru.
                </p>

                <div class="pt-3 border-t border-slate-100 flex items-center gap-3">
                    <button type="button" onclick="closeEditGuruModal()" class="flex-1 py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-bold text-xs sm:text-sm border border-slate-200/60 transition-all duration-150 active:scale-[0.98] cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-3 px-4 rounded-2xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Koreksi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditGuruModal(data) {
        document.getElementById('modalGuruName').innerText = data.name;
        document.getElementById('formEditGuruPresensi').action = '/admin/presensi-guru/' + data.id;
        document.getElementById('editGuruStatus').value = data.status;
        document.getElementById('editGuruCheckIn').value = data.check_in_time;
        document.getElementById('editGuruCheckOut').value = data.check_out_time;

        document.getElementById('modalEditGuruPresensi').classList.remove('hidden');
    }

    function closeEditGuruModal() {
        document.getElementById('modalEditGuruPresensi').classList.add('hidden');
    }
</script>
@endsection
