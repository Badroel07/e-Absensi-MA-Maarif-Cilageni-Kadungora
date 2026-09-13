<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nisn')->unique(); // Nomor Induk Siswa Nasional
            $table->foreignUuid('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->string('wali_name')->nullable();
            $table->string('wali_phone', 20)->nullable();
            $table->string('phone_number')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('status')->default('AKTIF'); // AKTIF, LULUS, KELUAR
            $table->timestamps();

            $table->index('classroom_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
