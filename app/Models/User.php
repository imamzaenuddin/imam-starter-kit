<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 't_user';

    protected $fillable = [
        'name',
        'email',
        'nama_panggilan',
        'nomor_ktp',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat_ktp',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'kabupaten_kota',
        'provinsi',
        'agama',
        'status_perkawinan',
        'pekerjaan',
        'kewarganegaraan',
        'foto_profil',
        'level_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'tanggal_lahir' => 'date',
            'password'          => 'hashed',
        ];
    }

    /** Relasi ke tabel m_level */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Cek apakah user punya izin tertentu pada sebuah menu.
     *
     * @param  string  $menuUrl   URL atau nama route menu
     * @param  string  $izin      'dapat_lihat'|'dapat_buat'|'dapat_ubah'|'dapat_hapus'
     */
    public function bisaMenu(string $menuUrl, string $izin = 'dapat_lihat'): bool
    {
        if (! $this->level_id) {
            return false;
        }

        return $this->level
            ->menus()
            ->where('url', $menuUrl)
            ->where('is_active', true)
            ->wherePivot($izin, true)
            ->exists();
    }

    /** Inisial nama user untuk avatar */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn(string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /** URL foto profil untuk avatar navbar */
    public function getProfilePhotoUrlAttribute(): string
    {
        if (! empty($this->foto_profil) && Storage::disk('public')->exists($this->foto_profil)) {
            return Storage::url($this->foto_profil);
        }

        return asset('assets/img/avatars/1.png');
    }
}
