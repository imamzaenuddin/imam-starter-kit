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
        Schema::create('m_fakultas', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel fakultas');
            $table->string('fakultas_id', 20)->primary();
            $table->string('id_perguruan_tinggi', 200);
            $table->string('nama', 100);
            $table->string('nama_ins', 100);
            $table->string('kode_pti', 100)->primary();
            $table->string('status_pt')->nullable();
            $table->string('kode_ptid', 100);
            $table->string('header', 100);
            $table->string('footer', 100);
            $table->string('file_kartu_pegawai1', 250)->nullable();
            $table->string('file_kartu_pegawai2', 250)->nullable();
            $table->string('akreditasi', 100);
            $table->string('no_skbanpt', 100);
            $table->string('live')->nullable();
            $table->string('id__sp', 100);
            $table->string('feeder_url', 100);
            $table->string('feeder_port', 100);
            $table->string('feeder_username', 100);
            $table->string('feeder_password', 100);
            $table->string('port', 100);
            $table->string('kode_id', 10);
            $table->string('pejabat', 200);
            $table->string('jabatan', 200);
            $table->text('keterangan')->nullable();
            $table->text('alamat')->nullable();
            $table->text('provinsi')->nullable();
            $table->bigInteger('start_no_fakultas');
            $table->bigInteger('no_fakultas');
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
        Schema::dropIfExists('m_fakultas');
    }
};