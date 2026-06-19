<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenisberkas extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenisberkas';
    protected $primaryKey = 'jenis_berkas_id';

    protected $fillable = [
        'id_legacy',
        'jenis_berkas_id',
        'nama',
        'bentuk',
        'type',
        'ukuran',
        'na',
        'wajib'
    ];
}