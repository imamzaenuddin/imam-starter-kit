<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes;

    protected $table = 'm_program';
    protected $primaryKey = 'program_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'program_id',
        'nama',
        'kode_id',
        'def',
        'keterangan',
        'na'
    ];
}