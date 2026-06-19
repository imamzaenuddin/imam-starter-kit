<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusdosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'status_dosen_id' => 'H',
                'no_id' => '3',
                'nama' => 'Honorer',
                'def' => 'N',
                'honor_mengajar' => 'Y',
                'na' => 'N',
                'id_legacy' => 'H',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_dosen_id' => 'K',
                'no_id' => '2',
                'nama' => 'Kontrak',
                'def' => 'N',
                'honor_mengajar' => 'N',
                'na' => 'N',
                'id_legacy' => 'K',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_dosen_id' => 'T',
                'no_id' => '1',
                'nama' => 'Tetap',
                'def' => 'N',
                'honor_mengajar' => 'N',
                'na' => 'N',
                'id_legacy' => 'T',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_dosen_id' => 'TT',
                'no_id' => '4',
                'nama' => 'Tidak Tetap',
                'def' => 'N',
                'honor_mengajar' => 'Y',
                'na' => 'N',
                'id_legacy' => 'TT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_dosen_id' => 'LB',
                'no_id' => '5',
                'nama' => 'Luar Biasa',
                'def' => 'N',
                'honor_mengajar' => 'N',
                'na' => 'N',
                'id_legacy' => 'LB',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_statusdosen')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_statusdosen')->insert($chunk);
        }
    }
}