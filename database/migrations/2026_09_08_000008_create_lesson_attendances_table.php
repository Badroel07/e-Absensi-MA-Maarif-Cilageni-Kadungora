<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_id')->nullable()->constrained('class_sessions')->nullOnDelete();
            $table->foreignUuid('schedule_id')->constrained('class_schedules')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('status')->default('ALPA'); // HADIR, IZIN, SAKIT, ALPA
            $table->text('notes')->nullable();
            $table->foreignUuid('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->double('latitude', 10, 7)->nullable();
            $table->double('longitude', 10, 7)->nullable();
            $table->float('distance_meters')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'student_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_attendances');
    }
};
