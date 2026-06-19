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
        Schema::create('m_pmbformsyarat', function (Blueprint $table) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel pmbformsyarat');
            $table->id('pmb_form_syarat_id');
            $table->integer('urutan');
            $table->string('nama', 50);
            $table->string('ada_script')->nullable();
            $table->text('script')->nullable();
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
        Schema::dropIfExists('m_pmbformsyarat');
    }
};