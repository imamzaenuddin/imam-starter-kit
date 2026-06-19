<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pmbformsyarat extends Model
{
    use SoftDeletes;

    protected $table = 'm_pmbformsyarat';
    protected $primaryKey = 'pmb_form_syarat_id';

    protected $fillable = [
        'id_legacy',
        'pmb_form_syarat_id',
        'urutan',
        'nama',
        'ada_script',
        'script',
        'keterangan',
        'na'
    ];
}