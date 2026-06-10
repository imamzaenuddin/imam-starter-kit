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
          session()->flash('sukses', 'Menu berhasil diperbarui.');
        } else {
          $menu = Menu::create($payload);
          app(LogAktivitasService::class)->catatManual(__('messages.menu_module_name'), __('messages.menu_log_add', ['nama' => $menu->nama]), '/admin/menus', [
            'menu_id' => $menu->id,
          ]);
          session()->flash('sukses', 'Menu baru berhasil ditambahkan.');
        }

        \Illuminate\Support\Facades\Cache::flush();
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
      \Illuminate\Support\Facades\Cache::flush();
      session()->flash('sukses', 'Menu berhasil dihapus.');
    }

    public function updateMenuStructure(array $structure): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($structure) {
            $saveRecursive = function (array $items, ?int $parentId) use (&$saveRecursive) {
                foreach ($items as $item) {
                    $id = (int) $item['id'];
                    Menu::where('id', $id)->update([
                        'parent_id' => $parentId,
                        'urutan'    => (int) $item['urutan'],
                    ]);
                    if (!empty($item['children'])) {
                        $saveRecursive($item['children'], $id);
                    }
                }
            };

            $saveRecursive($structure, null);
        });

        // Clear menu cache to update sidebar immediately
        \Illuminate\Support\Facades\Cache::flush();
        
        session()->flash('sukses', 'Struktur dan urutan menu berhasil diperbarui.');
    }

    private function isDescendantOf(int $menuId, int $parentId): bool
    {
        $menu = Menu::find($menuId);
        while ($menu && $menu->parent_id !== null) {
            if ($menu->parent_id === $parentId) {
                return true;
            }
            $menu = Menu::find($menu->parent_id);
        }
        return false;
    }

    private function dapatkanParentDropdown(): array
    {
        $allMenus = Menu::active()->orderBy('urutan')->get();

        // Build tree in memory
        foreach ($allMenus as $m) {
            $m->setRelation('children', new \Illuminate\Database\Eloquent\Collection);
        }

        $menuMap = $allMenus->keyBy('id');
        $tree = new \Illuminate\Database\Eloquent\Collection;

        foreach ($allMenus as $m) {
            if (empty($m->parent_id)) {
                $tree->push($m);
            } else {
                $parent = $menuMap->get($m->parent_id);
                if ($parent) {
                    $parent->children->push($m);
                }
            }
        }

        // Flatten tree with indentation
        $list = [];
        $flatten = function ($items, $depth = 0) use (&$flatten, &$list) {
            foreach ($items as $item) {
                // If editing, prevent selecting self or any of its descendants as parent
                if ($this->editId && ($item->id === $this->editId || $this->isDescendantOf($item->id, $this->editId))) {
                    continue;
                }
                
                $prefix = str_repeat('— ', $depth);
                $list[] = [
                    'id'   => $item->id,
                    'nama' => $prefix . $item->nama,
                ];
                if ($item->children->isNotEmpty()) {
                    $flatten($item->children, $depth + 1);
                }
            }
        };

        $flatten($tree);
        return $list;
    }

    public function with(): array
    {
        if ($this->search === '') {
            $allMenus = Menu::orderBy('urutan')->get();

            foreach ($allMenus as $m) {
                $m->setRelation('children', new \Illuminate\Database\Eloquent\Collection);
            }

            $menuMap = $allMenus->keyBy('id');
            $menus = new \Illuminate\Database\Eloquent\Collection;

            foreach ($allMenus as $m) {
                if (empty($m->parent_id)) {
                    $menus->push($m);
                } else {
                    $parent = $menuMap->get($m->parent_id);
                    if ($parent) {
                        $parent->children->push($m);
                    }
                }
            }
        } else {
            $menus = Menu::with('parent')
                ->where('nama', 'like', '%' . $this->search . '%')
                ->orderBy('urutan')
                ->paginate((int) config('app_runtime.pagination_default', 10));
        }

        return [
            'menus'    => $menus,
            'parents'  => $this->dapatkanParentDropdown(),
            'iconValid' => Menu::iconTersedia($this->icon),
            'iconPreviewClass' => Menu::classIconRender($this->icon),
        ];
    }
};
?>
@section('title', __('messages.admin_manage_menu_title'))

