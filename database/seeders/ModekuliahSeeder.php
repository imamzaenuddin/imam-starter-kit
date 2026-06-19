<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModekuliahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'mode_kuliah_id' => '1',
                'mode_kuliah_kode' => 'O',
                'nama' => 'Online',
                'keterangan' => null,
                'dokumentasi' => null,
                'bukti_dokumentasi' => 'Y',
                'link' => null,
                'bukti_link' => 'Y',
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'mode_kuliah_id' => '2',
                'mode_kuliah_kode' => 'F',
                'nama' => 'Offline',
                'keterangan' => null,
                'dokumentasi' => null,
                'bukti_dokumentasi' => 'N',
                'link' => null,
                'bukti_link' => 'N',
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'mode_kuliah_id' => '3',
                'mode_kuliah_kode' => 'M',
                'nama' => 'Campuran',
                'keterangan' => null,
                'dokumentasi' => null,
                'bukti_dokumentasi' => 'N',
                'link' => null,
                'bukti_link' => 'Y',
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_modekuliah')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_modekuliah')->insert($chunk);
        }
    }
}