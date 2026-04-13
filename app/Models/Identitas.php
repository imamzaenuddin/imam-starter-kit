<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Identitas extends Model
{
  protected $table = 'identitas';

  protected $fillable = [
    'nama_aplikasi',
    'singkatan_aplikasi',
    'versi',
    'icon',
    'logo_path',
    'main_color',
    'secondary_color',
    'email',
    'wa_center',
    'telepon',
    'website',
    'alamat',
    'slogan',
    'deskripsi',
    'footer_text',
    'fitur_login',
    'statistik_login',
    'is_active',
  ];

  protected $casts = [
    'fitur_login' => 'array',
    'statistik_login' => 'array',
    'is_active' => 'boolean',
  ];
}
