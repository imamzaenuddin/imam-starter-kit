<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $table = 'm_menu';

    protected $fillable = ['nama', 'url', 'icon', 'parent_id', 'urutan', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public static function classIconValid(): array
    {
        static $classIcon = null;

        if ($classIcon !== null) {
            return $classIcon;
        }

        $classFontLama = [];
        $lokasiCssLama = base_path('node_modules/boxicons/css/boxicons.css');

        if (is_file($lokasiCssLama)) {
            preg_match_all('/\.((?:bx|bxs|bxl)-[a-z0-9-]+):before/i', (string) file_get_contents($lokasiCssLama), $cocokCss);
            $classFontLama = $cocokCss[1] ?? [];
        }

        $classIconBaru = [];
        $lokasiJsonBaru = base_path('node_modules/@iconify/json/json/boxicons.json');

        if (is_file($lokasiJsonBaru)) {
            $isiJson = json_decode((string) file_get_contents($lokasiJsonBaru), true);
            $namaIcon = array_keys($isiJson['icons'] ?? []);
            $classIconBaru = array_map(fn($nama) => 'bx-' . $nama, $namaIcon);
        }

        return $classIcon = collect([...$classFontLama, ...$classIconBaru])->unique()->values()->all();
    }

    public static function namaClassIcon(?string $icon): ?string
    {
        $icon = trim((string) $icon);

        if ($icon === '') {
            return null;
        }

        return collect(preg_split('/\s+/', $icon) ?: [])
            ->first(fn($item) => preg_match('/^(?:bx|bxs|bxl)-[a-z0-9-]+$/i', (string) $item) === 1);
    }

    public static function iconTersedia(?string $icon): bool
    {
        $namaClass = static::namaClassIcon($icon);

        if (! $namaClass) {
            return trim((string) $icon) === '';
        }

        return in_array($namaClass, static::classIconValid(), true);
    }

    public static function classIconRender(?string $icon, string $kelasTambahan = ''): string
    {
        $icon = trim((string) $icon);
        $kelasTambahan = trim($kelasTambahan);

        if ($icon === '') {
            return $kelasTambahan;
        }

        $namaClass = static::namaClassIcon($icon);

        if (! $namaClass) {
            return trim($kelasTambahan . ' ' . $icon);
        }

        $classFontLama = [];
        $lokasiCssLama = base_path('node_modules/boxicons/css/boxicons.css');

        if (is_file($lokasiCssLama)) {
            preg_match_all('/\.((?:bx|bxs|bxl)-[a-z0-9-]+):before/i', (string) file_get_contents($lokasiCssLama), $cocokCss);
            $classFontLama = $cocokCss[1] ?? [];
        }

        if (in_array($namaClass, $classFontLama, true)) {
            return trim($kelasTambahan . ' ' . $icon);
        }

        if (str_starts_with($namaClass, 'bx-')) {
            return trim($kelasTambahan . ' iconify-bx iconify-' . $namaClass);
        }

        return trim($kelasTambahan . ' ' . $icon);
    }

    /** Menu induk (self-referential) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /** Menu anak (sub-menu) */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('urutan');
    }

    /** Level-level yang memiliki akses ke menu ini */
    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'm_level_menu')
            ->withPivot([
                'dapat_buat',
                'dapat_lihat',
                'dapat_ubah',
                'dapat_hapus',
                'dapat_backup',
                'dapat_restore',
                'dapat_hapus_backup',
            ])
            ->withTimestamps();
    }

    /** Scope: hanya menu aktif */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Scope: hanya menu root (tanpa parent) */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
