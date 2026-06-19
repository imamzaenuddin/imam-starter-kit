<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Arsip extends Model
{
    use SoftDeletes;

    protected $table = 'm_arsip';
    protected $primaryKey = 'arsip_id';

    protected $fillable = [
        'id_legacy',
        'arsip_id',
        'nama',
        'na',
        'kode_id'
    ];
}