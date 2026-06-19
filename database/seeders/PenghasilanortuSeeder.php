<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenghasilanortuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'penghasilan_ortu_id' => '11',
                'nama' => ' < Rp. 500.000,00',
                'na' => 'N',
                'id_legacy' => '11',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'penghasilan_ortu_id' => '12',
                'nama' => 'Rp. 500,000,00 - Rp. 999,999,00',
                'na' => 'N',
                'id_legacy' => '12',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'penghasilan_ortu_id' => '13',
                'nama' => 'Rp. 1.000.000,00 - Rp. 1.999.999,00',
                'na' => 'N',
                'id_legacy' => '13',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'penghasilan_ortu_id' => '14',
                'nama' => 'Rp. 2.000.000,00 - Rp. 4.999.999,00 ',
                'na' => 'N',
                'id_legacy' => '14',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'penghasilan_ortu_id' => '5',
                'nama' => 'Pensiun',
                'na' => 'N',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'penghasilan_ortu_id' => '0',
                'nama' => '0',
                'na' => 'N',
                'id_legacy' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'penghasilan_ortu_id' => '15',
                'nama' => 'Rp. 5.000.000,00 - Rp. 20.000.000,00 ',
                'na' => 'N',
                'id_legacy' => '15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'penghasilan_ortu_id' => '16',
                'nama' => '> Rp. 20.000.000,00 ',
                'na' => 'N',
                'id_legacy' => '16',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_penghasilanortu')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_penghasilanortu')->insert($chunk);
        }
    }
}