<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bipotnama extends Model
{
    use SoftDeletes;

    protected $table = 'm_bipotnama';
    protected $primaryKey = 'bipot_nama_id';

    protected $fillable = [
        'id_legacy',
        'bipot_nama_id',
        'kode_id',
        'rekening_id',
        'urutan',
        'nama',
        'singkatan',
        'trx_id',
        'baris',
        'detil',
        'def_jumlah',
        'def_besar',
        'diskon',
        'kena_denda',
        'dipotong_beasiswa',
        'catatan',
        'na',
        'pb'
    ];
}