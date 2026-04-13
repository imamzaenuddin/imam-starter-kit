<?php

use App\Models\Bahasa;
use App\Services\BahasaService;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $kode = '';
    public string $nama = '';
    public string $namaNative = '';
    public int $urutan = 0;
    public bool $isActive = true;
    public bool $isDefault = false;

    public ?int $editId = null;
    public bool $showModal = false;

    public function mount(): void
    {
        if (! auth()->user()->bisaMenu('/admin/bahasa', 'dapat_lihat')) {
            abort(403);
        }
    }

    public function sinkronkanFolder(): void
    {
        if (! auth()->user()->bisaMenu('/admin/bahasa', 'dapat_ubah')) {
            abort(403);
        }

        $jumlah = app(BahasaService::class)->sinkronkanDariFolder();
        app(LogAktivitasService::class)->catatManual(__('messages.language_module_name'), __('messages.language_log_sync_folder'), '/admin/bahasa', ['jumlah' => $jumlah]);
        session()->flash('sukses', __('messages.sync_success_total', ['jumlah' => $jumlah]));
    }

    public function buka(): void
    {
        if (! auth()->user()->bisaMenu('/admin/bahasa', 'dapat_buat')) {
            abort(403);
        }

        $this->reset(['kode', 'nama', 'namaNative', 'urutan', 'isActive', 'isDefault', 'editId']);
        $this->isActive = true;
        $this->isDefault = false;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        if (! auth()->user()->bisaMenu('/admin/bahasa', 'dapat_ubah')) {
            abort(403);
        }

        $bahasa = Bahasa::findOrFail($id);
        $this->editId = $bahasa->id;
        $this->kode = $bahasa->kode;
        $this->nama = $bahasa->nama;
        $this->namaNative = $bahasa->nama_native ?? '';
        $this->urutan = $bahasa->urutan;
        $this->isActive = $bahasa->is_active;
        $this->isDefault = $bahasa->is_default;
        $this->showModal = true;
    }

    public function simpan(): void
    {
        $izin = $this->editId ? 'dapat_ubah' : 'dapat_buat';
        if (! auth()->user()->bisaMenu('/admin/bahasa', $izin)) {
            abort(403);
        }

        $data = $this->validate([
          'kode' => 'required|string|max:10|regex:/^[a-z]{2}([_-][A-Z]{2})?$/|unique:m_bahasa,kode,' . ($this->editId ?? 'NULL'),
            'nama' => 'required|string|max:100',
            'namaNative' => 'nullable|string|max:100',
            'urutan' => 'required|integer|min:0|max:999',
            'isActive' => 'boolean',
            'isDefault' => 'boolean',
        ]);

        $payload = [
            'kode' => strtolower($data['kode']),
            'nama' => $data['nama'],
            'nama_native' => $data['namaNative'] ?: null,
            'urutan' => $data['urutan'],
            'is_active' => $data['isActive'],
            'is_default' => $data['isDefault'],
        ];

        $bahasa = $this->editId
            ? tap(Bahasa::findOrFail($this->editId))->update($payload)
            : Bahasa::create($payload);

        if ($payload['is_default']) {
            Bahasa::query()->where('id', '!=', $bahasa->id)->update(['is_default' => false]);
        }

        if ($payload['is_default'] && ! $payload['is_active']) {
            $bahasa->update(['is_active' => true]);
        }

        app(LogAktivitasService::class)->catatManual(
          __('messages.language_module_name'),
          $this->editId
            ? __('messages.language_log_update', ['kode' => $bahasa->kode])
            : __('messages.language_log_add', ['kode' => $bahasa->kode]),
          '/admin/bahasa',
          [
            'bahasa_id' => $bahasa->id,
          ]
        );

        $this->showModal = false;
        $this->reset(['kode', 'nama', 'namaNative', 'urutan', 'isActive', 'isDefault', 'editId']);
        $this->resetPage();
    }

    public function jadikanDefault(int $id): void
    {
        if (! auth()->user()->bisaMenu('/admin/bahasa', 'dapat_ubah')) {
            abort(403);
        }

        Bahasa::query()->update(['is_default' => false]);
        $bahasa = Bahasa::findOrFail($id);
        $bahasa->update(['is_default' => true, 'is_active' => true]);

        app(LogAktivitasService::class)->catatManual(__('messages.language_module_name'), __('messages.language_log_set_default', ['kode' => $bahasa->kode]), '/admin/bahasa', [
            'bahasa_id' => $bahasa->id,
        ]);
    }

    public function hapus(int $id): void
    {
        if (! auth()->user()->bisaMenu('/admin/bahasa', 'dapat_hapus')) {
            abort(403);
        }

        $bahasa = Bahasa::findOrFail($id);

        if ($bahasa->is_default) {
            session()->flash('error', __('messages.default_language_cannot_delete'));
            return;
        }

        app(LogAktivitasService::class)->catatManual(__('messages.language_module_name'), __('messages.language_log_delete', ['kode' => $bahasa->kode]), '/admin/bahasa', [
            'bahasa_id' => $bahasa->id,
        ]);

        $bahasa->delete();
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'bahasas' => Bahasa::query()
                ->when($this->search, function ($query) {
                    $query->where('kode', 'like', '%' . $this->search . '%')
                        ->orWhere('nama', 'like', '%' . $this->search . '%')
                        ->orWhere('nama_native', 'like', '%' . $this->search . '%');
                })
                ->orderBy('urutan')
                ->orderBy('kode')
                ->paginate(10),
            'folderBahasa' => app(BahasaService::class)->sumberBahasaTersedia(),
        ];
    }
};
?>
@section('title', __('messages.admin_manage_language_title'))

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1" style="color:#1e293b">{{ __('messages.admin_manage_language_heading') }}</h4>
      <p class="text-muted mb-0" style="font-size:.875rem">{{ __('messages.admin_manage_language_subheading') }}</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary" wire:click="sinkronkanFolder">
        <i class="bx bx-refresh me-1"></i> {{ __('messages.sync_folder') }}
      </button>
      <button class="btn btn-primary" wire:click="buka">
        <i class="bx bx-plus me-1"></i> {{ __('messages.add_language') }}
      </button>
    </div>
  </div>

  @if (session('sukses'))
    <div class="alert alert-success">{{ session('sukses') }}</div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <div class="card mb-4">
    <div class="card-body py-3">
      <input wire:model.live.debounce.300ms="search" type="search" class="form-control" placeholder="{{ __('messages.search_language_placeholder') }}">
      <small class="text-muted d-block mt-2">
        {{ __('messages.language_folder_sources') }}: {{ implode(' | ', $folderBahasa) }}
      </small>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>{{ __('messages.code') }}</th>
            <th>{{ __('messages.name') }}</th>
            <th>{{ __('messages.native') }}</th>
            <th>{{ __('messages.order') }}</th>
            <th>{{ __('messages.status') }}</th>
            <th>{{ __('messages.default') }}</th>
            <th class="text-center">{{ __('messages.action') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($bahasas as $bahasa)
            <tr>
              <td>{{ $bahasas->firstItem() + $loop->index }}</td>
              <td><span class="badge bg-label-primary">{{ $bahasa->kode }}</span></td>
              <td>{{ $bahasa->nama }}</td>
              <td>{{ $bahasa->nama_native ?? '-' }}</td>
              <td>{{ $bahasa->urutan }}</td>
              <td>
                @if ($bahasa->is_active)
                  <span class="badge bg-label-success">{{ __('messages.active') }}</span>
                @else
                  <span class="badge bg-label-secondary">{{ __('messages.inactive') }}</span>
                @endif
              </td>
              <td>
                @if ($bahasa->is_default)
                  <span class="badge bg-label-info">{{ __('messages.default') }}</span>
                @else
                  <button class="btn btn-sm btn-outline-primary" wire:click="jadikanDefault({{ $bahasa->id }})">{{ __('messages.set_default') }}</button>
                @endif
              </td>
              <td class="text-center">
                <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit({{ $bahasa->id }})">
                  <i class="bx bx-edit-alt"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-text-danger"
                        @click="Swal.fire({
                          title: '{{ __('messages.confirm_delete') }}',
                          text: '{{ __('messages.confirm_delete_language', ['kode' => $bahasa->kode]) }}',
                          icon: 'warning',
                          showCancelButton: true,
                          confirmButtonText: '{{ __('messages.yes_delete') }}',
                          cancelButtonText: '{{ __('messages.cancel') }}',
                        }).then(r => r.isConfirmed && $wire.hapus({{ $bahasa->id }}))"
                        >
                  <i class="bx bx-trash"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted py-4">{{ __('messages.no_language_data') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $bahasas->links() }}</div>
  </div>

  @if ($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.45)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $editId ? __('messages.edit_language') : __('messages.add_language') }}</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.locale_code') }}</label>
                <input wire:model="kode" type="text" class="form-control @error('kode') is-invalid @enderror" placeholder="id / en / en_US">
                @error('kode') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.name') }}</label>
                <input wire:model="nama" type="text" class="form-control @error('nama') is-invalid @enderror" placeholder="Bahasa Indonesia">
                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.native_name') }}</label>
                <input wire:model="namaNative" type="text" class="form-control @error('namaNative') is-invalid @enderror" placeholder="Bahasa Indonesia">
                @error('namaNative') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.order') }}</label>
                <input wire:model="urutan" type="number" min="0" class="form-control @error('urutan') is-invalid @enderror">
                @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="form-check mb-2">
                <input wire:model="isActive" type="checkbox" class="form-check-input" id="bahasaAktif">
                <label class="form-check-label" for="bahasaAktif">{{ __('messages.language_active') }}</label>
              </div>
              <div class="form-check">
                <input wire:model="isDefault" type="checkbox" class="form-check-input" id="bahasaDefault">
                <label class="form-check-label" for="bahasaDefault">{{ __('messages.set_system_default') }}</label>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModal',false)">{{ __('messages.cancel') }}</button>
              <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpan">
                <span wire:loading.remove wire:target="simpan">{{ __('messages.save') }}</span>
                <span wire:loading wire:target="simpan" style="display:none">
                  <span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.saving') }}
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
</div>
