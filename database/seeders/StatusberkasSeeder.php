<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusberkasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'status_berkas_id' => '1',
                'nama' => 'Draf (Draft)',
                'verifikasi' => 'N',
                'status' => 'secondary',
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_berkas_id' => '2',
                'nama' => 'Diajukan (Submitted)',
                'verifikasi' => 'Y',
                'status' => 'info',
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_berkas_id' => '3',
                'nama' => 'Dalam Proses (In Process)',
                'verifikasi' => 'N',
                'status' => 'warning',
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_berkas_id' => '4',
                'nama' => 'Ditangguhkan (Hold)',
                'verifikasi' => 'Y',
                'status' => 'warning',
                'na' => 'N',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_berkas_id' => '5',
                'nama' => 'Diterima (Accepted)',
                'verifikasi' => 'N',
                'status' => 'warning',
                'na' => 'N',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_berkas_id' => '6',
                'nama' => 'Ditolak (Rejected)',
                'verifikasi' => 'Y',
                'status' => 'danger',
                'na' => 'N',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_berkas_id' => '7',
                'nama' => 'Selesai (Completed)',
                'verifikasi' => 'Y',
                'status' => 'success',
                'na' => 'N',
                'id_legacy' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_berkas_id' => '8',
                'nama' => 'Diarsipkan (Archived)',
                'verifikasi' => 'N',
                'status' => 'Primary',
                'na' => 'N',
                'id_legacy' => '8',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_statusberkas')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_statusberkas')->insert($chunk);
        }
    }
}