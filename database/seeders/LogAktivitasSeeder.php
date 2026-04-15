<?php

namespace Database\Seeders;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Database\Seeder;

class LogAktivitasSeeder extends Seeder
{
    public function run(): void
    {
        if (LogAktivitas::query()->exists()) {
            return;
        }

        $superadmin = User::where('email', 'superadmin@admin.id')->first();
        $admin = User::where('email', 'admin@admin.id')->first();

        $data = [
            [
                'user_id' => $superadmin?->id,
                'modul' => 'Autentikasi',
                'aktivitas' => 'Login ke sistem',
                'url' => '/login',
                'metode' => 'POST',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder',
                'metadata' => ['status' => 'berhasil'],
            ],
            [
                'user_id' => $admin?->id,
                'modul' => 'Menu',
                'aktivitas' => 'Mengubah data menu',
                'url' => '/admin/menus',
                'metode' => 'PUT',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder',
                'metadata' => ['menu' => 'Kelola Menu'],
            ],
            [
                'user_id' => $superadmin?->id,
                'modul' => 'Hak Akses',
                'aktivitas' => 'Menyimpan mapping level-menu',
                'url' => '/admin/hak-akses',
                'metode' => 'POST',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder',
                'metadata' => ['level' => 'Superadmin'],
            ],
        ];

        foreach ($data as $item) {
            LogAktivitas::create($item);
        }
    }
}
