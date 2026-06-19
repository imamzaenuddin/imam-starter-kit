<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Modekuliah extends Model
{
    use SoftDeletes;

    protected $table = 'm_modekuliah';
    protected $primaryKey = 'mode_kuliah_id';

    protected $fillable = [
        'id_legacy',
        'mode_kuliah_id',
        'mode_kuliah_kode',
        'nama',
        'keterangan',
        'dokumentasi',
        'bukti_dokumentasi',
        'link',
        'bukti_link',
        'na'
    ];
}