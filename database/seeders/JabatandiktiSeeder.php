<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JabatandiktiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'jabatan_dikti_id' => '01',
                'nama' => 'AAM',
                'keterangan' => 'Asisten Ahli Muda',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => '01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jabatan_dikti_id' => '02',
                'nama' => 'AA',
                'keterangan' => 'Asisten Ahli',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => '02',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jabatan_dikti_id' => '03',
                'nama' => 'LMu',
                'keterangan' => 'Lektor Muda',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => '03',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jabatan_dikti_id' => '04',
                'nama' => 'LMa',
                'keterangan' => 'Lektor Madya',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => '04',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jabatan_dikti_id' => '05',
                'nama' => 'L',
                'keterangan' => 'Lektor',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => '05',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jabatan_dikti_id' => '06',
                'nama' => 'LKM',
                'keterangan' => 'Lektor Kepala Muda',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => '06',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jabatan_dikti_id' => '07',
                'nama' => 'LK',
                'keterangan' => 'Lektor Ketua',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => '07',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jabatan_dikti_id' => '08',
                'nama' => 'GBM',
                'keterangan' => 'Guru Besar Madya',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => '08',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jabatan_dikti_id' => '09',
                'nama' => 'GB',
                'keterangan' => 'Guru Besar',
                'def' => 'N',
                'na' => 'N',
                'id_legacy' => '09',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_jabatandikti')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_jabatandikti')->insert($chunk);
        }
    }
}