<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgamaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'agama_id' => '2',
                'nama' => 'Kristen',
                'na' => 'N',
                'kode_id' => '',
                'id_legacy' => 'KR',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'agama_id' => '3',
                'nama' => 'Katholik',
                'na' => 'N',
                'kode_id' => '',
                'id_legacy' => 'K',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'agama_id' => '1',
                'nama' => 'Islam',
                'na' => 'N',
                'kode_id' => '',
                'id_legacy' => 'I',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'agama_id' => '5',
                'nama' => 'Budha',
                'na' => 'N',
                'kode_id' => '',
                'id_legacy' => 'B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'agama_id' => '4',
                'nama' => 'Hindu',
                'na' => 'N',
                'kode_id' => '',
                'id_legacy' => 'H',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'agama_id' => '99',
                'nama' => 'Lain2',
                'na' => 'N',
                'kode_id' => '',
                'id_legacy' => 'L',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'agama_id' => '6',
                'nama' => 'Konghucu',
                'na' => 'N',
                'kode_id' => '',
                'id_legacy' => 'KH',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_agama')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_agama')->insert($chunk);
        }
    }
}