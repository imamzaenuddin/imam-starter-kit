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
        Schema::create('m_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel aktivitas');
            $table->integer('aktivitas_id')->index();
            $table->string('prodi_id', 50);
            $table->string('tahun_id', 6);
            $table->string('sk_tugas', 20)->nullable();
            $table->date('tanggal_sk_tugas')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('jenis_aktivitas_id', 5);
            $table->string('jenis_anggota_id', 5);
            $table->string('judul', 250);
            $table->string('lokasi', 250);
            $table->text('keterangan')->nullable();
            $table->string('jenis_mbkmid', 2);
            $table->string('kode_id', 50);
            $table->string('login_buat', 20);
            $table->dateTime('tanggal_buat');
            $table->date('tanggal_edit')->nullable();
            $table->string('login_edit', 20)->nullable();
            $table->string('error_code', 50)->nullable();
            $table->longText('error_desc')->nullable();
            $table->string('login_sync', 50)->nullable();
            $table->date('tanggal_sync')->nullable();
            $table->string('na')->nullable();
            $table->dateTime('modification_time');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_aktivitas');
    }
};