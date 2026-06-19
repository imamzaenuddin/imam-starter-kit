<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenispegawai extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenispegawai';
    protected $primaryKey = 'jenis_pegawai_id';

    protected $fillable = [
        'id_legacy',
        'jenis_pegawai_id',
        'nama',
        'na'
    ];
}