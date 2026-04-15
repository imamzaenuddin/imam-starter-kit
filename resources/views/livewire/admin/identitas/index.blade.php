<?php

use App\Models\Identitas;
use App\Services\IdentitasService;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

new #[Layout('components.layouts.app')] class extends Component {
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public ?int $editId = null;
    public bool $showModal = false;

    public string $namaAplikasi = '';
    public string $singkatanAplikasi = '';
    public string $versi = '1.0.0';
    public string $icon = 'bx bx-buildings';
    public string $mainColor = '#696cff';
    public string $secondaryColor = '#8592a3';
    public $logoUpload = null;
    public ?string $logoPath = null;
    public string $email = '';
    public string $waCenter = '';
    public string $telepon = '';
    public string $website = '';
    public string $alamat = '';
    public string $slogan = '';
    public string $deskripsi = '';
    public string $footerText = '';
    public array $fiturLogin = [];
    public array $statistikLogin = [];
    public bool $isActive = true;

    /** Preset warna cepat untuk tema aplikasi */
    public array $warnaPreset = [
        '#696cff', '#5a61ff', '#4f46e5', '#2563eb', '#0ea5e9', '#14b8a6',
        '#10b981', '#84cc16', '#eab308', '#f59e0b', '#f97316', '#ef4444',
        '#ec4899', '#d946ef', '#8b5cf6', '#64748b', '#8592a3', '#334155',
    ];

    public function mount(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/identitas', 'dapat_lihat')) {
            abort(403);
        }
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/identitas', 'dapat_buat')) {
            abort(403);
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        if (! auth()->user()?->bisaMenu('/admin/identitas', 'dapat_ubah')) {
            abort(403);
        }

        $data = Identitas::findOrFail($id);

        $this->editId = $data->id;
        $this->namaAplikasi = $data->nama_aplikasi;
        $this->singkatanAplikasi = $data->singkatan_aplikasi ?? '';
        $this->versi = $data->versi;
        $this->icon = $data->icon ?? '';
        $this->mainColor = $data->main_color ?? '#696cff';
        $this->secondaryColor = $data->secondary_color ?? '#8592a3';
        $this->logoPath = $data->logo_path;
        $this->logoUpload = null;
        $this->email = $data->email ?? '';
        $this->waCenter = $data->wa_center ?? '';
        $this->telepon = $data->telepon ?? '';
        $this->website = $data->website ?? '';
        $this->alamat = $data->alamat ?? '';
        $this->slogan = $data->slogan ?? '';
        $this->deskripsi = $data->deskripsi ?? '';
        $this->footerText = $data->footer_text ?? '';
        $this->fiturLogin = array_pad($data->fitur_login ?? [], 4, '');
        $statistikDefault = [
            ['nilai' => '', 'label' => ''],
            ['nilai' => '', 'label' => ''],
            ['nilai' => '', 'label' => ''],
        ];
        $this->statistikLogin = array_replace($statistikDefault, $data->statistik_login ?? []);
        $this->isActive = $data->is_active;
        $this->showModal = true;
    }

    public function simpan(): void
    {
        $izin = $this->editId ? 'dapat_ubah' : 'dapat_buat';
        if (! auth()->user()?->bisaMenu('/admin/identitas', $izin)) {
            abort(403);
        }

        $data = $this->validate([
            'namaAplikasi' => 'required|string|max:120',
            'singkatanAplikasi' => 'nullable|string|max:30',
            'versi' => 'required|string|max:30',
            'icon' => 'nullable|string|max:100',
            'mainColor' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'secondaryColor' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'logoUpload' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp,svg',
            'email' => 'nullable|email|max:120',
            'waCenter' => 'nullable|string|max:25',
            'telepon' => 'nullable|string|max:25',
            'website' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'slogan' => 'nullable|string|max:160',
            'deskripsi' => 'nullable|string',
            'footerText' => 'nullable|string|max:255',
            'fiturLogin' => 'nullable|array|size:4',
            'fiturLogin.*' => 'nullable|string|max:80',
            'statistikLogin' => 'nullable|array|size:3',
            'statistikLogin.*.nilai' => 'nullable|string|max:20',
            'statistikLogin.*.label' => 'nullable|string|max:50',
            'isActive' => 'boolean',
        ]);

        $payload = [
            'nama_aplikasi' => $data['namaAplikasi'],
            'singkatan_aplikasi' => $data['singkatanAplikasi'] ?: null,
            'versi' => $data['versi'],
            'icon' => $data['icon'] ?: null,
            'main_color' => $data['mainColor'] ?: '#696cff',
            'secondary_color' => $data['secondaryColor'] ?: '#8592a3',
            'logo_path' => $this->logoPath,
            'email' => $data['email'] ?: null,
            'wa_center' => $data['waCenter'] ?: null,
            'telepon' => $data['telepon'] ?: null,
            'website' => $data['website'] ?: null,
            'alamat' => $data['alamat'] ?: null,
            'slogan' => $data['slogan'] ?: null,
            'deskripsi' => $data['deskripsi'] ?: null,
            'footer_text' => $data['footerText'] ?: null,
            'fitur_login' => collect($data['fiturLogin'] ?? [])->map(fn ($item) => trim((string) $item))->values()->all(),
            'statistik_login' => collect($data['statistikLogin'] ?? [])->map(function ($item) {
                return [
                    'nilai' => trim((string) ($item['nilai'] ?? '')),
                    'label' => trim((string) ($item['label'] ?? '')),
                ];
            })->values()->all(),
            'is_active' => $data['isActive'],
        ];

        if ($this->logoUpload) {
            if ($this->logoPath && Storage::disk('public')->exists($this->logoPath)) {
                Storage::disk('public')->delete($this->logoPath);
            }
            $payload['logo_path'] = $this->logoUpload->store('identitas-logo', 'public');
        }

        if ($this->editId) {
            $identitas = Identitas::findOrFail($this->editId);
            $identitas->update($payload);
            app(LogAktivitasService::class)->catatManual(__('messages.identity_module_name'), __('messages.identity_log_update', ['nama' => $identitas->nama_aplikasi]), '/admin/identitas', [
                'identitas_id' => $identitas->id,
            ]);
        } else {
            if ($payload['is_active']) {
                Identitas::query()->update(['is_active' => false]);
            }
            $identitas = Identitas::create($payload);
            app(LogAktivitasService::class)->catatManual(__('messages.identity_module_name'), __('messages.identity_log_add', ['nama' => $identitas->nama_aplikasi]), '/admin/identitas', [
                'identitas_id' => $identitas->id,
            ]);
        }

        if ($payload['is_active']) {
            Identitas::where('id', '!=', $this->editId ?? 0)->update(['is_active' => false]);
        }

        app(IdentitasService::class)->hapusCache();

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function aktifkan(int $id): void
    {
        if (! auth()->user()?->bisaMenu('/admin/identitas', 'dapat_ubah')) {
            abort(403);
        }

        Identitas::query()->update(['is_active' => false]);
        Identitas::whereKey($id)->update(['is_active' => true]);
        $identitas = Identitas::findOrFail($id);
        app(LogAktivitasService::class)->catatManual(__('messages.identity_module_name'), __('messages.identity_log_activate', ['nama' => $identitas->nama_aplikasi]), '/admin/identitas', [
            'identitas_id' => $identitas->id,
        ]);
        app(IdentitasService::class)->hapusCache();
    }

    public function hapus(int $id): void
    {
        if (! auth()->user()?->bisaMenu('/admin/identitas', 'dapat_hapus')) {
            abort(403);
        }

        $data = Identitas::findOrFail($id);
        app(LogAktivitasService::class)->catatManual(__('messages.identity_module_name'), __('messages.identity_log_delete', ['nama' => $data->nama_aplikasi]), '/admin/identitas', [
            'identitas_id' => $data->id,
        ]);
        if ($data->logo_path && Storage::disk('public')->exists($data->logo_path)) {
            Storage::disk('public')->delete($data->logo_path);
        }
        $data->delete();
        app(IdentitasService::class)->hapusCache();
        $this->resetPage();
    }

    /** Normalisasi input warna agar selalu format #RRGGBB */
    private function normalisasiHex(?string $hex, string $fallback): string
    {
        if (! $hex) {
            return $fallback;
        }

        $hex = trim($hex);
        if (! str_starts_with($hex, '#')) {
            $hex = '#'.$hex;
        }

        return preg_match('/^#([A-Fa-f0-9]{6})$/', $hex) ? strtolower($hex) : $fallback;
    }

    public function updatedMainColor($value): void
    {
        $this->mainColor = $this->normalisasiHex($value, '#696cff');
    }

    public function updatedSecondaryColor($value): void
    {
        $this->secondaryColor = $this->normalisasiHex($value, '#8592a3');
    }

    public function pilihMainColor(string $warna): void
    {
        $this->mainColor = $this->normalisasiHex($warna, '#696cff');
    }

    public function pilihSecondaryColor(string $warna): void
    {
        $this->secondaryColor = $this->normalisasiHex($warna, '#8592a3');
    }

    private function resetForm(): void
    {
        $this->reset([
            'editId', 'namaAplikasi', 'singkatanAplikasi', 'versi', 'icon', 'mainColor', 'secondaryColor', 'logoUpload', 'logoPath', 'email', 'waCenter',
            'telepon', 'website', 'alamat', 'slogan', 'deskripsi', 'footerText', 'fiturLogin', 'statistikLogin', 'isActive',
        ]);

        $this->versi = '1.0.0';
        $this->icon = 'bx bx-buildings';
        $this->mainColor = '#696cff';
        $this->secondaryColor = '#8592a3';
        $this->fiturLogin = [
            __('messages.identity_default_feature_1'),
            __('messages.identity_default_feature_2'),
            __('messages.identity_default_feature_3'),
            __('messages.identity_default_feature_4'),
        ];
        $this->statistikLogin = [
            ['nilai' => '500+', 'label' => __('messages.identity_default_stat_label_1')],
            ['nilai' => '50+', 'label' => __('messages.identity_default_stat_label_2')],
            ['nilai' => '99%', 'label' => __('messages.identity_default_stat_label_3')],
        ];
        $this->isActive = true;
    }

    public function with(): array
    {
        return [
            'identitasList' => Identitas::query()
                ->when($this->search, fn ($q) => $q->where('nama_aplikasi', 'like', '%'.$this->search.'%'))
                ->orderByDesc('is_active')
                ->orderByDesc('id')
                ->paginate((int) config('app_runtime.pagination_default', 10)),
        ];
    }
};
?>
@section('title', __('messages.system_identity_title'))

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ __('messages.system_identity_heading') }}</h4>
            <p class="text-muted mb-0">{{ __('messages.system_identity_subheading') }}</p>
        </div>
        @if (auth()->user()?->bisaMenu('/admin/identitas', 'dapat_buat'))
            <button class="btn btn-primary" wire:click="buka">
                <i class="bx bx-plus me-1"></i> {{ __('messages.add_identity') }}
            </button>
        @endif
    </div>

    <div class="card mb-4">
        <div class="card-body py-3">
            <input wire:model.live.debounce.300ms="search" type="search" class="form-control"
                   placeholder="{{ __('messages.search_app_name_placeholder') }}">
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.identity') }}</th>
                        <th>{{ __('messages.contact') }}</th>
                        <th>{{ __('messages.website') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th class="text-center">{{ __('messages.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($identitasList as $item)
                        <tr>
                            <td>{{ $identitasList->firstItem() + $loop->index }}</td>
                            <td>
                                @if ($item->logo_path)
                                    <img src="{{ asset('storage/' . $item->logo_path) }}" alt="{{ __('messages.identity_logo_alt', ['name' => $item->nama_aplikasi]) }}"
                                         class="rounded mb-1" style="height:32px;width:32px;object-fit:cover;">
                                @endif
                                <div class="fw-semibold">{{ $item->nama_aplikasi }}</div>
                                @if ($item->singkatan_aplikasi)
                                    <small class="text-muted d-block">{{ __('messages.abbreviation') }}: {{ $item->singkatan_aplikasi }}</small>
                                @endif
                                <small class="text-muted d-block">{{ __('messages.version') }}: {{ $item->versi }}</small>
                                @if ($item->icon)
                                    <small class="text-muted"><i class="{{ $item->icon }}"></i> {{ $item->icon }}</small>
                                @endif
                                <small class="text-muted d-block mt-1">
                                    <span class="badge" style="background:{{ $item->main_color ?? '#696cff' }};color:#fff;">{{ __('messages.main') }}</span>
                                    <span class="badge" style="background:{{ $item->secondary_color ?? '#8592a3' }};color:#fff;">{{ __('messages.secondary') }}</span>
                                </small>
                            </td>
                            <td>
                                <div>{{ $item->email ?: '-' }}</div>
                                <small class="text-muted d-block">{{ __('messages.wa_center') }}: {{ $item->wa_center ?: '-' }}</small>
                            </td>
                            <td>
                                @if ($item->website)
                                    <a href="{{ $item->website }}" target="_blank">{{ $item->website }}</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($item->is_active)
                                    <span class="badge bg-label-success">{{ __('messages.active') }}</span>
                                @else
                                    <span class="badge bg-label-secondary">{{ __('messages.inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if (! $item->is_active && auth()->user()?->bisaMenu('/admin/identitas', 'dapat_ubah'))
                                    <button class="btn btn-sm btn-icon btn-text-success" wire:click="aktifkan({{ $item->id }})" title="{{ __('messages.activate') }}">
                                        <i class="bx bx-check-circle"></i>
                                    </button>
                                @endif
                                @if (auth()->user()?->bisaMenu('/admin/identitas', 'dapat_ubah'))
                                    <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit({{ $item->id }})" title="{{ __('messages.edit') }}">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                @endif
                                @if (auth()->user()?->bisaMenu('/admin/identitas', 'dapat_hapus'))
                                    <button class="btn btn-sm btn-icon btn-text-danger"
                                            title="{{ __('messages.delete') }}"
                                            @click="Swal.fire({
                                              title: '{{ __('messages.confirm_delete') }}',
                                              text: '{{ __('messages.confirm_delete_identity', ['nama' => addslashes($item->nama_aplikasi)]) }}',
                                              icon: 'warning',
                                              showCancelButton: true,
                                              confirmButtonText: '{{ __('messages.yes_delete') }}',
                                              cancelButtonText: '{{ __('messages.cancel') }}',
                                            }).then(r => r.isConfirmed && $wire.hapus({{ $item->id }}))"
                                            >
                                        <i class="bx bx-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">{{ __('messages.no_identity_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $identitasList->links() }}</div>
    </div>

    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.45)">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editId ? __('messages.edit_identity') : __('messages.add_identity') }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="simpan">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.app_name') }} <span class="text-danger">*</span></label>
                                    <input wire:model="namaAplikasi" type="text" class="form-control @error('namaAplikasi') is-invalid @enderror" placeholder="{{ __('messages.identity_app_name_placeholder') }}">
                                    @error('namaAplikasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">{{ __('messages.app_abbreviation') }}</label>
                                    <input wire:model="singkatanAplikasi" type="text" class="form-control @error('singkatanAplikasi') is-invalid @enderror" placeholder="{{ __('messages.identity_app_abbreviation_placeholder') }}">
                                    @error('singkatanAplikasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">{{ __('messages.version') }} <span class="text-danger">*</span></label>
                                    <input wire:model="versi" type="text" class="form-control @error('versi') is-invalid @enderror" placeholder="{{ __('messages.identity_version_placeholder') }}">
                                    @error('versi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.upload_logo') }}</label>
                                    <input wire:model="logoUpload" type="file" class="form-control" accept="image/*">
                                    <small class="text-muted">{{ __('messages.logo_upload_hint') }}</small>
                                    @error('logoUpload') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.logo_preview') }}</label>
                                    <div class="border rounded p-2 d-flex align-items-center gap-2" style="min-height:72px">
                                        @if ($logoUpload)
                                            <img src="{{ $logoUpload->temporaryUrl() }}" alt="{{ __('messages.identity_logo_preview_alt') }}" style="height:48px;width:48px;object-fit:cover" class="rounded">
                                            <span class="text-muted small">{{ __('messages.new_upload_preview') }}</span>
                                        @elseif ($logoPath)
                                            <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ __('messages.identity_current_logo_alt') }}" style="height:48px;width:48px;object-fit:cover" class="rounded">
                                            <span class="text-muted small">{{ __('messages.saved_logo') }}</span>
                                        @else
                                            <img src="{{ asset('assets/img/identitas/gedung-default.svg') }}" alt="{{ __('messages.identity_default_building_alt') }}" style="height:48px;width:48px;object-fit:cover" class="rounded">
                                            <span class="text-muted small">{{ __('messages.fallback_building_logo') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('messages.email') }}</label>
                                    <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="{{ __('messages.identity_email_placeholder') }}">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('messages.wa_center') }}</label>
                                    <input wire:model="waCenter" type="text" class="form-control" placeholder="{{ __('messages.identity_wa_placeholder') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('messages.phone') }}</label>
                                    <input wire:model="telepon" type="text" class="form-control" placeholder="{{ __('messages.identity_phone_placeholder') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.website') }}</label>
                                    <input wire:model="website" type="text" class="form-control" placeholder="{{ __('messages.identity_website_placeholder') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.address') }}</label>
                                    <input wire:model="alamat" type="text" class="form-control" placeholder="{{ __('messages.identity_address_placeholder') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.icon_boxicons') }}</label>
                                    <input wire:model="icon" type="text" class="form-control" placeholder="{{ __('messages.identity_icon_placeholder') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.slogan') }}</label>
                                    <input wire:model="slogan" type="text" class="form-control" placeholder="{{ __('messages.identity_slogan_placeholder') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.main_color') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text p-1">
                                            <input wire:model.live="mainColor" type="color" class="form-control form-control-color border-0 p-0" title="{{ __('messages.identity_pick_main_color') }}" style="width:28px;height:28px;min-width:28px">
                                        </span>
                                        <input wire:model.live="mainColor" type="text" class="form-control @error('mainColor') is-invalid @enderror" placeholder="#696cff">
                                        @error('mainColor') <div class="invalid-feedback">{{ __('messages.hex_color_format_error', ['contoh' => '#696cff']) }}</div> @enderror
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        @foreach ($warnaPreset as $warna)
                                            <button type="button" class="btn btn-sm p-0 border rounded-circle"
                                                    wire:click="pilihMainColor('{{ $warna }}')"
                                                    title="{{ __('messages.identity_pick_color', ['color' => $warna]) }}"
                                                    style="width:20px;height:20px;background:{{ $warna }}"></button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.secondary_color') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text p-1">
                                            <input wire:model.live="secondaryColor" type="color" class="form-control form-control-color border-0 p-0" title="{{ __('messages.identity_pick_secondary_color') }}" style="width:28px;height:28px;min-width:28px">
                                        </span>
                                        <input wire:model.live="secondaryColor" type="text" class="form-control @error('secondaryColor') is-invalid @enderror" placeholder="#8592a3">
                                        @error('secondaryColor') <div class="invalid-feedback">{{ __('messages.hex_color_format_error', ['contoh' => '#8592a3']) }}</div> @enderror
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        @foreach ($warnaPreset as $warna)
                                            <button type="button" class="btn btn-sm p-0 border rounded-circle"
                                                    wire:click="pilihSecondaryColor('{{ $warna }}')"
                                                    title="{{ __('messages.identity_pick_color', ['color' => $warna]) }}"
                                                    style="width:20px;height:20px;background:{{ $warna }}"></button>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('messages.menu_color_preview') }}</label>
                                    <div class="border rounded p-3">
                                        <div class="d-flex align-items-center justify-content-between rounded px-3 py-2"
                                             style="background: color-mix(in srgb, {{ $mainColor }} 14%, #ffffff); color: {{ $mainColor }};">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bx bx-grid-alt"></i>
                                                <span class="fw-semibold">{{ __('messages.active_menu') }}</span>
                                            </div>
                                            <i class="bx bx-chevron-down"></i>
                                        </div>
                                        <div class="d-flex gap-2 mt-2">
                                            <span class="badge" style="background: {{ $mainColor }}; color: #fff;">{{ __('messages.primary') }}</span>
                                            <span class="badge" style="background: {{ $secondaryColor }}; color: #fff;">{{ __('messages.secondary') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('messages.description') }}</label>
                                    <textarea wire:model="deskripsi" rows="3" class="form-control" placeholder="{{ __('messages.identity_description_placeholder') }}"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('messages.login_feature_pills') }}</label>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <input wire:model="fiturLogin.0" type="text" class="form-control" placeholder="{{ __('messages.identity_feature_placeholder', ['number' => 1]) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <input wire:model="fiturLogin.1" type="text" class="form-control" placeholder="{{ __('messages.identity_feature_placeholder', ['number' => 2]) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <input wire:model="fiturLogin.2" type="text" class="form-control" placeholder="{{ __('messages.identity_feature_placeholder', ['number' => 3]) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <input wire:model="fiturLogin.3" type="text" class="form-control" placeholder="{{ __('messages.identity_feature_placeholder', ['number' => 4]) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('messages.login_statistics') }}</label>
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-3"><input wire:model="statistikLogin.0.nilai" type="text" class="form-control" placeholder="{{ __('messages.identity_stat_value_placeholder', ['number' => 1]) }}"></div>
                                        <div class="col-md-3"><input wire:model="statistikLogin.0.label" type="text" class="form-control" placeholder="{{ __('messages.identity_stat_label_placeholder', ['number' => 1]) }}"></div>
                                        <div class="col-md-3"><input wire:model="statistikLogin.1.nilai" type="text" class="form-control" placeholder="{{ __('messages.identity_stat_value_placeholder', ['number' => 2]) }}"></div>
                                        <div class="col-md-3"><input wire:model="statistikLogin.1.label" type="text" class="form-control" placeholder="{{ __('messages.identity_stat_label_placeholder', ['number' => 2]) }}"></div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-3"><input wire:model="statistikLogin.2.nilai" type="text" class="form-control" placeholder="{{ __('messages.identity_stat_value_placeholder', ['number' => 3]) }}"></div>
                                        <div class="col-md-9"><input wire:model="statistikLogin.2.label" type="text" class="form-control" placeholder="{{ __('messages.identity_stat_label_placeholder', ['number' => 3]) }}"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('messages.footer_text') }}</label>
                                    <input wire:model="footerText" type="text" class="form-control" placeholder="{{ __('messages.identity_footer_placeholder') }}">
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input wire:model="isActive" type="checkbox" class="form-check-input" id="identitasActive">
                                        <label class="form-check-label" for="identitasActive">{{ __('messages.set_active_identity') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModal', false)">{{ __('messages.cancel') }}</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpan">
                                <span class="d-flex align-items-center justify-content-center gap-2">
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
    @endif
</div>
