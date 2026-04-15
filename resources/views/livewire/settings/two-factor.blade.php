<?php

use App\Services\TwoFactorService;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public bool $enabled = false;
    public int $pemilikAkunId = 0;

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        $this->pemilikAkunId = (int) $user->id;
        $this->enabled = (bool) $user->two_factor_enabled;
    }

    public function aktifkan(): void
    {
        $user = $this->ambilPemilikAkunAtauTolak();

        app(TwoFactorService::class)->aktifkanUntuk($user);
        $this->enabled = true;

        session()->flash('status', __('messages.two_factor_enabled_success'));
    }

    public function nonaktifkan(): void
    {
        $user = $this->ambilPemilikAkunAtauTolak();

        app(TwoFactorService::class)->nonaktifkanUntuk($user);
        $this->enabled = false;

        session()->flash('status', __('messages.two_factor_disabled_success'));
    }

    private function ambilPemilikAkunAtauTolak(): User
    {
        $aktor = auth()->user();
        $target = User::query()->find($this->pemilikAkunId);

        if (! app(TwoFactorService::class)->bolehKelola2fa($aktor, $target)) {
            abort(403);
        }

        return $target;
    }
};
?>

@section('title', __('messages.two_factor_settings_title'))

<div>
    <h4 class="fw-bold mb-1">{{ __('messages.two_factor_settings_heading') }}</h4>
    <p class="text-muted mb-4">{{ __('messages.two_factor_settings_subheading') }}</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">{{ __('messages.two_factor_status') }}</h6>
                    <p class="text-muted mb-0">
                        {{ $enabled ? __('messages.two_factor_status_enabled') : __('messages.two_factor_status_disabled') }}
                    </p>
                </div>
                <div>
                    @if ($enabled)
                        <button type="button" class="btn btn-outline-danger" wire:click="nonaktifkan" wire:loading.attr="disabled" wire:target="nonaktifkan">
                            <span wire:loading.remove wire:target="nonaktifkan">{{ __('messages.two_factor_disable_button') }}</span>
                            <span wire:loading wire:target="nonaktifkan" style="display:none">{{ __('messages.processing') }}</span>
                        </button>
                    @else
                        <button type="button" class="btn btn-primary" wire:click="aktifkan" wire:loading.attr="disabled" wire:target="aktifkan">
                            <span wire:loading.remove wire:target="aktifkan">{{ __('messages.two_factor_enable_button') }}</span>
                            <span wire:loading wire:target="aktifkan" style="display:none">{{ __('messages.processing') }}</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
