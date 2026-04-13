<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $table = 'm_menu';

    protected $fillable = ['nama', 'url', 'icon', 'parent_id', 'urutan', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** Menu induk (self-referential) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /** Menu anak (sub-menu) */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('urutan');
    }

    /** Level-level yang memiliki akses ke menu ini */
    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'm_level_menu')
            ->withPivot([
                'dapat_buat',
                'dapat_lihat',
                'dapat_ubah',
                'dapat_hapus',
                'dapat_backup',
                'dapat_restore',
                'dapat_hapus_backup',
            ])
            ->withTimestamps();
    }

    /** Scope: hanya menu aktif */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Scope: hanya menu root (tanpa parent) */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
