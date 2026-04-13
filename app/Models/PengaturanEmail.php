<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanEmail extends Model
{
    protected $table = 'pengaturan_email';

    protected $fillable = [
        'mailer',
        'host',
        'port',
        'enkripsi',
        'username',
        'password',
        'from_address',
        'from_name',
        'reply_to',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'port' => 'integer',
        'password' => 'encrypted',
    ];
}
