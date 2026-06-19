<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Benua extends Model
{
    use SoftDeletes;

    protected $table = 'm_benua';
    protected $primaryKey = 'benua_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'benua_id',
        'nama_benua',
        'na'
    ];
}