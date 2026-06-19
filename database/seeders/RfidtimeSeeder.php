<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RfidtimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'rfid_time_id' => '1',
                'nama' => 'Pagi1',
                'jam_mulai' => '08:30:00',
                'jam_selesai' => '10:00:00',
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rfid_time_id' => '2',
                'nama' => 'Pagi2',
                'jam_mulai' => '10:00:00',
                'jam_selesai' => '11:30:00',
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rfid_time_id' => '3',
                'nama' => 'Mlm1',
                'jam_mulai' => '18:30:00',
                'jam_selesai' => '20:00:00',
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rfid_time_id' => '4',
                'nama' => 'Mlm2',
                'jam_mulai' => '20:00:00',
                'jam_selesai' => '21:30:00',
                'na' => 'N',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_rfidtime')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_rfidtime')->insert($chunk);
        }
    }
}