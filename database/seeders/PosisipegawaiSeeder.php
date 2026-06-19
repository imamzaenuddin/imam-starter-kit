<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosisipegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'posisi_pegawai_id' => 'H',
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
                'posisi_pegawai_id' => 'K',
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
                'posisi_pegawai_id' => 'T',
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
                'posisi_pegawai_id' => 'TT',
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
                'posisi_pegawai_id' => 'LB',
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
        DB::table('m_posisipegawai')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_posisipegawai')->insert($chunk);
        }
    }
}