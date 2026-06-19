<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trx extends Model
{
    use SoftDeletes;

    protected $table = 'm_trx';
    protected $primaryKey = 'trx_id';

    protected $fillable = [
        'id_legacy',
        'trx_id',
        'nama',
        'na'
    ];
}