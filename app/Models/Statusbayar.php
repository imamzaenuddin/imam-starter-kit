<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statusbayar extends Model
{
    use SoftDeletes;

    protected $table = 'm_statusbayar';
    protected $primaryKey = 'status_bayar_id';

    protected $fillable = [
        'id_legacy',
        'status_bayar_id',
        'nama',
        'singkatan',
        'keterangan',
        'na'
    ];
}