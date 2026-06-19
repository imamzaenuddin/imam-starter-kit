<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statuskeluar extends Model
{
    use SoftDeletes;

    protected $table = 'm_statuskeluar';
    protected $primaryKey = 'status_keluar_id';

    protected $fillable = [
        'id_legacy',
        'status_keluar_id',
        'kode_id',
        'nama',
        'keluar',
        'def',
        'lulus',
        'na'
    ];
}