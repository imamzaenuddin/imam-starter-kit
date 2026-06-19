<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SumberinfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'info_id' => '12',
                'kode_id' => 'PI',
                'urutan' => '12',
                'nama' => 'Teman dari Orang Tua',
                'catatan' => null,
                'na' => 'N',
                'id_legacy' => '12',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '11',
                'kode_id' => 'PI',
                'urutan' => '11',
                'nama' => 'Orang Tua',
                'catatan' => null,
                'na' => 'N',
                'id_legacy' => '11',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '10',
                'kode_id' => 'PI',
                'urutan' => '10',
                'nama' => 'Pameran pendidikan',
                'catatan' => null,
                'na' => 'N',
                'id_legacy' => '10',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '9',
                'kode_id' => 'PI',
                'urutan' => '9',
                'nama' => 'Informasi online dari website',
                'catatan' => null,
                'na' => 'N',
                'id_legacy' => '9',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '8',
                'kode_id' => 'PI',
                'urutan' => '8',
                'nama' => 'Poster yang dipasang',
                'catatan' => null,
                'na' => 'N',
                'id_legacy' => '8',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '7',
                'kode_id' => 'PI',
                'urutan' => '7',
                'nama' => 'Spanduk yang terpasang',
                'catatan' => null,
                'na' => 'N',
                'id_legacy' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '6',
                'kode_id' => 'PI',
                'urutan' => '6',
                'nama' => 'Iklan di Radio',
                'catatan' => null,
                'na' => 'N',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '5',
                'kode_id' => 'PI',
                'urutan' => '5',
                'nama' => 'Iklan dari Media Cetak Lainnya',
                'catatan' => null,
                'na' => 'N',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '4',
                'kode_id' => 'PI',
                'urutan' => '4',
                'nama' => 'Iklan dari Seputar Indonesia',
                'catatan' => null,
                'na' => 'N',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '3',
                'kode_id' => 'PI',
                'urutan' => '3',
                'nama' => 'iklan dari Media Warta Kota',
                'catatan' => null,
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '2',
                'kode_id' => 'PI',
                'urutan' => '2',
                'nama' => 'Iklan dari Media Kompas',
                'catatan' => null,
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '1',
                'kode_id' => 'PI',
                'urutan' => '1',
                'nama' => 'Presentasi/Undangan ke Sekolah',
                'catatan' => null,
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '13',
                'kode_id' => 'PI',
                'urutan' => '13',
                'nama' => 'Teman / Saudara',
                'catatan' => null,
                'na' => 'N',
                'id_legacy' => '13',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '14',
                'kode_id' => 'PI',
                'urutan' => '14',
                'nama' => 'Informasi dari Mahasiswa Lain',
                'catatan' => null,
                'na' => 'N',
                'id_legacy' => '14',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '15',
                'kode_id' => 'PI',
                'urutan' => '16',
                'nama' => 'Lain-Lain',
                'catatan' => '',
                'na' => 'N',
                'id_legacy' => '15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'info_id' => '18',
                'kode_id' => 'PI',
                'urutan' => '15',
                'nama' => 'Brosur',
                'catatan' => '',
                'na' => 'N',
                'id_legacy' => '18',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_sumberinfo')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_sumberinfo')->insert($chunk);
        }
    }
}