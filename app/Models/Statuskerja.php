<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statuskerja extends Model
{
    use SoftDeletes;

    protected $table = 'm_statuskerja';
    protected $primaryKey = 'status_kerja_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'status_kerja_id',
        'nama',
        'def',
        'na'
    ];
}