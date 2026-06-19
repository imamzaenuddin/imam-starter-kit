<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatussipilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'status_sipil_id' => 'B',
                'nama' => 'Belum Menikah',
                'na' => 'N',
                'id_legacy' => 'B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_sipil_id' => 'K',
                'nama' => 'Menikah',
                'na' => 'N',
                'id_legacy' => 'K',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_sipil_id' => 'D',
                'nama' => 'Duda/Janda',
                'na' => 'N',
                'id_legacy' => 'D',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_statussipil')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_statussipil')->insert($chunk);
        }
    }
}