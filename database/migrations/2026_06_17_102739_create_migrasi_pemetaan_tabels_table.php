<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('migrasi_pemetaan_tabels', function (Blueprint $table) {
            $table->id();

            // Identitas tabel legacy
            $table->string('tabel_legacy', 100)->comment('Nama tabel di DB lama, contoh: prodi');
            $table->unsignedInteger('jml_baris_legacy')->default(0)->comment('Jumlah record di tabel legacy');
            $table->unsignedSmallInteger('jml_kolom_legacy')->default(0);

            // Klasifikasi oleh admin
            $table->string('klasifikasi', 20)->default('abaikan')
                  ->comment('master | transaksi | abaikan');

            // Nama tabel target di DB baru (auto-suggest atau manual)
            $table->string('tabel_baru', 100)->nullable()
                  ->comment('Nama tabel di DB baru, contoh: m_prodis');

            // Pemetaan field: disimpan sebagai JSON
            // Format: [{"legacy": "ProdiID", "baru": "prodi_id", "tipe": "int", "tipe_baru": "unsignedBigInteger"}, ...]
            $table->json('pemetaan_field')->nullable();

            // Status
            $table->string('status_impor', 20)->default('pending')
                  ->comment('pending | done | error | abaikan');

            // Metadata scan
            $table->timestamp('terakhir_scan_at')->nullable();
            $table->unsignedBigInteger('scanned_by')->nullable();

            $table->timestamps();

            $table->unique('tabel_legacy');
            $table->index('klasifikasi');
            $table->index('status_impor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('migrasi_pemetaan_tabels');
    }
};
