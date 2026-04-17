<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboard_widgets', function (Blueprint $table) {
            $table->unsignedInteger('chart_tinggi')->nullable()->after('chart_tipe');
            $table->json('chart_warna')->nullable()->after('chart_tinggi');
        });
    }

    public function down(): void
    {
        Schema::table('dashboard_widgets', function (Blueprint $table) {
            $table->dropColumn(['chart_tinggi', 'chart_warna']);
        });
    }
};
