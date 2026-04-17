<?php

namespace App\Services;

use App\Mail\SendTwoFactorOtp;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TwoFactorService
{
    public function bolehKelola2fa(?User $aktor, ?User $target): bool
    {
        if (! $aktor || ! $target) {
            return false;
        }

        return (int) $aktor->id === (int) $target->id;
    }

    public function wajibSaatLogin(?User $user, ?string $email = null): bool
    {
        if (! $user || ! $user->two_factor_enabled) {
            return false;
        }

        $konfigurasi = app(PengaturanAplikasiService::class)->konfigurasiAktif();
        $mode = (string) ($konfigurasi['otp_mode'] ?? 'always');

        if ($mode === 'always') {
            return true;
        }

        return $this->wajibSaatLoginAdaptif($user, $email, $konfigurasi);
    }

    public function keyKode(int $userId): string
    {
        return 'two_factor_code_user_'.$userId;
    }

    public function keyAttempt(int $userId): string
    {
        return 'two_factor_attempt_user_'.$userId;
    }

    public function kirimKodeLogin(User $user): void
    {
        $kode = (string) random_int(100000, 999999);

        Cache::put($this->keyKode($user->id), [
            'hash' => Hash::make($kode),
            'expires_at' => now()->addMinutes(5)->timestamp,
        ], now()->addMinutes(5));

        Cache::put($this->keyAttempt($user->id), 0, now()->addMinutes(5));

        Mail::send(new SendTwoFactorOtp($user, $kode));
    }

    public function verifyKode(User $user, string $kodeInput): bool
    {
        $data = Cache::get($this->keyKode($user->id));

        if (! is_array($data) || ! isset($data['hash'])) {
            return false;
        }

        $attempt = (int) Cache::get($this->keyAttempt($user->id), 0);

        if ($attempt >= 5) {
            return false;
        }

        $valid = Hash::check(trim($kodeInput), (string) $data['hash']);

        if (! $valid) {
            Cache::put($this->keyAttempt($user->id), $attempt + 1, now()->addMinutes(5));

            return false;
        }

        Cache::forget($this->keyKode($user->id));
        Cache::forget($this->keyAttempt($user->id));

        return true;
    }

    public function aktifkanUntuk(User $user): void
    {
        $user->forceFill([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ])->save();
    }

    public function nonaktifkanUntuk(User $user): void
    {
        $user->forceFill([
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
        ])->save();

        Cache::forget($this->keyKode($user->id));
        Cache::forget($this->keyAttempt($user->id));
    }

    public function catatLoginBerhasil(User $user): void
    {
        $user->forceFill([
            'last_login_at' => now(),
        ])->save();
    }

    private function wajibSaatLoginAdaptif(User $user, ?string $email, array $konfigurasi): bool
    {
        if (! $user->last_login_at) {
            return true;
        }

        $batasHariTidakLogin = max(0, (int) ($konfigurasi['otp_inactive_days'] ?? 30));

        if ($batasHariTidakLogin > 0 && $user->last_login_at->lte(now()->subDays($batasHariTidakLogin))) {
            return true;
        }

        $batasGagal = max(0, (int) ($konfigurasi['otp_failed_attempts'] ?? 3));
        $windowMenit = max(1, (int) ($konfigurasi['otp_failed_window_minutes'] ?? 15));

        if ($batasGagal === 0) {
            return false;
        }

        return $this->jumlahPercobaanLoginGagal($user, $email, $windowMenit) >= $batasGagal;
    }

    private function jumlahPercobaanLoginGagal(User $user, ?string $email, int $windowMenit): int
    {
        if (! Schema::hasTable('m_login_attempt')) {
            return 0;
        }

        $emailBersih = $email ? Str::lower(trim($email)) : Str::lower(trim((string) $user->email));

        return LoginAttempt::query()
            ->where(function ($query) use ($user, $emailBersih) {
                $query->where('user_id', $user->id);

                if ($emailBersih !== '') {
                    $query->orWhere('email', $emailBersih);
                }
            })
            ->whereIn('status', ['gagal', 'lockout'])
            ->where('created_at', '>=', now()->subMinutes($windowMenit))
            ->count();
    }
}
