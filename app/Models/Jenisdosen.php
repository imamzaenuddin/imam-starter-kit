<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenisdosen extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenisdosen';
    protected $primaryKey = 'jenis_dosen_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'jenis_dosen_id',
        'nama',
        'def',
        'na'
    ];
}