<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatuskrsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'status_krs_id' => 'A',
                'nama' => 'Aktif',
                'ikut' => 'Y',
                'hitung' => 'Y',
                'na' => 'N',
                'id_legacy' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_krs_id' => 'T',
                'nama' => 'Tunda',
                'ikut' => 'Y',
                'hitung' => 'N',
                'na' => 'N',
                'id_legacy' => 'T',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_krs_id' => 'M',
                'nama' => 'Mundur',
                'ikut' => 'N',
                'hitung' => 'N',
                'na' => 'N',
                'id_legacy' => 'M',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_krs_id' => 'S',
                'nama' => 'Skors',
                'ikut' => 'N',
                'hitung' => 'N',
                'na' => 'N',
                'id_legacy' => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_statuskrs')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_statuskrs')->insert($chunk);
        }
    }
}