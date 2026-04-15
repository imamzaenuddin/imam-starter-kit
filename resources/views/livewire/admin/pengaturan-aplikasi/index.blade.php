<?php

use App\Services\LogAktivitasService;
use App\Services\PengaturanAplikasiService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $timezone = 'Asia/Jakarta';
    public string $localeDefault = 'id';
    public int $batasUploadKb = 10240;
    public int $paginationDefault = 10;
    public string $otpMode = 'always';
    public int $otpInactiveDays = 30;
    public int $otpFailedAttempts = 3;
    public int $otpFailedWindowMinutes = 15;

    public function mount(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pengaturan-aplikasi', 'dapat_lihat')) {
            abort(403);
        }

        $konfigurasi = $this->service()->konfigurasiAktif();

        $this->timezone = $konfigurasi['timezone'];
        $this->localeDefault = $konfigurasi['locale_default'];
        $this->batasUploadKb = $konfigurasi['batas_upload_kb'];
        $this->paginationDefault = $konfigurasi['pagination_default'];
        $this->otpMode = $konfigurasi['otp_mode'];
        $this->otpInactiveDays = $konfigurasi['otp_inactive_days'];
        $this->otpFailedAttempts = $konfigurasi['otp_failed_attempts'];
        $this->otpFailedWindowMinutes = $konfigurasi['otp_failed_window_minutes'];
    }

    public function simpan(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pengaturan-aplikasi', 'dapat_ubah')) {
            abort(403);
        }

        $data = $this->validate([
            'timezone' => 'required|timezone:all',
            'localeDefault' => 'required|string|max:10',
            'batasUploadKb' => 'required|integer|min:512|max:102400',
            'paginationDefault' => 'required|integer|min:5|max:100',
            'otpMode' => 'required|string|in:always,adaptive',
            'otpInactiveDays' => 'required|integer|min:0|max:3650',
            'otpFailedAttempts' => 'required|integer|min:0|max:20',
            'otpFailedWindowMinutes' => 'required|integer|min:1|max:1440',
        ]);

        $this->service()->simpan([
            'timezone' => $data['timezone'],
            'locale_default' => $data['localeDefault'],
            'batas_upload_kb' => $data['batasUploadKb'],
            'pagination_default' => $data['paginationDefault'],
            'otp_mode' => $data['otpMode'],
            'otp_inactive_days' => $data['otpInactiveDays'],
            'otp_failed_attempts' => $data['otpFailedAttempts'],
            'otp_failed_window_minutes' => $data['otpFailedWindowMinutes'],
        ]);

        $this->service()->terapkanKonfigurasiRuntime();

        app(LogAktivitasService::class)->catatManual(
            __('messages.pengaturan_aplikasi_module'),
            __('messages.pengaturan_aplikasi_log_simpan'),
            '/admin/pengaturan-aplikasi',
            [
                'timezone' => $data['timezone'],
                'locale' => $data['localeDefault'],
                'batas_upload_kb' => $data['batasUploadKb'],
                'pagination_default' => $data['paginationDefault'],
                'otp_mode' => $data['otpMode'],
                'otp_inactive_days' => $data['otpInactiveDays'],
                'otp_failed_attempts' => $data['otpFailedAttempts'],
                'otp_failed_window_minutes' => $data['otpFailedWindowMinutes'],
            ]
        );

        session()->flash('sukses', __('messages.pengaturan_aplikasi_sukses_simpan'));
    }

    public function refreshCache(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pengaturan-aplikasi', 'dapat_ubah')) {
            abort(403);
        }

        $konfigurasi = $this->service()->refreshCache();
        $this->service()->terapkanKonfigurasiRuntime();

        $this->timezone = $konfigurasi['timezone'];
        $this->localeDefault = $konfigurasi['locale_default'];
        $this->batasUploadKb = $konfigurasi['batas_upload_kb'];
        $this->paginationDefault = $konfigurasi['pagination_default'];
        $this->otpMode = $konfigurasi['otp_mode'];
        $this->otpInactiveDays = $konfigurasi['otp_inactive_days'];
        $this->otpFailedAttempts = $konfigurasi['otp_failed_attempts'];
        $this->otpFailedWindowMinutes = $konfigurasi['otp_failed_window_minutes'];

        app(LogAktivitasService::class)->catatManual(
            __('messages.pengaturan_aplikasi_module'),
            __('messages.pengaturan_aplikasi_log_refresh_cache'),
            '/admin/pengaturan-aplikasi'
        );

        session()->flash('sukses', __('messages.pengaturan_aplikasi_sukses_refresh'));
    }

    public function with(): array
    {
        return [
            'timezoneList' => \DateTimeZone::listIdentifiers(),
            'localeList' => [
                'id' => 'Bahasa Indonesia',
                'en' => 'English',
            ],
        ];
    }

    private function service(): PengaturanAplikasiService
    {
        return app(PengaturanAplikasiService::class);
    }
};
?>

