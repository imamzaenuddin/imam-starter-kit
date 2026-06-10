<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Tabel Berita / Artikel ──────────────────────────────────────────
        Schema::create('t_berita', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('ringkasan')->nullable();
            $table->longText('isi');
            $table->string('foto')->nullable();             // Path storage
            $table->string('kategori')->default('Berita');   // Berita | Pengumuman | Kegiatan
            $table->string('penulis')->nullable();
            $table->date('tanggal_terbit')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);  // Tampil di landing page
            $table->unsignedBigInteger('views')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('t_user')->nullOnDelete();
            $table->timestamps();
        });

        // ── Tabel Konten Slider Hero ───────────────────────────────────────
        Schema::create('t_konten_slider', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('subjudul')->nullable();
            $table->string('foto');                         // Image path
            $table->string('warna_latar')->default('#2563eb'); // Hex color overlay
            $table->string('label_tombol')->nullable();
            $table->string('url_tombol')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('urutan')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('t_user')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_konten_slider');
        Schema::dropIfExists('t_berita');
    }
};
