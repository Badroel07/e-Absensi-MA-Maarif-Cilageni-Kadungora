<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        // 1. Backfill student emails that are currently NULL
        $studentsWithoutEmail = DB::table('users')
            ->where('role', 'siswa')
            ->whereNull('email')
            ->get(['id', 'identity_number']);

        foreach ($studentsWithoutEmail as $st) {
            DB::table('users')
                ->where('id', $st->id)
                ->update([
                    'email' => $st->identity_number.'@siswa.maarif.sch.id',
                ]);
        }

        // 2. Set uniform default passwords for each role
        DB::table('users')
            ->where('role', 'admin')
            ->update([
                'password' => Hash::make('p@55w0rd'),
            ]);

        DB::table('users')
            ->where('role', 'guru')
            ->update([
                'password' => Hash::make('akunguru@maarif'),
            ]);

        DB::table('users')
            ->where('role', 'siswa')
            ->update([
                'password' => Hash::make('akunsiswa@maarif'),
            ]);

        // 3. Make email column NOT NULL on users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }
};
