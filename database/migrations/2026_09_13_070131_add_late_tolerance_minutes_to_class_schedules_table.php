<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_schedules', function (Blueprint $table) {
            // Toleransi keterlambatan buka sesi per jadwal (mis. sesi pertama hari Senin setelah upacara = 60 menit)
            $table->unsignedSmallInteger('late_tolerance_minutes')->default(0)->after('end_time');
        });
    }

    public function down(): void
    {
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->dropColumn('late_tolerance_minutes');
        });
    }
};
