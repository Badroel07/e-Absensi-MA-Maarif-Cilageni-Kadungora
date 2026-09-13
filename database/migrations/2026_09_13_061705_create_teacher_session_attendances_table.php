<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_session_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('schedule_id')->constrained('class_schedules')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('class_session_id')->nullable()->constrained('class_sessions')->nullOnDelete();
            $table->date('attendance_date');
            $table->string('status')->default('HADIR'); // HADIR, TERLAMBAT, IZIN, SAKIT, DINAS_LUAR, ALPA
            $table->timestamp('attended_at')->nullable();
            $table->double('latitude', 10, 7)->nullable();
            $table->double('longitude', 10, 7)->nullable();
            $table->float('distance_meters')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['schedule_id', 'teacher_id', 'attendance_date'], 'tsa_schedule_teacher_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_session_attendances');
    }
};
