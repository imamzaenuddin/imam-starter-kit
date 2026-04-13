<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('m_menu') || ! Schema::hasTable('m_level') || ! Schema::hasTable('m_level_menu')) {
            return;
        }

        $parentId = DB::table('m_menu')->where('nama', 'Sistem')->whereNull('parent_id')->value('id');

        if (! $parentId) {
            return;
        }

        $menuId = DB::table('m_menu')->where('url', '/admin/users')->value('id');

        if (! $menuId) {
            $menuId = DB::table('m_menu')->insertGetId([
                'nama' => 'Kelola User',
                'url' => '/admin/users',
                'icon' => 'bx bx-user-pin',
                'parent_id' => $parentId,
                'urutan' => 18,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Geser urutan menu setelah posisi baru agar tetap rapi
            DB::table('m_menu')
                ->where('parent_id', $parentId)
                ->where('id', '!=', $menuId)
                ->where('urutan', '>=', 18)
                ->increment('urutan');
        }

        $superadminId = DB::table('m_level')->where('nama_level', 'Superadmin')->value('id');
        $adminId = DB::table('m_level')->where('nama_level', 'Admin')->value('id');

        foreach (array_filter([$superadminId, $adminId]) as $levelId) {
            DB::table('m_level_menu')->updateOrInsert(
                ['level_id' => $levelId, 'menu_id' => $menuId],
                [
                    'dapat_lihat' => true,
                    'dapat_buat' => true,
                    'dapat_ubah' => true,
                    'dapat_hapus' => true,
                    'dapat_backup' => false,
                    'dapat_restore' => false,
                    'dapat_hapus_backup' => false,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('m_menu') || ! Schema::hasTable('m_level_menu')) {
            return;
        }

        $menuId = DB::table('m_menu')->where('url', '/admin/users')->value('id');

        if (! $menuId) {
            return;
        }

        DB::table('m_level_menu')->where('menu_id', $menuId)->delete();
        DB::table('m_menu')->where('id', $menuId)->delete();
    }
};
