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
        Schema::create('m_statusawal', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel statusawal');
            $table->string('status_awal_id', 5)->primary();
            $table->integer('status_awal')->nullable();
            $table->string('nama', 50)->nullable();
            $table->string('beli_formulir')->nullable();
            $table->string('jalur_khusus')->nullable();
            $table->string('tanpa_test')->nullable();
            $table->string('catatan', 255)->nullable();
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
        Schema::dropIfExists('m_statusawal');
    }
};