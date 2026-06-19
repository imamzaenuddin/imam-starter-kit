<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transportasi extends Model
{
    use SoftDeletes;

    protected $table = 'm_transportasi';
    protected $primaryKey = 'transportasi_id';

    protected $fillable = [
        'id_legacy',
        'transportasi_id',
        'nama',
        'na'
    ];
}