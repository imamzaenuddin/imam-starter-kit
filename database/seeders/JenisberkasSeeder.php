<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisberkasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'jenis_berkas_id' => '1',
                'nama' => 'Word/PDF Skripsi/TA  ( FINAL/ACC Lengkap )',
                'bentuk' => '0',
                'type' => null,
                'ukuran' => '8120',
                'na' => 'N',
                'wajib' => 'Y',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_berkas_id' => '2',
                'nama' => 'Monitoring dan Lembar Pengesahan Pembimbing  (bertandatangan)',
                'bentuk' => '0',
                'type' => null,
                'ukuran' => '1024',
                'na' => 'Y',
                'wajib' => 'Y',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_berkas_id' => '3',
                'nama' => 'Berita Acara dan Lembar Pengesahan Sidang (bertandatangan)',
                'bentuk' => '0',
                'type' => null,
                'ukuran' => '1024',
                'na' => 'Y',
                'wajib' => 'Y',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_berkas_id' => '4',
                'nama' => 'Source Code / Koding Program',
                'bentuk' => '0',
                'type' => null,
                'ukuran' => '1024',
                'na' => 'N',
                'wajib' => 'N',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_berkas_id' => '6',
                'nama' => 'Surat Pernyataan Keaslian (Bermaterai)',
                'bentuk' => '0',
                'type' => null,
                'ukuran' => '1024',
                'na' => 'N',
                'wajib' => 'Y',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_berkas_id' => '7',
                'nama' => 'Daftar Nilai Praktek Kerja Lapangan',
                'bentuk' => '0',
                'type' => null,
                'ukuran' => '1024',
                'na' => 'N',
                'wajib' => 'Y',
                'id_legacy' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_berkas_id' => '8',
                'nama' => 'Surat Balasan Observasi/Penelitian (Berstample)',
                'bentuk' => '0',
                'type' => null,
                'ukuran' => '1024',
                'na' => 'N',
                'wajib' => 'Y',
                'id_legacy' => '8',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_berkas_id' => '9',
                'nama' => 'Foto Kegiatan Obserfasi/Wawancara/Kuesioner(opsional)',
                'bentuk' => '0',
                'type' => null,
                'ukuran' => '1024',
                'na' => 'N',
                'wajib' => 'N',
                'id_legacy' => '9',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_berkas_id' => '5',
                'nama' => 'DataBase',
                'bentuk' => '0',
                'type' => null,
                'ukuran' => '1024',
                'na' => 'N',
                'wajib' => 'N',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_berkas_id' => '10',
                'nama' => 'Berita acara serah terima Alat (Skripsi menggunakan perangkat)',
                'bentuk' => '0',
                'type' => null,
                'ukuran' => '1024',
                'na' => 'N',
                'wajib' => 'N',
                'id_legacy' => '10',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_jenisberkas')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_jenisberkas')->insert($chunk);
        }
    }
}