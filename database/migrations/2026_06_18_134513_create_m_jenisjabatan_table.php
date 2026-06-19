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
        Schema::create('m_jenisjabatan', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel jenisjabatan');
            $table->id('jenis_jabatan_id');
            $table->string('singkatan', 50)->nullable();
            $table->string('nama', 100)->nullable();
            $table->integer('urutan')->nullable();
            $table->text('catatan')->nullable();
            $table->string('login_buat', 100)->nullable();
            $table->date('tanggal_buat')->nullable();
            $table->string('login_edit', 100)->nullable();
            $table->date('tanggal_edit')->nullable();
            $table->string('na')->nullable();
            $table->string('kode_id', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_jenisjabatan');
    }
};