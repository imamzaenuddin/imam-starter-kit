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
        Schema::create('m_program', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel program');
            $table->string('program_id', 50)->primary();
            $table->string('nama', 100)->nullable();
            $table->string('kode_id', 10)->nullable()->index();
            $table->string('def')->nullable();
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
        Schema::dropIfExists('m_program');
    }
};