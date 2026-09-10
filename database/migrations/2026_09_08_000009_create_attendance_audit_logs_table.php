<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lesson_attendance_id')->constrained('lesson_attendances')->cascadeOnDelete();
            $table->foreignUuid('changed_by')->constrained('users')->cascadeOnDelete();
            $table->string('old_status');
            $table->string('new_status');
            $table->text('reason');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_audit_logs');
    }
};
