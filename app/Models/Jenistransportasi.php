<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenistransportasi extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenistransportasi';
    protected $primaryKey = 'jenis_transportasi_id';

    protected $fillable = [
        'id_legacy',
        'jenis_transportasi_id',
        'nama',
        'na',
        'kode_id'
    ];
}