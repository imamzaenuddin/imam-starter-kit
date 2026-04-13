<?php

namespace App\Services;

use App\Models\Identitas;
use Illuminate\Support\Facades\Cache;

class IdentitasService
{
    public function aktif(): ?Identitas
    {
        /** @var ?Identitas $result */
        $result = Cache::remember('identitas_aktif', now()->addMinutes(30), function () {
            $identitasAktif = Identitas::query()
                ->where('is_active', true)
                ->latest('id')
                ->first();

            // Fallback untuk kebutuhan branding UI ketika mode maintenance aktif.
            // Logika maintenance tetap aman karena pengecekan maintenance tidak memakai service ini.
            return $identitasAktif ?: Identitas::query()->latest('id')->first();
        });

        return $result;
    }

    public function hapusCache(): void
    {
        Cache::forget('identitas_aktif');
    }
}
