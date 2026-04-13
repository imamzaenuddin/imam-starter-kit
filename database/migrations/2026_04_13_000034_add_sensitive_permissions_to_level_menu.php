<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_level_menu', function (Blueprint $table) {
            if (! Schema::hasColumn('m_level_menu', 'dapat_backup')) {
                $table->boolean('dapat_backup')->default(false)->after('dapat_hapus');
            }
            if (! Schema::hasColumn('m_level_menu', 'dapat_restore')) {
                $table->boolean('dapat_restore')->default(false)->after('dapat_backup');
            }
            if (! Schema::hasColumn('m_level_menu', 'dapat_hapus_backup')) {
                $table->boolean('dapat_hapus_backup')->default(false)->after('dapat_restore');
            }
        });

        $menuBackupId = DB::table('m_menu')->where('url', '/admin/backup-restore')->value('id');
        $superadminId = DB::table('m_level')->where('nama_level', 'Superadmin')->value('id');
        $adminId = DB::table('m_level')->where('nama_level', 'Admin')->value('id');

        if ($menuBackupId && $superadminId) {
            DB::table('m_level_menu')
                ->where('level_id', $superadminId)
                ->where('menu_id', $menuBackupId)
                ->update([
                    'dapat_backup' => true,
                    'dapat_restore' => true,
                    'dapat_hapus_backup' => true,
                ]);
        }

        if ($menuBackupId && $adminId) {
            DB::table('m_level_menu')
                ->where('level_id', $adminId)
                ->where('menu_id', $menuBackupId)
                ->update([
                    'dapat_backup' => false,
                    'dapat_restore' => false,
                    'dapat_hapus_backup' => false,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('m_level_menu', function (Blueprint $table) {
            if (Schema::hasColumn('m_level_menu', 'dapat_hapus_backup')) {
                $table->dropColumn('dapat_hapus_backup');
            }
            if (Schema::hasColumn('m_level_menu', 'dapat_restore')) {
                $table->dropColumn('dapat_restore');
            }
            if (Schema::hasColumn('m_level_menu', 'dapat_backup')) {
                $table->dropColumn('dapat_backup');
            }
        });
    }
};
