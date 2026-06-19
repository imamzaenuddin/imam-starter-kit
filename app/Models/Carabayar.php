<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carabayar extends Model
{
    use SoftDeletes;

    protected $table = 'm_carabayar';
    protected $primaryKey = 'cara_bayar_id';

    protected $fillable = [
        'id_legacy',
        'cara_bayar_id',
        'kode_id',
        'nama',
        'na'
    ];
}