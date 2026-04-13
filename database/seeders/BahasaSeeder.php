<?php

namespace Database\Seeders;

use App\Models\Bahasa;
use App\Services\BahasaService;
use Illuminate\Database\Seeder;

class BahasaSeeder extends Seeder
{
    public function run(): void
    {
        app(BahasaService::class)->sinkronkanDariFolder();

        Bahasa::query()->updateOrCreate(
            ['kode' => 'id'],
            [
                'nama' => 'Bahasa Indonesia',
                'nama_native' => 'Bahasa Indonesia',
                'urutan' => 1,
                'is_active' => true,
                'is_default' => true,
            ]
        );

        Bahasa::query()->updateOrCreate(
            ['kode' => 'en'],
            [
                'nama' => 'English',
                'nama_native' => 'English',
                'urutan' => 2,
                'is_active' => true,
                'is_default' => false,
            ]
        );

        $defaultId = Bahasa::query()->where('kode', 'id')->value('id');
        if ($defaultId) {
            Bahasa::query()->where('id', '!=', $defaultId)->update(['is_default' => false]);
        }
    }
}
