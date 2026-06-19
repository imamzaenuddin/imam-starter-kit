<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PendidikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'pendidikan_id' => '40',
                'nama' => 'S3',
                'na' => 'N',
                'id_legacy' => '40',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '15',
                'nama' => 'S2',
                'na' => 'N',
                'id_legacy' => '15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '30',
                'nama' => 'S1',
                'na' => 'N',
                'id_legacy' => '30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '23',
                'nama' => 'D4',
                'na' => 'N',
                'id_legacy' => '23',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '22',
                'nama' => 'D3',
                'na' => 'N',
                'id_legacy' => '22',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '21',
                'nama' => 'D2',
                'na' => 'N',
                'id_legacy' => '21',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '20',
                'nama' => 'D1',
                'na' => 'N',
                'id_legacy' => '20',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '17',
                'nama' => 'Spesialis 1',
                'na' => 'N',
                'id_legacy' => '17',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '37',
                'nama' => 'Spesialis 2',
                'na' => 'N',
                'id_legacy' => '37',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '19',
                'nama' => 'Profesi',
                'na' => 'N',
                'id_legacy' => '19',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '6',
                'nama' => 'SMU/SMA/ Sederajat',
                'na' => 'N',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '6',
                'nama' => 'SMK',
                'na' => 'Y',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '5',
                'nama' => 'SMP / Sederajat',
                'na' => 'N',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '4',
                'nama' => 'SD / Sederajat',
                'na' => 'N',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '1',
                'nama' => 'PAUD',
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '0',
                'nama' => 'Tidak Sekolah',
                'na' => 'N',
                'id_legacy' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '2',
                'nama' => 'TK / Sederajat',
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '3',
                'nama' => 'Putus SD',
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '7',
                'nama' => 'Paket A',
                'na' => 'N',
                'id_legacy' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '8',
                'nama' => 'Paket B',
                'na' => 'N',
                'id_legacy' => '8',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '9',
                'nama' => 'Paket C',
                'na' => 'N',
                'id_legacy' => '9',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '31',
                'nama' => 'Profesi',
                'na' => 'N',
                'id_legacy' => '31',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '91',
                'nama' => 'Informal',
                'na' => 'N',
                'id_legacy' => '91',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '36',
                'nama' => 'S2 Terapan',
                'na' => 'N',
                'id_legacy' => '36',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '41',
                'nama' => 'S3 Terapan',
                'na' => 'N',
                'id_legacy' => '41',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '90',
                'nama' => 'Non Formal',
                'na' => 'N',
                'id_legacy' => '90',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pendidikan_id' => '99',
                'nama' => 'Lainnya',
                'na' => 'N',
                'id_legacy' => '99',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_pendidikan')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_pendidikan')->insert($chunk);
        }
    }
}