<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('m_chat_ai_sumber_level')) {
            return;
        }

        Schema::create('m_chat_ai_sumber_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_ai_sumber_id')
                ->constrained('m_chat_ai_sumber')
                ->cascadeOnDelete();
            $table->foreignId('level_id')
                ->constrained('m_level')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['chat_ai_sumber_id', 'level_id'], 'chat_ai_sumber_level_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_chat_ai_sumber_level');
    }
};
