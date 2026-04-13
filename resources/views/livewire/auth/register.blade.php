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

    <p class="text-center">
        <span>{{ __('messages.already_have_account') }}</span>
        <a href="{{ route('login') }}" wire:navigate>
            <span>{{ __('messages.sign_in_instead') }}</span>
        </a>
    </p>
</div>
