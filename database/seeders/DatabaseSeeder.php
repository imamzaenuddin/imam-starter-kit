<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LevelMenuSeeder::class,
            BahasaSeeder::class,
            IdentitasSeeder::class,
            LogAktivitasSeeder::class,
            DashboardWidgetSeeder::class,
            ChatAiSeeder::class,
            ImamStarterKitContentSeeder::class,
            PreferensiAgamaMenuSeeder::class,
        ]);
    }
}
