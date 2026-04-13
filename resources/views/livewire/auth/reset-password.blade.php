<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $tampilkanPassword = false;
    public bool $tampilkanKonfirmasiPassword = false;

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email');
    }

    public function togglePasswordVisibility(): void
    {
        $this->tampilkanPassword = ! $this->tampilkanPassword;
    }

    public function toggleConfirmPasswordVisibility(): void
    {
        $this->tampilkanKonfirmasiPassword = ! $this->tampilkanKonfirmasiPassword;
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PasswordReset) {
            $this->addError('email', __($status));
            return;
        }

        Session::flash('status', __($status));
        $this->redirectRoute('login', navigate: true);
    }
}; ?>

@section('title', __('messages.reset_password_page_title'))

@section('page-style')
@vite([
    'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection

<div>
    <h4 class="mb-1">{{ __('messages.reset_password_title') }} 🔑</h4>
    <p class="mb-6">{{ __('messages.reset_password_subtitle') }}</p>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-info mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="resetPassword" class="mb-6">
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
            <label class="form-label" for="password">{{ __('messages.new_password') }}</label>
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

        <button type="submit" class="btn btn-primary d-grid w-100 mb-6">
            {{ __('messages.set_new_password') }}
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="d-flex justify-content-center" wire:navigate>
                <i class="bx bx-chevron-left scaleX-n1-rtl me-1"></i>
                {{ __('messages.back_to_login') }}
            </a>
        </div>
    </form>
</div>
