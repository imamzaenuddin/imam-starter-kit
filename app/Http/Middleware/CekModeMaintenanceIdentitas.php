<?php

namespace App\Http\Middleware;

use App\Models\Identitas;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class CekModeMaintenanceIdentitas
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->modeMaintenanceAktif()) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && ! $this->isSuperadmin($user)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', __('messages.maintenance_login_blocked'));
        }

        return $next($request);
    }

    private function modeMaintenanceAktif(): bool
    {
        if (! Schema::hasTable('identitas')) {
            return false;
        }

        return ! Identitas::query()->where('is_active', true)->exists();
    }

    private function isSuperadmin($user): bool
    {
        return strtolower((string) optional($user->level)->nama_level) === 'superadmin';
    }
}
