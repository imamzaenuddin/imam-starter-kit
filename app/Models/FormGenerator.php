<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormGenerator extends Model
{
    protected $table = 'm_form_generator';

    protected $fillable = [
        'nama_modul',
        'slug',
        'nama_menu',
        'menu_url',
        'icon',
        'parent_menu_id',
        'sumber_import',
        'tipe_modul',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(FormGeneratorField::class, 'form_generator_id')->orderBy('urutan');
    }

    public function dataEntri(): HasMany
    {
        return $this->hasMany(FormGeneratorData::class, 'form_generator_id')->latest('id');
    }

    public function parentMenu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_menu_id');
    }
}
