<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Berita extends Model
{
    use HasFactory;

    protected $table = 't_berita';

    protected $fillable = [
        'judul',
        'slug',
        'ringkasan',
        'isi',
        'foto',
        'kategori',
        'penulis',
        'tanggal_terbit',
        'is_published',
        'is_featured',
        'views',
        'created_by',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'is_published'   => 'boolean',
        'is_featured'    => 'boolean',
        'views'          => 'integer',
    ];

    /**
     * Relasi ke pembuat berita (User).
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
