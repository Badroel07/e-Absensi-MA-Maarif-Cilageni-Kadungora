<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // "Terlambat" bukan kategori absen — hanya penanda pada status HADIR
        DB::table('teacher_session_attendances')
            ->where('status', 'TERLAMBAT')
            ->update(['status' => 'HADIR']);
    }

    public function down(): void
    {
        // Tidak dapat dibatalkan: penanda terlambat dihitung ulang dari attended_at
    }
};
