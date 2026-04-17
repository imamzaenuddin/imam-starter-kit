<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('nama_widget', 120);
            $table->text('deskripsi')->nullable();
            $table->string('sumber_data', 50);
            $table->string('tipe_tampilan', 30)->default('statistik');
            $table->string('tipe_query', 30)->default('count');
            $table->string('kolom_agregasi', 50)->nullable();
            $table->string('kolom_label', 50)->nullable();
            $table->string('kolom_nilai', 50)->nullable();
            $table->string('filter_kolom', 50)->nullable();
            $table->string('filter_operator', 20)->nullable();
            $table->string('filter_nilai', 255)->nullable();
            $table->string('layout_kolom', 50)->default('col-xl-3 col-md-6');
            $table->string('warna', 20)->default('primary');
            $table->string('icon', 100)->nullable();
            $table->unsignedInteger('batas_data')->default(5);
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('dashboard_widget_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_widget_id')->constrained('dashboard_widgets')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['dashboard_widget_id', 'level_id'], 'dashboard_widget_level_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_widget_level');
        Schema::dropIfExists('dashboard_widgets');
    }
};
