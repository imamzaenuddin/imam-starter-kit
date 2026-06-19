<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unitorganisasi extends Model
{
    use SoftDeletes;

    protected $table = 'm_unitorganisasi';
    protected $primaryKey = 'unit_organisasi_id';

    protected $fillable = [
        'id_legacy',
        'unit_organisasi_id',
        'nama',
        'kode_id',
        'na'
    ];
}