<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BajuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'baju_id' => 'S',
                'nama' => 'Small',
                'na' => 'N',
                'id_legacy' => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'baju_id' => 'M',
                'nama' => 'Middle',
                'na' => 'N',
                'id_legacy' => 'M',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'baju_id' => 'L',
                'nama' => 'Large',
                'na' => 'N',
                'id_legacy' => 'L',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'baju_id' => 'X',
                'nama' => 'Xtra',
                'na' => 'N',
                'id_legacy' => 'X',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'baju_id' => 'XL',
                'nama' => 'Xtra Large',
                'na' => 'N',
                'id_legacy' => 'XL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'baju_id' => 'XXL',
                'nama' => 'Double Xtr',
                'na' => 'N',
                'id_legacy' => 'XXL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'baju_id' => 'XS',
                'nama' => 'Xtra Small',
                'na' => 'N',
                'id_legacy' => 'XS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_baju')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_baju')->insert($chunk);
        }
    }
}