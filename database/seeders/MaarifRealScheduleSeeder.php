<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder data asli MA Ma'arif Cilageni Kadungora — Tahun Pelajaran 2026/2027.
 *
 * Sumber: dokumen "Jadwal Pelajaran Intrakurikuler, Kokurikuler dan Ekstrakurikuler".
 * Blok non-tatap muka (Upacara, Istirahat, Literasi, Shalat Dzuhur + MBG,
 * Kepesantrenan, Ekstrakurikuler, Pramuka, KOKURIKULER tanpa guru) tidak dibuatkan
 * jadwal karena class_schedules mewajibkan guru.
 */
class MaarifRealScheduleSeeder extends Seeder
{
    private const ACADEMIC_YEAR = '2026/2027';

    /** @var array<int, array{name: string, email: string, jabatan: string, default_subject: ?string}> */
    private array $teachers = [
        1 => ['name' => 'Yadi Abdurrozak, M.M', 'email' => 'yadi.abdurrozak@maarif.sch.id', 'jabatan' => 'Kepala Madrasah', 'default_subject' => null],
        2 => ['name' => 'Deden Abdul Fatah Yasin, S.Pd.I', 'email' => 'deden.abdul.fatah.yasin@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'PAI-FKH'],
        3 => ['name' => 'Dra. Hj. Maesaroh', 'email' => 'maesaroh@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'MTK'],
        4 => ['name' => 'Peri Hapid Muslim, S.Ag, M.Si', 'email' => 'peri.hapid.muslim@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'PAI-QH'],
        5 => ['name' => 'Tata Tajul Mupahir, S.Pd.I', 'email' => 'tata.tajul.mupahir@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'ASW'],
        6 => ['name' => 'Euis Omasih, S.Pd', 'email' => 'euis.omasi@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'BSU'],
        7 => ['name' => 'Iroh Rohanah, S.Ag', 'email' => 'iroh.rohanah@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'BARAB'],
        8 => ['name' => 'Yuyun Nurhasanah, S.Pd.I', 'email' => 'yuyun.nurhasanah@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'SEJI'],
        9 => ['name' => 'Septi Susanti, S.Pd., Gr', 'email' => 'septi.susanti@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'KIM'],
        10 => ['name' => 'Puspa Nurendah Fitriani, S.Pd., Gr', 'email' => 'puspa.nurendah.fitriani@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'BING'],
        11 => ['name' => 'Yeti Rosmiati, S.Pd', 'email' => 'yeti.rosmiati@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'GEO'],
        12 => ['name' => 'Ela Islanda, M.Pd., M.Ce., Gr', 'email' => 'ela.islanda@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'FIS'],
        13 => ['name' => 'Syafei Ridwanna, S.Pd., Gr', 'email' => 'syafei.ridwanna@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'PJOK'],
        14 => ['name' => 'Sinta Sundara, S.Pd', 'email' => 'sinta.sundara@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'BINDO'],
        15 => ['name' => 'Irfan Fajar Ramadhan, S.Pd', 'email' => 'irfan.fajar.ramadhan@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'BINDO'],
        16 => ['name' => 'Widi Wahyuni, S.Pd., Gr', 'email' => 'widi.wahyuni@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'MTK'],
        17 => ['name' => 'Sobur Hermawan, S.Sos., Gr', 'email' => 'sobur.hermawan@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'SOS'],
        18 => ['name' => 'Agnia Rahmah, S.Pd', 'email' => 'agnia.rahmah@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'PKN'],
        19 => ['name' => 'Riki Hilmansah, S.Pd.I', 'email' => 'riki.hilmansah@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'BARAB'],
        20 => ['name' => 'Samsam Manikam, S.E., Gr', 'email' => 'samsam.manikam@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'EKO'],
        21 => ['name' => 'Wahyudin, S.Kom', 'email' => 'wahyudin@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'INF'],
        22 => ['name' => 'Epen Ependi, S.T', 'email' => 'epen.ependi@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'EKOP'],
        23 => ['name' => 'Rina Nur Anggraeni, S.Pd', 'email' => 'rina.nur.anggraeni@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'BIO'],
        24 => ['name' => 'Elsa Fauziah, S.Sos', 'email' => 'elsa.fauziah@maarif.sch.id', 'jabatan' => 'Guru BK', 'default_subject' => 'BK'],
        25 => ['name' => 'Imas Kartini, S.Pd.I', 'email' => 'imas.kartini@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'PAI-SKI'],
        26 => ['name' => 'Hadi Rouf, S.Pd.I', 'email' => 'hadi.rouf@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'PAI-AA'],
        27 => ['name' => 'Jihan Julyanti Yuhdi, S.Pd', 'email' => 'jihan.julyanti.yuhdi@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'SOS'],
        28 => ['name' => 'Wawan Setiawan, S.Pd.I', 'email' => 'wawan.setiawan@maarif.sch.id', 'jabatan' => 'Guru Mapel', 'default_subject' => 'KNU'],
    ];

    private const CLASS_COLUMNS = [
        'X-1', 'X-2', 'X-3',
        'XI SAINS', 'XI SOSHUM 1', 'XI SOSHUM 2',
        'XII SAINS 1', 'XII SAINS 2', 'XII SOSHUM 1', 'XII SOSHUM 2',
    ];

    /**
     * Grid jadwal per hari. Kunci luar = jam ke-, nilai = [waktu, sel per kolom kelas].
     * Sel berupa angka = nomor guru; null = blok non-tatap muka / tanpa guru.
     *
     * @var array<string, array<int, array{0: array{string, string}, 1: list<?string>}>>
     */
    private array $grid = [
        'Senin' => [
            2 => [['07:35:00', '08:10:00'], ['11', '20', '25', '10', '27', '4', '19', '15', '8', '14']],
            3 => [['08:10:00', '08:45:00'], ['11', '20', '25', '10', '27', '4', '19', '15', '8', '14']],
            4 => [['08:45:00', '09:20:00'], ['14', '11', '20', '10', '8', '26', '3', '15', '19', '4']],
            5 => [['09:20:00', '09:55:00'], ['14', '11', '20', '5', '8', '26', '10', '24', '19', '4']],
            6 => [['09:55:00', '10:30:00'], ['5', '14', '4', '26', '8', '27', '10', '25', '21', '11']],
            7 => [['11:00:00', '11:35:00'], ['24', '14', '4', '26', '5', '27', '10', '25', '21', '11']],
            8 => [['11:35:00', '12:10:00'], ['20', '25', '24', '3', '10', '27', '4', '8', '14', '11']],
            9 => [['12:10:00', '12:45:00'], ['20', '25', '26', '3', '10', '24', '4', '10', '14', '8']],
            11 => [['13:20:00', '13:55:00'], ['25', '23', '14', '3', '10', '11', '21', '10', '4', '26']],
            12 => [['13:55:00', '14:30:00'], ['25', '23', '14', '24', '3', '11', '21', '10', '4', '26']],
            13 => [['14:30:00', '15:05:00'], [null, null, null, '23', '3', '21', '26', '14', '11', '19']],
            14 => [['15:05:00', '15:40:00'], [null, null, null, '23', '3', '21', '26', '14', '11', '19']],
        ],
        'Selasa' => [
            1 => [['07:00:00', '07:35:00'], ['10', '7', '11', '4', '8', '22', '12', '2', '13', '18']],
            2 => [['07:35:00', '08:10:00'], ['10', '7', '11', '4', '8', '22', '12', '2', '13', '18']],
            3 => [['08:10:00', '08:45:00'], ['16', '10', '13', '23', '6', '22', '18', '4', '8', '2']],
            4 => [['08:45:00', '09:20:00'], ['16', '10', '13', '9', '22', '8', '18', '4', '6', '2']],
            5 => [['09:20:00', '09:55:00'], ['16', '6', '10', '9', '22', '8', '23', '19', '18', '13']],
            6 => [['09:55:00', '10:30:00'], ['4', '6', '10', '9', '22', '8', '23', '19', '18', '13']],
            7 => [['11:00:00', '11:35:00'], ['4', '16', '10', '12', '11', '3', '2', '23', '22', '6']],
            8 => [['11:35:00', '12:10:00'], ['6', '16', '18', '12', '11', '3', '2', '23', '22', '8']],
            9 => [['12:10:00', '12:45:00'], ['6', '16', '18', '12', '7', '3', '9', '23', '2', '8']],
            11 => [['13:20:00', '13:55:00'], ['18', '4', '23', '8', '7', '10', '6', '12', '2', '22']],
            12 => [['13:55:00', '14:30:00'], ['18', '4', '23', '7', '11', '10', '9', '12', '3', '22']],
            13 => [['14:30:00', '15:05:00'], [null, null, null, '7', '11', '10', '9', '12', '3', '22']],
        ],
        'Rabu' => [
            1 => [['07:00:00', '07:35:00'], ['12', '15', '4', '28', '14', '25', '23', '9', '5', '8']],
            2 => [['07:35:00', '08:10:00'], ['12', '15', '4', '16', '14', '25', '23', '9', '17', '8']],
            3 => [['08:10:00', '08:45:00'], ['5', '15', '16', '2', '14', '28', '23', '9', '17', '8']],
            4 => [['08:45:00', '09:20:00'], ['15', '5', '16', '2', '28', '14', '9', '21', '8', '25']],
            5 => [['09:20:00', '09:55:00'], ['15', '13', '16', '23', '2', '14', '9', '21', '8', '25']],
            6 => [['09:55:00', '10:30:00'], ['15', '13', '5', '23', '2', '14', '25', '6', '8', '10']],
            7 => [['11:00:00', '11:35:00'], ['4', '8', '15', '16', '21', '6', '25', '23', '17', '10']],
            8 => [['11:35:00', '12:10:00'], ['4', '17', '15', '16', '21', '2', '14', '23', '25', '10']],
            9 => [['12:10:00', '12:45:00'], ['10', '17', '15', '6', '8', '2', '14', '18', '25', '5']],
            11 => [['13:20:00', '13:55:00'], ['23', '7', '6', '14', '11', '8', '15', '18', '10', '17']],
            12 => [['13:55:00', '14:30:00'], ['23', '7', '6', '14', '25', '11', '15', '12', '10', '17']],
            13 => [['14:30:00', '15:05:00'], [null, null, null, '14', '25', '11', '15', '12', '10', '17']],
        ],
        'Kamis' => [
            1 => [['07:00:00', '07:35:00'], ['18', '4', '12', '13', '22', '7', '24', '26', '17', '21']],
            2 => [['07:35:00', '08:05:00'], ['18', '4', '12', '13', '22', '7', '16', '26', '17', '21']],
            3 => [['08:05:00', '08:35:00'], ['2', '10', '7', '21', '13', '22', '16', '9', '26', '24']],
            4 => [['08:35:00', '09:05:00'], ['2', '24', '7', '21', '13', '22', '12', '9', '26', '3']],
            5 => [['09:05:00', '09:35:00'], ['17', '2', '18', '9', '26', '13', '12', '5', '22', '11']],
            6 => [['09:35:00', '10:05:00'], ['17', '2', '18', '9', '26', '13', '12', '3', '22', '11']],
            7 => [['10:35:00', '11:05:00'], ['7', '18', '2', '16', '4', '11', '5', '3', '22', '17']],
            8 => [['11:05:00', '11:35:00'], ['7', '18', '2', '16', '4', '5', '8', '3', '11', '17']],
            9 => [['11:35:00', '12:05:00'], ['21', '9', '17', '12', '18', '8', '3', '16', '11', '22']],
            10 => [['12:05:00', '12:35:00'], ['21', '9', '17', '12', '18', '8', '3', '16', '11', '22']],
        ],
        'Jumat' => [
            1 => [['07:00:00', '07:35:00'], ['7', '12', '21', '14', '27', '18', '13', '16', '24', '15']],
            2 => [['07:35:00', '08:10:00'], ['7', '12', '21', '14', '27', '18', '13', '16', '2', '15']],
            3 => [['08:10:00', '08:45:00'], ['13', '18', '9', '25', '14', '27', '2', '16', '3', '15']],
            4 => [['08:45:00', '09:20:00'], ['13', '18', '9', '25', '14', '27', '16', '2', '15', '3']],
            5 => [['09:50:00', '10:25:00'], ['9', '21', '7', '18', '27', '14', '16', '13', '15', '3']],
            6 => [['10:25:00', '11:00:00'], ['9', '21', '7', '18', '24', '14', '16', '13', '15', '2']],
        ],
    ];

    public function run(): void
    {
        // 1. Rombel asli sesuai dokumen jadwal
        $classrooms = [];
        foreach (self::CLASS_COLUMNS as $name) {
            $classrooms[$name] = Classroom::updateOrCreate(
                ['name' => $name],
                [
                    'grade_level' => (string) (str_starts_with($name, 'XII') ? 12 : (str_starts_with($name, 'XI') ? 11 : 10)),
                    'academic_year' => self::ACADEMIC_YEAR,
                ]
            );
        }

        // 2. Mata pelajaran (kode lama dipakai ulang agar tidak duplikat)
        $subjects = [];
        $subjectsData = [
            'PAI-QH' => "Al-Qur'an Hadits",
            'PAI-AA' => 'Akidah Akhlak',
            'PAI-FKH' => 'Fikih',
            'PAI-SKI' => 'Sejarah Kebudayaan Islam (SKI)',
            'BARAB' => 'Bahasa Arab',
            'MTK' => 'Matematika',
            'BINDO' => 'Bahasa Indonesia',
            'BING' => 'Bahasa Inggris',
            'PJOK' => 'Pendidikan Jasmani & Olahraga',
            'KNU' => 'Ke-NU-an',
            'ASW' => 'Aswaja',
            'BSU' => 'Bahasa Sunda',
            'SEJI' => 'Sejarah',
            'SEJTL' => 'Sejarah Tingkat Lanjut',
            'KIM' => 'Kimia',
            'KIMP' => 'Kimia Peminatan',
            'GEO' => 'Geografi',
            'GEOP' => 'Geografi Peminatan',
            'FIS' => 'Fisika',
            'FISP' => 'Fisika Peminatan',
            'SENB' => 'Seni Budaya',
            'MTKL' => 'Matematika Tingkat Lanjut',
            'SOS' => 'Sosiologi',
            'SOSP' => 'Sosiologi Peminatan',
            'PKN' => 'Pendidikan Pancasila (PKn)',
            'EKO' => 'Ekonomi',
            'INF' => 'Informatika',
            'EKOP' => 'Ekonomi Peminatan',
            'BIO' => 'Biologi',
            'BIOP' => 'Biologi Peminatan',
            'BK' => 'Bimbingan Konseling (BK)',
            'KOK' => 'Kokurikuler',
        ];
        foreach ($subjectsData as $code => $name) {
            $subjects[$code] = Subject::updateOrCreate(['code' => $code], ['name' => $name]);
        }

        // 3. Guru: akun login + profil pegawai
        $teacherProfiles = [];
        foreach ($this->teachers as $no => $t) {
            $user = User::updateOrCreate(
                ['email' => $t['email']],
                [
                    'name' => $t['name'],
                    'password' => Hash::make('akunguru@maarif'),
                    'role' => 'guru',
                    'is_active' => true,
                ]
            );
            $teacherProfiles[$no] = Teacher::updateOrCreate(
                ['user_id' => $user->id],
                ['jabatan' => $t['jabatan']]
            );
        }

        // 4. Jadwal mingguan dari grid asli
        $created = 0;
        foreach ($this->grid as $day => $periods) {
            foreach ($periods as [$times, $cells]) {
                foreach ($cells as $colIndex => $cell) {
                    if ($cell === null || ! ctype_digit($cell)) {
                        continue;
                    }

                    $teacherNo = (int) $cell;
                    if (! isset($teacherProfiles[$teacherNo])) {
                        continue;
                    }

                    $className = self::CLASS_COLUMNS[$colIndex];
                    $subjectCode = $this->resolveSubjectCode($teacherNo, $className);

                    ClassSchedule::updateOrCreate(
                        [
                            'classroom_id' => $classrooms[$className]->id,
                            'subject_id' => $subjects[$subjectCode]->id,
                            'teacher_id' => $teacherProfiles[$teacherNo]->id,
                            'day_of_week' => $day,
                            'start_time' => $times[0],
                        ],
                        [
                            'end_time' => $times[1],
                        ]
                    );
                    $created++;
                }
            }
        }

        $this->command?->info("Jadwal asli MA Ma'arif Cilageni: {$created} baris jadwal dibuat/diperbarui.");
    }

    /**
     * Menentukan mata pelajaran untuk sel grid berdasarkan nomor guru dan rombel.
     * Guru dengan mapel peminatan (A/B/C) dipetakan mengikuti jalur rombel
     * (SAINS/SOSHUM) dan tingkat; selain itu memakai mapel utama (kode A).
     */
    private function resolveSubjectCode(int $teacherNo, string $className): string
    {
        $isSains = str_contains($className, 'SAINS');
        $isSoshum = str_contains($className, 'SOSHUM');
        $grade = str_starts_with($className, 'XII') ? 12 : (str_starts_with($className, 'XI') ? 11 : 10);

        $code = match ($teacherNo) {
            8 => $isSoshum ? 'SEJTL' : 'SEJI',
            9 => $grade > 10 && $isSains ? 'KIMP' : 'KIM',
            11 => $grade > 10 && $isSoshum ? 'GEOP' : 'GEO',
            12 => $grade > 10 && $isSains ? 'FISP' : 'FIS',
            14 => $grade === 11 ? 'BINDO' : 'SENB',
            15 => $grade > 10 ? 'BINDO' : 'KOK',
            16 => $grade > 10 && $isSains ? 'MTKL' : 'MTK',
            17 => $grade === 12 && $isSoshum ? 'SOSP' : 'SOS',
            19 => $grade === 12 ? 'BARAB' : 'KOK',
            23 => $grade > 10 && $isSains ? 'BIOP' : 'BIO',
            26 => $grade === 10 ? 'KNU' : 'PAI-AA',
            default => $this->teachers[$teacherNo]['default_subject'],
        };

        if ($code === null) {
            throw new \InvalidArgumentException("Guru nomor {$teacherNo} tidak memiliki mata pelajaran.");
        }

        return $code;
    }
}
