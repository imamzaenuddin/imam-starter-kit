<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusbayarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'status_bayar_id' => '1',
                'nama' => 'Lunas',
                'singkatan' => 'L',
                'keterangan' => 'Lunas Pembayaran',
                'na' => 'N',
                'id_legacy' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_bayar_id' => '2',
                'nama' => 'Belum bayar / Pending',
                'singkatan' => 'P',
                'keterangan' => 'Belum bayar / Pending',
                'na' => 'N',
                'id_legacy' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_bayar_id' => '3',
                'nama' => 'Dibayar sebagian',
                'singkatan' => 'BS',
                'keterangan' => 'Dibayar sebagian',
                'na' => 'N',
                'id_legacy' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_bayar_id' => '4',
                'nama' => 'Tertunggak',
                'singkatan' => 'T',
                'keterangan' => 'Tertunggak',
                'na' => 'N',
                'id_legacy' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_bayar_id' => '5',
                'nama' => 'Dibatalkan',
                'singkatan' => 'B',
                'keterangan' => 'Dibatalkan',
                'na' => 'N',
                'id_legacy' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_bayar_id' => '6',
                'nama' => 'Gagal',
                'singkatan' => 'G',
                'keterangan' => 'Gagal',
                'na' => 'N',
                'id_legacy' => '6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_bayar_id' => '7',
                'nama' => 'Verifikasi',
                'singkatan' => 'V',
                'keterangan' => 'Verifikasi',
                'na' => 'N',
                'id_legacy' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_bayar_id' => '8',
                'nama' => 'Refund / Pengembalian',
                'singkatan' => 'R',
                'keterangan' => 'Refund / Pengembalian',
                'na' => 'N',
                'id_legacy' => '8',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_statusbayar')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_statusbayar')->insert($chunk);
        }
    }
}