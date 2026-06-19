<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PmbusmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'pmbusm_id' => 'PSI',
                'kode_id' => 'PI',
                'nama' => 'Psikotes',
                'cara_penempatan' => 'Urut',
                'keterangan' => '',
                'ada_script' => null,
                'login_buat' => 'admin',
                'tgl_buat' => '2010-01-01 00:58:48',
                'login_edit' => '0210010029',
                'tgl_edit' => '2021-04-24 14:11:01',
                'na' => 'Y',
                'id_legacy' => 'PSI',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pmbusm_id' => 'TPA',
                'kode_id' => 'PI',
                'nama' => 'Tes Potensi Akademik',
                'cara_penempatan' => 'Manual',
                'keterangan' => '',
                'ada_script' => null,
                'login_buat' => 'admin',
                'tgl_buat' => '2010-01-01 00:59:20',
                'login_edit' => 'admin',
                'tgl_edit' => '2011-01-02 21:22:06',
                'na' => 'N',
                'id_legacy' => 'TPA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_pmbusm')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_pmbusm')->insert($chunk);
        }
    }
}