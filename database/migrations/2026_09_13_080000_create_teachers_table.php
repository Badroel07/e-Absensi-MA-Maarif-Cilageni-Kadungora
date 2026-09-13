<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nip')->unique()->nullable(); // NIP / NUPTK / No. Pegawai
            $table->string('jabatan')->nullable();
            $table->string('status_kepegawaian')->default('AKTIF'); // AKTIF, PNS, P3K, PTY, Pensiun
            $table->string('phone_number')->nullable();
            $table->date('birth_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
