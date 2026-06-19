<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenislibur extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenislibur';
    protected $primaryKey = 'jenis_libur_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'jenis_libur_id',
        'nama',
        'warna',
        'kode_id',
        'keterangan',
        'na'
    ];
}