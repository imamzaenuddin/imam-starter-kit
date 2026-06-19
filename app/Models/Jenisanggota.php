<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenisanggota extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenisanggota';
    protected $primaryKey = 'jenis_anggota_id';

    protected $fillable = [
        'id_legacy',
        'jenis_anggota_id',
        'nama',
        'na'
    ];
}