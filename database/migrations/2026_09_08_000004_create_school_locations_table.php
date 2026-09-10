<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->double('latitude', 10, 7)->default(-7.1147000);
            $table->double('longitude', 10, 7)->default(107.8845000);
            $table->integer('radius_meters')->default(75); // 50-100 meter
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_locations');
    }
};
