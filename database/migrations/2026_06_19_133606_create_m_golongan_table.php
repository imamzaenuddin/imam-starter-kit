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
        Schema::create('m_golongan', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel golongan');
            $table->string('golongan_id', 10)->primary();
            $table->string('kategori_id', 10)->primary();
            $table->string('prodi_id', 20)->primary();
            $table->string('kode_id', 20)->primary();
            $table->string('pangkat', 50);
            $table->string('nama', 50);
            $table->string('def')->nullable();
            $table->string('tunjangan_fungsional', 20);
            $table->string('tunjangan_sks', 20);
            $table->string('tunjangan_transport', 20);
            $table->string('tunjangan_tetap', 20);
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
        Schema::dropIfExists('m_golongan');
    }
};