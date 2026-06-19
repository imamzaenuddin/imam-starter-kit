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
        Schema::create('m_pmbgrade', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel pmbgrade');
            $table->string('grade_nilai', 5)->primary();
            $table->string('kode_id', 50)->primary();
            $table->decimal('nilai_ujian_min', 10, 2);
            $table->decimal('nilai_ujian_max', 10, 2);
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
        Schema::dropIfExists('m_pmbgrade');
    }
};