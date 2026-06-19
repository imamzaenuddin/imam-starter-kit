<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PmbformsyaratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'pmb_form_syarat_id' => '1',
                'urutan' => '1',
                'nama' => 'Ijazah Legalisir Asli',
                'ada_script' => 'N',
                'script' => '',
                'keterangan' => '3 Lbr',
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pmb_form_syarat_id' => '2',
                'urutan' => '4',
                'nama' => 'Foto Warna 2x3',
                'ada_script' => 'N',
                'script' => '',
                'keterangan' => '2 Lbr (Mohon Di Scanning untuk data SIA/KTM/IJAZAH/WISUDA/BUKU ALUMNI)',
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pmb_form_syarat_id' => '3',
                'urutan' => '8',
                'nama' => 'Nilai UAN Minimum',
                'ada_script' => 'Y',
                'script' => 'NilaiSekolah>==INPUT=',
                'keterangan' => '',
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pmb_form_syarat_id' => '4',
                'urutan' => '7',
                'nama' => 'TPA',
                'ada_script' => 'N',
                'script' => '',
                'keterangan' => 'Nilai Akan di masukkan melalui Query Tes Potensi Akademik',
                'na' => 'Y',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pmb_form_syarat_id' => '5',
                'urutan' => '2',
                'nama' => 'Foto Copy Identitas',
                'ada_script' => 'N',
                'script' => '',
                'keterangan' => 'E-KTP',
                'na' => 'N',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pmb_form_syarat_id' => '6',
                'urutan' => '3',
                'nama' => 'Foto Copy KK',
                'ada_script' => 'N',
                'script' => '',
                'keterangan' => '1 Lbr',
                'na' => 'N',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pmb_form_syarat_id' => '7',
                'urutan' => '5',
                'nama' => 'Materai Rp6000',
                'ada_script' => 'N',
                'script' => '',
                'keterangan' => '1 Lbr',
                'na' => 'N',
                'id_legacy' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pmb_form_syarat_id' => '8',
                'urutan' => '6',
                'nama' => 'Biaya Pendaftaran',
                'ada_script' => 'N',
                'script' => '',
                'keterangan' => 'Tercetak melalui system SIA',
                'na' => 'N',
                'id_legacy' => '8',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pmb_form_syarat_id' => '9',
                'urutan' => '9',
                'nama' => 'Foto Copy Akta Kelahiran',
                'ada_script' => 'N',
                'script' => '',
                'keterangan' => '1lbr',
                'na' => 'N',
                'id_legacy' => '9',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_pmbformsyarat')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_pmbformsyarat')->insert($chunk);
        }
    }
}