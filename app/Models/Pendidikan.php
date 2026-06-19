<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pendidikan extends Model
{
    use SoftDeletes;

    protected $table = 'm_pendidikan';
    protected $primaryKey = 'pendidikan_id';

    protected $fillable = [
        'id_legacy',
        'pendidikan_id',
        'nama',
        'na'
    ];
}