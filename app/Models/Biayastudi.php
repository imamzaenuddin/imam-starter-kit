<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Biayastudi extends Model
{
    use SoftDeletes;

    protected $table = 'm_biayastudi';
    protected $primaryKey = 'biaya_studi_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'biaya_studi_id',
        'nama',
        'beasiswa',
        'beasiswa_id',
        'na'
    ];
}