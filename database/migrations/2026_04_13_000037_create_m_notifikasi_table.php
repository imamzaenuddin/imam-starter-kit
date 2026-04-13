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
        Schema::create('m_notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('t_user')->onDelete('cascade');
            $table->string('judul', 255);
            $table->text('pesan');
            $table->enum('tipe', [
                'backup_selesai',
                'restore_selesai',
                'restore_gagal',
                'perubahan_data',
                'aktivitas_penting',
                'peringatan',
                'info',
            ])->default('info');
            $table->string('path_terkait', 255)->nullable();
            $table->boolean('dibaca')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('dibaca');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_notifikasi');
    }
};
