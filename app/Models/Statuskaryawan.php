<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statuskaryawan extends Model
{
    use SoftDeletes;

    protected $table = 'm_statuskaryawan';
    protected $primaryKey = 'status_karyawan_id';

    protected $fillable = [
        'id_legacy',
        'status_karyawan_id',
        'nama',
        'na'
    ];
}