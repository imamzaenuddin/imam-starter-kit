<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'program_id' => 'R',
                'nama' => 'Regular',
                'kode_id' => 'PI',
                'def' => 'N',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => 'R',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'program_id' => 'K',
                'nama' => 'Karyawan',
                'kode_id' => 'PI',
                'def' => 'N',
                'keterangan' => null,
                'na' => 'Y',
                'id_legacy' => 'K',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_program')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_program')->insert($chunk);
        }
    }
}