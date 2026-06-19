<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JeniskeluarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'jenis_keluar_id' => '1',
                'nama' => 'Lulus',
                'tambahan' => 'N',
                'dep' => null,
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_keluar_id' => '2',
                'nama' => 'Mutasi',
                'tambahan' => 'N',
                'dep' => 'K',
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_keluar_id' => '3',
                'nama' => 'Dikeluarkan',
                'tambahan' => 'N',
                'dep' => 'K',
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_keluar_id' => '4',
                'nama' => 'Mengundurkan diri',
                'tambahan' => 'N',
                'dep' => 'K',
                'na' => 'N',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_keluar_id' => '5',
                'nama' => 'Putus Sekolah',
                'tambahan' => 'N',
                'dep' => null,
                'na' => 'N',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_keluar_id' => '6',
                'nama' => 'Wafat',
                'tambahan' => 'N',
                'dep' => null,
                'na' => 'N',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_keluar_id' => '7',
                'nama' => 'Hilang',
                'tambahan' => 'N',
                'dep' => null,
                'na' => 'N',
                'id_legacy' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_keluar_id' => '8',
                'nama' => 'Alih Fungsi',
                'tambahan' => 'N',
                'dep' => null,
                'na' => 'N',
                'id_legacy' => '8',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_keluar_id' => '9',
                'nama' => 'Pensiun',
                'tambahan' => 'N',
                'dep' => null,
                'na' => 'N',
                'id_legacy' => '9',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_keluar_id' => 'Z',
                'nama' => 'Lainnya',
                'tambahan' => 'N',
                'dep' => null,
                'na' => 'N',
                'id_legacy' => 'Z',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_jeniskeluar')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_jeniskeluar')->insert($chunk);
        }
    }
}