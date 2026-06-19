<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statuspegawai extends Model
{
    use SoftDeletes;

    protected $table = 'm_statuspegawai';
    protected $primaryKey = 'status_pegawai_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'status_pegawai_id',
        'no_id',
        'nama',
        'na'
    ];
}