<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agama extends Model
{
    use SoftDeletes;

    protected $table = 'm_agama';
    protected $primaryKey = 'agama_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'agama_id',
        'nama',
        'na',
        'kode_id'
    ];
}