<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisliburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'jenis_libur_id' => 'N',
                'nama' => 'Nasional',
                'warna' => '#0000FF',
                'kode_id' => 'PI',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_libur_id' => 'AS',
                'nama' => 'Antar Semester',
                'warna' => '#00FF00',
                'kode_id' => 'PI',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => 'AS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_libur_id' => 'MG',
                'nama' => 'Mingguan',
                'warna' => '#FF0000',
                'kode_id' => 'PI',
                'keterangan' => 'Setiap minggu',
                'na' => 'N',
                'id_legacy' => 'MG',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_libur_id' => '01',
                'nama' => 'Libur Nasional',
                'warna' => 'FF0000',
                'kode_id' => 'PI',
                'keterangan' => 'Tahun Baru 2011',
                'na' => 'N',
                'id_legacy' => '01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_libur_id' => '02',
                'nama' => 'Libur Kuliah',
                'warna' => '1A12FF',
                'kode_id' => 'PI',
                'keterangan' => 'Jika ada kegiatan Umum
',
                'na' => 'N',
                'id_legacy' => '02',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_jenislibur')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_jenislibur')->insert($chunk);
        }
    }
}