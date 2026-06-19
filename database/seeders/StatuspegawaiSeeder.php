<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatuspegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'status_pegawai_id' => 'A',
                'no_id' => '1',
                'nama' => 'Aktif',
                'na' => 'N',
                'id_legacy' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_pegawai_id' => 'C',
                'no_id' => '2',
                'nama' => 'Cuti',
                'na' => 'N',
                'id_legacy' => 'C',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_pegawai_id' => 'K',
                'no_id' => '3',
                'nama' => 'Keluar',
                'na' => 'N',
                'id_legacy' => 'K',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_pegawai_id' => 'M',
                'no_id' => '4',
                'nama' => 'Almarhum',
                'na' => 'N',
                'id_legacy' => 'M',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_pegawai_id' => 'P',
                'no_id' => '5',
                'nama' => 'Pensiun',
                'na' => 'N',
                'id_legacy' => 'P',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_pegawai_id' => 'S',
                'no_id' => '6',
                'nama' => 'Studi Lanjut',
                'na' => 'N',
                'id_legacy' => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_pegawai_id' => 'T',
                'no_id' => '7',
                'nama' => 'Tugas di Instansi Lain',
                'na' => 'N',
                'id_legacy' => 'T',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_statuspegawai')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_statuspegawai')->insert($chunk);
        }
    }
}