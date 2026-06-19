<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_migrasis', function (Blueprint $table) {
            $table->id();

            // Identifikasi proses
            $table->tinyInteger('fase')->comment('1=ETL Setup, 2=Master, 3=Transaksi');
            $table->string('entitas', 100)->comment('Nama entitas migrasi, contoh: program_studi');
            $table->string('tabel_legacy', 100)->nullable()->comment('Nama tabel sumber di DB legacy');
            $table->string('tabel_target', 100)->nullable()->comment('Nama tabel tujuan di DB baru');

            // Status eksekusi
            $table->string('status', 20)->default('pending')
                  ->comment('pending | running | done | error | cancelled');

            // Statistik
            $table->unsignedInteger('total_legacy')->default(0);
            $table->unsignedInteger('total_imported')->default(0);
            $table->unsignedInteger('total_skipped')->default(0);
            $table->unsignedInteger('total_error')->default(0);

            // Metadata
            $table->text('pesan_error')->nullable();
            $table->string('job_id')->nullable()->comment('ID Laravel Queue Job');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            // Siapa yang memicu (tanpa FK — users mungkin belum ada saat migrate)
            $table->unsignedBigInteger('user_id')->nullable()->comment('ID user yang memicu');

            $table->timestamps();

            // Index untuk query cepat
            $table->index(['fase', 'entitas']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_migrasis');
    }
};
