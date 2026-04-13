<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bahasa extends Model
{
    protected $table = 'm_bahasa';

    protected $fillable = [
        'kode',
        'nama',
        'nama_native',
        'urutan',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];
}
