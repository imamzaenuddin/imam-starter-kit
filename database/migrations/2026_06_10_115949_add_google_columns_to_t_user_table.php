<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_user', function (Blueprint $table) {
            if (!Schema::hasColumn('t_user', 'google_id')) {
                $table->string('google_id')->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('t_user', 'google_avatar')) {
                $table->string('google_avatar')->nullable()->after('google_id');
            }
            
            // Jadikan password nullable untuk login via oauth
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('t_user', function (Blueprint $table) {
            if (Schema::hasColumn('t_user', 'google_id')) {
                $table->dropColumn('google_id');
            }
            if (Schema::hasColumn('t_user', 'google_avatar')) {
                $table->dropColumn('google_avatar');
            }
            
            // Kembalikan password menjadi NOT NULL
            $table->string('password')->nullable(false)->change();
        });
    }
};
