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
        Schema::create('m_gradeipk', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel gradeipk');
            $table->string('grade_ipk', 5)->primary();
            $table->string('kode_id', 50)->index();
            $table->decimal('ipk_min', 10, 2);
            $table->decimal('ipk_max', 10, 2);
            $table->integer('sks_min');
            $table->text('keterangan')->nullable();
            $table->string('login_buat', 50)->nullable();
            $table->date('tgl_buat')->nullable();
            $table->string('login_edit', 50)->nullable();
            $table->date('tgl_edit')->nullable();
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
        Schema::dropIfExists('m_gradeipk');
    }
};