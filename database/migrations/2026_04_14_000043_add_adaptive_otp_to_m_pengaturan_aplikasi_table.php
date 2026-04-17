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
            if (! Schema::hasColumn('m_pengaturan_aplikasi', 'otp_mode')) {
                $table->string('otp_mode', 20)->default('always')->after('pagination_default');
            }

            if (! Schema::hasColumn('m_pengaturan_aplikasi', 'otp_inactive_days')) {
                $table->unsignedInteger('otp_inactive_days')->default(30)->after('otp_mode');
            }

            if (! Schema::hasColumn('m_pengaturan_aplikasi', 'otp_failed_attempts')) {
                $table->unsignedInteger('otp_failed_attempts')->default(3)->after('otp_inactive_days');
            }

            if (! Schema::hasColumn('m_pengaturan_aplikasi', 'otp_failed_window_minutes')) {
                $table->unsignedInteger('otp_failed_window_minutes')->default(15)->after('otp_failed_attempts');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('m_pengaturan_aplikasi')) {
            return;
        }

        Schema::table('m_pengaturan_aplikasi', function (Blueprint $table) {
            foreach (['otp_failed_window_minutes', 'otp_failed_attempts', 'otp_inactive_days', 'otp_mode'] as $kolom) {
                if (Schema::hasColumn('m_pengaturan_aplikasi', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};
