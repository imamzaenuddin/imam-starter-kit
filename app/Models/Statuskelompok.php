<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statuskelompok extends Model
{
    use SoftDeletes;

    protected $table = 'm_statuskelompok';
    protected $primaryKey = 'status_kelompok_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'status_kelompok_id',
        'program_id',
        'nama',
        'jam_mulai',
        'jam_selesai',
        'kode_id',
        'def',
        'keterangan',
        'na'
    ];
}