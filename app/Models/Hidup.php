<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hidup extends Model
{
    use SoftDeletes;

    protected $table = 'm_hidup';
    protected $primaryKey = 'hidup_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'hidup_id',
        'nama',
        'na'
    ];
}