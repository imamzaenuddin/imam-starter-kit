<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_form_generator', function (Blueprint $table) {
            $table->enum('tipe_modul', ['master', 'transaksi'])->default('master')->after('sumber_import');
        });
    }

    public function down(): void
    {
        Schema::table('m_form_generator', function (Blueprint $table) {
            $table->dropColumn('tipe_modul');
        });
    }
};
