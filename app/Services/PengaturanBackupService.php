<?php

namespace App\Services;

use App\Models\PengaturanBackup;
use Illuminate\Support\Facades\Schema;

class PengaturanBackupService
{
    public function konfigurasiScheduler(): array
    {
        $default = [
            'jadwal_harian_tipe' => (string) config('backup.jadwal_harian_tipe', 'transaksi'),
            'jadwal_harian_jam' => (string) config('backup.jadwal_harian_jam', '01:00'),
            'jadwal_mingguan_tipe' => (string) config('backup.jadwal_mingguan_tipe', 'full'),
            'jadwal_mingguan_hari' => (string) config('backup.jadwal_mingguan_hari', 'sunday'),
            'jadwal_mingguan_jam' => (string) config('backup.jadwal_mingguan_jam', '02:00'),
            'retensi_hari' => (int) config('backup.retensi_hari', 30),
            'restore_auto_backup' => (bool) config('backup.restore_auto_backup', true),
            'restore_auto_backup_tipe' => (string) config('backup.restore_auto_backup_tipe', 'full'),
            'restore_lock_timeout_detik' => (int) config('backup.restore_lock_timeout_detik', 900),
        ];

        if (! Schema::hasTable('m_pengaturan_backup')) {
            return $default;
        }

        $pengaturan = PengaturanBackup::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (! $pengaturan) {
            return $default;
        }

        return [
            'jadwal_harian_tipe' => (string) ($pengaturan->jadwal_harian_tipe ?: $default['jadwal_harian_tipe']),
            'jadwal_harian_jam' => (string) ($pengaturan->jadwal_harian_jam ?: $default['jadwal_harian_jam']),
            'jadwal_mingguan_tipe' => (string) ($pengaturan->jadwal_mingguan_tipe ?: $default['jadwal_mingguan_tipe']),
            'jadwal_mingguan_hari' => (string) ($pengaturan->jadwal_mingguan_hari ?: $default['jadwal_mingguan_hari']),
            'jadwal_mingguan_jam' => (string) ($pengaturan->jadwal_mingguan_jam ?: $default['jadwal_mingguan_jam']),
            'retensi_hari' => (int) ($pengaturan->retensi_hari ?: $default['retensi_hari']),
            'restore_auto_backup' => (bool) $pengaturan->restore_auto_backup,
            'restore_auto_backup_tipe' => (string) ($pengaturan->restore_auto_backup_tipe ?: $default['restore_auto_backup_tipe']),
            'restore_lock_timeout_detik' => (int) ($pengaturan->restore_lock_timeout_detik ?: $default['restore_lock_timeout_detik']),
        ];
    }

    public function simpan(array $data): PengaturanBackup
    {
        PengaturanBackup::query()->update(['is_active' => false]);

        return PengaturanBackup::query()->create([
            'jadwal_harian_tipe' => $data['jadwal_harian_tipe'],
            'jadwal_harian_jam' => $data['jadwal_harian_jam'],
            'jadwal_mingguan_tipe' => $data['jadwal_mingguan_tipe'],
            'jadwal_mingguan_hari' => $data['jadwal_mingguan_hari'],
            'jadwal_mingguan_jam' => $data['jadwal_mingguan_jam'],
            'retensi_hari' => $data['retensi_hari'],
            'restore_auto_backup' => $data['restore_auto_backup'],
            'restore_auto_backup_tipe' => $data['restore_auto_backup_tipe'],
            'restore_lock_timeout_detik' => $data['restore_lock_timeout_detik'],
            'is_active' => true,
        ]);
    }
}
