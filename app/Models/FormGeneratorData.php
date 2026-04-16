<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormGeneratorData extends Model
{
    protected $table = 't_form_generator_data';

    protected $fillable = [
        'form_generator_id',
        'payload',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_active' => 'boolean',
    ];

    public function generator(): BelongsTo
    {
        return $this->belongsTo(FormGenerator::class, 'form_generator_id');
    }
}
