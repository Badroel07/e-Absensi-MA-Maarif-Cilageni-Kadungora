<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // users: filter role + is_active + classroom (AdminDashboard, UserManagement, TeacherAttendance)
        if (! $this->indexExists('users', 'idx_users_role_is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['role', 'is_active'], 'idx_users_role_is_active');
            });
        }
        if (! $this->indexExists('users', 'idx_users_classroom_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('classroom_id', 'idx_users_classroom_id');
            });
        }
        if (! $this->indexExists('users', 'idx_users_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('name', 'idx_users_name');
            });
        }

        // lesson_attendances: dipakai di whereDate attendance_date + status + student_id (Student history, Dashboard, Report)
        if (! $this->indexExists('lesson_attendances', 'idx_lesson_att_date_status')) {
            Schema::table('lesson_attendances', function (Blueprint $table) {
                $table->index(['attendance_date', 'status'], 'idx_lesson_att_date_status');
            });
        }
        if (! $this->indexExists('lesson_attendances', 'idx_lesson_att_student_id')) {
            Schema::table('lesson_attendances', function (Blueprint $table) {
                $table->index('student_id', 'idx_lesson_att_student_id');
            });
        }
        if (! $this->indexExists('lesson_attendances', 'idx_lesson_att_session_id')) {
            Schema::table('lesson_attendances', function (Blueprint $table) {
                $table->index('session_id', 'idx_lesson_att_session_id');
            });
        }

        // daily_attendances: whereDate attendance_date + check_in_status, user_id lookup
        if (! $this->indexExists('daily_attendances', 'idx_daily_att_date')) {
            Schema::table('daily_attendances', function (Blueprint $table) {
                $table->index('attendance_date', 'idx_daily_att_date');
            });
        }
        if (! $this->indexExists('daily_attendances', 'idx_daily_att_date_status')) {
            Schema::table('daily_attendances', function (Blueprint $table) {
                $table->index(['attendance_date', 'check_in_status'], 'idx_daily_att_date_status');
            });
        }

        // class_schedules: where teacher_id + day_of_week, classroom_id + day_of_week, dipakai di overlap check & jadwal
        if (! $this->indexExists('class_schedules', 'idx_schedules_teacher_day')) {
            Schema::table('class_schedules', function (Blueprint $table) {
                $table->index(['teacher_id', 'day_of_week'], 'idx_schedules_teacher_day');
            });
        }
        if (! $this->indexExists('class_schedules', 'idx_schedules_classroom_day')) {
            Schema::table('class_schedules', function (Blueprint $table) {
                $table->index(['classroom_id', 'day_of_week'], 'idx_schedules_classroom_day');
            });
        }

        // class_sessions: where schedule_id + created_at, teacher_id, status + created_at
        if (! $this->indexExists('class_sessions', 'idx_sessions_schedule_created')) {
            Schema::table('class_sessions', function (Blueprint $table) {
                $table->index(['schedule_id', 'created_at'], 'idx_sessions_schedule_created');
            });
        }
        if (! $this->indexExists('class_sessions', 'idx_sessions_teacher_id')) {
            Schema::table('class_sessions', function (Blueprint $table) {
                $table->index('teacher_id', 'idx_sessions_teacher_id');
            });
        }
        if (! $this->indexExists('class_sessions', 'idx_sessions_status')) {
            Schema::table('class_sessions', function (Blueprint $table) {
                $table->index('status', 'idx_sessions_status');
            });
        }
    }

    public function down(): void
    {
        foreach ([
            ['users', 'idx_users_role_is_active'],
            ['users', 'idx_users_classroom_id'],
            ['users', 'idx_users_name'],
            ['lesson_attendances', 'idx_lesson_att_date_status'],
            ['lesson_attendances', 'idx_lesson_att_student_id'],
            ['lesson_attendances', 'idx_lesson_att_session_id'],
            ['daily_attendances', 'idx_daily_att_date'],
            ['daily_attendances', 'idx_daily_att_date_status'],
            ['class_schedules', 'idx_schedules_teacher_day'],
            ['class_schedules', 'idx_schedules_classroom_day'],
            ['class_sessions', 'idx_sessions_schedule_created'],
            ['class_sessions', 'idx_sessions_teacher_id'],
            ['class_sessions', 'idx_sessions_status'],
        ] as [$table, $index]) {
            if ($this->indexExists($table, $index)) {
                Schema::table($table, function (Blueprint $table) use ($index) {
                    $table->dropIndex($index);
                });
            }
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=? AND name=?", [$table, $indexName]);

            return count($indexes) > 0;
        }

        return count(DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        )) > 0;
    }
};
