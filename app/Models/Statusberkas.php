<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statusberkas extends Model
{
    use SoftDeletes;

    protected $table = 'm_statusberkas';
    protected $primaryKey = 'status_berkas_id';

    protected $fillable = [
        'id_legacy',
        'status_berkas_id',
        'nama',
        'verifikasi',
        'status',
        'na'
    ];
}