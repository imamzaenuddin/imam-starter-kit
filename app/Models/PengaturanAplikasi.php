<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanAplikasi extends Model
{
    protected $table = 'm_pengaturan_aplikasi';

    protected $fillable = [
        'timezone',
        'locale_default',
        'batas_upload_kb',
        'pagination_default',
        'otp_mode',
        'otp_inactive_days',
        'otp_failed_attempts',
        'otp_failed_window_minutes',
        'is_active',
    ];

    protected $casts = [
        'batas_upload_kb' => 'integer',
        'pagination_default' => 'integer',
        'otp_inactive_days' => 'integer',
        'otp_failed_attempts' => 'integer',
        'otp_failed_window_minutes' => 'integer',
        'is_active' => 'boolean',
    ];
}
