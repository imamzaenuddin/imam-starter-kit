<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatuskaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'status_karyawan_id' => '1',
                'nama' => 'Tetap',
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_karyawan_id' => '2',
                'nama' => 'Kontrak',
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_karyawan_id' => '3',
                'nama' => 'Honorer',
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_statuskaryawan')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_statuskaryawan')->insert($chunk);
        }
    }
}