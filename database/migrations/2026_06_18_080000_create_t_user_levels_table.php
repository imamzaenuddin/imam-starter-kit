<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_user_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('t_user')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('m_level')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'level_id']);
        });

        // Seed pivot table dengan data level_id yang sudah ada di t_user saat ini
        $users = DB::table('t_user')->whereNotNull('level_id')->get();
        foreach ($users as $user) {
            DB::table('t_user_level')->insertOrIgnore([
                'user_id' => $user->id,
                'level_id' => $user->level_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('t_user_level');
    }
};
