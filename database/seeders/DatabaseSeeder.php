<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\LessonAttendance;
use App\Models\SchoolLocation;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\TeacherAttendanceService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(MaarifRealScheduleSeeder::class);

        // 1. Lokasi Madrasah
        SchoolLocation::updateOrCreate(
            ['name' => "MA Ma'arif Cilageni Kadungora"],
            [
                'latitude' => -7.1147000,
                'longitude' => 107.8845000,
                'radius_meters' => 75,
                'is_active' => true,
            ]
        );

        // 2. Master Kelas (Rombel)
        $classes = [
            ['name' => '10A', 'grade_level' => '10', 'academic_year' => '2026/2027'],
            ['name' => '10B', 'grade_level' => '10', 'academic_year' => '2026/2027'],
            ['name' => '11A', 'grade_level' => '11', 'academic_year' => '2026/2027'],
            ['name' => '11B', 'grade_level' => '11', 'academic_year' => '2026/2027'],
            ['name' => '12A', 'grade_level' => '12', 'academic_year' => '2026/2027'],
            ['name' => '12B', 'grade_level' => '12', 'academic_year' => '2026/2027'],
        ];

        $classModels = [];
        foreach ($classes as $c) {
            $classModels[$c['name']] = Classroom::updateOrCreate(
                ['name' => $c['name']],
                $c
            );
        }

        // 3. Master Mata Pelajaran
        $subjectsData = [
            ['code' => 'PAI-QH', 'name' => "Al-Qur'an Hadits"],
            ['code' => 'PAI-AA', 'name' => 'Akidah Akhlak'],
            ['code' => 'PAI-FKH', 'name' => 'Fikih'],
            ['code' => 'PAI-SKI', 'name' => 'Sejarah Kebudayaan Islam (SKI)'],
            ['code' => 'BARAB', 'name' => 'Bahasa Arab'],
            ['code' => 'MTK', 'name' => 'Matematika'],
            ['code' => 'BINDO', 'name' => 'Bahasa Indonesia'],
            ['code' => 'BING', 'name' => 'Bahasa Inggris'],
            ['code' => 'IPA', 'name' => 'Ilmu Pengetahuan Alam (IPA)'],
            ['code' => 'IPS', 'name' => 'Ilmu Pengetahuan Sosial (IPS)'],
            ['code' => 'PJOK', 'name' => 'Pendidikan Jasmani & Olahraga'],
        ];

        $subjectModels = [];
        foreach ($subjectsData as $s) {
            $subjectModels[$s['code']] = Subject::updateOrCreate(
                ['code' => $s['code']],
                $s
            );
        }

        // 4. Pengguna: Admin TU & Guru Piket (akun login + profil pegawai)
        $admin = User::updateOrCreate(
            ['email' => 'admin@maarif.sch.id'],
            [
                'name' => 'Ahmad Subandi, S.AP (Staf TU)',
                'password' => Hash::make('p@55w0rd'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
        $this->upsertTeacherProfile($admin, ['nip' => '198501012010011001', 'jabatan' => 'Staf TU', 'birth_date' => '1985-01-01', 'phone_number' => '081234567890']);

        $guru1 = User::updateOrCreate(
            ['email' => 'ahmad.dahlan@maarif.sch.id'],
            [
                'name' => 'Ust. H. Ahmad Dahlan, S.Pd.I',
                'password' => Hash::make('akunguru@maarif'),
                'role' => 'guru',
                'is_active' => true,
            ]
        );
        $teacher1 = $this->upsertTeacherProfile($guru1, ['nip' => '197505122000031002', 'jabatan' => 'Guru Mapel', 'birth_date' => '1975-05-12', 'phone_number' => '081223344551']);

        $guru2 = User::updateOrCreate(
            ['email' => 'siti.maryam@maarif.sch.id'],
            [
                'name' => 'Usth. Siti Maryam, S.Pd',
                'password' => Hash::make('akunguru@maarif'),
                'role' => 'guru',
                'is_active' => true,
            ]
        );
        $teacher2 = $this->upsertTeacherProfile($guru2, ['nip' => '198208152005012003', 'jabatan' => 'Guru Mapel', 'birth_date' => '1982-08-15', 'phone_number' => '081223344552']);

        $guru3 = User::updateOrCreate(
            ['email' => 'ridwan@maarif.sch.id'],
            [
                'name' => 'Ust. M. Ridwan, M.Pd',
                'password' => Hash::make('akunguru@maarif'),
                'role' => 'guru',
                'is_active' => true,
            ]
        );
        $teacher3 = $this->upsertTeacherProfile($guru3, ['nip' => '198810202012011004', 'jabatan' => 'Guru Mapel', 'birth_date' => '1988-10-20', 'phone_number' => '081223344553']);

        // 6. Pengguna: Siswa Kelas 10A & 11A (akun login + profil siswa)
        $students10A = [
            ['nisn' => '0091234501', 'name' => 'Muhammad Al-Fatih', 'birth_date' => '2011-05-10', 'phone' => '085100000001'],
            ['nisn' => '0091234502', 'name' => 'Aisyah Nur Rohmah', 'birth_date' => '2011-03-15', 'phone' => '085100000002'],
            ['nisn' => '0091234503', 'name' => 'Bilal Habasyi', 'birth_date' => '2011-08-22', 'phone' => '085100000003'],
            ['nisn' => '0091234504', 'name' => 'Fatimah Az-Zahra', 'birth_date' => '2011-11-05', 'phone' => '085100000004'],
            ['nisn' => '0091234505', 'name' => 'Hamzah Asadullah', 'birth_date' => '2011-01-30', 'phone' => '085100000005'],
        ];

        $students11A = [
            ['nisn' => '0081234501', 'name' => 'Umar Abdul Aziz', 'birth_date' => '2010-04-12', 'phone' => '085200000001'],
            ['nisn' => '0081234502', 'name' => 'Zahra Khadijah', 'birth_date' => '2010-07-18', 'phone' => '085200000002'],
            ['nisn' => '0081234503', 'name' => 'Ali bin Abi Thalib', 'birth_date' => '2010-09-25', 'phone' => '085200000003'],
            ['nisn' => '0081234504', 'name' => 'Mariam Jameelah', 'birth_date' => '2010-12-01', 'phone' => '085200000004'],
            ['nisn' => '0081234505', 'name' => 'Salman Al-Farisi', 'birth_date' => '2010-02-14', 'phone' => '085200000005'],
        ];

        foreach (['10A' => $students10A, '11A' => $students11A] as $className => $students) {
            foreach ($students as $st) {
                $user = User::updateOrCreate(
                    ['email' => $st['nisn'].'@siswa.maarif.sch.id'],
                    [
                        'name' => $st['name'],
                        'password' => Hash::make('akunsiswa@maarif'),
                        'role' => 'siswa',
                        'is_active' => true,
                    ]
                );

                Student::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nisn' => $st['nisn'],
                        'classroom_id' => $classModels[$className]->id,
                        'phone_number' => $st['phone'],
                        'birth_date' => $st['birth_date'],
                        'status' => 'AKTIF',
                    ]
                );
            }
        }

        // 7. Master Jadwal Pelajaran (Mingguan) — teacher_id menunjuk profil guru
        $todayDay = TeacherAttendanceService::getIndonesianDayName(Carbon::today());
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        $schedulesSeed = [
            ['classroom_id' => $classModels['10A']->id, 'subject_id' => $subjectModels['PAI-FKH']->id, 'teacher_id' => $teacher1->id, 'day_of_week' => $todayDay, 'start_time' => '07:30:00', 'end_time' => '09:00:00'],
            ['classroom_id' => $classModels['10A']->id, 'subject_id' => $subjectModels['MTK']->id, 'teacher_id' => $teacher2->id, 'day_of_week' => $todayDay, 'start_time' => '09:15:00', 'end_time' => '10:45:00'],
            ['classroom_id' => $classModels['11A']->id, 'subject_id' => $subjectModels['BINDO']->id, 'teacher_id' => $teacher3->id, 'day_of_week' => $todayDay, 'start_time' => '07:30:00', 'end_time' => '09:00:00'],
            ['classroom_id' => $classModels['11A']->id, 'subject_id' => $subjectModels['PAI-FKH']->id, 'teacher_id' => $teacher1->id, 'day_of_week' => $todayDay, 'start_time' => '10:00:00', 'end_time' => '11:30:00'],
        ];

        foreach ($days as $day) {
            if ($day === $todayDay) {
                continue;
            }
            $schedulesSeed[] = ['classroom_id' => $classModels['10A']->id, 'subject_id' => $subjectModels['PAI-QH']->id, 'teacher_id' => $teacher1->id, 'day_of_week' => $day, 'start_time' => '07:30:00', 'end_time' => '09:00:00'];
            $schedulesSeed[] = ['classroom_id' => $classModels['10A']->id, 'subject_id' => $subjectModels['IPA']->id, 'teacher_id' => $teacher2->id, 'day_of_week' => $day, 'start_time' => '09:15:00', 'end_time' => '10:45:00'];
            $schedulesSeed[] = ['classroom_id' => $classModels['11A']->id, 'subject_id' => $subjectModels['BARAB']->id, 'teacher_id' => $teacher3->id, 'day_of_week' => $day, 'start_time' => '07:30:00', 'end_time' => '09:00:00'];
        }

        foreach ($schedulesSeed as $sch) {
            ClassSchedule::updateOrCreate(
                [
                    'classroom_id' => $sch['classroom_id'],
                    'subject_id' => $sch['subject_id'],
                    'day_of_week' => $sch['day_of_week'],
                    'start_time' => $sch['start_time'],
                ],
                $sch
            );
        }

        // 8. Sample historical data for reports
        $yesterday = Carbon::yesterday();
        $sampleStudents = Student::all();
        $sampleSchedule = ClassSchedule::first();

        if ($sampleSchedule) {
            foreach ($sampleStudents as $idx => $st) {
                $status = ($idx % 5 === 0) ? 'IZIN' : (($idx % 5 === 1) ? 'SAKIT' : 'HADIR');
                LessonAttendance::updateOrCreate(
                    [
                        'schedule_id' => $sampleSchedule->id,
                        'student_id' => $st->id,
                        'attendance_date' => $yesterday,
                    ],
                    [
                        'status' => $status,
                        'notes' => $status !== 'HADIR' ? 'Keterangan izin dari orang tua' : null,
                        'verified_at' => $yesterday->copy()->setTime(7, 45),
                    ]
                );
            }
        }
    }

    private function upsertTeacherProfile(User $user, array $data): Teacher
    {
        return Teacher::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );
    }
}
