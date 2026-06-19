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
        Schema::create('m_pmbusm', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel pmbusm');
            $table->string('pmbusm_id', 10)->primary();
            $table->string('kode_id', 50);
            $table->string('nama', 50)->nullable();
            $table->string('cara_penempatan', 20);
            $table->text('keterangan')->nullable();
            $table->string('ada_script', 50)->nullable();
            $table->string('login_buat', 50)->nullable();
            $table->dateTime('tgl_buat')->nullable();
            $table->string('login_edit', 50)->nullable();
            $table->dateTime('tgl_edit')->nullable();
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
        Schema::dropIfExists('m_pmbusm');
    }
};