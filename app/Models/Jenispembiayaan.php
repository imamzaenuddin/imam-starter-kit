<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenispembiayaan extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenispembiayaan';
    protected $primaryKey = 'jenis_pembiayaan_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'jenis_pembiayaan_id',
        'nama',
        'na'
    ];
}