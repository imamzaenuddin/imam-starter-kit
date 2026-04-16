<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('m_menu') || ! Schema::hasTable('m_level') || ! Schema::hasTable('m_level_menu')) {
            return;
        }

        $parentId = DB::table('m_menu')->where('nama', 'Sistem')->whereNull('parent_id')->value('id');

        $menuId = DB::table('m_menu')->where('url', '/admin/form-generator-wizard')->value('id');

        if (! $menuId) {
            $menuId = DB::table('m_menu')->insertGetId([
                'nama' => 'Form Generator Wizard',
                'url' => '/admin/form-generator-wizard',
                'icon' => 'bx bx-customize',
                'parent_id' => $parentId,
                'urutan' => 25,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $superadminLevelId = DB::table('m_level')
            ->whereRaw('LOWER(nama_level) = ?', ['superadmin'])
            ->value('id');

        if ($superadminLevelId) {
            $exists = DB::table('m_level_menu')
                ->where('level_id', $superadminLevelId)
                ->where('menu_id', $menuId)
                ->exists();

            if (! $exists) {
                DB::table('m_level_menu')->insert([
                    'level_id' => $superadminLevelId,
                    'menu_id' => $menuId,
                    'dapat_lihat' => true,
                    'dapat_buat' => true,
                    'dapat_ubah' => true,
                    'dapat_hapus' => true,
                    'dapat_backup' => false,
                    'dapat_restore' => false,
                    'dapat_hapus_backup' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('m_menu') || ! Schema::hasTable('m_level_menu')) {
            return;
        }

        $menuId = DB::table('m_menu')->where('url', '/admin/form-generator-wizard')->value('id');

        if ($menuId) {
            DB::table('m_level_menu')->where('menu_id', $menuId)->delete();
            DB::table('m_menu')->where('id', $menuId)->delete();
        }
    }
};
