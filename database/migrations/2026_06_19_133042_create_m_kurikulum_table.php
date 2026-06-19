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
        Schema::create('m_kurikulum', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel kurikulum');
            $table->id('kurikulum_id');
            $table->string('kurikulum_dikti_id', 50);
            $table->string('kurikulum_kode', 20);
            $table->string('sk_kurikulum', 20);
            $table->string('nama', 50)->nullable();
            $table->string('kode_id', 10)->index();
            $table->string('prodi_id', 20)->index();
            $table->string('tahun_id', 20);
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->string('sesi', 50)->nullable();
            $table->integer('jml_sesi');
            $table->integer('sksw_ajib');
            $table->integer('sks_pilihan');
            $table->integer('total_sks');
            $table->string('final_dosen')->nullable();
            $table->string('error_code', 50)->nullable();
            $table->longText('error_desc')->nullable();
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
        Schema::dropIfExists('m_kurikulum');
    }
};