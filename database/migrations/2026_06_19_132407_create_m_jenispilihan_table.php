<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('m_jenispilihan', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel jenispilihan');
            $table->id('jenis_pilihan_id');
            $table->string('kode_id', 10)->index();
            $table->integer('urutan');
            $table->string('singkatan', 10)->nullable();
            $table->string('nama', 50);
            $table->string('prodi_id', 20)->index();
            $table->string('ta')->nullable();
            $table->string('na')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_jenispilihan');
    }
};