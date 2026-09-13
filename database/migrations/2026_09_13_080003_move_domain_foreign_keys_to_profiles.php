<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pindahkan foreign key domain dari users.id ke tabel profil
     * (teachers.id / students.id). Nilai diremap via teachers.user_id /
     * students.user_id yang sudah di-backfill. FK aktor sistem
     * (confirmed_by, recorded_by, changed_by, daily_attendances.user_id)
     * tetap menunjuk users.id.
     *
     * Setiap langkah idempoten agar aman dijalankan ulang.
     */
    public function up(): void
    {
        $this->repointTeacherTable('class_schedules', 'idx_schedules_teacher_day', ['teacher_id', 'day_of_week']);
        $this->repointTeacherTable('class_sessions', 'idx_sessions_teacher_id', ['teacher_id']);
        $this->repointTeacherTable(
            'teacher_session_attendances',
            null,
            ['schedule_id', 'teacher_id', 'attendance_date'],
            true
        );

        // lesson_attendances.student_id -> students.id
        $table = 'lesson_attendances';

        if (Schema::hasColumn($table, 'student_id') && ! Schema::hasColumn($table, 'student_profile_id')) {
            Schema::table($table, function (Blueprint $t) {
                $t->uuid('student_profile_id')->nullable()->after('student_id');
            });
        }

        if (Schema::hasColumn($table, 'student_profile_id')) {
            DB::statement('UPDATE lesson_attendances la JOIN students s ON s.user_id = la.student_id SET la.student_profile_id = s.id');
        }

        $this->dropForeignKeyToUsers($table, 'student_id');
        $this->dropForeignKeyIfExists($table, $table.'_schedule_id_foreign');
        $this->dropIndexIfExists($table, 'idx_lesson_att_student_id');
        $this->dropIndexIfExists($table, $table.'_student_id_index');
        $this->dropIndexIfExists($table, $table.'_student_id_foreign');
        $this->dropIndexIfExists($table, 'lesson_attendances_schedule_id_student_id_attendance_date_unique');

        if (Schema::hasColumn($table, 'student_profile_id')) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('student_id');
            });

            Schema::table($table, function (Blueprint $t) {
                $t->renameColumn('student_profile_id', 'student_id');
            });
        }

        DB::statement('UPDATE lesson_attendances SET student_id = LOWER(student_id) WHERE student_id IS NOT NULL');
        DB::statement('ALTER TABLE lesson_attendances MODIFY student_id UUID NOT NULL');

        $this->addForeignKeyIfMissing($table, 'student_id', 'students');
        $this->addIndexIfMissing($table, 'student_id', 'idx_lesson_att_student_id');
        $this->addIndexIfMissing(
            $table,
            ['schedule_id', 'student_id', 'attendance_date'],
            'lesson_attendances_schedule_id_student_id_attendance_date_unique',
            true
        );
        $this->addForeignKeyIfMissing($table, 'schedule_id', 'class_schedules');
    }

    public function down(): void
    {
        // Tidak dibatalkan: pemetaan nilai lama (users.id) sudah tidak tersedia
        // setelah kolom diganti; pulihkan dari backup database bila perlu.
    }

    private function repointTeacherTable(string $table, ?string $indexName, ?array $indexColumns, bool $isUnique = false): void
    {
        if (Schema::hasColumn($table, 'teacher_id') && ! Schema::hasColumn($table, 'teacher_profile_id')) {
            Schema::table($table, function (Blueprint $t) {
                $t->uuid('teacher_profile_id')->nullable()->after('teacher_id');
            });
        }

        if (Schema::hasColumn($table, 'teacher_profile_id')) {
            DB::statement("UPDATE `{$table}` x JOIN teachers t ON t.user_id = x.teacher_id SET x.teacher_profile_id = t.id");
        }

        $this->dropForeignKeyToUsers($table, 'teacher_id');

        if ($indexName !== null) {
            $this->dropIndexIfExists($table, $indexName);
        }

        // Index implisit yang dibuat MySQL/MariaDB untuk FK lama
        // (bisa bernama *_teacher_id_index atau mengikuti nama constraint FK)
        $this->dropIndexIfExists($table, $table.'_teacher_id_index');
        $this->dropIndexIfExists($table, $table.'_teacher_id_foreign');

        if ($isUnique) {
            // FK schedule_id memakai unique composite ini sebagai index — drop dulu
            $this->dropForeignKeyIfExists($table, $table.'_schedule_id_foreign');
            $this->dropIndexIfExists($table, 'tsa_schedule_teacher_date_unique');
            $this->dropIndexIfExists($table, $table.'_schedule_id_teacher_id_attendance_date_unique');
            $this->dropIndexIfExists($table, $table.'_teacher_id_attendance_date_unique');
        }

        if (Schema::hasColumn($table, 'teacher_profile_id')) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('teacher_id');
            });

            Schema::table($table, function (Blueprint $t) {
                $t->renameColumn('teacher_profile_id', 'teacher_id');
            });
        }

        // Normalisasi ke lowercase sebelum konversi ke tipe UUID native MariaDB
        DB::statement("UPDATE `{$table}` SET teacher_id = LOWER(teacher_id) WHERE teacher_id IS NOT NULL");
        DB::statement("ALTER TABLE `{$table}` MODIFY teacher_id UUID NOT NULL");

        $this->addForeignKeyIfMissing($table, 'teacher_id', 'teachers');

        if ($indexName !== null && $indexColumns !== null) {
            Schema::table($table, function (Blueprint $t) use ($indexColumns, $indexName, $isUnique) {
                if ($isUnique) {
                    $t->unique($indexColumns, $indexName);
                } else {
                    $t->index($indexColumns, $indexName);
                }
            });
        }

        if ($isUnique) {
            $this->addForeignKeyIfMissing($table, 'schedule_id', 'class_schedules');
        }
    }

    private function dropForeignKeyIfExists(string $table, string $foreignKey): void
    {
        $exists = DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $foreignKey]
        );

        if ((int) $exists->c > 0) {
            Schema::table($table, function (Blueprint $t) use ($foreignKey) {
                $t->dropForeign($foreignKey);
            });
        }
    }

    private function dropForeignKeyToUsers(string $table, string $column): void
    {
        $constraint = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME = ?',
            [$table, $column, 'users']
        );

        if ($constraint !== null) {
            Schema::table($table, function (Blueprint $t) use ($constraint) {
                $t->dropForeign($constraint->CONSTRAINT_NAME);
            });
        }
    }

    private function addForeignKeyIfMissing(string $table, string $column, string $referenced): void
    {
        $exists = DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME = ?',
            [$table, $column, $referenced]
        );

        if ((int) $exists->c === 0) {
            Schema::table($table, function (Blueprint $t) use ($column, $referenced) {
                $t->foreign($column)->references('id')->on($referenced)->cascadeOnDelete();
            });
        }
    }

    private function addIndexIfMissing(string $table, array|string $columns, string $name, bool $unique = false): void
    {
        $exists = DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
            [$table, $name]
        );

        if ((int) $exists->c === 0) {
            Schema::table($table, function (Blueprint $t) use ($columns, $name, $unique) {
                if ($unique) {
                    $t->unique((array) $columns, $name);
                } else {
                    $t->index((array) $columns, $name);
                }
            });
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        $exists = DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
            [$table, $index]
        );

        if ((int) $exists->c > 0) {
            Schema::table($table, function (Blueprint $t) use ($index) {
                $t->dropIndex($index);
            });
        }
    }
};
