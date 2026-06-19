<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PegawainilaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'pegawai_nilai_id' => '1',
                'kode_id' => 'PI',
                'nama' => 'Sangat Kurang',
                'bobot' => '1',
                'deskripsi' => null,
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pegawai_nilai_id' => '2',
                'kode_id' => 'PI',
                'nama' => 'Kurang',
                'bobot' => '2',
                'deskripsi' => null,
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pegawai_nilai_id' => '3',
                'kode_id' => 'PI',
                'nama' => 'Cukup',
                'bobot' => '3',
                'deskripsi' => null,
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pegawai_nilai_id' => '4',
                'kode_id' => 'PI',
                'nama' => 'Baik',
                'bobot' => '4',
                'deskripsi' => null,
                'na' => 'N',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pegawai_nilai_id' => '5',
                'kode_id' => 'PI',
                'nama' => 'Sangat Baik',
                'bobot' => '5',
                'deskripsi' => null,
                'na' => 'N',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_pegawainilai')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_pegawainilai')->insert($chunk);
        }
    }
}