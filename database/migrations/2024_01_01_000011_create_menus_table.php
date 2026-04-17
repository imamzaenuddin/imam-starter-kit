<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('url', 255)->nullable();
            $table->string('icon', 100)->nullable()->comment('Class ikon Boxicons, contoh: bx bx-home');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('menus')
                ->nullOnDelete();
            $table->unsignedSmallInteger('urutan')->default(0)->comment('Urutan tampil di sidebar');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
