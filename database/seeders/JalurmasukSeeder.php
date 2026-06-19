<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JalurmasukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'jalur_masuk_id' => '1',
                'nama' => 'SBMPTN',
                'na' => 'Y',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_masuk_id' => '2',
                'nama' => 'SNMPTN',
                'na' => 'Y',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_masuk_id' => '3',
                'nama' => 'PMDK Penelusuran minat dan kemampuan (akademik)',
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_masuk_id' => '4',
                'nama' => 'Prestasi	Penelusuran minat dan kemampuan (prestasi)',
                'na' => 'N',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_masuk_id' => '5',
                'nama' => 'Seleksi Mandiri PTN',
                'na' => 'Y',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_masuk_id' => '6',
                'nama' => 'Seleksi Mandiri PTS',
                'na' => 'Y',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_masuk_id' => '7',
                'nama' => 'Ujian Masuk Bersama PTN (UMB-PT)',
                'na' => 'Y',
                'id_legacy' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_masuk_id' => '8',
                'nama' => 'Ujian Masuk Bersama PTS (UMB-PTS)',
                'na' => 'Y',
                'id_legacy' => '8',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_masuk_id' => '9',
                'nama' => 'Program Internasional',
                'na' => 'N',
                'id_legacy' => '9',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_masuk_id' => '11',
                'nama' => 'Program Kerjasama Perusahaan/Institusi/Pemerintah',
                'na' => 'N',
                'id_legacy' => '11',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_masuk_id' => '12',
                'nama' => 'Seleksi Mandiri',
                'na' => 'N',
                'id_legacy' => '12',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_masuk_id' => '13',
                'nama' => 'Ujian Masuk Bersama Lainnya',
                'na' => 'N',
                'id_legacy' => '13',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_masuk_id' => '14',
                'nama' => 'Seleksi Nasional Berdasarkan Tes (SNBT)',
                'na' => 'N',
                'id_legacy' => '14',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_masuk_id' => '15',
                'nama' => ' Seleksi Nasional Berdasarkan Prestasi (SNBP)',
                'na' => 'N',
                'id_legacy' => '15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_jalurmasuk')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_jalurmasuk')->insert($chunk);
        }
    }
}