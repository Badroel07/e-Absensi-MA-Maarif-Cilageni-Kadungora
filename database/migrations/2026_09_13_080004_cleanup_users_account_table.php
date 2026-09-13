<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * users menjadi tabel akun murni: nomor induk (NISN/NIP), kelas,
     * tanggal lahir, dan telepon kini hidup di profil domain.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_classroom_id_foreign');
            $table->dropIndex('idx_users_classroom_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['identity_number', 'classroom_id', 'birth_date', 'phone_number']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('identity_number')->unique()->nullable();
            $table->foreignUuid('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->date('birth_date')->nullable();
            $table->string('phone_number')->nullable();
            $table->index('classroom_id', 'idx_users_classroom_id');
        });
    }
};
