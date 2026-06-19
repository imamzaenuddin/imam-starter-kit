<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenispresensi extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenispresensi';
    protected $primaryKey = 'jenis_presensi_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'jenis_presensi_id',
        'nama',
        'nilai',
        'chr',
        'def',
        'na'
    ];
}