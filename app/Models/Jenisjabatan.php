<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenisjabatan extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenisjabatan';
    protected $primaryKey = 'jenis_jabatan_id';

    protected $fillable = [
        'id_legacy',
        'jenis_jabatan_id',
        'singkatan',
        'nama',
        'urutan',
        'catatan',
        'login_buat',
        'tanggal_buat',
        'login_edit',
        'tanggal_edit',
        'na',
        'kode_id'
    ];
}