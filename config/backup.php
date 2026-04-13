<?php

return [
    'jadwal_harian_tipe' => env('BACKUP_JADWAL_HARIAN_TIPE', 'transaksi'),
    'jadwal_harian_jam' => env('BACKUP_JADWAL_HARIAN_JAM', '01:00'),

    'jadwal_mingguan_tipe' => env('BACKUP_JADWAL_MINGGUAN_TIPE', 'full'),
    'jadwal_mingguan_hari' => env('BACKUP_JADWAL_MINGGUAN_HARI', 'sunday'),
    'jadwal_mingguan_jam' => env('BACKUP_JADWAL_MINGGUAN_JAM', '02:00'),

    'retensi_hari' => (int) env('BACKUP_RETENSI_HARI', 30),

    'restore_auto_backup' => (bool) env('BACKUP_RESTORE_AUTO_BACKUP', true),
    'restore_auto_backup_tipe' => env('BACKUP_RESTORE_AUTO_BACKUP_TIPE', 'full'),
    'restore_lock_timeout_detik' => (int) env('BACKUP_RESTORE_LOCK_TIMEOUT_DETIK', 900),
];
