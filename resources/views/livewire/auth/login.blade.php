<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;
    public bool $tampilkanPassword = false;

    public function togglePasswordVisibility(): void
    {
        $this->tampilkanPassword = ! $this->tampilkanPassword;
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        if ($this->modeMaintenanceAktif() && ! $this->isSuperadmin(Auth::user())) {
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('messages.maintenance_login_blocked'),
            ]);
        }

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    protected function modeMaintenanceAktif(): bool
    {
        if (! Schema::hasTable('m_identitas')) {
            return false;
        }

        return ! \App\Models\Identitas::query()->where('is_active', true)->exists();
    }

    protected function isSuperadmin(?\App\Models\User $user): bool
    {
        return $user && strtolower((string) optional($user->level)->nama_level) === 'superadmin';
    }
};
?>
@php
    $identitas = app(\App\Services\IdentitasService::class)->aktif();
    $namaAplikasi = $identitas?->nama_aplikasi ?? 'Sistem Informasi Organisasi';
    $emailHelpdesk = $identitas?->email;
    $maintenanceAktif = \Illuminate\Support\Facades\Schema::hasTable('m_identitas')
        && ! \App\Models\Identitas::query()->where('is_active', true)->exists();
@endphp

@section('title', __('messages.login_page_title', ['app' => $namaAplikasi]))

<div>
    {{-- Heading --}}
    <h4 class="fw-bold mb-1" style="color:#1e293b;font-size:1.4rem">{{ __('messages.welcome_title') }} 👋</h4>
    <p class="mb-4" style="color:#64748b;font-size:.875rem;line-height:1.5">
        {{ __('messages.login_subtitle', ['app' => $namaAplikasi]) }}
    </p>

    @if ($maintenanceAktif)
        <div class="d-flex align-items-start mb-4 p-3 rounded-3" style="background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;font-size:.875rem">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-right:.5rem;margin-top:.1rem">
                <path d="M12 9v4" stroke="#9a3412" stroke-width="1.8" stroke-linecap="round"/>
                <circle cx="12" cy="16.2" r="1" fill="#9a3412"/>
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="#9a3412" stroke-width="1.8" fill="none"/>
            </svg>
            <div>
                <div class="fw-semibold mb-1">{{ __('messages.maintenance_mode_title') }}</div>
                <div>{{ __('messages.maintenance_login_blocked') }}</div>
            </div>
        </div>
    @endif

    {{-- Session Status --}}
    @if (session('status'))
        <div class="d-flex align-items-center mb-4 p-3 rounded-3" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;font-size:.875rem">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-right:.5rem">
                <circle cx="12" cy="12" r="9" stroke="#166534" stroke-width="1.8"/>
                <path d="M8 12l3 3 5-5" stroke="#166534" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login" class="mb-4">

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold" style="font-size:.82rem;color:#374151;letter-spacing:.3px">
                {{ __('messages.email_label_caps') }}
            </label>
            <div class="input-group @error('email') is-invalid @enderror">
                <span class="input-group-text" style="border-right:0;background:#f8fafc;border-color:#e2e8f0;color:var(--sio-main-color,#696cff)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                        <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M2 8l10 6 10-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </span>
                <input
                    wire:model="email"
                    type="email"
                    class="form-control"
                    style="border-left:0;background:#f8fafc;border-color:#e2e8f0;font-size:.9rem"
                    id="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="{{ $emailHelpdesk ?: 'email@organisasi.id' }}"
                >
            </div>
            @error('email')
                <div class="invalid-feedback d-block" style="font-size:.78rem">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="form-label fw-semibold mb-0" style="font-size:.82rem;color:#374151;letter-spacing:.3px">
                    {{ __('messages.password_label_caps') }}
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate
                       class="text-decoration-none" style="font-size:.78rem;color:var(--sio-main-color,#696cff);font-weight:500">
                        {{ __('messages.forgot_password') }}
                    </a>
                @endif
            </div>
            <div class="position-relative">
                <div class="input-group @error('password') is-invalid @enderror">
                <span class="input-group-text" style="border-right:0;background:#f8fafc;border-color:#e2e8f0;color:var(--sio-main-color,#696cff)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                        <rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M8 11V7a4 4 0 0 1 8 0v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </span>
                <input
                    wire:model="password"
                    type="{{ $tampilkanPassword ? 'text' : 'password' }}"
                    class="form-control"
                    style="border-left:0;background:#f8fafc;border-color:#e2e8f0;font-size:.9rem;padding-right:2.8rem"
                    id="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••••••"
                >
                </div>
                <button
                        type="button"
                        wire:click="togglePasswordVisibility"
                        class="btn btn-sm p-0 d-inline-flex align-items-center justify-content-center"
                        aria-label="Tampilkan atau sembunyikan kata sandi"
                        aria-pressed="{{ $tampilkanPassword ? 'true' : 'false' }}"
                        style="position:absolute;right:.85rem;top:50%;transform:translateY(-50%);width:1.25rem;height:1.25rem;z-index:5;cursor:pointer;color:#94a3b8;background:transparent;border:0"
                >
                    @if (! $tampilkanPassword)
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" stroke="#94a3b8" stroke-width="1.8"/>
                        <circle cx="12" cy="12" r="3" stroke="#94a3b8" stroke-width="1.8"/>
                    </svg>
                    @else
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" stroke="var(--sio-main-color,#696cff)" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" stroke="var(--sio-main-color,#696cff)" stroke-width="1.8" stroke-linecap="round"/>
                        <line x1="1" y1="1" x2="23" y2="23" stroke="var(--sio-main-color,#696cff)" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    @endif
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block" style="font-size:.78rem">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remember Me --}}
        <div class="mb-4 d-flex align-items-center justify-content-between">
            <div class="form-check mb-0">
                <input wire:model="remember" type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember"
                       style="font-size:.85rem;color:#64748b;cursor:pointer">
                    {{ __('messages.remember_me_30_days') }}
                </label>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="sio-btn-submit"
                wire:loading.attr="disabled" wire:target="login">
            <span class="d-flex align-items-center justify-content-center gap-2">
                <span wire:loading.remove wire:target="login">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" style="vertical-align:middle;margin-right:.25rem">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="10 17 15 12 10 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="15" y1="12" x2="3" y2="12" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    {{ __('messages.login_to_system') }}
                </span>
                <span wire:loading wire:target="login" style="display:none">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="vertical-align:middle;margin-right:.25rem"></span>
                    {{ __('messages.processing') }}
                </span>
            </span>
        </button>

    </form>

    {{-- Register link --}}
    @if (Route::has('register'))
        <div class="text-center pt-2" style="border-top:1px solid #f1f5f9">
            <p class="mb-0 mt-3" style="font-size:.85rem;color:#64748b">
                {{ __('messages.no_account_yet') }}
                <a href="{{ route('register') }}" wire:navigate
                   class="text-decoration-none fw-semibold" style="color:var(--sio-main-color,#696cff)">
                    {{ __('messages.register_now') }}
                </a>
            </p>
        </div>
    @endif
</div>
