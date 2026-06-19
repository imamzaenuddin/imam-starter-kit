<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TempattinggalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'tempat_tinggal_id' => 'A',
                'nama' => 'Asrama',
                'na' => 'N',
                'id_legacy' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tempat_tinggal_id' => 'S',
                'nama' => 'Sendiri',
                'na' => 'N',
                'id_legacy' => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tempat_tinggal_id' => 'K',
                'nama' => 'Keluarga',
                'na' => 'N',
                'id_legacy' => 'K',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tempat_tinggal_id' => 'I',
                'nama' => 'Indekos',
                'na' => 'N',
                'id_legacy' => 'I',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tempat_tinggal_id' => 'L',
                'nama' => 'Lain-lain',
                'na' => 'N',
                'id_legacy' => 'L',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_tempattinggal')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_tempattinggal')->insert($chunk);
        }
    }
}