<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Saat extends Model
{
    use SoftDeletes;

    protected $table = 'm_saat';
    protected $primaryKey = 'saat_id';

    protected $fillable = [
        'id_legacy',
        'saat_id',
        'nama',
        'na'
    ];
}