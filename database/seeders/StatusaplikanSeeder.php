<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusaplikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'status_aplikan_id' => 'APL',
                'urutan' => '1',
                'nama' => 'Tahap Aplikasi',
                'kode_id' => 'PI',
                'status_aplikan_before' => null,
                'status_aplikan_after' => 'BLI',
                'keterangan' => 'Tahap Peminat/Aplikan mengajukan minat untuk menjadi calon mahasiswa ',
                'na' => 'N',
                'id_legacy' => 'APL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_aplikan_id' => 'BLI',
                'urutan' => '2',
                'nama' => 'Tahap Pembelian Formulir',
                'kode_id' => 'PI',
                'status_aplikan_before' => 'APL',
                'status_aplikan_after' => 'DFT',
                'keterangan' => 'Tahap Peminat/Aplikan membeli/mengambil formulir pendaftaran.',
                'na' => 'N',
                'id_legacy' => 'BLI',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_aplikan_id' => 'DFT',
                'urutan' => '3',
                'nama' => 'Tahap Pendaftaran',
                'kode_id' => 'PI',
                'status_aplikan_before' => 'BLI',
                'status_aplikan_after' => 'USM',
                'keterangan' => 'Tahap aplikan/peminat mendaftarkan diri menjadi calon mahasiswa',
                'na' => 'N',
                'id_legacy' => 'DFT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_aplikan_id' => 'USM',
                'urutan' => '4',
                'nama' => 'Tahap Ujian Saringan Masuk',
                'kode_id' => 'PI',
                'status_aplikan_before' => 'DFT',
                'status_aplikan_after' => 'LLS',
                'keterangan' => 'Tahap calon mahasiswa menghadiri ujian saringan masuk',
                'na' => 'N',
                'id_legacy' => 'USM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_aplikan_id' => 'LLS',
                'urutan' => '5',
                'nama' => 'Tahap Lulus Ujian',
                'kode_id' => 'PI',
                'status_aplikan_before' => 'USM',
                'status_aplikan_after' => 'REG',
                'keterangan' => 'Tahap mahasiswa lulus ujian saringan masuk',
                'na' => 'N',
                'id_legacy' => 'LLS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_aplikan_id' => 'REG',
                'urutan' => '6',
                'nama' => 'Tahap Registrasi Mahasiswa',
                'kode_id' => 'PI',
                'status_aplikan_before' => 'LLS',
                'status_aplikan_after' => null,
                'keterangan' => 'Tahap calon mahasiswa melakukan registrasi ulang',
                'na' => 'N',
                'id_legacy' => 'REG',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_statusaplikan')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_statusaplikan')->insert($chunk);
        }
    }
}