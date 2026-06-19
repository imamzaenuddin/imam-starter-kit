<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Baju extends Model
{
    use SoftDeletes;

    protected $table = 'm_baju';
    protected $primaryKey = 'baju_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'baju_id',
        'nama',
        'na'
    ];
}