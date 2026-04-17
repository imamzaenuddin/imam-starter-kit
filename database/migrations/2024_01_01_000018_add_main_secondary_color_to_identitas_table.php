<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identitas', function (Blueprint $table) {
            $table->string('main_color', 7)->nullable()->after('logo_path');
            $table->string('secondary_color', 7)->nullable()->after('main_color');
        });
    }

    public function down(): void
    {
        Schema::table('identitas', function (Blueprint $table) {
            $table->dropColumn(['main_color', 'secondary_color']);
        });
    }
};
