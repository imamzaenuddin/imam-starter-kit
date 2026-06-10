<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;
    public bool $tampilkanPassword = false;
    public bool $tampilkanKonfirmasiPassword = false;

    public function togglePasswordVisibility(): void
    {
        $this->tampilkanPassword = ! $this->tampilkanPassword;
    }

    public function toggleConfirmPasswordVisibility(): void
    {
        $this->tampilkanKonfirmasiPassword = ! $this->tampilkanKonfirmasiPassword;
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

@section('title', __('messages.register_page_title'))

@section('page-style')
@vite([
    'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection

<div>
    <h4 class="mb-1">{{ __('messages.register_title') }} 🚀</h4>
    <p class="mb-6">{{ __('messages.register_subtitle') }}</p>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-info mb-4">
            {{ session('status') }}
        </div>
    @endif

    {{-- Error OAuth --}}
    @if (session('error'))
        <div class="d-flex align-items-center mb-4 p-3 rounded-3" style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-size:.875rem">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-right:.5rem">
                <circle cx="12" cy="12" r="9" stroke="#991b1b" stroke-width="1.8"/>
                <path d="M12 9v4M12 16.2h.01" stroke="#991b1b" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit="register" class="mb-6">
        <div class="mb-6">
            <label for="name" class="form-label">{{ __('messages.name') }}</label>
            <input
                wire:model="name"
                type="text"
                class="form-control @error('name') is-invalid @enderror"
                id="name"
                required
                autofocus
                autocomplete="name"
                placeholder="{{ __('messages.enter_your_name') }}"
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-6">
            <label for="email" class="form-label">{{ __('messages.email') }}</label>
            <input
                wire:model="email"
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                required
                autocomplete="email"
                placeholder="{{ __('messages.enter_your_email') }}"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

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
                        autocomplete="new-password"
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

        <div class="mb-6">
            <label class="form-label" for="password_confirmation">{{ __('messages.confirm_password') }}</label>
            <div class="position-relative">
                <div class="input-group input-group-merge">
                    <input
                        wire:model="password_confirmation"
                        type="{{ $tampilkanKonfirmasiPassword ? 'text' : 'password' }}"
                        class="form-control @error('password_confirmation') is-invalid @enderror"
                        id="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                        style="padding-right:2.8rem"
                    >
                </div>
                <button
                    type="button"
                    wire:click="toggleConfirmPasswordVisibility"
                    class="btn btn-sm p-0 d-inline-flex align-items-center justify-content-center"
                    aria-label="Tampilkan atau sembunyikan konfirmasi kata sandi"
                    title="Tampilkan atau sembunyikan konfirmasi kata sandi"
                    aria-pressed="{{ $tampilkanKonfirmasiPassword ? 'true' : 'false' }}"
                    style="position:absolute;right:.85rem;top:50%;transform:translateY(-50%);width:1.25rem;height:1.25rem;z-index:5;cursor:pointer;color:#94a3b8;background:transparent;border:0"
                >
                    @if (! $tampilkanKonfirmasiPassword)
                        <i class="bx bx-show"></i>
                    @else
                        <i class="bx bx-hide" style="color:var(--sio-main-color,#696cff)"></i>
                    @endif
                </button>
            </div>
            @error('password_confirmation')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-8">
            <div class="form-check mb-0 ms-2">
                <input wire:model="terms" type="checkbox" class="form-check-input @error('terms') is-invalid @enderror" id="terms">
                <label class="form-check-label" for="terms">
                    {{ __('messages.i_agree_to') }}
                    <a href="javascript:void(0);">{{ __('messages.privacy_policy_terms') }}</a>
                </label>
                @error('terms')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary d-grid w-100 mb-6">
            {{ __('messages.sign_up') }}
        </button>
    </form>

    <div class="my-4">
        <div style="font-size: 0.8rem; color: #94a3b8; display: flex; align-items: center; justify-content: center; gap: 10px;">
            <span style="height: 1px; background: #e2e8f0; flex-grow: 1;"></span>
            <span>atau</span>
            <span style="height: 1px; background: #e2e8f0; flex-grow: 1;"></span>
        </div>
    </div>

    <a href="{{ route('auth.google') }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2 mb-4" 
       style="font-size: 0.9rem; border-color: #cbd5e1; border-radius: 8px; padding: 0.6rem 1rem; font-weight: 600; color: #475569; background: #fff;">
        <svg width="18" height="18" viewBox="0 0 24 24" style="vertical-align: middle;">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        Daftar dengan Google
    </a>

    <p class="text-center">
        <span>{{ __('messages.already_have_account') }}</span>
        <a href="{{ route('login') }}" wire:navigate>
            <span>{{ __('messages.sign_in_instead') }}</span>
        </a>
    </p>
</div>
