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
        Schema::create('m_statuspegawai', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel statuspegawai');
            $table->string('status_pegawai_id', 10)->primary();
            $table->string('no_id', 50)->nullable();
            $table->string('nama', 50)->nullable();
            $table->string('na', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_statuspegawai');
    }
};