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
        Schema::create('m_tapengajuan', function (Blueprint $table) {
            $table->id();
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel tapengajuan');
            $table->integer('ta_pengajuan_id')->index();
            $table->integer('mhsw_id')->nullable();
            $table->string('judul', 50);
            $table->longText('masalah')->nullable();
            $table->string('metode', 255);
            $table->string('lokasi', 50);
            $table->string('alamat', 50);
            $table->string('login_buat', 50);
            $table->dateTime('tanggal_buat');
            $table->string('login_edit', 50);
            $table->dateTime('tanggal_edit');
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
        Schema::dropIfExists('m_tapengajuan');
    }
};