@extends('layouts.admin')

@section('title', 'Jadwal Pelajaran — Admin')
@section('page-title', 'Jadwal Pelajaran Mingguan')

@section('content')
<div class="space-y-6">
    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 heading-font tracking-tight">Jadwal Pelajaran</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Agenda jadwal mingguan, alokasi jam pelajaran per kelas, dan guru pengampu</p>
        </div>
    </div>

    <!-- Action & Filters -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.jadwal.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            <select name="day" class="px-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
                <option value="">Semua Hari</option>
                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $d)
                    <option value="{{ $d }}" {{ request('day') === $d ? 'selected' : '' }}>{{ $d }}</option>
                @endforeach
            </select>

            <select name="classroom_id" class="px-4 py-2.5 text-xs sm:text-sm border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
                <option value="">Semua Kelas</option>
                @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" {{ request('classroom_id') == $c->id ? 'selected' : '' }}>
                        Kelas {{ $c->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white rounded-2xl text-xs font-extrabold transition-all duration-150 active:scale-95 shadow-md shadow-slate-800/20 cursor-pointer">
                <i data-lucide="filter" class="w-4 h-4"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['day', 'classroom_id']))
                <a href="{{ route('admin.jadwal.index') }}" class="inline-flex items-center justify-center px-3.5 py-2.5 text-xs text-slate-500 hover:text-slate-800 font-bold transition-colors duration-150 cursor-pointer">Reset</a>
            @endif
        </form>

        <button type="button" onclick="openAddModal()" class="inline-flex items-center justify-center gap-2 py-3 px-5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs sm:text-sm rounded-2xl shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] cursor-pointer shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Jadwal</span>
        </button>
    </div>

    <!-- Table Jadwal -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-500 uppercase font-extrabold text-[11px] tracking-wider">
                        <th class="py-3.5 px-5">Hari</th>
                        <th class="py-3.5 px-5">Jam Pelajaran</th>
                        <th class="py-3.5 px-5">Kelas</th>
                        <th class="py-3.5 px-5">Mata Pelajaran</th>
                        <th class="py-3.5 px-5">Bapak/Ibu Guru Pengampu</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schedules as $sch)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-5 font-bold text-slate-900">
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-mono font-bold text-xs border border-slate-200">
                                    {{ $sch->day_of_week }}
                                </span>
                            </td>
                            <td class="py-4 px-5 font-mono font-bold text-emerald-800">
                                {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }} WIB
                            </td>
                            <td class="py-4 px-5">
                                <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 font-extrabold text-xs border border-emerald-200/80">
                                    Kelas {{ $sch->classroom->name }}
                                </span>
                            </td>
                            <td class="py-4 px-5 font-bold text-slate-900 text-sm">{{ $sch->subject->name }}</td>
                            <td class="py-4 px-5 text-slate-700 font-medium">{{ $sch->teacher->name }}</td>
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <button type="button" onclick="openEditModal({{ json_encode([
                                        'id' => $sch->id,
                                        'classroom_id' => $sch->classroom_id,
                                        'subject_id' => $sch->subject_id,
                                        'teacher_id' => $sch->teacher_id,
                                        'day_of_week' => $sch->day_of_week,
                                        'start_time' => substr($sch->start_time, 0, 5),
                                        'end_time' => substr($sch->end_time, 0, 5),
                                    ]) }})" title="Ubah Jadwal" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer shadow-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                        <span>Edit</span>
                                    </button>

                                    <form action="{{ route('admin.jadwal.destroy', $sch) }}" method="POST" class="inline-flex m-0 p-0"
                                        data-confirm="Apakah Anda yakin ingin menghapus jadwal pelajaran <strong>{{ $sch->subject->name }}</strong> untuk kelas <strong>{{ $sch->classroom->name }}</strong> di hari {{ $sch->day_of_week }}?"
                                        data-confirm-title="Hapus Jadwal Pelajaran"
                                        data-confirm-type="danger"
                                        data-confirm-btn="Ya, Hapus"
                                        data-confirm-icon="trash-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Jadwal" class="inline-flex items-center gap-1.5 py-1.5 px-3 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer shadow-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 text-white/90 shrink-0"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 font-medium">Tidak ada jadwal pelajaran yang cocok dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-slate-100">
            {{ $schedules->links() }}
        </div>
    </div>

    <!-- Modal Tambah Jadwal -->
    <div id="modalAddJadwal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-md bg-white rounded-3xl p-6 sm:p-7 shadow-2xl space-y-5 border border-slate-200 animate-in fade-in zoom-in duration-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center border border-emerald-200/80">
                        <i data-lucide="calendar-plus" class="w-5 h-5 text-emerald-700"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-900 heading-font text-base">Tambah Jadwal Pelajaran</h3>
                </div>
                <button type="button" onclick="closeAddModal()" class="p-2 -mr-1 -mt-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-150 active:scale-95 cursor-pointer" aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.jadwal.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Hari <span class="text-rose-500">*</span></label>
                        <select name="day_of_week" required class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Kelas <span class="text-rose-500">*</span></label>
                        <select name="classroom_id" required class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
                            <option value="">Pilih Kelas</option>
                            @foreach($classrooms as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <select name="subject_id" required class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
                        <option value="">Pilih Mata Pelajaran</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Bapak/Ibu Guru Pengampu <span class="text-rose-500">*</span></label>
                    <select name="teacher_id" required class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
                        <option value="">Pilih Guru Pengampu</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Jam Mulai <span class="text-rose-500">*</span></label>
                        <input type="time" name="start_time" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-mono transition-all">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Jam Selesai <span class="text-rose-500">*</span></label>
                        <input type="time" name="end_time" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-mono transition-all">
                    </div>
                </div>

                <p class="text-[11px] text-slate-500 bg-slate-50 p-3 rounded-2xl border border-slate-200 font-medium">
                    * Sistem secara otomatis memeriksa agar jadwal tidak bertabrakan dengan jadwal guru atau kelas lainnya.
                </p>

                <div class="pt-3 flex items-center gap-3">
                    <button type="button" onclick="closeAddModal()" class="flex-1 py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-bold text-xs sm:text-sm border border-slate-200/60 transition-all duration-150 active:scale-[0.98] cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-3 px-4 rounded-2xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Jadwal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Jadwal -->
    <div id="modalEditJadwal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-md bg-white rounded-3xl p-6 sm:p-7 shadow-2xl space-y-5 border border-slate-200 animate-in fade-in zoom-in duration-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-800 flex items-center justify-center border border-sky-200/80">
                        <i data-lucide="calendar" class="w-5 h-5 text-sky-700"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-900 heading-font text-base">Ubah Jadwal Pelajaran</h3>
                </div>
                <button type="button" onclick="closeEditModal()" class="p-2 -mr-1 -mt-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-150 active:scale-95 cursor-pointer" aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEditJadwal" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Hari <span class="text-rose-500">*</span></label>
                        <select id="editJadwalDay" name="day_of_week" required class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Kelas <span class="text-rose-500">*</span></label>
                        <select id="editJadwalClassroom" name="classroom_id" required class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
                            <option value="">Pilih Kelas</option>
                            @foreach($classrooms as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <select id="editJadwalSubject" name="subject_id" required class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
                        <option value="">Pilih Mata Pelajaran</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Bapak/Ibu Guru Pengampu <span class="text-rose-500">*</span></label>
                    <select id="editJadwalTeacher" name="teacher_id" required class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-medium transition-all">
                        <option value="">Pilih Guru Pengampu</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Jam Mulai <span class="text-rose-500">*</span></label>
                        <input type="time" id="editJadwalStartTime" name="start_time" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-mono transition-all">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Jam Selesai <span class="text-rose-500">*</span></label>
                        <input type="time" id="editJadwalEndTime" name="end_time" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-600 focus:outline-none bg-slate-50/50 focus:bg-white font-mono transition-all">
                    </div>
                </div>

                <p class="text-[11px] text-slate-400 italic">
                    * Validasi bentrok jadwal akan mengecualikan jadwal yang sedang diedit ini.
                </p>

                <div class="pt-3 flex items-center gap-3">
                    <button type="button" onclick="closeEditModal()" class="flex-1 py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-bold text-xs sm:text-sm border border-slate-200/60 transition-all duration-150 active:scale-[0.98] cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-3 px-4 rounded-2xl bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-900/20 transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Perbarui Jadwal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAddModal() { document.getElementById('modalAddJadwal').classList.remove('hidden'); }
    function closeAddModal() { document.getElementById('modalAddJadwal').classList.add('hidden'); }

    function openEditModal(schedule) {
        document.getElementById('formEditJadwal').action = '/admin/jadwal/' + schedule.id;
        document.getElementById('editJadwalDay').value = schedule.day_of_week;
        document.getElementById('editJadwalClassroom').value = schedule.classroom_id;
        document.getElementById('editJadwalSubject').value = schedule.subject_id;
        document.getElementById('editJadwalTeacher').value = schedule.teacher_id;
        document.getElementById('editJadwalStartTime').value = schedule.start_time;
        document.getElementById('editJadwalEndTime').value = schedule.end_time;

        document.getElementById('modalEditJadwal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('modalEditJadwal').classList.add('hidden');
    }
</script>
@endpush
