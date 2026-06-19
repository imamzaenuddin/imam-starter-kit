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
        Schema::create('m_posisipegawai', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel posisipegawai');
            $table->string('posisi_pegawai_id', 5)->primary();
            $table->integer('no_id')->nullable()->index();
            $table->string('nama', 50);
            $table->string('def')->nullable();
            $table->string('honor_mengajar')->nullable();
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
        Schema::dropIfExists('m_posisipegawai');
    }
};