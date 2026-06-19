<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pegawainilai extends Model
{
    use SoftDeletes;

    protected $table = 'm_pegawainilai';
    protected $primaryKey = 'pegawai_nilai_id';

    protected $fillable = [
        'id_legacy',
        'pegawai_nilai_id',
        'kode_id',
        'nama',
        'bobot',
        'deskripsi',
        'na'
    ];
}