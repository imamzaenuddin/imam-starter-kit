<?php

namespace Database\Seeders;

use App\Models\Identitas;
use Illuminate\Database\Seeder;

class IdentitasSeeder extends Seeder
{
    public function run(): void
    {
        Identitas::updateOrCreate(
            ['id' => 1],
            [
                'nama_aplikasi' => 'Imam Starter-Kit',
                'singkatan_aplikasi' => 'ISK',
                'versi' => '1.0.0',
                'icon' => 'bx bx-buildings',
                'logo_path' => null,
                'main_color' => '#696cff',
                'secondary_color' => '#8592a3',
                'email' => 'helpdesk@admin.id',
                'wa_center' => '6281234567890',
                'telepon' => '(021) 555-0101',
                'website' => 'https://admin.id',
                'alamat' => 'Jl. Organisasi No. 1, Jakarta',
                'slogan' => 'Terintegrasi, Akurat, dan Adaptif',
                'deskripsi' => 'Platform manajemen organisasi untuk mengelola data anggota, menu akses, dan pelaporan secara terpusat.',
                'footer_text' => 'Imam Starter-Kit',
                'fitur_login' => [
                    'Manajemen Anggota',
                    'Laporan Real-time',
                    'Keamanan Terjamin',
                    'Akses Multi-peran',
                ],
                'statistik_login' => [
                    ['nilai' => '500+', 'label' => 'Anggota Aktif'],
                    ['nilai' => '50+', 'label' => 'Departemen'],
                    ['nilai' => '99%', 'label' => 'Uptime'],
                ],
                'is_active' => true,
            ]
        );
    }
}
