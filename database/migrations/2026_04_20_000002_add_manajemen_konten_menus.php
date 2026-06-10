<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('m_menu') || !Schema::hasTable('m_level') || !Schema::hasTable('m_level_menu')) {
            return;
        }

        // ── Menu: Manajemen Konten (Parent) ──────────────────────────────
        $parentId = DB::table('m_menu')->insertGetId([
            'parent_id'  => null,
            'nama'       => 'Manajemen Konten',
            'url'        => '#',
            'icon'       => 'bx bx-edit-alt',
            'urutan'     => 85,
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ── Menu: Berita & Artikel ────────────────────────────────────────
        $beritaId = DB::table('m_menu')->insertGetId([
            'parent_id'  => $parentId,
            'nama'       => 'Berita & Artikel',
            'url'        => '/manajemen-konten/berita',
            'icon'       => 'bx bx-news',
            'urutan'     => 1,
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ── Menu: Slider Halaman Utama ────────────────────────────────────
        $sliderId = DB::table('m_menu')->insertGetId([
            'parent_id'  => $parentId,
            'nama'       => 'Slider Halaman Utama',
            'url'        => '/manajemen-konten/slider',
            'icon'       => 'bx bx-slideshow',
            'urutan'     => 2,
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ── Assign ke level Superadmin & Admin ─────────────────────────────
        $adminLevels = DB::table('m_level')->orderBy('id')->take(2)->pluck('id');

        foreach ($adminLevels as $idx => $levelId) {
            $isSuperAdmin = ($idx === 0);
            foreach ([$parentId, $beritaId, $sliderId] as $menuId) {
                DB::table('m_level_menu')->updateOrInsert(
                    ['level_id' => $levelId, 'menu_id' => $menuId],
                    [
                        'dapat_lihat'   => true,
                        'dapat_buat'    => true,
                        'dapat_ubah'    => true,
                        'dapat_hapus'   => $isSuperAdmin,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('m_menu') || !Schema::hasTable('m_level_menu')) {
            return;
        }

        $parent = DB::table('m_menu')->where('url', '#')->where('nama', 'Manajemen Konten')->first();
        if ($parent) {
            $children = DB::table('m_menu')->where('parent_id', $parent->id)->pluck('id');
            DB::table('m_level_menu')->whereIn('menu_id', $children)->delete();
            DB::table('m_level_menu')->where('menu_id', $parent->id)->delete();
            DB::table('m_menu')->whereIn('id', $children)->delete();
            DB::table('m_menu')->where('id', $parent->id)->delete();
        }
    }
};
