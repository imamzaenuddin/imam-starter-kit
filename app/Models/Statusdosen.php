<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statusdosen extends Model
{
    use SoftDeletes;

    protected $table = 'm_statusdosen';
    protected $primaryKey = 'status_dosen_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'status_dosen_id',
        'no_id',
        'nama',
        'def',
        'honor_mengajar',
        'na'
    ];
}