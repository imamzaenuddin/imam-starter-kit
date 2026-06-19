<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogMigrasi extends Model
{
    protected $table = 'log_migrasis';

    protected $fillable = [
        'fase',
        'entitas',
        'tabel_legacy',
        'tabel_target',
        'status',
        'total_legacy',
        'total_imported',
        'total_skipped',
        'total_error',
        'pesan_error',
        'job_id',
        'started_at',
        'finished_at',
        'user_id',
    ];

    protected $casts = [
        'fase'          => 'integer',
        'total_legacy'  => 'integer',
        'total_imported'=> 'integer',
        'total_skipped' => 'integer',
        'total_error'   => 'integer',
        'started_at'    => 'datetime',
        'finished_at'   => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ──────────────────────────────────────────
    public function scopeFase($query, int $fase)
    {
        return $query->where('fase', $fase);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeEntitas($query, string $entitas)
    {
        return $query->where('entitas', $entitas);
    }

    // ── Helpers ─────────────────────────────────────────
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    public function isError(): bool
    {
        return $this->status === 'error';
    }

    public function durasiDetik(): ?int
    {
        if ($this->started_at && $this->finished_at) {
            return $this->finished_at->diffInSeconds($this->started_at);
        }
        return null;
    }

    public function durasiLabel(): string
    {
        $detik = $this->durasiDetik();
        if ($detik === null) return '-';
        if ($detik < 60) return "{$detik} detik";
        $menit = intdiv($detik, 60);
        $sisa  = $detik % 60;
        return "{$menit}m {$sisa}s";
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'done'      => 'bg-success',
            'running'   => 'bg-primary',
            'error'     => 'bg-danger',
            'cancelled' => 'bg-secondary',
            default     => 'bg-warning text-dark',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'done'      => 'Selesai',
            'running'   => 'Berjalan',
            'error'     => 'Error',
            'cancelled' => 'Dibatalkan',
            default     => 'Menunggu',
        };
    }
}
