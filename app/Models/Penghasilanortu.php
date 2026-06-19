<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penghasilanortu extends Model
{
    use SoftDeletes;

    protected $table = 'm_penghasilanortu';
    protected $primaryKey = 'penghasilan_ortu_id';

    protected $fillable = [
        'id_legacy',
        'penghasilan_ortu_id',
        'nama',
        'na'
    ];
}