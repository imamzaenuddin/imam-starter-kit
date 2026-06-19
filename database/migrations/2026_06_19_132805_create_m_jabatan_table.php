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
        Schema::create('m_jabatan', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel jabatan');
            $table->id('jabatan_id');
            $table->string('kode_jabatan', 10)->nullable()->index();
            $table->string('fakultas_id', 10)->nullable();
            $table->string('nama', 50)->nullable();
            $table->string('nama__en', 50)->nullable();
            $table->string('jenis', 50);
            $table->string('login_buat', 50)->nullable();
            $table->date('tanggal_buat')->nullable();
            $table->date('tgl_buat')->nullable();
            $table->string('login_edit', 50)->nullable();
            $table->date('tanggal_edit')->nullable();
            $table->dateTime('tgl_edit');
            $table->string('def')->nullable();
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
        Schema::dropIfExists('m_jabatan');
    }
};