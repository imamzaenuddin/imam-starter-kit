<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('modul', 100)->nullable();
            $table->string('aktivitas', 255);
            $table->string('url', 255)->nullable();
            $table->string('metode', 10)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['modul']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};
