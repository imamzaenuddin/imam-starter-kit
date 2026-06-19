<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenismbkm extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenismbkm';
    protected $primaryKey = 'jenis_mbkm_id';

    protected $fillable = [
        'id_legacy',
        'jenis_mbkm_id',
        'nama',
        'login_buat',
        'tanggal_buat',
        'login_edit',
        'tanggal_edit',
        'na'
    ];
}