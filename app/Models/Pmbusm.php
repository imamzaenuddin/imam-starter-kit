<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pmbusm extends Model
{
    use SoftDeletes;

    protected $table = 'm_pmbusm';
    protected $primaryKey = 'pmbusm_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'pmbusm_id',
        'kode_id',
        'nama',
        'cara_penempatan',
        'keterangan',
        'ada_script',
        'login_buat',
        'tgl_buat',
        'login_edit',
        'tgl_edit',
        'na'
    ];
}