<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sistemId = DB::table('menus')
            ->where('nama', 'Sistem')
            ->whereNull('parent_id')
            ->value('id');

        if (! $sistemId) {
            return;
        }

        $menuId = DB::table('menus')->where('url', '/admin/pengaturan-email')->value('id');

        if (! $menuId) {
            $menuId = DB::table('menus')->insertGetId([
                'nama' => 'Pengaturan Email',
                'url' => '/admin/pengaturan-email',
                'icon' => 'bx bx-envelope',
                'parent_id' => $sistemId,
                'urutan' => 17,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $superadminId = DB::table('levels')->where('nama_level', 'Superadmin')->value('id');
        if ($superadminId) {
            $exists = DB::table('level_menu')
                ->where('level_id', $superadminId)
                ->where('menu_id', $menuId)
                ->exists();

            if (! $exists) {
                DB::table('level_menu')->insert([
                    'level_id' => $superadminId,
                    'menu_id' => $menuId,
                    'dapat_buat' => 1,
                    'dapat_lihat' => 1,
                    'dapat_ubah' => 1,
                    'dapat_hapus' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $menuId = DB::table('menus')->where('url', '/admin/pengaturan-email')->value('id');

        if (! $menuId) {
            return;
        }

        DB::table('level_menu')->where('menu_id', $menuId)->delete();
        DB::table('menus')->where('id', $menuId)->delete();
    }
};
