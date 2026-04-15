<?php

use App\Models\User;
use App\Services\LoginAttemptService;
use App\Services\TwoFactorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|min:6|max:6')]
    public string $kode = '';

    public function mount(): void
    {
        if (! session()->has('two_factor_user_id')) {
            $this->redirectRoute('login', navigate: true);
        }
    }

    public function verifikasi(): void
    {
        $this->validate();

        $userId = (int) session('two_factor_user_id');
        $remember = (bool) session('two_factor_remember', false);

        $user = User::query()->find($userId);

        if (! $user) {
            session()->forget(['two_factor_user_id', 'two_factor_remember']);
            throw ValidationException::withMessages([
                'kode' => __('messages.two_factor_user_not_found'),
            ]);
        }

        $valid = app(TwoFactorService::class)->verifyKode($user, $this->kode);

        if (! $valid) {
            app(LoginAttemptService::class)->catat(
                'gagal',
                $user->email,
                request(),
                $user,
                'Kode 2FA tidak valid'
            );

            throw ValidationException::withMessages([
                'kode' => __('messages.two_factor_invalid_code'),
            ]);
        }

        session()->forget(['two_factor_user_id', 'two_factor_remember']);

        Auth::loginUsingId($user->id, $remember);
        Session::regenerate();
        app(TwoFactorService::class)->catatLoginBerhasil($user);

        app(LoginAttemptService::class)->catat(
            'sukses',
            $user->email,
            request(),
            $user,
            'Login berhasil dengan verifikasi 2FA'
        );

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    public function kirimUlang(): void
    {
        $userId = (int) session('two_factor_user_id');
        $user = User::query()->find($userId);

        if (! $user) {
            session()->forget(['two_factor_user_id', 'two_factor_remember']);
            $this->redirectRoute('login', navigate: true);

            return;
        }

        app(TwoFactorService::class)->kirimKodeLogin($user);
        session()->flash('status', __('messages.two_factor_resent'));
    }
};
?>

@section('title', __('messages.two_factor_challenge_title'))

<div>
    <h4 class="fw-bold mb-1" style="color:#1e293b;font-size:1.4rem">{{ __('messages.two_factor_heading') }}</h4>
    <p class="mb-4" style="color:#64748b;font-size:.875rem;line-height:1.5">
        {{ __('messages.two_factor_subheading') }}
    </p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form wire:submit="verifikasi" class="mb-4">
        <div class="mb-3">
            <label for="kode" class="form-label fw-semibold">{{ __('messages.two_factor_code_label') }}</label>
            <input
                wire:model="kode"
                id="kode"
                type="text"
                inputmode="numeric"
                maxlength="6"
                class="form-control @error('kode') is-invalid @enderror"
                placeholder="000000"
                autocomplete="one-time-code"
            >
            @error('kode') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled" wire:target="verifikasi">
            <span wire:loading.remove wire:target="verifikasi">{{ __('messages.two_factor_verify_button') }}</span>
            <span wire:loading wire:target="verifikasi" style="display:none">{{ __('messages.processing') }}</span>
        </button>
    </form>

    <button type="button" class="btn btn-outline-secondary w-100" wire:click="kirimUlang" wire:loading.attr="disabled" wire:target="kirimUlang">
        <span wire:loading.remove wire:target="kirimUlang">{{ __('messages.two_factor_resend_button') }}</span>
        <span wire:loading wire:target="kirimUlang" style="display:none">{{ __('messages.processing') }}</span>
    </button>
</div>
