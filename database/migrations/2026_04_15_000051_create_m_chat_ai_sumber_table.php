<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('m_chat_ai_sumber')) {
            Schema::create('m_chat_ai_sumber', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100);
                $table->string('sumber_data', 50);
                $table->string('tipe_data', 20)->default('statistik');
                $table->string('tipe_query', 20)->default('count');
                $table->string('kolom_agregasi', 100)->nullable();
                $table->json('kolom_tampil')->nullable();
                $table->string('filter_kolom', 100)->nullable();
                $table->string('filter_operator', 10)->nullable();
                $table->string('filter_nilai', 255)->nullable();
                $table->unsignedInteger('batas_data')->default(10);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('urutan')->default(0);
                $table->timestamps();

                $table->index(['is_active', 'urutan']);
                $table->index('sumber_data');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('m_chat_ai_sumber');
    }
};
