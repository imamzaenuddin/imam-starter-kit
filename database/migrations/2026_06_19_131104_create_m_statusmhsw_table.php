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
        Schema::create('m_statusmhsw', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel statusmhsw');
            $table->string('status_mhsw_id', 5)->primary();
            $table->string('jenis_keluar_id', 5);
            $table->string('kode_id', 10);
            $table->string('nama', 50);
            $table->smallInteger('nilai');
            $table->string('status_semester')->nullable();
            $table->string('keluar')->nullable();
            $table->string('status_kembali')->nullable();
            $table->string('def')->nullable();
            $table->string('lulus')->nullable();
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
        Schema::dropIfExists('m_statusmhsw');
    }
};