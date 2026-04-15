<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('m_login_attempt')) {
            Schema::create('m_login_attempt', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('t_user')->nullOnDelete();
                $table->string('email', 150)->nullable()->index();
                $table->string('ip_address', 45)->nullable()->index();
                $table->string('user_agent', 1000)->nullable();
                $table->string('status', 20)->index(); // sukses|gagal|lockout
                $table->string('alasan', 255)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('m_login_attempt');
    }
};
