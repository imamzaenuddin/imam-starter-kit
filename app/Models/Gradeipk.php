<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gradeipk extends Model
{
    use SoftDeletes;

    protected $table = 'm_gradeipk';
    protected $primaryKey = 'kode_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'grade_ipk',
        'kode_id',
        'ipk_min',
        'ipk_max',
        'sks_min',
        'keterangan',
        'login_buat',
        'tgl_buat',
        'login_edit',
        'tgl_edit',
        'na'
    ];
}