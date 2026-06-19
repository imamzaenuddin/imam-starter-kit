<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenissekolah extends Model
{
    use SoftDeletes;

    protected $table = 'm_jenissekolah';
    protected $primaryKey = 'jenis_sekolah_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'jenis_sekolah_id',
        'nama',
        'satu_group',
        'na'
    ];
}