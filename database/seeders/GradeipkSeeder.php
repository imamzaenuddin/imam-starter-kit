<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeipkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'grade_ipk' => 'A',
                'kode_id' => 'PI',
                'ipk_min' => '3.60',
                'ipk_max' => '4.00',
                'sks_min' => '50',
                'keterangan' => '70%',
                'login_buat' => 'admin',
                'tgl_buat' => '2010-11-18',
                'login_edit' => 'imam',
                'tgl_edit' => '2014-01-14',
                'na' => 'Y',
                'id_legacy' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_gradeipk')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_gradeipk')->insert($chunk);
        }
    }
}