<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BenuaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'benua_id' => '94',
                'nama_benua' => 'ASIA',
                'na' => 'N',
                'id_legacy' => '94',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'benua_id' => '95',
                'nama_benua' => 'AMERICA',
                'na' => 'N',
                'id_legacy' => '95',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'benua_id' => '96',
                'nama_benua' => 'EROPA',
                'na' => 'N',
                'id_legacy' => '96',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'benua_id' => '97',
                'nama_benua' => 'AUSTRALIA',
                'na' => 'N',
                'id_legacy' => '97',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'benua_id' => '98',
                'nama_benua' => 'AFRICA',
                'na' => 'N',
                'id_legacy' => '98',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_benua')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_benua')->insert($chunk);
        }
    }
}