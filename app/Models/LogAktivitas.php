<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{
    protected $table = 't_log_aktivitas';

    protected $fillable = [
        'user_id',
        'modul',
        'aktivitas',
        'url',
        'metode',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Filter by modul
     */
    public function scopeByModul($query, string $modul)
    {
        return $query->where('modul', $modul);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by metode (GET, POST, PUT, DELETE, etc)
     */
    public function scopeByMetode($query, string $metode)
    {
        return $query->where('metode', strtoupper($metode));
    }

    /**
     * Scope: Filter by IP address
     */
    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip_address', 'like', '%'.$ip.'%');
    }

    /**
     * Scope: Filter by status code (from metadata)
     */
    public function scopeByStatusCode($query, int $statusCode)
    {
        return $query->where('metadata->status_code', $statusCode);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateBetween($query, ?string $fromDate, ?string $toDate)
    {
        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        return $query;
    }

    /**
     * Scope: Search dalam aktivitas, modul, url, ip
     */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('aktivitas', 'like', '%'.$keyword.'%')
                ->orWhere('modul', 'like', '%'.$keyword.'%')
                ->orWhere('url', 'like', '%'.$keyword.'%')
                ->orWhere('ip_address', 'like', '%'.$keyword.'%')
                ->orWhereHas('user', function ($uq) use ($keyword) {
                    $uq->where('name', 'like', '%'.$keyword.'%')
                        ->orWhere('email', 'like', '%'.$keyword.'%');
                });
        });
    }

    /**
     * Scope: Get distinct modul list
     */
    public function scopeGetModulList($query)
    {
        return $query->select('modul')
            ->whereNotNull('modul')
            ->distinct()
            ->orderBy('modul');
    }
}
