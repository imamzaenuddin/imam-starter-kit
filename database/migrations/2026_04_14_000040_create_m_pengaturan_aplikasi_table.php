<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('m_pengaturan_aplikasi')) {
            Schema::create('m_pengaturan_aplikasi', function (Blueprint $table) {
                $table->id();
                $table->string('timezone', 100)->default(config('app.timezone', 'Asia/Jakarta'));
                $table->string('locale_default', 10)->default(config('app.locale', 'id'));
                $table->unsignedInteger('batas_upload_kb')->default(10240);
                $table->unsignedInteger('pagination_default')->default(10);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('m_pengaturan_aplikasi');
    }
};
