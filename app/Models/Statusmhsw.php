<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statusmhsw extends Model
{
    use SoftDeletes;

    protected $table = 'm_statusmhsw';
    protected $primaryKey = 'status_mhsw_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'status_mhsw_id',
        'jenis_keluar_id',
        'kode_id',
        'nama',
        'nilai',
        'status_semester',
        'keluar',
        'status_kembali',
        'def',
        'lulus',
        'na'
    ];
}