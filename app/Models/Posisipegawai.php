<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Posisipegawai extends Model
{
    use SoftDeletes;

    protected $table = 'm_posisipegawai';
    protected $primaryKey = 'posisi_pegawai_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'posisi_pegawai_id',
        'no_id',
        'nama',
        'def',
        'honor_mengajar',
        'na'
    ];
}