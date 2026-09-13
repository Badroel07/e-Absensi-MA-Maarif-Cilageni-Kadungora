<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill: setiap user lama mendapat profil domain dari identity_number lama.
     * - role siswa  -> students (nisn)
     * - role guru/admin -> teachers (nip)
     */
    public function up(): void
    {
        DB::statement('INSERT INTO students (id, user_id, nisn, classroom_id, phone_number, birth_date, status, created_at, updated_at)
            SELECT UUID(), id, identity_number, classroom_id, phone_number, birth_date, \'AKTIF\', NOW(), NOW()
            FROM users WHERE role = \'siswa\'');

        DB::statement('INSERT INTO teachers (id, user_id, nip, phone_number, birth_date, status_kepegawaian, created_at, updated_at)
            SELECT UUID(), id, identity_number, phone_number, birth_date, \'AKTIF\', NOW(), NOW()
            FROM users WHERE role IN (\'guru\', \'admin\')');
    }

    public function down(): void
    {
        // Nilai asli masih tersimpan di profil; backfill tidak dibatalkan
    }
};
