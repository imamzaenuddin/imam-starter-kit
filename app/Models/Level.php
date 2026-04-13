<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    protected $table = 'm_level';

    protected $fillable = ['nama_level', 'deskripsi', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** Level memiliki banyak user */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Menu yang boleh diakses oleh level ini.
     * Kolom pivot: dapat_buat, dapat_lihat, dapat_ubah, dapat_hapus
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'm_level_menu')
            ->withPivot(['dapat_buat', 'dapat_lihat', 'dapat_ubah', 'dapat_hapus'])
            ->withTimestamps();
    }

    /** Widget dashboard yang ditampilkan untuk level ini */
    public function dashboardWidgets(): BelongsToMany
    {
        return $this->belongsToMany(DashboardWidget::class, 'm_dashboard_widget_level')
            ->withTimestamps();
    }
}
