<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenisjadwal extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenisjadwal';
    protected $primaryKey = 'jenis_jadwal_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'jenis_jadwal_id',
        'nama',
        'tambahan',
        'dep',
        'na'
    ];
}