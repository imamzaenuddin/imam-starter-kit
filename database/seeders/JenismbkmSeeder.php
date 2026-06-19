<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenismbkmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'jenis_mbkm_id' => '1',
                'nama' => 'Flagship',
                'login_buat' => null,
                'tanggal_buat' => null,
                'login_edit' => null,
                'tanggal_edit' => null,
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_mbkm_id' => '2',
                'nama' => 'Mandiri',
                'login_buat' => null,
                'tanggal_buat' => null,
                'login_edit' => null,
                'tanggal_edit' => null,
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_jenismbkm')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_jenismbkm')->insert($chunk);
        }
    }
}