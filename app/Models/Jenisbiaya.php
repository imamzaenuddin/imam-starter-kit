<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenisbiaya extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenisbiaya';
    protected $primaryKey = 'jenis_biaya_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'jenis_biaya_id',
        'nama',
        'no_urut_biaya',
        'na'
    ];
}