<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class ReferensiAgamaMenuSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tambah/Cari parent menu "Referensi"
        $mReferensi = Menu::firstOrCreate(
            ['nama' => 'Referensi', 'parent_id' => null],
            [
                'url'       => null,
                'icon'      => 'bx bx-slider-alt',
                'urutan'    => 80,
                'is_active' => true,
            ]
        );

        // 2. Tambah/Cari child menu "Agama"
        $mAgama = Menu::firstOrCreate(
            ['nama' => 'Agama', 'parent_id' => $mReferensi->id],
            [
                'url'       => '/admin/referensi/agama',
                'icon'      => 'bx bx-book-bookmark',
                'urutan'    => 1,
                'is_active' => true,
            ]
        );

        // 3. Berikan akses ke Superadmin & Admin
        $levels = Level::whereIn('nama_level', ['Superadmin', 'Admin'])->get();

        foreach ($levels as $lvl) {
            // Akses ke parent menu
            $lvl->menus()->syncWithoutDetaching([
                $mReferensi->id => [
                    'dapat_lihat'        => true,
                    'dapat_buat'         => true,
                    'dapat_ubah'         => true,
                    'dapat_hapus'        => true,
                    'dapat_backup'       => false,
                    'dapat_restore'      => false,
                    'dapat_hapus_backup' => false,
                ],
                // Akses ke child menu
                $mAgama->id => [
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

        $this->command->info('✅ Menu "Referensi -> Agama" berhasil ditambahkan untuk Superadmin & Admin.');
    }
}
