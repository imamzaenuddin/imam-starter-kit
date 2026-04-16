<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('m_chat_ai_sumber')) {
            return;
        }

        Schema::table('m_chat_ai_sumber', function (Blueprint $table) {
            if (! Schema::hasColumn('m_chat_ai_sumber', 'is_data_personal')) {
                $table->boolean('is_data_personal')->default(false)->after('batas_data');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('m_chat_ai_sumber')) {
            return;
        }

        Schema::table('m_chat_ai_sumber', function (Blueprint $table) {
            if (Schema::hasColumn('m_chat_ai_sumber', 'is_data_personal')) {
                $table->dropColumn('is_data_personal');
            }
        });
    }
};
