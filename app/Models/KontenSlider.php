<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KontenSlider extends Model
{
    use HasFactory;

    protected $table = 't_konten_slider';

    protected $fillable = [
        'judul',
        'subjudul',
        'foto',
        'warna_latar',
        'label_tombol',
        'url_tombol',
        'is_active',
        'urutan',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan'    => 'integer',
    ];

    /**
     * Relasi ke pembuat slider (User).
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
