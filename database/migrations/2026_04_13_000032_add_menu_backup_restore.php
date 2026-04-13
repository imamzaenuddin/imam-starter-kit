<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sistemId = DB::table('m_menu')
            ->where('nama', 'Sistem')
            ->whereNull('parent_id')
            ->value('id');

        if (! $sistemId) {
            return;
        }

        $menuId = DB::table('m_menu')->where('url', '/admin/backup-restore')->value('id');

        if (! $menuId) {
            $menuId = DB::table('m_menu')->insertGetId([
                'nama' => 'Backup & Restore',
                'url' => '/admin/backup-restore',
                'icon' => 'bx bx-data',
                'parent_id' => $sistemId,
                'urutan' => 18,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $superadminId = DB::table('m_level')->where('nama_level', 'Superadmin')->value('id');

        if ($superadminId) {
            DB::table('m_level_menu')->updateOrInsert(
                ['level_id' => $superadminId, 'menu_id' => $menuId],
                [
                    'dapat_lihat' => 1,
                    'dapat_buat' => 1,
                    'dapat_ubah' => 1,
                    'dapat_hapus' => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $adminId = DB::table('m_level')->where('nama_level', 'Admin')->value('id');

        if ($adminId) {
            DB::table('m_level_menu')->updateOrInsert(
                ['level_id' => $adminId, 'menu_id' => $menuId],
                [
                    'dapat_lihat' => 0,
                    'dapat_buat' => 0,
                    'dapat_ubah' => 0,
                    'dapat_hapus' => 0,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $userIds = DB::table('t_user')
            ->whereIn('level_id', array_filter([$superadminId, $adminId]))
            ->pluck('id');

        foreach ($userIds as $userId) {
            Cache::forget("menu_user_{$userId}");
        }
    }

    public function down(): void
    {
        $menuId = DB::table('m_menu')->where('url', '/admin/backup-restore')->value('id');

        if (! $menuId) {
            return;
        }

        DB::table('m_level_menu')->where('menu_id', $menuId)->delete();
        DB::table('m_menu')->where('id', $menuId)->delete();
    }
};
