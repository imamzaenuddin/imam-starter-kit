<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisdosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'jenis_dosen_id' => 'DSN',
                'nama' => 'Dosen',
                'def' => 'Y',
                'na' => 'N',
                'id_legacy' => 'DSN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_dosen_id' => 'ASS',
                'nama' => 'Asisten',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => 'ASS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_jenisdosen')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_jenisdosen')->insert($chunk);
        }
    }
}