<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MigrasiPemetaanTabel extends Model
{
    protected $table = 'migrasi_pemetaan_tabels';

    protected $fillable = [
        'tabel_legacy',
        'jml_baris_legacy',
        'jml_kolom_legacy',
        'klasifikasi',
        'tabel_baru',
        'pemetaan_field',
        'status_impor',
        'terakhir_scan_at',
        'scanned_by',
    ];

    protected $casts = [
        'pemetaan_field'   => 'array',
        'terakhir_scan_at' => 'datetime',
        'jml_baris_legacy' => 'integer',
        'jml_kolom_legacy' => 'integer',
    ];

    // ── Scopes ────────────────────────────────────────────────
    public function scopeMaster($query)       { return $query->where('klasifikasi', 'master'); }
    public function scopeTransaksi($query)    { return $query->where('klasifikasi', 'transaksi'); }
    public function scopeAktif($query)        { return $query->whereIn('klasifikasi', ['master', 'transaksi']); }

    // ── Helpers ───────────────────────────────────────────────
    public function klasifikasiLabel(): string
    {
        return match ($this->klasifikasi) {
            'master'    => 'Master (m_*)',
            'transaksi' => 'Transaksi (t_*)',
            default     => 'Abaikan',
        };
    }

    public function klasifikasiBadge(): string
    {
        return match ($this->klasifikasi) {
            'master'    => 'bg-primary',
            'transaksi' => 'bg-warning text-dark',
            default     => 'bg-secondary',
        };
    }

    public function statusImporBadge(): string
    {
        return match ($this->status_impor) {
            'done'    => 'bg-success',
            'error'   => 'bg-danger',
            'abaikan' => 'bg-secondary',
            default   => 'bg-light text-dark border',
        };
    }
}
