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
        Schema::create('m_ruang', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel ruang');
            $table->string('ruang_id', 20)->primary();
            $table->string('kode_id', 50)->primary();
            $table->string('nama', 50)->nullable();
            $table->string('kampus_id', 20)->index();
            $table->string('rfid_device_id', 20);
            $table->smallInteger('lantai')->nullable();
            $table->string('prodi_id', 255)->nullable();
            $table->string('ruang_kuliah')->nullable();
            $table->integer('kapasitas')->nullable();
            $table->integer('kapasitas_ujian')->nullable();
            $table->smallInteger('kolom_ujian')->nullable();
            $table->string('untuk_usm')->nullable();
            $table->text('keterangan')->nullable();
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
        Schema::dropIfExists('m_ruang');
    }
};