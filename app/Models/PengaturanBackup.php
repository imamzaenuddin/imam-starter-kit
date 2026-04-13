<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanBackup extends Model
{
    protected $table = 'm_pengaturan_backup';

    protected $fillable = [
        'jadwal_harian_tipe',
        'jadwal_harian_jam',
        'jadwal_mingguan_tipe',
        'jadwal_mingguan_hari',
        'jadwal_mingguan_jam',
        'retensi_hari',
        'restore_auto_backup',
        'restore_auto_backup_tipe',
        'restore_lock_timeout_detik',
        'is_active',
    ];

    protected $casts = [
        'retensi_hari' => 'integer',
        'restore_auto_backup' => 'boolean',
        'restore_lock_timeout_detik' => 'integer',
        'is_active' => 'boolean',
    ];
}
