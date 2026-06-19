<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenistinggal extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenistinggal';
    protected $primaryKey = 'jenis_tinggal_id';

    protected $fillable = [
        'id_legacy',
        'jenis_tinggal_id',
        'nama',
        'na'
    ];
}