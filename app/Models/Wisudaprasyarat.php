<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wisudaprasyarat extends Model
{
    use SoftDeletes;

    protected $table = 'm_wisudaprasyarat';
    protected $primaryKey = 'prasyarat_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'prasyarat_id',
        'kode_id',
        'nama',
        'na'
    ];
}