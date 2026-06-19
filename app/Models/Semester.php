<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Semester extends Model
{
    use SoftDeletes;

    protected $table = 'm_semester';
    protected $primaryKey = 'semester_id';

    protected $fillable = [
        'id_legacy',
        'semester_id',
        'nama',
        'na'
    ];
}