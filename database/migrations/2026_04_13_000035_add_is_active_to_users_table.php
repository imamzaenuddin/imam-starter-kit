<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('t_user')) {
            return;
        }

        if (! Schema::hasColumn('t_user', 'is_active')) {
            Schema::table('t_user', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('level_id');
            });
        }

        DB::table('t_user')->whereNull('is_active')->update(['is_active' => true]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('t_user') || ! Schema::hasColumn('t_user', 'is_active')) {
            return;
        }

        Schema::table('t_user', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
