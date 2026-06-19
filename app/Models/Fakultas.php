<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fakultas extends Model
{
    use SoftDeletes;

    protected $table = 'm_fakultas';
    protected $primaryKey = 'fakultas_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_legacy',
        'fakultas_id',
        'id_perguruan_tinggi',
        'nama',
        'nama_ins',
        'kode_pti',
        'status_pt',
        'kode_ptid',
        'header',
        'footer',
        'file_kartu_pegawai1',
        'file_kartu_pegawai2',
        'akreditasi',
        'no_skbanpt',
        'live',
        'id__sp',
        'feeder_url',
        'feeder_port',
        'feeder_username',
        'feeder_password',
        'port',
        'kode_id',
        'pejabat',
        'jabatan',
        'keterangan',
        'alamat',
        'provinsi',
        'start_no_fakultas',
        'no_fakultas',
        'na'
    ];
}