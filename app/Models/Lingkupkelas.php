<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lingkupkelas extends Model
{
    use SoftDeletes;

    protected $table = 'm_lingkupkelas';
    protected $primaryKey = 'lingkup_kelas_id';

    protected $fillable = [
        'id_legacy',
        'lingkup_kelas_id',
        'lingkup_kelas_kode',
        'nama',
        'na'
    ];
}