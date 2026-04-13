<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $password = '';
    public bool $tampilkanPassword = false;

    public function togglePasswordVisibility(): void
    {
        $this->tampilkanPassword = ! $this->tampilkanPassword;
    }

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

@section('title', __('messages.confirm_password_page_title'))

@section('page-style')
@vite([
    'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection

<div>
    <h4 class="mb-1">{{ __('messages.security_verification_title') }} 🔐</h4>
    <p class="mb-6">{{ __('messages.security_verification_subtitle') }}</p>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-info mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="confirmPassword" class="mb-6">
        <div class="mb-6">
            <label class="form-label" for="password">{{ __('messages.password') }}</label>
            <div class="position-relative">
                <div class="input-group input-group-merge">
                    <input
                        wire:model="password"
                        type="{{ $tampilkanPassword ? 'text' : 'password' }}"
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        required
                        autocomplete="current-password"
                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                        style="padding-right:2.8rem"
                    >
                </div>
                <button
                    type="button"
                    wire:click="togglePasswordVisibility"
                    class="btn btn-sm p-0 d-inline-flex align-items-center justify-content-center"
                    aria-label="Tampilkan atau sembunyikan kata sandi"
                    title="Tampilkan atau sembunyikan kata sandi"
                    aria-pressed="{{ $tampilkanPassword ? 'true' : 'false' }}"
                    style="position:absolute;right:.85rem;top:50%;transform:translateY(-50%);width:1.25rem;height:1.25rem;z-index:5;cursor:pointer;color:#94a3b8;background:transparent;border:0"
                >
                    @if (! $tampilkanPassword)
                        <i class="bx bx-show"></i>
                    @else
                        <i class="bx bx-hide" style="color:var(--sio-main-color,#696cff)"></i>
                    @endif
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary d-grid w-100 mb-6">
            {{ __('messages.confirm_password_button') }}
        </button>
    </form>

    <div class="text-center">
        <a href="{{ route('dashboard') }}" class="d-flex justify-content-center" wire:navigate>
            <i class="bx bx-chevron-left scaleX-n1-rtl me-1"></i>
            {{ __('messages.back_to_dashboard') }}
        </a>
    </div>
</div>