@section('page-style')
<style>
  .menu-tree-container {
    padding: 1.5rem;
  }
  .menu-drag-list {
    list-style: none;
    padding-left: 0;
    margin-bottom: 0;
  }
  .menu-drag-item {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    margin-bottom: 0.75rem;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }
  .menu-drag-item:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
  }
  .menu-drag-item-content {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
  }
  .drag-handle {
    cursor: grab;
    color: #94a3b8;
    font-size: 1.25rem;
    padding: 0.25rem;
    margin-right: 0.5rem;
    border-radius: 4px;
    transition: background-color 0.15s ease;
  }
  .drag-handle:hover {
    background-color: #f1f5f9;
    color: #475569;
  }
  .drag-handle:active {
    cursor: grabbing;
  }
  .menu-drag-child-list {
    list-style: none;
    padding-left: 2.5rem;
    margin-top: 0;
    margin-bottom: 0;
    position: relative;
    min-height: 10px;
  }
  .menu-drag-child-list::before {
    content: '';
    position: absolute;
    left: 1.25rem;
    top: 0;
    bottom: 1rem;
    width: 2px;
    background-color: #e2e8f0;
  }
  .menu-drag-child-list .menu-drag-item {
    position: relative;
  }
  .menu-drag-child-list .menu-drag-item::before {
    content: '';
    position: absolute;
    left: -1.25rem;
    top: 1.25rem;
    width: 1.25rem;
    height: 2px;
    background-color: #e2e8f0;
  }
  .menu-drag-child-list:empty {
    min-height: 35px;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    margin-left: 2.5rem;
    margin-right: 1rem;
    margin-bottom: 0.75rem;
    background: #f8fafc;
  }
  .menu-drag-child-list:empty::before {
    display: none;
  }
  /* Remove empty list hiding to allow unlimited nested drops */
</style>
@endsection

