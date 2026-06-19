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
        Schema::create('m_jenisberkas', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel jenisberkas');
            $table->id('jenis_berkas_id');
            $table->string('nama', 100)->nullable();
            $table->string('bentuk')->nullable();
            $table->string('type', 100)->nullable();
            $table->string('ukuran', 100);
            $table->string('na')->nullable();
            $table->string('wajib')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_jenisberkas');
    }
};