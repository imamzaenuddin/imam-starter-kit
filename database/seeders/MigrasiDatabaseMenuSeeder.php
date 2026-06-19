<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class MigrasiDatabaseMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Cari parent menu "Sistem" yang sudah ada
        $sistem = Menu::where('nama', 'Sistem')->whereNull('parent_id')->first();

        if (! $sistem) {
            $this->command->warn('⚠ Menu "Sistem" tidak ditemukan. Pastikan LevelMenuSeeder sudah dijalankan.');
            return;
        }

        // Tambahkan menu Migrasi Database
        $mMigrasi = Menu::firstOrCreate(
            ['nama' => 'Migrasi Database', 'parent_id' => $sistem->id],
            [
                'url'       => '/admin/migrasi-database',
                'icon'      => 'bx bx-transfer-alt',
                'urutan'    => 26,
                'is_active' => true,
            ]
        );

        // Berikan akses HANYA ke Superadmin
        $superadmin = Level::where('nama_level', 'Superadmin')->first();

        if ($superadmin) {
            $superadmin->menus()->syncWithoutDetaching([
                $mMigrasi->id => [
                    'dapat_lihat'        => true,
                    'dapat_buat'         => true,
                    'dapat_ubah'         => true,
                    'dapat_hapus'        => false,
                    'dapat_backup'       => false,
                    'dapat_restore'      => false,
                    'dapat_hapus_backup' => false,
                ],
            ]);
            $this->command->info('✅ Menu "Migrasi Database" berhasil ditambahkan untuk Superadmin.');
        } else {
            $this->command->warn('⚠ Level "Superadmin" tidak ditemukan.');
        }
    }
}
