<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('m_pengaturan_backup', function (Blueprint $table) {
      $table->id();
      $table->string('jadwal_harian_tipe', 20)->default('transaksi');
      $table->string('jadwal_harian_jam', 5)->default('01:00');
      $table->string('jadwal_mingguan_tipe', 20)->default('full');
      $table->string('jadwal_mingguan_hari', 10)->default('sunday');
      $table->string('jadwal_mingguan_jam', 5)->default('02:00');
      $table->unsignedSmallInteger('retensi_hari')->default(30);
      $table->boolean('restore_auto_backup')->default(true);
      $table->string('restore_auto_backup_tipe', 20)->default('full');
      $table->unsignedInteger('restore_lock_timeout_detik')->default(900);
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });

    DB::table('m_pengaturan_backup')->insert([
      'jadwal_harian_tipe' => (string) config('backup.jadwal_harian_tipe', 'transaksi'),
      'jadwal_harian_jam' => (string) config('backup.jadwal_harian_jam', '01:00'),
      'jadwal_mingguan_tipe' => (string) config('backup.jadwal_mingguan_tipe', 'full'),
      'jadwal_mingguan_hari' => (string) config('backup.jadwal_mingguan_hari', 'sunday'),
      'jadwal_mingguan_jam' => (string) config('backup.jadwal_mingguan_jam', '02:00'),
      'retensi_hari' => (int) config('backup.retensi_hari', 30),
      'restore_auto_backup' => (bool) config('backup.restore_auto_backup', true),
      'restore_auto_backup_tipe' => (string) config('backup.restore_auto_backup_tipe', 'full'),
      'restore_lock_timeout_detik' => (int) config('backup.restore_lock_timeout_detik', 900),
      'is_active' => true,
      'created_at' => now(),
      'updated_at' => now(),
    ]);
  }

  public function down(): void
  {
    Schema::dropIfExists('m_pengaturan_backup');
  }
};
