<?php
/**
 * Halaman CRUD Menu
 *
 * Route  : GET /admin/menus  (name: admin.menus)
 * Layout : components.layouts.app
 */

use App\Models\Menu;
use App\Services\LogAktivitasService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string  $search   = '';
    public string  $nama     = '';
    public string  $url      = '';
    public string  $icon     = '';
    public ?int    $parentId = null;
    public int     $urutan   = 0;
    public bool    $isActive = true;
    public ?int    $editId   = null;
    public bool    $showModal = false;

    public function buka(): void
    {
        $this->reset(['nama', 'url', 'icon', 'parentId', 'urutan', 'isActive', 'editId']);
        $this->isActive   = true;
        $this->showModal  = true;
    }

    public function edit(int $id): void
    {
        $menu = Menu::findOrFail($id);
        $this->editId    = $menu->id;
        $this->nama      = $menu->nama;
        $this->url       = $menu->url ?? '';
        $this->icon      = $menu->icon ?? '';
        $this->parentId  = $menu->parent_id;
        $this->urutan    = $menu->urutan;
        $this->isActive  = $menu->is_active;
        $this->showModal = true;
    }

    public function simpan(): void
    {
        $data = $this->validate([
            'nama'     => 'required|string|max:100',
            'url'      => 'nullable|string|max:255',
            'icon'     => 'nullable|string|max:100',
          'parentId' => 'nullable|exists:m_menu,id',
            'urutan'   => 'required|integer|min:0',
            'isActive' => 'boolean',
        ]);

          if (! Menu::iconTersedia($data['icon'] ?? null)) {
            throw ValidationException::withMessages([
              'icon' => 'Class icon tidak tersedia di versi Boxicons proyek ini. Gunakan contoh seperti bx bx-home, bx bx-home-circle, atau bx bx-building-house.',
            ]);
          }

        $payload = [
            'nama'      => $data['nama'],
            'url'       => $data['url'] ?: null,
            'icon'      => $data['icon'] ?: null,
            'parent_id' => $data['parentId'],
            'urutan'    => $data['urutan'],
            'is_active' => $data['isActive'],
        ];

        if ($this->editId) {
          $menu = Menu::findOrFail($this->editId);
          $menu->update($payload);
          app(LogAktivitasService::class)->catatManual(__('messages.menu_module_name'), __('messages.menu_log_update', ['nama' => $menu->nama]), '/admin/menus', [
            'menu_id' => $menu->id,
          ]);
        } else {
          $menu = Menu::create($payload);
          app(LogAktivitasService::class)->catatManual(__('messages.menu_module_name'), __('messages.menu_log_add', ['nama' => $menu->nama]), '/admin/menus', [
            'menu_id' => $menu->id,
          ]);
        }

        $this->showModal = false;
        $this->reset(['nama', 'url', 'icon', 'parentId', 'urutan', 'isActive', 'editId']);
        $this->resetPage();
    }

    public function hapus(int $id): void
    {
      $menu = Menu::findOrFail($id);
      app(LogAktivitasService::class)->catatManual(__('messages.menu_module_name'), __('messages.menu_log_delete', ['nama' => $menu->nama]), '/admin/menus', [
        'menu_id' => $menu->id,
      ]);
      $menu->delete();
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'menus'    => Menu::with('parent')
                ->when($this->search, fn ($q) => $q->where('nama', 'like', '%' . $this->search . '%'))
                ->orderBy('urutan')
            ->paginate((int) config('app_runtime.pagination_default', 10)),
            'parents'  => Menu::whereNull('parent_id')->active()->orderBy('urutan')->get(),
        'iconValid' => Menu::iconTersedia($this->icon),
        'iconPreviewClass' => Menu::classIconRender($this->icon),
        ];
    }
};
?>
@section('title', __('messages.admin_manage_menu_title'))

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1" style="color:#1e293b">{{ __('messages.admin_manage_menu_heading') }}</h4>
      <p class="text-muted mb-0" style="font-size:.875rem">{{ __('messages.admin_manage_menu_subheading') }}</p>
    </div>
    <button class="btn btn-primary" wire:click="buka">
      <i class="bx bx-plus me-1"></i> {{ __('messages.add_menu') }}
    </button>
  </div>

  <div class="card mb-4">
    <div class="card-body py-3">
      <input wire:model.live.debounce.300ms="search" type="search"
              class="form-control" placeholder="{{ __('messages.search_menu_placeholder') }}">
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>{{ __('messages.menu_name') }}</th>
            <th>{{ __('messages.parent') }}</th>
            <th>{{ __('messages.url') }}</th>
            <th>{{ __('messages.icon') }}</th>
            <th class="text-center">{{ __('messages.order') }}</th>
            <th>{{ __('messages.status') }}</th>
            <th class="text-center">{{ __('messages.action') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($menus as $menu)
            <tr>
              <td>{{ $menus->firstItem() + $loop->index }}</td>
              <td class="fw-semibold">{{ $menu->nama }}</td>
              <td class="text-muted">{{ $menu->parent?->nama ?? '-' }}</td>
              <td><code>{{ $menu->url ?? '-' }}</code></td>
              <td>
                @if ($menu->icon)
                  <i class="{{ \App\Models\Menu::classIconRender($menu->icon) }}"></i>
                  <small class="text-muted ms-1">{{ $menu->icon }}</small>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td class="text-center">{{ $menu->urutan }}</td>
              <td>
                @if ($menu->is_active)
                  <span class="badge bg-label-success">{{ __('messages.active') }}</span>
                @else
                  <span class="badge bg-label-secondary">{{ __('messages.inactive') }}</span>
                @endif
              </td>
              <td class="text-center">
                <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit({{ $menu->id }})" title="{{ __('messages.edit') }}">
                  <i class="bx bx-edit-alt"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-text-danger"
                        title="{{ __('messages.delete') }}"
                        @click="Swal.fire({
                          title: '{{ __('messages.confirm_delete') }}',
                          text: '{{ __('messages.confirm_delete_menu', ['nama' => addslashes($menu->nama)]) }}',
                          icon: 'warning',
                          showCancelButton: true,
                          confirmButtonText: '{{ __('messages.yes_delete') }}',
                          cancelButtonText: '{{ __('messages.cancel') }}',
                        }).then(r => r.isConfirmed && $wire.hapus({{ $menu->id }}))"
                        >
                  <i class="bx bx-trash"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">{{ __('messages.no_menu_data') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $menus->links() }}</div>
  </div>

  @if ($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.45)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $editId ? __('messages.edit_menu') : __('messages.add_menu') }}</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.menu_name') }} <span class="text-danger">*</span></label>
                <input wire:model="nama" type="text" class="form-control @error('nama') is-invalid @enderror"
                       placeholder="{{ __('messages.menu_name_example') }}">
                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.parent_menu') }}</label>
                <select wire:model="parentId" class="form-select">
                  <option value="">{{ __('messages.no_parent_root') }}</option>
                  @foreach ($parents as $p)
                    <option value="{{ $p->id }}">{{ $p->nama }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.url') }}</label>
                <input wire:model="url" type="text" class="form-control" placeholder="{{ __('messages.menu_url_placeholder') }}">
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.icon') }}
                  <a href="https://boxicons.com" target="_blank" class="ms-1" style="font-size:.75rem">(Boxicons)</a>
                </label>
                <input wire:model.live.debounce.300ms="icon" type="text" class="form-control @error('icon') is-invalid @enderror" placeholder="{{ __('messages.menu_icon_placeholder') }}">
                @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">Gunakan class icon Boxicons lokal. Contoh: bx bx-home, bx bx-home-alt-3, bx bx-widget, bx bx-building-house.</div>
                @if ($icon)
                  <div class="border rounded p-3 mt-2 d-flex align-items-center gap-2">
                    @if ($iconValid)
                      <i class="{{ $iconPreviewClass }} bx-sm"></i>
                      <span class="text-muted small">Preview: {{ $icon }}</span>
                    @else
                      <i class="bx bx-error-circle text-danger bx-sm"></i>
                      <span class="text-danger small">Class icon tidak ditemukan di Boxicons versi proyek ini.</span>
                    @endif
                  </div>
                @endif
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.order') }}</label>
                <input wire:model="urutan" type="number" min="0" class="form-control">
              </div>

              <div class="form-check">
                <input wire:model="isActive" type="checkbox" class="form-check-input" id="menuActiveCheck">
                <label class="form-check-label" for="menuActiveCheck">{{ __('messages.menu_active') }}</label>
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
