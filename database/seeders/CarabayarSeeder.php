<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarabayarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'cara_bayar_id' => '1',
                'kode_id' => 'PI',
                'nama' => 'Mobile Banking',
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cara_bayar_id' => '2',
                'kode_id' => 'PI',
                'nama' => 'Internet Banking',
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cara_bayar_id' => '3',
                'kode_id' => 'PI',
                'nama' => 'SMS Banking',
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cara_bayar_id' => '4',
                'kode_id' => 'PI',
                'nama' => 'Phone Banking',
                'na' => 'N',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cara_bayar_id' => '5',
                'kode_id' => 'PI',
                'nama' => 'ATM',
                'na' => 'N',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cara_bayar_id' => '6',
                'kode_id' => 'PI',
                'nama' => 'Transfer Bank',
                'na' => 'N',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cara_bayar_id' => '0',
                'kode_id' => 'PI',
                'nama' => 'Setor Tunai',
                'na' => 'N',
                'id_legacy' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_carabayar')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_carabayar')->insert($chunk);
        }
    }
}