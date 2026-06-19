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
        Schema::create('m_statusaplikan', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel statusaplikan');
            $table->string('status_aplikan_id', 3)->primary();
            $table->smallInteger('urutan');
            $table->string('nama', 50)->nullable();
            $table->string('kode_id', 10);
            $table->string('status_aplikan_before', 3)->nullable();
            $table->string('status_aplikan_after', 3)->nullable();
            $table->text('keterangan')->nullable();
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
        Schema::dropIfExists('m_statusaplikan');
    }
};