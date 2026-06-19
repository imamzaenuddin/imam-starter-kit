<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusawalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'status_awal_id' => 'P',
                'status_awal' => '2',
                'nama' => 'Pindahan',
                'beli_formulir' => 'Y',
                'jalur_khusus' => 'N',
                'tanpa_test' => 'Y',
                'catatan' => '',
                'na' => 'Y',
                'id_legacy' => 'P',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_awal_id' => 'B',
                'status_awal' => '1',
                'nama' => 'Peserta Didik Baru',
                'beli_formulir' => 'Y',
                'jalur_khusus' => 'N',
                'tanpa_test' => 'N',
                'catatan' => '',
                'na' => 'N',
                'id_legacy' => 'B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_awal_id' => 'S',
                'status_awal' => '3',
                'nama' => 'Naik Kelas',
                'beli_formulir' => 'N',
                'jalur_khusus' => 'Y',
                'tanpa_test' => 'Y',
                'catatan' => 'Untuk siswa SMA berprestasi',
                'na' => 'Y',
                'id_legacy' => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_awal_id' => 'D',
                'status_awal' => '4',
                'nama' => 'Akselerasi',
                'beli_formulir' => 'N',
                'jalur_khusus' => 'N',
                'tanpa_test' => 'Y',
                'catatan' => '',
                'na' => 'Y',
                'id_legacy' => 'D',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_awal_id' => 'M',
                'status_awal' => '5',
                'nama' => 'Mengulang',
                'beli_formulir' => 'N',
                'jalur_khusus' => 'Y',
                'tanpa_test' => 'Y',
                'catatan' => 'Untuk calon mahasiswa asing',
                'na' => 'Y',
                'id_legacy' => 'M',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_awal_id' => 'LS',
                'status_awal' => '6',
                'nama' => 'Lanjut Semester',
                'beli_formulir' => 'N',
                'jalur_khusus' => 'N',
                'tanpa_test' => 'N',
                'catatan' => null,
                'na' => 'Y',
                'id_legacy' => 'LS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_awal_id' => 'PA',
                'status_awal' => '8',
                'nama' => 'Pindah Alih Bentuk',
                'beli_formulir' => 'N',
                'jalur_khusus' => 'N',
                'tanpa_test' => 'N',
                'catatan' => null,
                'na' => 'Y',
                'id_legacy' => 'PA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_awal_id' => 'A',
                'status_awal' => '11',
                'nama' => 'Alih Jenjang',
                'beli_formulir' => 'N',
                'jalur_khusus' => 'N',
                'tanpa_test' => 'Y',
                'catatan' => null,
                'na' => 'Y',
                'id_legacy' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_awal_id' => 'LJ',
                'status_awal' => '12',
                'nama' => 'Lintas Jalur',
                'beli_formulir' => 'N',
                'jalur_khusus' => 'N',
                'tanpa_test' => 'Y',
                'catatan' => null,
                'na' => 'Y',
                'id_legacy' => 'LJ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_awal_id' => 'RPLP',
                'status_awal' => '13',
                'nama' => 'RPL Perolehan SKS',
                'beli_formulir' => 'Y',
                'jalur_khusus' => 'N',
                'tanpa_test' => 'Y',
                'catatan' => 'Pengakuan atas pengalaman/pendidikan informal ke capaian pembelajaran pendidikan formal.',
                'na' => 'N',
                'id_legacy' => 'RPLP',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_awal_id' => 'PG',
                'status_awal' => '14',
                'nama' => 'Pendidikan Non-Gelar (Course)',
                'beli_formulir' => 'Y',
                'jalur_khusus' => 'N',
                'tanpa_test' => 'N',
                'catatan' => null,
                'na' => 'Y',
                'id_legacy' => 'PG',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_awal_id' => 'FT',
                'status_awal' => '15',
                'nama' => 'Fast Track',
                'beli_formulir' => 'Y',
                'jalur_khusus' => 'N',
                'tanpa_test' => 'N',
                'catatan' => null,
                'na' => 'Y',
                'id_legacy' => 'FT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_awal_id' => 'RPLT',
                'status_awal' => '16',
                'nama' => 'RPL Transfer SKS',
                'beli_formulir' => 'Y',
                'jalur_khusus' => 'N',
                'tanpa_test' => 'Y',
                'catatan' => 'Pengakuan atas hasil pembelajaran formal ke capaian pembelajaran. Transfer kredit mengakomodir 2 jenis : Alih jenjang dan Lintas Jalur',
                'na' => 'N',
                'id_legacy' => 'RPLT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_statusawal')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_statusawal')->insert($chunk);
        }
    }
}