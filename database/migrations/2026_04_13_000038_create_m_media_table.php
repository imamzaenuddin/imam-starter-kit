<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('m_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('t_user')->onDelete('cascade');
            $table->string('nama_asli', 255);
            $table->string('nama_file', 255)->unique();
            $table->string('mime_type', 100);
            $table->bigInteger('ukuran_byte');
            $table->enum('kategori', ['logo', 'profil', 'dokumen', 'lainnya'])->default('lainnya');
            $table->string('path_relatif', 500);
            $table->string('disk', 50)->default('public');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('kategori');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_media');
    }
};
