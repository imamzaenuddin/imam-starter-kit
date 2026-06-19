<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'bank_id' => '002',
                'nama' => 'Bank Rakyat Indonesia (BRI)',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/2/2e/BRI_Logo.svg',
                'na' => 'N',
                'id_legacy' => '002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_id' => '008',
                'nama' => 'Bank Mandiri',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg',
                'na' => 'N',
                'id_legacy' => '008',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_id' => '009',
                'nama' => 'Bank Negara Indonesia (BNI)',
                'logo' => 'https://upload.wikimedia.org/wikipedia/id/5/55/BNI_logo.svg',
                'na' => 'N',
                'id_legacy' => '009',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_id' => '011',
                'nama' => 'Bank Danamon',
                'logo' => 'https://upload.wikimedia.org/wikipedia/id/a/a0/Bank_Danamon_logo.svg',
                'na' => 'N',
                'id_legacy' => '011',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_id' => '013',
                'nama' => 'Bank Permata',
                'logo' => 'https://upload.wikimedia.org/wikipedia/id/5/51/PermataBank_logo.svg',
                'na' => 'N',
                'id_legacy' => '013',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_id' => '014',
                'nama' => 'Bank Central Asia (BCA)',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg',
                'na' => 'N',
                'id_legacy' => '014',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_id' => '016',
                'nama' => 'Bank Maybank Indonesia',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/e/e6/Maybank_Logo.svg',
                'na' => 'N',
                'id_legacy' => '016',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_id' => '022',
                'nama' => 'Bank CIMB Niaga',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/3/38/CIMB_Niaga_logo.svg',
                'na' => 'N',
                'id_legacy' => '022',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_id' => '028',
                'nama' => 'Bank OCBC NISP',
                'logo' => 'https://upload.wikimedia.org/wikipedia/id/d/da/Logo_OCBC_Indonesia.svg',
                'na' => 'N',
                'id_legacy' => '028',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_id' => '110',
                'nama' => 'Bank Jabar Banten (BJB)',
                'logo' => 'https://upload.wikimedia.org/wikipedia/id/d/d5/Logo_Bank_BJB.svg',
                'na' => 'N',
                'id_legacy' => '110',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_id' => '111',
                'nama' => 'Bank DKI',
                'logo' => 'https://upload.wikimedia.org/wikipedia/id/0/01/Bank_DKI_logo.svg',
                'na' => 'N',
                'id_legacy' => '111',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_id' => '200',
                'nama' => 'Bank Tabungan Negara (BTN)',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/3/3d/Logo_BTN.svg',
                'na' => 'N',
                'id_legacy' => '200',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_id' => '422',
                'nama' => 'Bank Raya (BRI Agro)',
                'logo' => 'https://upload.wikimedia.org/wikipedia/id/c/c5/Bank_Raya_logo.svg',
                'na' => 'N',
                'id_legacy' => '422',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_id' => '451',
                'nama' => 'Bank Syariah Indonesia (BSI)',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/a/a0/Bank_Syariah_Indonesia_logo.svg',
                'na' => 'N',
                'id_legacy' => '451',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_id' => '542',
                'nama' => 'Bank Jago',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/b/b3/Bank_Jago_logo.svg',
                'na' => 'N',
                'id_legacy' => '542',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Kosongkan tabel sebelum insert
        DB::table('m_bank')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk($data, 100) as $chunk) {
            DB::table('m_bank')->insert($chunk);
        }
    }
}