<div id="menu-manager-root">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1" style="color:#1e293b">{{ __('messages.admin_manage_menu_heading') }}</h4>
      <p class="text-muted mb-0" style="font-size:.875rem">{{ __('messages.admin_manage_menu_subheading') }}</p>
    </div>
    <button class="btn btn-primary" wire:click="buka">
      <i class="bx bx-plus me-1"></i> {{ __('messages.add_menu') }}
    </button>
  </div>

  @if (session('sukses'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
      <div class="d-flex align-items-center">
        <i class="bx bx-check-circle me-2" style="font-size: 1.25rem;"></i>
        <span>{{ session('sukses') }}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
      <div class="d-flex align-items-center">
        <i class="bx bx-error-circle me-2" style="font-size: 1.25rem;"></i>
        <span>{{ session('error') }}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="card mb-4">
    <div class="card-body py-3">
      <input wire:model.live.debounce.300ms="search" type="search"
              class="form-control" placeholder="{{ __('messages.search_menu_placeholder') }}">
    </div>
  </div>

  @if ($search !== '')
    {{-- Flat Table for Search --}}
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
  @else
    @php
      $renderDragItem = function ($menu) use (&$renderDragItem) {
          $iconHtml = '';
          if ($menu->icon) {
              $iconHtml = '<i class="' . e(\App\Models\Menu::classIconRender($menu->icon)) . ' me-2 text-primary bx-sm"></i>';
          }
          
          $statusBadge = $menu->is_active
              ? '<span class="badge bg-label-success">' . e(__('messages.active')) . '</span>'
              : '<span class="badge bg-label-secondary">' . e(__('messages.inactive')) . '</span>';
              
          $urlHtml = '';
          if ($menu->url) {
              $urlHtml = '<code class="ms-2 px-2 py-0.5 rounded bg-light text-secondary" style="font-size: 0.75rem;">' . e($menu->url) . '</code>';
          }

          $confirmTitle = __('messages.confirm_delete');
          $confirmText = __('messages.confirm_delete_menu', ['nama' => addslashes($menu->nama)]);
          $yesDelete = __('messages.yes_delete');
          $cancelText = __('messages.cancel');

          $clickJs = 'Swal.fire({
            title: \'' . addslashes($confirmTitle) . '\',
            text: \'' . addslashes($confirmText) . '\',
            icon: \'warning\',
            showCancelButton: true,
            confirmButtonText: \'' . addslashes($yesDelete) . '\',
            cancelButtonText: \'' . addslashes($cancelText) . '\',
          }).then(r => r.isConfirmed && $wire.hapus(' . (int) $menu->id . '))';

          $html = '<li class="menu-drag-item" data-id="' . e($menu->id) . '">
            <div class="menu-drag-item-content d-flex align-items-center">
              <div class="drag-handle">
                <i class="bx bx-grid-vertical"></i>
              </div>
              <div class="menu-drag-item-info d-flex align-items-center flex-grow-1">
                ' . $iconHtml . '
                <span class="fw-semibold text-dark">' . e($menu->nama) . '</span>
                ' . $urlHtml . '
                <span class="ms-auto me-3">
                  ' . $statusBadge . '
                </span>
              </div>
              <div class="menu-drag-item-actions d-flex align-items-center gap-1">
                <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit(' . e($menu->id) . ')" title="' . e(__('messages.edit')) . '">
                  <i class="bx bx-edit-alt"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-text-danger"
                        title="' . e(__('messages.delete')) . '"
                        @click="' . e($clickJs) . '"
                        >
                  <i class="bx bx-trash"></i>
                </button>
              </div>
            </div>
            <ul class="menu-drag-child-list" data-parent-id="' . e($menu->id) . '">';
          
          foreach ($menu->children as $child) {
              $html .= $renderDragItem($child);
          }
          
          $html .= '</ul></li>';
          return $html;
      };
    @endphp

    {{-- Drag & Drop Tree Layout --}}
    <div class="card">
      <div class="menu-tree-container">
        <div class="alert alert-light border d-flex align-items-center mb-4 text-muted" style="font-size: 0.85rem; background-color: #f8fafc;">
          <i class="bx bx-info-circle me-2 text-primary" style="font-size: 1.15rem;"></i>
          <span>Seret gagang <i class="bx bx-grid-vertical"></i> untuk menyusun urutan menu secara dinamis. Anda dapat menaruh sub-menu di bawah menu utama (mendukung tingkat kedalaman tak terbatas).</span>
        </div>
        
        <ul class="menu-drag-list menu-root-list" id="menu-root">
          @forelse ($menus as $menu)
            {!! $renderDragItem($menu) !!}
          @empty
            <div class="text-center py-5 text-muted">
              <i class="bx bx-folder-open display-4 mb-2"></i>
              <p class="mb-0">{{ __('messages.no_menu_data') }}</p>
            </div>
          @endforelse
        </ul>
      </div>
    </div>
  @endif

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
                    <option value="{{ $p['id'] }}">{{ $p['nama'] }}</option>
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

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
  window.initializeMenuSorting = function() {
    if (window.menuSortableInstances) {
      window.menuSortableInstances.forEach(inst => inst.destroy());
    }
    window.menuSortableInstances = [];
    
    const rootEl = document.getElementById('menu-root');
    if (rootEl) {
      const rootInst = Sortable.create(rootEl, {
        group: 'nested-menus',
        animation: 150,
        handle: '.drag-handle',
        fallbackOnBody: true,
        swapThreshold: 0.65,
        onEnd() {
          window.saveMenuStructure();
        }
      });
      window.menuSortableInstances.push(rootInst);
    }
    
    document.querySelectorAll('.menu-drag-child-list').forEach(el => {
      const childInst = Sortable.create(el, {
        group: 'nested-menus',
        animation: 150,
        handle: '.drag-handle',
        fallbackOnBody: true,
        swapThreshold: 0.65,
        onEnd() {
          window.saveMenuStructure();
        }
      });
      window.menuSortableInstances.push(childInst);
    });
  };

  window.saveMenuStructure = function() {
    const parseList = function(ulElement) {
      if (!ulElement) return [];
      const items = [];
      ulElement.querySelectorAll(':scope > li').forEach((li, index) => {
        const id = li.getAttribute('data-id');
        const childUl = li.querySelector(':scope > .menu-drag-child-list');
        const children = parseList(childUl);
        items.push({
          id: id,
          urutan: index + 1,
          children: children
        });
      });
      return items;
    };

    const rootEl = document.getElementById('menu-root');
    const structure = parseList(rootEl);
    
    // Find our specific Livewire component wrapper
    const wireEl = document.getElementById('menu-manager-root');
    if (wireEl) {
      const componentId = wireEl.getAttribute('wire:id') || wireEl.getAttribute('id');
      if (typeof Livewire !== 'undefined' && Livewire.find) {
        const component = Livewire.find(componentId);
        if (component) {
          component.updateMenuStructure(structure);
        }
      }
    }
  };

  document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ respond }) => {
      respond(({ status, response }) => {
        setTimeout(() => {
          window.initializeMenuSorting();
        }, 50);
      });
    });
  });

  document.addEventListener('livewire:navigated', () => {
    window.initializeMenuSorting();
  });

  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
      window.initializeMenuSorting();
    }, 50);
  });
</script>
@endsection
