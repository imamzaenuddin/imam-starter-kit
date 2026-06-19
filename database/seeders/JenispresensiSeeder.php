<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenispresensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'jenis_presensi_id' => 'H',
                'nama' => 'Hadir',
                'nilai' => '1',
                'chr' => '51',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => 'H',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_presensi_id' => 'I',
                'nama' => 'Ijin',
                'nilai' => '1',
                'chr' => '38',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => 'I',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_presensi_id' => 'S',
                'nama' => 'Sakit',
                'nilai' => '1',
                'chr' => '58',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_presensi_id' => 'M',
                'nama' => 'Mangkir',
                'nilai' => '0',
                'chr' => '53',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => 'M',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_presensi_id' => '0',
                'nama' => 'Kosong',
                'nilai' => '0',
                'chr' => '0',
                'def' => 'Y',
                'na' => 'N',
                'id_legacy' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_jenispresensi')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_jenispresensi')->insert($chunk);
        }
    }
}