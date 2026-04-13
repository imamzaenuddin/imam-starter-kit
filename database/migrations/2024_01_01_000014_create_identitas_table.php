<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identitas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_aplikasi', 120);
            $table->string('versi', 30)->default('1.0.0');
            $table->string('icon', 100)->nullable()->comment('Class icon Boxicons, contoh: bx bx-buildings');
            $table->string('email', 120)->nullable();
            $table->string('wa_center', 25)->nullable();
            $table->string('telepon', 25)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->string('slogan', 160)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('footer_text', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identitas');
    }
};
