<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChatAiSumber extends Model
{
    protected $table = 'm_chat_ai_sumber';

    protected $fillable = [
        'nama',
        'sumber_data',
        'tipe_data',
        'tipe_query',
        'kolom_agregasi',
        'kolom_tampil',
        'filter_kolom',
        'filter_operator',
        'filter_nilai',
        'batas_data',
        'is_data_personal',
        'is_active',
        'urutan',
    ];

    protected $casts = [
        'kolom_tampil' => 'array',
        'batas_data' => 'integer',
        'is_data_personal' => 'boolean',
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'm_chat_ai_sumber_level')
            ->withTimestamps();
    }
}
