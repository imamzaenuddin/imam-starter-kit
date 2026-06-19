<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatuskelompokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'status_kelompok_id' => '1',
                'program_id' => 'PAG',
                'nama' => 'PAGI',
                'jam_mulai' => '08:29:00',
                'jam_selesai' => '11:30:00',
                'kode_id' => 'PI',
                'def' => 'N',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_kelompok_id' => '2',
                'program_id' => 'MLM',
                'nama' => 'MALAM',
                'jam_mulai' => '18:30:00',
                'jam_selesai' => '21:30:00',
                'kode_id' => 'PI',
                'def' => 'N',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_kelompok_id' => '3',
                'program_id' => 'SHF',
                'nama' => 'SHIFT',
                'jam_mulai' => '08:29:00',
                'jam_selesai' => '21:30:00',
                'kode_id' => 'PI',
                'def' => 'N',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_kelompok_id' => '4',
                'program_id' => 'KAR',
                'nama' => 'KARYAWAN',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '23:59:00',
                'kode_id' => 'PI',
                'def' => 'N',
                'keterangan' => null,
                'na' => 'N',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_statuskelompok')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_statuskelompok')->insert($chunk);
        }
    }
}