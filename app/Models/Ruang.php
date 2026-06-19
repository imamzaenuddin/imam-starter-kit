<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ruang extends Model
{
    use SoftDeletes;

    protected $table = 'm_ruang';
    protected $primaryKey = 'ruang_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'ruang_id',
        'kode_id',
        'nama',
        'kampus_id',
        'rfid_device_id',
        'lantai',
        'prodi_id',
        'ruang_kuliah',
        'kapasitas',
        'kapasitas_ujian',
        'kolom_ujian',
        'untuk_usm',
        'keterangan',
        'na'
    ];
}