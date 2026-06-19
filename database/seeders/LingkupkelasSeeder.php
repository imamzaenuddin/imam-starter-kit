<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LingkupkelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'lingkup_kelas_id' => '1',
                'lingkup_kelas_kode' => '1',
                'nama' => 'Internal',
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'lingkup_kelas_id' => '2',
                'lingkup_kelas_kode' => '2',
                'nama' => 'External',
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'lingkup_kelas_id' => '3',
                'lingkup_kelas_kode' => '3',
                'nama' => 'Campuran',
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_lingkupkelas')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_lingkupkelas')->insert($chunk);
        }
    }
}