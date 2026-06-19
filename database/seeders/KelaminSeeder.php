<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KelaminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'kelamin_id' => 'L',
                'nama' => 'Laki-Laki',
                'na' => 'N',
                'id_legacy' => 'L',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kelamin_id' => 'P',
                'nama' => 'Perempuan',
                'na' => 'N',
                'id_legacy' => 'P',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_kelamin')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_kelamin')->insert($chunk);
        }
    }
}