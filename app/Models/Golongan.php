<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Golongan extends Model
{
    use SoftDeletes;

    protected $table = 'm_golongan';
    protected $primaryKey = 'golongan_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'golongan_id',
        'kategori_id',
        'prodi_id',
        'kode_id',
        'pangkat',
        'nama',
        'def',
        'tunjangan_fungsional',
        'tunjangan_sks',
        'tunjangan_transport',
        'tunjangan_tetap',
        'na'
    ];
}