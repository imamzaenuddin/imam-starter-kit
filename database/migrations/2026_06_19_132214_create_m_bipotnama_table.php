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
        Schema::create('m_bipotnama', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel bipotnama');
            $table->id('bipot_nama_id');
            $table->string('kode_id', 10)->index();
            $table->string('rekening_id', 50)->nullable();
            $table->integer('urutan');
            $table->string('nama', 50);
            $table->string('singkatan', 10)->nullable();
            $table->integer('trx_id');
            $table->integer('baris');
            $table->string('detil')->nullable();
            $table->bigInteger('def_jumlah');
            $table->bigInteger('def_besar');
            $table->string('diskon')->nullable();
            $table->string('kena_denda')->nullable();
            $table->string('dipotong_beasiswa')->nullable();
            $table->string('catatan', 255)->nullable();
            $table->string('na')->nullable();
            $table->string('pb')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_bipotnama');
    }
};