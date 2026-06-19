<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'trx_id' => '-1',
                'nama' => 'Potongan',
                'na' => 'N',
                'id_legacy' => '-1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'trx_id' => '1',
                'nama' => 'Biaya',
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_trx')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_trx')->insert($chunk);
        }
    }
}