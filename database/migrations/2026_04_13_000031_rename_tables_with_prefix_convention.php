<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // Master/reference tables (jarang berubah)
        $this->renameIfExists('levels', 'm_level');
        $this->renameIfExists('menus', 'm_menu');
        $this->renameIfExists('level_menu', 'm_level_menu');
        $this->renameIfExists('identitas', 'm_identitas');
        $this->renameIfExists('dashboard_widgets', 'm_dashboard_widget');
        $this->renameIfExists('dashboard_widget_level', 'm_dashboard_widget_level');
        $this->renameIfExists('bahasa', 'm_bahasa');
        $this->renameIfExists('pengaturan_email', 'm_pengaturan_email');

        // Transaction tables (sering berubah)
        $this->renameIfExists('users', 't_user');
        $this->renameIfExists('log_aktivitas', 't_log_aktivitas');
        $this->renameIfExists('chat_ai_riwayat', 't_chat_ai_riwayat');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->renameIfExists('m_level', 'levels');
        $this->renameIfExists('m_menu', 'menus');
        $this->renameIfExists('m_level_menu', 'level_menu');
        $this->renameIfExists('m_identitas', 'identitas');
        $this->renameIfExists('m_dashboard_widget', 'dashboard_widgets');
        $this->renameIfExists('m_dashboard_widget_level', 'dashboard_widget_level');
        $this->renameIfExists('m_bahasa', 'bahasa');
        $this->renameIfExists('m_pengaturan_email', 'pengaturan_email');

        $this->renameIfExists('t_user', 'users');
        $this->renameIfExists('t_log_aktivitas', 'log_aktivitas');
        $this->renameIfExists('t_chat_ai_riwayat', 'chat_ai_riwayat');

        Schema::enableForeignKeyConstraints();
    }

    private function renameIfExists(string $from, string $to): void
    {
        if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
            Schema::rename($from, $to);
        }
    }
};
