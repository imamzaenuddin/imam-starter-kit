<?php

use App\Models\Level;
use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan parent Sistem ada
        $administrasi = Menu::where('nama', 'Sistem')->whereNull('parent_id')->first();

        if (! $administrasi) {
            return;
        }

        // Tambah menu Pemeliharaan di bawah Sistem (urutan 23)
        $mPemeliharaan = Menu::firstOrCreate(
            ['nama' => 'Pemeliharaan', 'parent_id' => $administrasi->id],
            [
                'url'       => '/admin/pemeliharaan',
                'icon'      => 'bx bx-wrench',
                'urutan'    => 23,
                'is_active' => true,
            ]
        );

        // Superadmin: akses penuh
        $superadmin = Level::where('nama_level', 'Superadmin')->first();
        if ($superadmin) {
            $superadmin->menus()->syncWithoutDetaching([
                $mPemeliharaan->id => [
                    'dapat_lihat'        => true,
                    'dapat_buat'         => true,
                    'dapat_ubah'         => true,
                    'dapat_hapus'        => true,
                    'dapat_backup'       => false,
                    'dapat_restore'      => false,
                    'dapat_hapus_backup' => false,
                ],
            ]);
        }

        // Admin & Anggota: tidak mendapat akses (default)
        // Level lain tidak ditambahkan agar aman
    }

    public function down(): void
    {
        $menu = Menu::where('url', '/admin/pemeliharaan')->first();
        if ($menu) {
            $menu->menus()->detach();
            $menu->delete();
        }
    }
};
