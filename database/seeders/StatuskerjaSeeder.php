<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatuskerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'status_kerja_id' => 'A',
                'nama' => 'Dosen Tetap',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_kerja_id' => 'B',
                'nama' => 'Dosen PNS Dipekerjakan',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => 'B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_kerja_id' => 'C',
                'nama' => 'Dosen Honorer PTN',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => 'C',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_kerja_id' => 'D',
                'nama' => 'Dosen Honorer Non PTN',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => 'D',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_kerja_id' => 'E',
                'nama' => 'Dosen Kontrak',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => 'E',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_statuskerja')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_statuskerja')->insert($chunk);
        }
    }
}