<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'saat_id' => '0',
                'nama' => 'Kapan saja',
                'na' => 'N',
                'id_legacy' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'saat_id' => '1',
                'nama' => 'Awal Sesi',
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'saat_id' => '2',
                'nama' => 'Tengah Sesi',
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'saat_id' => '3',
                'nama' => 'Akhir Sesi',
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_saat')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_saat')->insert($chunk);
        }
    }
}