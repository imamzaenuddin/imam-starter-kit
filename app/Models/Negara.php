<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Negara extends Model
{
    use SoftDeletes;

    protected $table = 'm_negara';
    protected $primaryKey = 'negara_id';

    protected $fillable = [
        'id_legacy',
        'negara_id',
        'kode_benua',
        'nama_benua',
        'kode_negara',
        'kode_huruf',
        'nama',
        'na'
    ];
}