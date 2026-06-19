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
        Schema::create('m_modekuliah', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel modekuliah');
            $table->id('mode_kuliah_id');
            $table->string('mode_kuliah_kode', 50);
            $table->string('nama', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('dokumentasi', 250)->nullable();
            $table->string('bukti_dokumentasi')->nullable();
            $table->string('link', 250)->nullable();
            $table->string('bukti_link')->nullable();
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
        Schema::dropIfExists('m_modekuliah');
    }
};