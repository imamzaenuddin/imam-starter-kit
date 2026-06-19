<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jabatandikti extends Model
{
    use SoftDeletes;

    protected $table = 'm_jabatandikti';
    protected $primaryKey = 'jabatan_dikti_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'jabatan_dikti_id',
        'nama',
        'keterangan',
        'def',
        'na'
    ];
}