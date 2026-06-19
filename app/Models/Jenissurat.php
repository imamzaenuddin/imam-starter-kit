<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenissurat extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenissurat';
    protected $primaryKey = 'jenis_surat_id';

    protected $fillable = [
        'id_legacy',
        'jenis_surat_id',
        'nama',
        'jenis_surat_kode',
        'na'
    ];
}