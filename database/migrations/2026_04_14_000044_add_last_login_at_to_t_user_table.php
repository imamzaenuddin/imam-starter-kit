<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('t_user') || Schema::hasColumn('t_user', 'last_login_at')) {
            return;
        }

        Schema::table('t_user', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('two_factor_confirmed_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('t_user') || ! Schema::hasColumn('t_user', 'last_login_at')) {
            return;
        }

        Schema::table('t_user', function (Blueprint $table) {
            $table->dropColumn('last_login_at');
        });
    }
};
