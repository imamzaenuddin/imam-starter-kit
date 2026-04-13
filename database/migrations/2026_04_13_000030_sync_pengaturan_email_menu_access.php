<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $menuId = DB::table('menus')->where('url', '/admin/pengaturan-email')->value('id');

        if (! $menuId) {
            return;
        }

        $superadminId = DB::table('levels')->where('nama_level', 'Superadmin')->value('id');
        $adminId = DB::table('levels')->where('nama_level', 'Admin')->value('id');

        foreach ([$superadminId, $adminId] as $levelId) {
            if (! $levelId) {
                continue;
            }

            DB::table('level_menu')->updateOrInsert(
                ['level_id' => $levelId, 'menu_id' => $menuId],
                [
                    'dapat_lihat' => 1,
                    'dapat_buat' => $levelId === $superadminId ? 1 : 0,
                    'dapat_ubah' => 1,
                    'dapat_hapus' => $levelId === $superadminId ? 1 : 0,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $userIds = DB::table('users')
            ->whereIn('level_id', array_filter([$superadminId, $adminId]))
            ->pluck('id');

        foreach ($userIds as $userId) {
            Cache::forget("menu_user_{$userId}");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $menuId = DB::table('menus')->where('url', '/admin/pengaturan-email')->value('id');
        $adminId = DB::table('levels')->where('nama_level', 'Admin')->value('id');

        if ($menuId && $adminId) {
            DB::table('level_menu')
                ->where('level_id', $adminId)
                ->where('menu_id', $menuId)
                ->delete();
        }
    }
};
