<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfidtime extends Model
{
    use SoftDeletes;

    protected $table = 'm_rfidtime';
    protected $primaryKey = 'rfid_time_id';

    protected $fillable = [
        'id_legacy',
        'rfid_time_id',
        'nama',
        'jam_mulai',
        'jam_selesai',
        'na'
    ];
}