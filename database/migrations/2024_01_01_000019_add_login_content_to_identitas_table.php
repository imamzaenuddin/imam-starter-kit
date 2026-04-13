<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identitas', function (Blueprint $table) {
            $table->json('fitur_login')->nullable()->after('footer_text');
            $table->json('statistik_login')->nullable()->after('fitur_login');
        });
    }

    public function down(): void
    {
        Schema::table('identitas', function (Blueprint $table) {
            $table->dropColumn(['fitur_login', 'statistik_login']);
        });
    }
};
