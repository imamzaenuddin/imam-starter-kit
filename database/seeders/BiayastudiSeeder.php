<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BiayastudiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'biaya_studi_id' => 'O',
                'nama' => 'Orang Tua',
                'beasiswa' => 'N',
                'beasiswa_id' => '1',
                'na' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_studi_id' => 'W',
                'nama' => 'Wali',
                'beasiswa' => 'N',
                'beasiswa_id' => '1',
                'na' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_studi_id' => 'I ',
                'nama' => 'Ikatan Dinas',
                'beasiswa' => 'N',
                'beasiswa_id' => '2',
                'na' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_studi_id' => 'S',
                'nama' => 'Sendiri/Mandiri',
                'beasiswa' => 'N',
                'beasiswa_id' => '1',
                'na' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_studi_id' => 'B',
                'nama' => 'Beasiswa',
                'beasiswa' => 'Y',
                'beasiswa_id' => '2',
                'na' => 'Y',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_studi_id' => 'L',
                'nama' => 'Lain2',
                'beasiswa' => 'N',
                'beasiswa_id' => '1',
                'na' => 'Y',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_studi_id' => 'KIP',
                'nama' => 'Kartu Indonesia Pintar',
                'beasiswa' => 'Y',
                'beasiswa_id' => '3',
                'na' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_studi_id' => 'UKT',
                'nama' => 'Uang Kuliah Tungga (UKT)',
                'beasiswa' => 'Y',
                'beasiswa_id' => '3',
                'na' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_studi_id' => 'Y',
                'nama' => 'Yayasan',
                'beasiswa' => 'Y',
                'beasiswa_id' => '2',
                'na' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_studi_id' => 'A',
                'nama' => 'Aspirasi Dewan',
                'beasiswa' => 'Y',
                'beasiswa_id' => '3',
                'na' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_studi_id' => 'DD',
                'nama' => 'Dinas Pemdidikan (DISDIK)',
                'beasiswa' => 'Y',
                'beasiswa_id' => '3',
                'na' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_studi_id' => 'AY',
                'nama' => 'Aspirasi YMII',
                'beasiswa' => 'Y',
                'beasiswa_id' => '2',
                'na' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_studi_id' => 'BW',
                'nama' => 'Beasiswa Walikota',
                'beasiswa' => 'Y',
                'beasiswa_id' => '2',
                'na' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'biaya_studi_id' => 'CBW',
                'nama' => 'Beasiswa Walikota',
                'beasiswa' => 'Y',
                'beasiswa_id' => '2',
                'na' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_biayastudi')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_biayastudi')->insert($chunk);
        }
    }
}