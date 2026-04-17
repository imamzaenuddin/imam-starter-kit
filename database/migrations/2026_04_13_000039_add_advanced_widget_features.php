<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('m_dashboard_widget') && ! Schema::hasColumn('m_dashboard_widget', 'bandingkan_periode')) {
            Schema::table('m_dashboard_widget', function (Blueprint $table) {
                $table->boolean('bandingkan_periode')->default(false)->after('batas_data');
                $table->string('bandingkan_dengan')->nullable()->after('bandingkan_periode');
                $table->integer('kpi_target')->nullable()->after('bandingkan_dengan');
                $table->boolean('tampilkan_progress_bar')->default(false)->after('kpi_target');
                $table->string('warna_threshold_hijau')->default('90ddd52')->after('tampilkan_progress_bar');
                $table->string('warna_threshold_kuning')->default('ffc107')->after('warna_threshold_hijau');
                $table->string('warna_threshold_merah')->default('dc3545')->after('warna_threshold_kuning');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('m_dashboard_widget')) {
            Schema::table('m_dashboard_widget', function (Blueprint $table) {
                if (Schema::hasColumn('m_dashboard_widget', 'bandingkan_periode')) {
                    $table->dropColumn([
                        'bandingkan_periode',
                        'bandingkan_dengan',
                        'kpi_target',
                        'tampilkan_progress_bar',
                        'warna_threshold_hijau',
                        'warna_threshold_kuning',
                        'warna_threshold_merah',
                    ]);
                }
            });
        }
    }
};
