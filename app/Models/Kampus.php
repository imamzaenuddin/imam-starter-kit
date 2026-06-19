<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kampus extends Model
{
    use SoftDeletes;

    protected $table = 'm_kampus';
    protected $primaryKey = 'kampus_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'kampus_id',
        'no_id',
        'nama',
        'alamat',
        'kota',
        'kode_id',
        'telepon',
        'wa',
        'fax',
        'aktif',
        'def',
        'na'
    ];
}