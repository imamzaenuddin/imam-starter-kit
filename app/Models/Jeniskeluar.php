<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jeniskeluar extends Model
{
    use SoftDeletes;

    protected $table = 'm_jeniskeluar';
    protected $primaryKey = 'jenis_keluar_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'jenis_keluar_id',
        'nama',
        'tambahan',
        'dep',
        'na'
    ];
}