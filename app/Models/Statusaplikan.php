<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statusaplikan extends Model
{
    use SoftDeletes;

    protected $table = 'm_statusaplikan';
    protected $primaryKey = 'status_aplikan_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'status_aplikan_id',
        'urutan',
        'nama',
        'kode_id',
        'status_aplikan_before',
        'status_aplikan_after',
        'keterangan',
        'na'
    ];
}