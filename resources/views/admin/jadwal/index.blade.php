@extends('layouts.admin')

@section('title', 'Jadwal Pelajaran — Admin')
@section('page-title', 'Jadwal Pelajaran Mingguan')

@section('content')
<div class="space-y-6">

    {{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 heading-font tracking-tight">Jadwal Pelajaran</h1>
            <p class="text-xs text-slate-500 mt-0.5">Agenda mingguan, alokasi jam pelajaran per rombel, dan penugasan guru pengampu.</p>
        </div>
        <button type="button" onclick="openAddModal()"
            class="inline-flex items-center justify-center gap-2 py-2.5 px-5 bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs rounded-xl shadow-sm transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600 shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Jadwal</span>
        </button>
    </div>

    {{-- ── FILTER BAR ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4">
        <form method="GET" action="{{ route('admin.jadwal.index') }}" data-loading-form class="flex flex-wrap items-center gap-3">
            <select name="day"
                class="px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white font-medium transition cursor-pointer">
                <option value="">Semua Hari</option>
                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $d)
                    <option value="{{ $d }}" {{ request('day') === $d ? 'selected' : '' }}>{{ $d }}</option>
                @endforeach
            </select>

            <select name="classroom_id"
                class="px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:outline-none bg-slate-50 focus:bg-white font-medium transition cursor-pointer">
                <option value="">Semua Kelas</option>
                @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" {{ request('classroom_id') == $c->id ? 'selected' : '' }}>
                        Kelas {{ $c->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-700">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Filter</span>
            </button>

            @if(request()->hasAny(['day', 'classroom_id']))
                <a href="{{ route('admin.jadwal.index') }}"
                   class="text-xs text-slate-500 hover:text-slate-800 font-semibold transition-colors duration-150 cursor-pointer">
                   Reset
                </a>
            @endif
        </form>
    </div>

    {{-- ── TABLE JADWAL ─────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-semibold text-[10px] tracking-widest">
                        <th class="py-3 px-5">Hari</th>
                        <th class="py-3 px-5">Jam Pelajaran</th>
                        <th class="py-3 px-5">Kelas</th>
                        <th class="py-3 px-5">Mata Pelajaran</th>
                        <th class="py-3 px-5">Guru Pengampu</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schedules as $sch)
                        <tr class="hover:bg-slate-50/60 transition-colors duration-100">
                            {{-- Hari --}}
                            <td class="py-3.5 px-5 font-semibold text-slate-900">
                                <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200">
                                    {{ $sch->day_of_week }}
                                </span>
                            </td>

                            {{-- Jam Pelajaran --}}
                            <td class="py-3.5 px-5 font-mono font-medium text-slate-800 mono-font text-xs">
                                {{ substr($sch->start_time, 0, 5) }} – {{ substr($sch->end_time, 0, 5) }} WIB
                            </td>

                            {{-- Kelas --}}
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-1 rounded-md bg-maarif-50 text-maarif-800 border border-maarif-200/70 font-semibold text-xs">
                                    Kelas {{ $sch->classroom->name }}
                                </span>
                            </td>

                            {{-- Mapel --}}
                            <td class="py-3.5 px-5 font-semibold text-slate-900 text-sm">
                                {{ $sch->subject->name }}
                            </td>

                            {{-- Guru Pengampu --}}
                            <td class="py-3.5 px-5 text-slate-700 font-medium text-xs">
                                {{ $sch->teacher->name }}
                            </td>

                            {{-- Aksi --}}
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    {{-- Edit --}}
                                    <button type="button"
                                        onclick="openEditModal({{ json_encode([
                                            'id' => $sch->id,
                                            'classroom_id' => $sch->classroom_id,
                                            'subject_id' => $sch->subject_id,
                                            'teacher_id' => $sch->teacher_id,
                                            'day_of_week' => $sch->day_of_week,
                                            'start_time' => substr($sch->start_time, 0, 5),
                                            'end_time' => substr($sch->end_time, 0, 5),
                                        ]) }})"
                                        title="Ubah Jadwal"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-sky-50 hover:bg-sky-100 border border-sky-200 text-sky-700 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 shrink-0"></i>
                                    </button>

                                    {{-- Hapus --}}
                                    <form action="{{ route('admin.jadwal.destroy', $sch) }}" method="POST" class="inline-flex m-0 p-0"
                                        data-confirm="Apakah Anda yakin ingin menghapus jadwal pelajaran <strong>{{ $sch->subject->name }}</strong> untuk kelas <strong>{{ $sch->classroom->name }}</strong> di hari {{ $sch->day_of_week }}?"
                                        data-confirm-title="Hapus Jadwal Pelajaran"
                                        data-confirm-type="danger"
                                        data-confirm-btn="Ya, Hapus"
                                        data-confirm-icon="trash-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Jadwal"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 shrink-0"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="inline-flex flex-col items-center gap-2">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i data-lucide="calendar-x" class="w-6 h-6 text-slate-400"></i>
                                    </span>
                                    <p class="text-sm font-semibold text-slate-600">
                                        @if(request()->hasAny(['day', 'classroom_id']))
                                            Tidak ada jadwal pelajaran yang cocok dengan filter.
                                        @else
                                            Belum ada jadwal pelajaran yang dibuat.
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        @if(request()->hasAny(['day', 'classroom_id']))
                                            Coba sesuaikan filter atau <a href="{{ route('admin.jadwal.index') }}" class="text-maarif-700 font-semibold hover:underline">reset filter</a>.
                                        @else
                                            Klik <button onclick="openAddModal()" class="text-maarif-700 font-semibold hover:underline">Tambah Jadwal</button> untuk menyusun jadwal pelajaran kelas.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

            {{-- Pagination --}}
            @if($schedules->hasPages())
                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $schedules->links() }}
                </div>
            @endif
    </div>

    {{-- ── MODAL TAMBAH JADWAL ──────────────────────────────────────── --}}
    <div id="modalAddJadwal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 hidden transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="modalAddTitle">
        <div class="w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-200/80 max-h-[92vh] flex flex-col overflow-hidden transform transition-all">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/60 shrink-0">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-maarif-50 text-maarif-700 border border-maarif-200/70 flex items-center justify-center shadow-xs">
                        <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 id="modalAddTitle" class="font-bold text-slate-900 heading-font text-base sm:text-lg">Tambah Jadwal Pelajaran</h3>
                        <p class="text-xs text-slate-400 font-medium">Plotting mata pelajaran, guru pengampu, kelas, dan rentang jam</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddModal()"
                    class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-all duration-150 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                    aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.jadwal.store') }}" method="POST" data-loading-form class="flex-1 overflow-y-auto p-6 space-y-5 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Hari Pelajaran <span class="text-rose-500">*</span></label>
                        <select name="day_of_week" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50/70 focus:bg-white text-xs transition cursor-pointer font-medium text-slate-800">
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Rombel / Kelas <span class="text-rose-500">*</span></label>
                        <select name="classroom_id" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50/70 focus:bg-white text-xs transition cursor-pointer font-medium text-slate-800">
                            <option value="">Pilih Kelas</option>
                            @foreach($classrooms as $c)
                                <option value="{{ $c->id }}">Kelas {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1.5">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <select name="subject_id" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50/70 focus:bg-white text-xs transition cursor-pointer font-medium">
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1.5">Guru Pengampu <span class="text-rose-500">*</span></label>
                        <select name="teacher_id" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50/70 focus:bg-white text-xs transition cursor-pointer font-medium">
                            <option value="">Pilih Guru Pengampu</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Jam Mulai <span class="text-rose-500">*</span></label>
                        <input type="time" name="start_time" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50/70 focus:bg-white mono-font font-medium text-xs transition">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Jam Selesai <span class="text-rose-500">*</span></label>
                        <input type="time" name="end_time" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-maarif-600 focus:border-maarif-600 focus:outline-none bg-slate-50/70 focus:bg-white mono-font font-medium text-xs transition">
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-200/80 text-[11px] text-slate-500 flex items-start gap-2.5">
                    <i data-lucide="shield-check" class="w-4 h-4 text-maarif-700 shrink-0 mt-0.5"></i>
                    <span>Sistem secara otomatis memverifikasi ketersediaan ruang kelas dan jadwal guru untuk mencegah jadwal bentrok.</span>
                </div>

                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" onclick="closeAddModal()"
                        class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-semibold text-xs border border-slate-200 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        Batal
                    </button>
                    <button type="submit"
                        class="py-2.5 px-6 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs shadow-xs transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Jadwal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── MODAL EDIT JADWAL ────────────────────────────────────────── --}}
    <div id="modalEditJadwal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 hidden transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="modalEditTitle">
        <div class="w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-200/80 max-h-[92vh] flex flex-col overflow-hidden transform transition-all">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/60 shrink-0">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-700 border border-sky-200/70 flex items-center justify-center shadow-xs">
                        <i data-lucide="pencil" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 id="modalEditTitle" class="font-bold text-slate-900 heading-font text-base sm:text-lg">Perbarui Jadwal Pelajaran</h3>
                        <p class="text-xs text-slate-400 font-medium">Sesuaikan hari, waktu, guru pengampu, atau kelas</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal()"
                    class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-all duration-150 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                    aria-label="Tutup modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEditJadwal" method="POST" data-loading-form class="flex-1 overflow-y-auto p-6 space-y-5 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Hari <span class="text-rose-500">*</span></label>
                        <select id="editJadwalDay" name="day_of_week" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none bg-slate-50/70 focus:bg-white text-xs transition cursor-pointer font-medium text-slate-800">
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Kelas <span class="text-rose-500">*</span></label>
                        <select id="editJadwalClassroom" name="classroom_id" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none bg-slate-50/70 focus:bg-white text-xs transition cursor-pointer font-medium text-slate-800">
                            <option value="">Pilih Kelas</option>
                            @foreach($classrooms as $c)
                                <option value="{{ $c->id }}">Kelas {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1.5">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <select id="editJadwalSubject" name="subject_id" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none bg-slate-50/70 focus:bg-white text-xs transition cursor-pointer font-medium">
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1.5">Guru Pengampu <span class="text-rose-500">*</span></label>
                        <select id="editJadwalTeacher" name="teacher_id" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none bg-slate-50/70 focus:bg-white text-xs transition cursor-pointer font-medium">
                            <option value="">Pilih Guru Pengampu</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Jam Mulai <span class="text-rose-500">*</span></label>
                        <input type="time" id="editJadwalStartTime" name="start_time" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none bg-slate-50/70 focus:bg-white mono-font font-medium text-xs transition">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Jam Selesai <span class="text-rose-500">*</span></label>
                        <input type="time" id="editJadwalEndTime" name="end_time" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-600 focus:border-sky-600 focus:outline-none bg-slate-50/70 focus:bg-white mono-font font-medium text-xs transition">
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal()"
                        class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-semibold text-xs border border-slate-200 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                        Batal
                    </button>
                    <button type="submit"
                        class="py-2.5 px-6 rounded-xl bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white font-semibold text-xs shadow-xs transition-all duration-150 active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-maarif-600">
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
    function openAddModal() { 
        document.getElementById('modalAddJadwal').classList.remove('hidden'); 
    }
    function closeAddModal() { 
        document.getElementById('modalAddJadwal').classList.add('hidden'); 
    }

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

    // Close modal on backdrop click & ESC key
    ['modalAddJadwal', 'modalEditJadwal'].forEach(id => {
        const el = document.getElementById(id);
        el.addEventListener('click', function (e) {
            if (e.target === el) {
                el.classList.add('hidden');
            }
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddModal();
            closeEditModal();
        }
    });
</script>
@endpush
