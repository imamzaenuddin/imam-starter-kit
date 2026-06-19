<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitorganisasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'unit_organisasi_id' => '1',
                'nama' => 'YMII',
                'kode_id' => null,
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unit_organisasi_id' => '2',
                'nama' => 'LPMIK',
                'kode_id' => null,
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unit_organisasi_id' => '3',
                'nama' => 'STMIK',
                'kode_id' => null,
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unit_organisasi_id' => '4',
                'nama' => 'AMIK',
                'kode_id' => null,
                'na' => 'N',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unit_organisasi_id' => '5',
                'nama' => 'TK',
                'kode_id' => null,
                'na' => 'N',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unit_organisasi_id' => '6',
                'nama' => 'STBA',
                'kode_id' => null,
                'na' => 'N',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unit_organisasi_id' => '7',
                'nama' => 'AKSEMA',
                'kode_id' => null,
                'na' => 'N',
                'id_legacy' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_unitorganisasi')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_unitorganisasi')->insert($chunk);
        }
    }
}