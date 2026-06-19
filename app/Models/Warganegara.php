<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warganegara extends Model
{
    use SoftDeletes;

    protected $table = 'm_warganegara';
    protected $primaryKey = 'warga_negara_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'warga_negara_id',
        'nama',
        'na'
    ];
}