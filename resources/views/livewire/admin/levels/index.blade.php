<?php
/**
 * Halaman CRUD Level User
 *
 * Route  : GET /admin/levels  (name: admin.levels)
 * Layout : components.layouts.app
 */

use App\Models\Level;
use App\Services\LogAktivitasService;
use App\Services\MenuService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search    = '';
    public string $namaLevel = '';
    public string $deskripsi = '';
    public bool   $isActive  = true;

    public ?int $editId = null;
    public bool $showModal = false;

    /** Buka modal tambah */
    public function buka(): void
    {
        $this->reset(['namaLevel', 'deskripsi', 'isActive', 'editId']);
        $this->isActive  = true;
        $this->showModal = true;
    }

    /** Buka modal edit */
    public function edit(int $id): void
    {
        $level = Level::findOrFail($id);
        $this->editId    = $level->id;
        $this->namaLevel = $level->nama_level;
        $this->deskripsi = $level->deskripsi ?? '';
        $this->isActive  = $level->is_active;
        $this->showModal = true;
    }

    /** Simpan (tambah / update) */
    public function simpan(): void
    {
        $data = $this->validate([
          'namaLevel' => 'required|string|max:50|unique:m_level,nama_level,' . ($this->editId ?? 'NULL'),
            'deskripsi' => 'nullable|string|max:255',
            'isActive'  => 'boolean',
        ]);

        if ($this->editId) {
            $level = Level::findOrFail($this->editId);
            $level->update([
                'nama_level' => $data['namaLevel'],
                'deskripsi'  => $data['deskripsi'],
                'is_active'  => $data['isActive'],
            ]);
          app(LogAktivitasService::class)->catatManual(__('messages.level_module_name'), __('messages.level_log_update', ['nama' => $level->nama_level]), '/admin/levels', [
            'level_id' => $level->id,
          ]);
            // Hapus cache menu semua user di level ini
            app(MenuService::class)->hapusCacheLevel($level->id);
        } else {
          $level = Level::create([
                'nama_level' => $data['namaLevel'],
                'deskripsi'  => $data['deskripsi'],
                'is_active'  => $data['isActive'],
            ]);
          app(LogAktivitasService::class)->catatManual(__('messages.level_module_name'), __('messages.level_log_add', ['nama' => $level->nama_level]), '/admin/levels', [
            'level_id' => $level->id,
          ]);
        }

        $this->showModal = false;
        $this->reset(['namaLevel', 'deskripsi', 'isActive', 'editId']);
        $this->resetPage();
    }

    /** Hapus level */
    public function hapus(int $id): void
    {
        $level = Level::findOrFail($id);
      app(LogAktivitasService::class)->catatManual(__('messages.level_module_name'), __('messages.level_log_delete', ['nama' => $level->nama_level]), '/admin/levels', [
        'level_id' => $level->id,
      ]);
        app(MenuService::class)->hapusCacheLevel($level->id);
        $level->delete();
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'levels' => Level::query()
                ->when($this->search, fn ($q) => $q->where('nama_level', 'like', '%' . $this->search . '%'))
                ->orderBy('nama_level')
            ->paginate((int) config('app_runtime.pagination_default', 10)),
        ];
    }
};
?>
@section('title', __('messages.admin_manage_level_title'))

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1" style="color:#1e293b">{{ __('messages.admin_manage_level_heading') }}</h4>
      <p class="text-muted mb-0" style="font-size:.875rem">{{ __('messages.admin_manage_level_subheading') }}</p>
    </div>
    <button class="btn btn-primary" wire:click="buka">
      <i class="bx bx-plus me-1"></i> {{ __('messages.add_level') }}
    </button>
  </div>

  {{-- Search --}}
  <div class="card mb-4">
    <div class="card-body py-3">
      <input wire:model.live.debounce.300ms="search" type="search"
              class="form-control" placeholder="{{ __('messages.search_level_placeholder') }}">
    </div>
  </div>

  {{-- Tabel --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>{{ __('messages.level_name') }}</th>
            <th>{{ __('messages.description') }}</th>
            <th>{{ __('messages.status') }}</th>
            <th class="text-center">{{ __('messages.action') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($levels as $level)
            <tr>
              <td>{{ $levels->firstItem() + $loop->index }}</td>
              <td class="fw-semibold">{{ $level->nama_level }}</td>
              <td class="text-muted">{{ $level->deskripsi ?? '-' }}</td>
              <td>
                @if ($level->is_active)
                  <span class="badge bg-label-success">{{ __('messages.active') }}</span>
                @else
                  <span class="badge bg-label-secondary">{{ __('messages.inactive') }}</span>
                @endif
              </td>
              <td class="text-center">
                <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit({{ $level->id }})" title="{{ __('messages.edit') }}">
                  <i class="bx bx-edit-alt"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-text-danger"
                        title="{{ __('messages.delete') }}"
                        @click="Swal.fire({
                          title: '{{ __('messages.confirm_delete') }}',
                          text: '{{ __('messages.confirm_delete_level', ['nama' => addslashes($level->nama_level)]) }}',
                          icon: 'warning',
                          showCancelButton: true,
                          confirmButtonText: '{{ __('messages.yes_delete') }}',
                          cancelButtonText: '{{ __('messages.cancel') }}',
                        }).then(r => r.isConfirmed && $wire.hapus({{ $level->id }}))"
                        >
                  <i class="bx bx-trash"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">{{ __('messages.no_level_data') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $levels->links() }}</div>
  </div>

  {{-- Modal Tambah/Edit --}}
  @if ($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.45)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $editId ? __('messages.edit_level') : __('messages.add_level') }}</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.level_name') }} <span class="text-danger">*</span></label>
                <input wire:model="namaLevel" type="text" class="form-control @error('namaLevel') is-invalid @enderror"
                       placeholder="{{ __('messages.level_name_example') }}">
                @error('namaLevel') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.description') }}</label>
                <textarea wire:model="deskripsi" class="form-control" rows="2"
                          placeholder="{{ __('messages.level_description_placeholder') }}"></textarea>
              </div>
              <div class="form-check">
                <input wire:model="isActive" type="checkbox" class="form-check-input" id="isActiveCheck">
                <label class="form-check-label" for="isActiveCheck">{{ __('messages.level_active') }}</label>
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
