<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nama_panggilan', 100)->nullable()->after('name');
            $table->string('nomor_ktp', 30)->nullable()->after('nama_panggilan');
            $table->string('foto_profil')->nullable()->after('nomor_ktp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nama_panggilan', 'nomor_ktp', 'foto_profil']);
        });
    }
};
