<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MenuService
{
    /**
     * Ambil hierarki menu yang diizinkan bagi level user yang sedang login.
     * Hasil di-cache per user agar tidak query berulang setiap request.
     *
     * @return Collection<int, Menu>   Koleksi menu root beserta children-nya
     */
    public function menuTersedia(): Collection
    {
        $user = Auth::user();

        if (! $user || ! $user->level_id) {
            return new Collection();
        }

        $cacheKey = "menu_user_{$user->id}";

        /** @var Collection<int, Menu> $result */
        $result = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($user) {
            // Ambil semua menu_id yang boleh dilihat oleh level user ini
            $menuIds = $user->level
                ->menus()
                ->active()
                ->wherePivot('dapat_lihat', true)
                ->pluck('m_menu.id');

            // Muat menu root beserta children yang diizinkan, eager-load pivot sekali
            return Menu::with(['children' => function ($query) use ($menuIds) {
                $query->whereIn('id', $menuIds)
                    ->active()
                    ->orderBy('urutan');
            }])
                ->whereIn('id', $menuIds)
                ->active()
                ->root()
                ->orderBy('urutan')
                ->get();
        });

        return $result instanceof Collection ? $result : new Collection();
    }

    /**
     * Cek izin spesifik user pada URL menu tertentu.
     *
     * @param  string  $url    URL menu (nilai kolom `url` di tabel menus)
     * @param  string  $izin   Kolom pivot: dapat_lihat|dapat_buat|dapat_ubah|dapat_hapus
     */
    public function boleh(string $url, string $izin = 'dapat_lihat'): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->level_id) {
            return false;
        }

        $izinValid = ['dapat_lihat', 'dapat_buat', 'dapat_ubah', 'dapat_hapus'];

        if (! in_array($izin, $izinValid, true)) {
            return false;
        }

        return $user->level
            ->menus()
            ->where('url', $url)
            ->where('is_active', true)
            ->wherePivot($izin, true)
            ->exists();
    }

    /**
     * Hapus cache menu milik user tertentu (panggil setelah update mapping).
     */
    public function hapusCache(int $userId): void
    {
        Cache::forget("menu_user_{$userId}");
    }

    /**
     * Hapus cache menu seluruh user (panggil setelah bulk update level/menu).
     */
    public function hapusCacheLevel(int $levelId): void
    {
        // Hapus cache semua user yang memiliki level ini
        \App\Models\User::where('level_id', $levelId)
            ->pluck('id')
            ->each(fn($id) => Cache::forget("menu_user_{$id}"));
    }
}
