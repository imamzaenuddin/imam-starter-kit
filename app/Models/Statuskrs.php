<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statuskrs extends Model
{
    use SoftDeletes;

    protected $table = 'm_statuskrs';
    protected $primaryKey = 'status_krs_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'status_krs_id',
        'nama',
        'ikut',
        'hitung',
        'na'
    ];
}