<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sumberinfo extends Model
{
    use SoftDeletes;

    protected $table = 'm_sumberinfo';
    protected $primaryKey = 'info_id';

    protected $fillable = [
        'id_legacy',
        'info_id',
        'kode_id',
        'urutan',
        'nama',
        'catatan',
        'na'
    ];
}