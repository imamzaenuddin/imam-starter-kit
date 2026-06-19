<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenistransportasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'jenis_transportasi_id' => '1',
                'nama' => 'Jalan kaki',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_transportasi_id' => '2',
                'nama' => 'Angkutan umum/bus/pete-pete',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_transportasi_id' => '3',
                'nama' => 'Mobil/bus antar jemput',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_transportasi_id' => '4',
                'nama' => 'Kereta api',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_transportasi_id' => '5',
                'nama' => 'Ojek',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_transportasi_id' => '6',
                'nama' => 'Andong/bendi/sado/dokar/delman/becak',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_transportasi_id' => '7',
                'nama' => 'Perahu penyeberangan/rakit/getek',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_transportasi_id' => '8',
                'nama' => 'Kuda',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '8',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_transportasi_id' => '9',
                'nama' => 'Sepeda',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '9',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_transportasi_id' => '10',
                'nama' => 'Sepeda motor',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '10',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_transportasi_id' => '11',
                'nama' => 'Mobil pribadi',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '11',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis_transportasi_id' => '12',
                'nama' => 'Lainnya',
                'na' => 'N',
                'kode_id' => 'PI',
                'id_legacy' => '12',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_jenistransportasi')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_jenistransportasi')->insert($chunk);
        }
    }
}