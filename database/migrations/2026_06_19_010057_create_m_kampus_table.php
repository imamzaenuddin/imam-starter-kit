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
        Schema::create('m_kampus', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel kampus');
            $table->string('kampus_id', 20)->primary();
            $table->bigInteger('no_id')->nullable()->index();
            $table->string('nama', 50)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->string('kota', 50)->nullable();
            $table->string('kode_id', 10)->nullable()->index();
            $table->string('telepon', 50)->nullable();
            $table->string('wa', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('aktif')->nullable();
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
        Schema::dropIfExists('m_kampus');
    }
};