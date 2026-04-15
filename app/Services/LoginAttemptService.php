<?php

namespace App\Services;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LoginAttemptService
{
    public function catat(
        string $status,
        ?string $email,
        ?Request $request = null,
        ?User $user = null,
        ?string $alasan = null,
        array $metadata = []
    ): void {
        if (! Schema::hasTable('m_login_attempt')) {
            return;
        }

        $request ??= request();
        $emailBersih = $email ? Str::lower(trim($email)) : null;

        if (! $user && $emailBersih) {
            $user = User::query()->where('email', $emailBersih)->first();
        }

        LoginAttempt::query()->create([
            'user_id' => $user?->id,
            'email' => $emailBersih,
            'ip_address' => $request?->ip(),
            'user_agent' => Str::limit((string) $request?->userAgent(), 1000, ''),
            'status' => $status,
            'alasan' => $alasan,
            'metadata' => $metadata,
        ]);
    }
}
