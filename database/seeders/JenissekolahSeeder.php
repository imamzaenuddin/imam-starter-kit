<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenissekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'jenis_sekolah_id' => 'KRISTEN',
                'nama' => 'Kristen/Katolik Non Penabur',
                'satu_group' => 'N',
                'na' => 'N',
                'id_legacy' => 'KRISTEN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_sekolah_id' => 'UMUM',
                'nama' => 'Umum',
                'satu_group' => 'N',
                'na' => 'N',
                'id_legacy' => 'UMUM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_sekolah_id' => 'NEGERI',
                'nama' => 'Negeri',
                'satu_group' => 'N',
                'na' => 'N',
                'id_legacy' => 'NEGERI',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_sekolah_id' => 'LN',
                'nama' => 'Luar Negeri',
                'satu_group' => 'N',
                'na' => 'N',
                'id_legacy' => 'LN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_sekolah_id' => 'ISLAM',
                'nama' => 'Islam',
                'satu_group' => 'N',
                'na' => 'N',
                'id_legacy' => 'ISLAM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_jenissekolah')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_jenissekolah')->insert($chunk);
        }
    }
}