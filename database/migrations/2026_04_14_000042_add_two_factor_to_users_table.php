<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_user', function (Blueprint $table) {
            if (! Schema::hasColumn('t_user', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('is_active');
            }

            if (! Schema::hasColumn('t_user', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_user', function (Blueprint $table) {
            $drop = [];

            if (Schema::hasColumn('t_user', 'two_factor_enabled')) {
                $drop[] = 'two_factor_enabled';
            }

            if (Schema::hasColumn('t_user', 'two_factor_confirmed_at')) {
                $drop[] = 'two_factor_confirmed_at';
            }

            if (! empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
