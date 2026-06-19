<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statussipil extends Model
{
    use SoftDeletes;

    protected $table = 'm_statussipil';
    protected $primaryKey = 'status_sipil_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'status_sipil_id',
        'nama',
        'na'
    ];
}