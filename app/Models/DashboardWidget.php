<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DashboardWidget extends Model
{
    protected $table = 'm_dashboard_widget';

    protected $fillable = [
        'nama_widget',
        'deskripsi',
        'sumber_data',
        'tipe_tampilan',
        'tipe_query',
        'chart_tipe',
        'chart_tinggi',
        'chart_warna',
        'kolom_agregasi',
        'kolom_label',
        'kolom_nilai',
        'filter_kolom',
        'filter_operator',
        'filter_nilai',
        'layout_kolom',
        'warna',
        'icon',
        'batas_data',
        'bandingkan_periode',
        'bandingkan_dengan',
        'kpi_target',
        'tampilkan_progress_bar',
        'warna_threshold_hijau',
        'warna_threshold_kuning',
        'warna_threshold_merah',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'batas_data' => 'integer',
        'urutan' => 'integer',
        'chart_tinggi' => 'integer',
        'chart_warna' => 'array',
        'bandingkan_periode' => 'boolean',
        'tampilkan_progress_bar' => 'boolean',
        'kpi_target' => 'integer',
        'is_active' => 'boolean',
    ];

    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'm_dashboard_widget_level')
            ->withTimestamps();
    }
}
