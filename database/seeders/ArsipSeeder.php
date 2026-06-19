<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArsipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'arsip_id' => '1',
                'nama' => 'e-KTP',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'arsip_id' => '2',
                'nama' => 'Kartu Keluarga',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'arsip_id' => '3',
                'nama' => 'Ijazah SMU/SMK/MA/S1/S2',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'arsip_id' => '4',
                'nama' => 'Transkrip / Daftar Nilai',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'arsip_id' => '5',
                'nama' => 'Akte Kelahiran',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'arsip_id' => '99',
                'nama' => 'Lain-Lain',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '99',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'arsip_id' => '6',
                'nama' => 'SIM/Kartu Mahasiswa',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'arsip_id' => '7',
                'nama' => 'Sertifikat Vaksinasi',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'arsip_id' => '100',
                'nama' => 'SKTM',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '100',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'arsip_id' => '111',
                'nama' => 'hgtgt',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '111',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'arsip_id' => '111111',
                'nama' => '../../',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '111111',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_arsip')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_arsip')->insert($chunk);
        }
    }
}