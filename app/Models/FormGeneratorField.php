<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormGeneratorField extends Model
{
    protected $table = 'm_form_generator_field';

    protected $fillable = [
        'form_generator_id',
        'nama_field',
        'label_field',
        'tipe_data',
        'tipe_input',
        'opsi_pilihan',
        'is_required',
        'is_tampil_form',
        'is_tampil_list',
        'urutan',
    ];

    protected $casts = [
        'opsi_pilihan' => 'array',
        'is_required' => 'boolean',
        'is_tampil_form' => 'boolean',
        'is_tampil_list' => 'boolean',
    ];

    public function generator(): BelongsTo
    {
        return $this->belongsTo(FormGenerator::class, 'form_generator_id');
    }
}
