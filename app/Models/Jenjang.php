<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenjang extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenjang';
    protected $primaryKey = 'jenjang_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'jenjang_id',
        'dikti_id',
        'jenjang_dikti_id',
        'nama',
        'keterangan',
        'def',
        'na'
    ];
}