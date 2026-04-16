<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('m_pengaturan_aplikasi')) {
            return;
        }

        Schema::table('m_pengaturan_aplikasi', function (Blueprint $table) {
            if (! Schema::hasColumn('m_pengaturan_aplikasi', 'chat_ai_konteks')) {
                $table->json('chat_ai_konteks')->nullable()->after('otp_failed_window_minutes')
                    ->comment('Daftar kunci konteks yang diaktifkan untuk Chat AI Asisten (JSON array)');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('m_pengaturan_aplikasi')) {
            return;
        }

        Schema::table('m_pengaturan_aplikasi', function (Blueprint $table) {
            if (Schema::hasColumn('m_pengaturan_aplikasi', 'chat_ai_konteks')) {
                $table->dropColumn('chat_ai_konteks');
            }
        });
    }
};
