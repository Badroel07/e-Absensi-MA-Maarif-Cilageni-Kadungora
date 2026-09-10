<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('schedule_id')->constrained('class_schedules')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('pin_code', 4);
            $table->integer('duration_minutes')->default(3);
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->string('status')->default('ACTIVE'); // ACTIVE, EXPIRED, LOCKED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