@section('title', __('messages.pengaturan_aplikasi_title'))

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ __('messages.pengaturan_aplikasi_heading') }}</h4>
            <p class="text-muted mb-0">{{ __('messages.pengaturan_aplikasi_subheading') }}</p>
        </div>
    </div>

    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('messages.pengaturan_aplikasi_form_title') }}</h5>
            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="refreshCache" wire:loading.attr="disabled" wire:target="refreshCache">
                <span wire:loading.remove wire:target="refreshCache"><i class="bx bx-refresh me-1"></i>{{ __('messages.pengaturan_aplikasi_refresh_cache') }}</span>
                <span wire:loading wire:target="refreshCache" style="display:none">{{ __('messages.processing') }}</span>
            </button>
        </div>
        <div class="card-body">
            <form wire:submit="simpan">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.pengaturan_aplikasi_timezone') }}</label>
                        <select wire:model="timezone" class="form-select @error('timezone') is-invalid @enderror">
                            @foreach ($timezoneList as $tz)
                                <option value="{{ $tz }}">{{ $tz }}</option>
                            @endforeach
                        </select>
                        @error('timezone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.pengaturan_aplikasi_locale') }}</label>
                        <select wire:model="localeDefault" class="form-select @error('localeDefault') is-invalid @enderror">
                            @foreach ($localeList as $kode => $nama)
                                <option value="{{ $kode }}">{{ $nama }} ({{ $kode }})</option>
                            @endforeach
                        </select>
                        @error('localeDefault') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.pengaturan_aplikasi_batas_upload') }}</label>
                        <input type="number" min="512" max="102400" wire:model="batasUploadKb" class="form-control @error('batasUploadKb') is-invalid @enderror">
                        <div class="form-text">{{ __('messages.pengaturan_aplikasi_batas_upload_hint') }}</div>
                        @error('batasUploadKb') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.pengaturan_aplikasi_pagination') }}</label>
                        <input type="number" min="5" max="100" wire:model="paginationDefault" class="form-control @error('paginationDefault') is-invalid @enderror">
                        <div class="form-text">{{ __('messages.pengaturan_aplikasi_pagination_hint') }}</div>
                        @error('paginationDefault') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <hr class="my-1">
                        <h6 class="mb-1">{{ __('messages.pengaturan_aplikasi_otp_title') }}</h6>
                        <p class="text-muted mb-0">{{ __('messages.pengaturan_aplikasi_otp_subheading') }}</p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.pengaturan_aplikasi_otp_mode') }}</label>
                        <select wire:model="otpMode" class="form-select @error('otpMode') is-invalid @enderror">
                            <option value="always">{{ __('messages.pengaturan_aplikasi_otp_mode_always') }}</option>
                            <option value="adaptive">{{ __('messages.pengaturan_aplikasi_otp_mode_adaptive') }}</option>
                        </select>
                        <div class="form-text">{{ __('messages.pengaturan_aplikasi_otp_mode_hint') }}</div>
                        @error('otpMode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.pengaturan_aplikasi_otp_inactive_days') }}</label>
                        <input type="number" min="0" max="3650" wire:model="otpInactiveDays" class="form-control @error('otpInactiveDays') is-invalid @enderror">
                        <div class="form-text">{{ __('messages.pengaturan_aplikasi_otp_inactive_days_hint') }}</div>
                        @error('otpInactiveDays') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.pengaturan_aplikasi_otp_failed_attempts') }}</label>
                        <input type="number" min="0" max="20" wire:model="otpFailedAttempts" class="form-control @error('otpFailedAttempts') is-invalid @enderror">
                        <div class="form-text">{{ __('messages.pengaturan_aplikasi_otp_failed_attempts_hint') }}</div>
                        @error('otpFailedAttempts') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.pengaturan_aplikasi_otp_failed_window_minutes') }}</label>
                        <input type="number" min="1" max="1440" wire:model="otpFailedWindowMinutes" class="form-control @error('otpFailedWindowMinutes') is-invalid @enderror">
                        <div class="form-text">{{ __('messages.pengaturan_aplikasi_otp_failed_window_minutes_hint') }}</div>
                        @error('otpFailedWindowMinutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpan">
                        <span class="d-flex align-items-center gap-2">
                            <span wire:loading.remove wire:target="simpan">{{ __('messages.save') }}</span>
                            <span wire:loading wire:target="simpan" style="display:none">
                                <span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.saving') }}
                            </span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
