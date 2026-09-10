<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->string('check_in_status')->nullable(); // HADIR, TERLAMBAT, ALPA
            $table->string('check_out_status')->nullable(); // TEPAT_WAKTU, CEPAT_PULANG
            $table->double('check_in_latitude', 10, 7)->nullable();
            $table->double('check_in_longitude', 10, 7)->nullable();
            $table->double('check_out_latitude', 10, 7)->nullable();
            $table->double('check_out_longitude', 10, 7)->nullable();
            $table->float('check_in_distance_meters')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_attendances');
    }
};
