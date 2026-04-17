<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    /** @use HasFactory<\Database\Factories\MediaFactory> */
    use HasFactory;

    protected $table = 'm_media';

    protected $fillable = [
        'user_id',
        'nama_asli',
        'nama_file',
        'mime_type',
        'ukuran_byte',
        'kategori',
        'path_relatif',
        'disk',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope: Filter berdasarkan kategori
     */
    public function scopeKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Scope: Filter berdasarkan disk
     */
    public function scopeDisk($query, string $disk)
    {
        return $query->where('disk', $disk);
    }

    /**
     * Scope: File untuk user tertentu
     */
    public function scopeUntukUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Urutkan berdasarkan waktu pembuatan (terbaru dulu)
     */
    public function scopeTerbaru($query)
    {
        return $query->latest('created_at');
    }

    /**
     * Get full storage path untuk file access
     */
    public function getStoragePathAttribute(): string
    {
        return $this->disk.'/'.$this->path_relatif;
    }

    /**
     * Cek apakah file masih ada di storage
     */
    public function fileExists(): bool
    {
        try {
            return Storage::disk($this->disk)->exists($this->path_relatif);
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Format ukuran file untuk tampilan (KB, MB, GB, dll)
     */
    public function getUkuranFormatAttribute(): string
    {
        $bytes = $this->ukuran_byte;
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2).' '.$units[$unit];
    }

    /**
     * Get file extension
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->nama_asli, PATHINFO_EXTENSION);
    }

    /**
     * Cek apakah file adalah gambar
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Cek apakah file adalah dokumen
     */
    public function isDocument(): bool
    {
        $docTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv',
        ];

        return in_array($this->mime_type, $docTypes);
    }
}
