<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    /** @use HasFactory<\Database\Factories\NotifikasiFactory> */
    use HasFactory;

    protected $table = 'm_notifikasi';

    protected $fillable = [
        'user_id',
        'judul',
        'pesan',
        'tipe',
        'path_terkait',
        'dibaca',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'dibaca' => 'boolean',
            'read_at' => 'datetime',
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
     * Scope: Notifikasi belum dibaca
     */
    public function scopeBelumDibaca($query)
    {
        return $query->where('dibaca', false);
    }

    /**
     * Scope: Notifikasi untuk user tertentu
     */
    public function scopeUntukUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Notifikasi berdasarkan tipe
     */
    public function scopeBerdasarkanTipe($query, string $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    /**
     * Tandai notifikasi sebagai sudah dibaca
     */
    public function tandaiBaca(): void
    {
        $this->update([
            'dibaca' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Tandai semua notifikasi user sebagai sudah dibaca
     */
    public static function tandaiSemuaBaca(int $userId): void
    {
        static::untukUser($userId)
            ->belumDibaca()
            ->update([
                'dibaca' => true,
                'read_at' => now(),
            ]);
    }
}
