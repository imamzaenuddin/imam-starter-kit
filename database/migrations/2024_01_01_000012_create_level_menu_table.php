<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('level_menu', function (Blueprint $table) {
      $table->id();
      $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
      $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();

      // Hak akses granular per menu
      $table->boolean('dapat_buat')->default(false);
      $table->boolean('dapat_lihat')->default(true);
      $table->boolean('dapat_ubah')->default(false);
      $table->boolean('dapat_hapus')->default(false);

      $table->timestamps();

      // Satu level hanya boleh punya satu entri per menu
      $table->unique(['level_id', 'menu_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('level_menu');
  }
};
