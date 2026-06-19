<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatuskeluarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'status_keluar_id' => '1',
                'kode_id' => 'PI',
                'nama' => 'Lulus',
                'keluar' => 'N',
                'def' => 'N',
                'lulus' => 'N',
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_keluar_id' => '2',
                'kode_id' => 'PI',
                'nama' => 'Mutasi',
                'keluar' => 'N',
                'def' => 'N',
                'lulus' => 'N',
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_keluar_id' => '3',
                'kode_id' => 'PI',
                'nama' => 'Dikeluarkan',
                'keluar' => 'N',
                'def' => 'N',
                'lulus' => 'N',
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_keluar_id' => '4',
                'kode_id' => 'PI',
                'nama' => 'Mengundurkan diri',
                'keluar' => 'N',
                'def' => 'N',
                'lulus' => 'N',
                'na' => 'N',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_keluar_id' => '5',
                'kode_id' => 'PI',
                'nama' => 'Putus Sekolah',
                'keluar' => 'N',
                'def' => 'N',
                'lulus' => 'N',
                'na' => 'N',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_keluar_id' => '6',
                'kode_id' => 'PI',
                'nama' => 'Wafat',
                'keluar' => 'N',
                'def' => 'N',
                'lulus' => 'N',
                'na' => 'N',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_keluar_id' => '7',
                'kode_id' => 'PI',
                'nama' => 'Hilang',
                'keluar' => 'N',
                'def' => 'N',
                'lulus' => 'N',
                'na' => 'N',
                'id_legacy' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_keluar_id' => '8',
                'kode_id' => 'PI',
                'nama' => 'Alih Fungsi',
                'keluar' => 'N',
                'def' => 'N',
                'lulus' => 'N',
                'na' => 'N',
                'id_legacy' => '8',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_keluar_id' => '9',
                'kode_id' => 'PI',
                'nama' => 'Pensiun',
                'keluar' => 'N',
                'def' => 'N',
                'lulus' => 'N',
                'na' => 'N',
                'id_legacy' => '9',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_keluar_id' => '10',
                'kode_id' => 'PI',
                'nama' => 'Lainnya',
                'keluar' => 'N',
                'def' => 'N',
                'lulus' => 'N',
                'na' => 'N',
                'id_legacy' => '10',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_keluar_id' => '11',
                'kode_id' => 'PI',
                'nama' => 'Putus Studi',
                'keluar' => 'N',
                'def' => 'N',
                'lulus' => 'N',
                'na' => 'N',
                'id_legacy' => '11',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_statuskeluar')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_statuskeluar')->insert($chunk);
        }
    }
}