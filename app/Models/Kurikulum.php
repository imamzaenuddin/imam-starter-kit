<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kurikulum extends Model
{
    use SoftDeletes;

    protected $table = 'm_kurikulum';
    protected $primaryKey = 'kurikulum_id';

    protected $fillable = [
        'id_legacy',
        'kurikulum_id',
        'kurikulum_dikti_id',
        'kurikulum_kode',
        'sk_kurikulum',
        'nama',
        'kode_id',
        'prodi_id',
        'tahun_id',
        'tgl_mulai',
        'tgl_selesai',
        'sesi',
        'jml_sesi',
        'sks_wajib',
        'sks_pilihan',
        'total_sks',
        'final_dosen',
        'error_code',
        'error_desc',
        'na'
    ];
}