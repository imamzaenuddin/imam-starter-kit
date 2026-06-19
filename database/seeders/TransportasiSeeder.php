<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransportasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'transportasi_id' => '1',
                'nama' => 'Jalan kaki',
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transportasi_id' => '2',
                'nama' => 'Kendaraan pribadi',
                'na' => 'Y',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transportasi_id' => '3',
                'nama' => 'Angkutan umum/bus/pete-pete',
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transportasi_id' => '4',
                'nama' => 'Mobil/bus antar jemput',
                'na' => 'N',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transportasi_id' => '5',
                'nama' => 'Kereta api',
                'na' => 'N',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transportasi_id' => '6',
                'nama' => 'Ojek',
                'na' => 'N',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transportasi_id' => '7',
                'nama' => 'Andong/bendi/sado/dokar/delman/becak',
                'na' => 'N',
                'id_legacy' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transportasi_id' => '8',
                'nama' => 'Perahu penyeberangan/rakit/getek',
                'na' => 'N',
                'id_legacy' => '8',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transportasi_id' => '11',
                'nama' => 'Kuda',
                'na' => 'N',
                'id_legacy' => '11',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transportasi_id' => '12',
                'nama' => 'Sepeda',
                'na' => 'N',
                'id_legacy' => '12',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transportasi_id' => '13',
                'nama' => 'Sepeda motor',
                'na' => 'N',
                'id_legacy' => '13',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transportasi_id' => '14',
                'nama' => 'Mobil pribadi',
                'na' => 'N',
                'id_legacy' => '14',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transportasi_id' => '99',
                'nama' => 'Lainnya',
                'na' => 'N',
                'id_legacy' => '99',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_transportasi')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_transportasi')->insert($chunk);
        }
    }
}