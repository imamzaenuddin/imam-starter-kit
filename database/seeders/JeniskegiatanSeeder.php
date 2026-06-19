<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JeniskegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'jenis_kegiatan_id' => '110100',
                'nama' => 'Melaksanakan perkuliahan/tutorial/perkuliahan praktikum dan membimbing, menguji serta menyelenggarakan pendidikan di laboratorium, praktik keguruan, bengkel/studio/kebun percobaan/teknologi pengajaran',
                'kode_id' => 'PI',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '110100',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_kegiatan_id' => '110200',
                'nama' => 'Membimbing seminar mahasiswa
',
                'kode_id' => 'PI',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '110200',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_kegiatan_id' => '110300',
                'nama' => 'Membimbing kuliah kerja nyata, praktik kerja nyata',
                'kode_id' => 'PI',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '110300',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_kegiatan_id' => '110400',
                'nama' => 'Membimbing dan ikut membimbing dalam menghasilkan disertasi, tesis, skripsi, dan laporan akhir studi yang sesuai dengan bidang penugasannya
',
                'kode_id' => 'PI',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '110400',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_kegiatan_id' => '110500',
                'nama' => 'Bertugas sebagai penguji pada ujian akhir
',
                'kode_id' => 'PI',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '110500',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_kegiatan_id' => '110600',
                'nama' => 'Membina kegiatan mahasiswa di bidang akademik dan kemahasiswaan
',
                'kode_id' => 'PI',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '110600',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_kegiatan_id' => '110700',
                'nama' => 'Mengembangkan program kuliah yang mempunyai nilai kebaharuan metode atau substansi
',
                'kode_id' => 'PI',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '110700',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_kegiatan_id' => '110800',
                'nama' => 'Mengembangkan bahan pengajaran/bahan kuliah yang mempunyai nilai kebaharuan
',
                'kode_id' => 'PI',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '110800',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_kegiatan_id' => '110900',
                'nama' => 'Menyampaikan orasi ilmiah di tingkat perguruan tinggi
',
                'kode_id' => 'PI',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '110900',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_kegiatan_id' => '111000',
                'nama' => 'Menduduki jabatan pimpinan perguruan tinggi 
',
                'kode_id' => 'PI',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '111000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_kegiatan_id' => '111100',
                'nama' => 'Membimbing dosen yang mempunyai jabatan akademik lebih rendah
',
                'kode_id' => 'PI',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '111100',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_kegiatan_id' => '111200',
                'nama' => 'Melaksanakan kegiatan detasering dan pencangkokan di luar institusi tempat bekerja
',
                'kode_id' => 'PI',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '111200',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_jeniskegiatan')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_jeniskegiatan')->insert($chunk);
        }
    }
}