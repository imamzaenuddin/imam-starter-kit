<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenispilihan extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenispilihan';
    protected $primaryKey = 'jenis_pilihan_id';

    protected $fillable = [
        'id_legacy',
        'jenis_pilihan_id',
        'kode_id',
        'urutan',
        'singkatan',
        'nama',
        'prodi_id',
        'ta',
        'na'
    ];
}