<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisjadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'jenis_jadwal_id' => 'K',
                'nama' => 'Kuliah',
                'tambahan' => 'N',
                'dep' => null,
                'na' => 'N',
                'id_legacy' => 'K',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_jadwal_id' => 'R',
                'nama' => 'Responsi',
                'tambahan' => 'Y',
                'dep' => 'K',
                'na' => 'N',
                'id_legacy' => 'R',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_jadwal_id' => 'L',
                'nama' => 'Lab',
                'tambahan' => 'Y',
                'dep' => 'K',
                'na' => 'N',
                'id_legacy' => 'L',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_jadwal_id' => 'T',
                'nama' => 'Tutorial',
                'tambahan' => 'Y',
                'dep' => 'K',
                'na' => 'N',
                'id_legacy' => 'T',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_jenisjadwal')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_jenisjadwal')->insert($chunk);
        }
    }
}