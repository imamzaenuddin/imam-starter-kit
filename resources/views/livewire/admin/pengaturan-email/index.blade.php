<?php

use App\Models\PengaturanEmail;
use App\Services\LogAktivitasService;
use App\Services\PengaturanEmailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $mailer = 'smtp';
    public string $host = '';
    public int $port = 587;
    public string $enkripsi = 'tls';
    public ?string $username = null;
    public ?string $password = null;
    public string $fromAddress = '';
    public string $fromName = '';
    public ?string $replyTo = null;
    public bool $isActive = true;
    public ?int $editId = null;

    public string $emailUji = '';

    public function mount(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pengaturan-email', 'dapat_lihat')) {
            abort(403);
        }

        $pengaturan = PengaturanEmail::query()->latest('id')->first();

        if (! $pengaturan) {
            return;
        }

        $this->editId = $pengaturan->id;
        $this->mailer = $pengaturan->mailer;
        $this->host = $pengaturan->host;
        $this->port = $pengaturan->port;
        $this->enkripsi = $pengaturan->enkripsi ?: '';
        $this->username = $pengaturan->username;
        $this->password = $pengaturan->password;
        $this->fromAddress = $pengaturan->from_address;
        $this->fromName = $pengaturan->from_name;
        $this->replyTo = $pengaturan->reply_to;
        $this->isActive = $pengaturan->is_active;
    }

    public function simpan(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pengaturan-email', 'dapat_ubah')) {
            abort(403);
        }

        $data = $this->validate([
            'mailer' => 'required|string|in:smtp',
            'host' => 'required|string|max:150',
            'port' => 'required|integer|min:1|max:65535',
            'enkripsi' => ['nullable', 'string', Rule::in(['tls', 'ssl', 'starttls'])],
            'username' => 'nullable|string|max:150',
            'password' => 'nullable|string|max:255',
            'fromAddress' => 'required|email|max:150',
            'fromName' => 'required|string|max:150',
            'replyTo' => 'nullable|email|max:150',
            'isActive' => 'boolean',
        ]);

        $payload = [
            'mailer' => $data['mailer'],
            'host' => $data['host'],
            'port' => $data['port'],
            'enkripsi' => $data['enkripsi'] ?: null,
            'username' => $data['username'] ?: null,
            'password' => $data['password'] ?: null,
            'from_address' => $data['fromAddress'],
            'from_name' => $data['fromName'],
            'reply_to' => $data['replyTo'] ?: null,
            'is_active' => $data['isActive'],
        ];

        if ($this->editId) {
            $pengaturan = PengaturanEmail::query()->findOrFail($this->editId);
            $pengaturan->update($payload);
        } else {
            if ($payload['is_active']) {
                PengaturanEmail::query()->update(['is_active' => false]);
            }

            $pengaturan = PengaturanEmail::query()->create($payload);
            $this->editId = $pengaturan->id;
        }

        if ($payload['is_active']) {
            PengaturanEmail::query()->where('id', '!=', $pengaturan->id)->update(['is_active' => false]);
        }

        app(PengaturanEmailService::class)->terapkanKonfigurasiRuntime($pengaturan);

        app(LogAktivitasService::class)->catatManual(
            'Pengaturan Email',
            'Menyimpan konfigurasi pengiriman email',
            '/admin/pengaturan-email',
            ['pengaturan_email_id' => $pengaturan->id]
        );

        session()->flash('sukses', __('messages.email_setting_saved'));
    }

    public function ujiKirim(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pengaturan-email', 'dapat_ubah')) {
            abort(403);
        }

        $data = $this->validate([
            'emailUji' => 'required|email|max:150',
        ]);

        $pengaturan = new PengaturanEmail([
            'mailer' => $this->mailer,
            'host' => $this->host,
            'port' => $this->port,
            'enkripsi' => $this->enkripsi ?: null,
            'username' => $this->username,
            'password' => $this->password,
            'from_address' => $this->fromAddress,
            'from_name' => $this->fromName,
            'reply_to' => $this->replyTo,
            'is_active' => true,
        ]);

        app(PengaturanEmailService::class)->terapkanKonfigurasiRuntime($pengaturan);

        try {
            Mail::raw(__('messages.email_test_body', ['app' => config('app.name')]), function ($message) use ($data) {
                $message->to($data['emailUji'])
                    ->subject(__('messages.email_test_subject'));

                if (! empty($this->replyTo)) {
                    $message->replyTo($this->replyTo, $this->fromName);
                }
            });

            app(LogAktivitasService::class)->catatManual(
                'Pengaturan Email',
                'Uji kirim email ke ' . $data['emailUji'],
                '/admin/pengaturan-email',
                ['email_tujuan' => $data['emailUji']]
            );

            session()->flash('sukses', __('messages.email_test_success', ['email' => $data['emailUji']]));
        } catch (\Throwable $e) {
            session()->flash('error', __('messages.email_test_failed', ['message' => $e->getMessage()]));
        }
    }
};
?>

@section('title', __('messages.email_setting_title'))

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ __('messages.email_setting_heading') }}</h4>
            <p class="text-muted mb-0">{{ __('messages.email_setting_subheading') }}</p>
        </div>
    </div>

    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('messages.email_setting_form_title') }}</h5>
        </div>
        <div class="card-body">
            <form wire:submit="simpan">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Mailer</label>
                        <select wire:model="mailer" class="form-select">
                            <option value="smtp">SMTP</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Host SMTP</label>
                        <input type="text" wire:model="host" class="form-control" placeholder="smtp.mailtrap.io">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Port</label>
                        <input type="number" wire:model="port" class="form-control" min="1" max="65535">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Enkripsi</label>
                        <select wire:model="enkripsi" class="form-select">
                            <option value="">Tanpa Enkripsi</option>
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="starttls">STARTTLS</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Username SMTP</label>
                        <input type="text" wire:model="username" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Password SMTP</label>
                        <input type="password" wire:model="password" class="form-control" autocomplete="new-password">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">From Address</label>
                        <input type="email" wire:model="fromAddress" class="form-control" placeholder="noreply@domain.id">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">From Name</label>
                        <input type="text" wire:model="fromName" class="form-control" placeholder="Sistem Informasi Organisasi">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Reply-To (opsional)</label>
                        <input type="email" wire:model="replyTo" class="form-control" placeholder="support@domain.id">
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input wire:model="isActive" type="checkbox" class="form-check-input" id="isActiveEmail">
                            <label class="form-check-label" for="isActiveEmail">{{ __('messages.email_setting_active') }}</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpan">
                        <span wire:loading.remove wire:target="simpan">{{ __('messages.save') }}</span>
                        <span wire:loading wire:target="simpan" style="display:none">{{ __('messages.saving') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('messages.email_test_title') }}</h5>
        </div>
        <div class="card-body">
            <form wire:submit="ujiKirim">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">{{ __('messages.email_test_target') }}</label>
                        <input type="email" wire:model="emailUji" class="form-control" placeholder="contoh@email.com">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-outline-primary w-100" wire:loading.attr="disabled" wire:target="ujiKirim">
                            <span wire:loading.remove wire:target="ujiKirim">{{ __('messages.email_test_send_button') }}</span>
                            <span wire:loading wire:target="ujiKirim" style="display:none">{{ __('messages.processing') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
