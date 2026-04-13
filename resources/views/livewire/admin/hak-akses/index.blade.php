<?php
/**
 * Halaman Mapping Hak Akses Level ↔ Menu
 *
 * Route  : GET /admin/hak-akses  (name: admin.hak-akses)
 * Layout : components.layouts.app
 *
 * Fitur  : Pilih Level → tampilkan semua menu root+children
 *          dengan toggle checkbox per hak akses (lihat/buat/ubah/hapus).
 *          Perubahan disimpan atomik via sync.
 */

use App\Models\Level;
use App\Models\Menu;
use App\Services\LogAktivitasService;
use App\Services\MenuService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {

    public ?int $selectedLevel = null;
    public array $permissions  = [];   // ['menu_id' => ['lihat'=>bool, 'buat'=>bool, ...]]

    public function updatedSelectedLevel(): void
    {
        $this->muatPermissions();
    }

    /** Muat mapping level saat ini dari DB */
    private function muatPermissions(): void
    {
        $this->permissions = [];

        if (! $this->selectedLevel) return;

        $level = Level::with('menus')->find($this->selectedLevel);

        if (! $level) return;

        // Inisialisasi semua menu dengan false
        $allIds = Menu::active()->pluck('id');
        foreach ($allIds as $id) {
            $this->permissions[$id] = [
                'lihat'  => false,
                'buat'   => false,
                'ubah'   => false,
                'hapus'  => false,
            'backup' => false,
            'restore' => false,
            'hapus_backup' => false,
            ];
        }

        // Isi dari pivot
        foreach ($level->menus as $menu) {
            $this->permissions[$menu->id] = [
                'lihat'  => (bool) $menu->pivot->dapat_lihat,
                'buat'   => (bool) $menu->pivot->dapat_buat,
                'ubah'   => (bool) $menu->pivot->dapat_ubah,
                'hapus'  => (bool) $menu->pivot->dapat_hapus,
            'backup' => (bool) ($menu->pivot->dapat_backup ?? false),
            'restore' => (bool) ($menu->pivot->dapat_restore ?? false),
            'hapus_backup' => (bool) ($menu->pivot->dapat_hapus_backup ?? false),
            ];
        }
    }

    /** Simpan semua mapping sekaligus */
    public function simpan(): void
    {
        if (! $this->selectedLevel) return;

        $level = Level::findOrFail($this->selectedLevel);
        $menuBackupRestoreId = (int) (Menu::query()->where('url', '/admin/backup-restore')->value('id') ?? 0);

        $sync = [];
        foreach ($this->permissions as $menuId => $hak) {
            $isMenuBackupRestore = ((int) $menuId === $menuBackupRestoreId);

            $izinBackup = $isMenuBackupRestore ? (bool) ($hak['backup'] ?? false) : false;
            $izinRestore = $isMenuBackupRestore ? (bool) ($hak['restore'] ?? false) : false;
            $izinHapusBackup = $isMenuBackupRestore ? (bool) ($hak['hapus_backup'] ?? false) : false;

            // Hanya simpan jika minimal 'lihat' aktif
            if (
                $hak['lihat'] ||
                $hak['buat'] ||
                $hak['ubah'] ||
                $hak['hapus'] ||
                $izinBackup ||
                $izinRestore ||
                $izinHapusBackup
            ) {
                $sync[(int) $menuId] = [
                    'dapat_lihat' => (bool) $hak['lihat'],
                    'dapat_buat'  => (bool) $hak['buat'],
                    'dapat_ubah'  => (bool) $hak['ubah'],
                    'dapat_hapus' => (bool) $hak['hapus'],
                    'dapat_backup' => $izinBackup,
                    'dapat_restore' => $izinRestore,
                    'dapat_hapus_backup' => $izinHapusBackup,
                ];
            }
        }

        // sync() hapus entri lama & masukkan yang baru — atomic, aman
        $level->menus()->sync($sync);

        // Hapus cache menu semua user di level ini
        app(MenuService::class)->hapusCacheLevel($level->id);
        app(LogAktivitasService::class)->catatManual(__('messages.access_mapping_module_name'), __('messages.access_mapping_log_update', ['level' => $level->nama_level]), '/admin/hak-akses', [
          'level_id' => $level->id,
          'jumlah_menu' => count($sync),
        ]);

        session()->flash('sukses', __('messages.access_mapping_saved'));
    }

    public function with(): array
    {
        return [
            'levels'   => Level::where('is_active', true)->orderBy('nama_level')->get(),
            'rootMenus' => Menu::with(['children' => fn ($q) => $q->active()->orderBy('urutan')])
                               ->active()
                               ->root()
                               ->orderBy('urutan')
                               ->get(),
        ];
    }
};
?>
@section('title', __('messages.menu_access_title'))

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1" style="color:#1e293b">{{ __('messages.access_mapping_heading') }}</h4>
      <p class="text-muted mb-0" style="font-size:.875rem">
        {{ __('messages.access_mapping_subheading') }}
      </p>
    </div>
  </div>

  @if (session('sukses'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center mb-4" role="alert">
      <i class="bx bx-check-circle me-2"></i> {{ session('sukses') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- Pilih Level --}}
  <div class="card mb-4">
    <div class="card-body">
      <label class="form-label fw-semibold">{{ __('messages.select_user_level') }}</label>
      <select wire:model.live="selectedLevel" class="form-select" style="max-width:320px">
        <option value="">{{ __('messages.select_level_option') }}</option>
        @foreach ($levels as $level)
          <option value="{{ $level->id }}">{{ $level->nama_level }}</option>
        @endforeach
      </select>
    </div>
  </div>

  @if ($selectedLevel)
    <form wire:submit="simpan">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <span class="fw-semibold">{{ __('messages.menu_access_list') }}</span>
          <button type="submit" class="btn btn-primary btn-sm"
                  wire:loading.attr="disabled" wire:target="simpan">
            <span wire:loading.remove wire:target="simpan">
              <i class="bx bx-save me-1"></i>{{ __('messages.save_changes') }}
            </span>
            <span wire:loading wire:target="simpan" style="display:none">
              <span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.saving') }}
            </span>
          </button>
        </div>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="min-width:220px">{{ __('messages.menu') }}</th>
                <th class="text-center">
                  <span class="badge bg-label-primary">{{ __('messages.view') }}</span>
                </th>
                <th class="text-center">
                  <span class="badge bg-label-success">{{ __('messages.create') }}</span>
                </th>
                <th class="text-center">
                  <span class="badge bg-label-warning">{{ __('messages.update') }}</span>
                </th>
                <th class="text-center">
                  <span class="badge bg-label-danger">{{ __('messages.delete') }}</span>
                </th>
                <th class="text-center">
                  <span class="badge bg-label-info">{{ __('messages.backup_permission_backup') }}</span>
                </th>
                <th class="text-center">
                  <span class="badge bg-label-warning">{{ __('messages.backup_permission_restore') }}</span>
                </th>
                <th class="text-center">
                  <span class="badge bg-label-danger">{{ __('messages.backup_permission_delete') }}</span>
                </th>
              </tr>
            </thead>
            <tbody>
              @foreach ($rootMenus as $root)
                @php
                    $isMenuBackupRoot = $root->url === '/admin/backup-restore';
                @endphp
                {{-- Baris menu root --}}
                <tr class="table-light">
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      @if ($root->icon)
                        <i class="{{ $root->icon }} text-primary"></i>
                      @endif
                      <span class="fw-semibold">{{ $root->nama }}</span>
                    </div>
                  </td>
                  @foreach (['lihat','buat','ubah','hapus','backup','restore','hapus_backup'] as $hak)
                    <td class="text-center">
                      @if (in_array($hak, ['backup', 'restore', 'hapus_backup'], true) && ! $isMenuBackupRoot)
                        <span class="text-muted">-</span>
                      @else
                        <input type="checkbox"
                               class="form-check-input"
                               wire:model="permissions.{{ $root->id }}.{{ $hak }}">
                      @endif
                    </td>
                  @endforeach
                </tr>

                {{-- Baris sub-menu --}}
                @foreach ($root->children as $child)
                  @php
                      $isMenuBackupChild = $child->url === '/admin/backup-restore';
                  @endphp
                  <tr>
                    <td style="padding-left:2rem">
                      <div class="d-flex align-items-center gap-2 text-muted">
                        <i class="bx bx-subdirectory-right"></i>
                        @if ($child->icon)
                          <i class="{{ $child->icon }}"></i>
                        @endif
                        {{ $child->nama }}
                      </div>
                    </td>
                    @foreach (['lihat','buat','ubah','hapus','backup','restore','hapus_backup'] as $hak)
                      <td class="text-center">
                        @if (in_array($hak, ['backup', 'restore', 'hapus_backup'], true) && ! $isMenuBackupChild)
                          <span class="text-muted">-</span>
                        @else
                          <input type="checkbox"
                                 class="form-check-input"
                                 wire:model="permissions.{{ $child->id }}.{{ $hak }}">
                        @endif
                      </td>
                    @endforeach
                  </tr>
                @endforeach
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </form>
  @else
    <div class="card">
      <div class="card-body text-center py-5 text-muted">
        <i class="bx bx-key" style="font-size:2.5rem;opacity:.4"></i>
        <p class="mt-2 mb-0">{{ __('messages.select_level_first_for_access') }}</p>
      </div>
    </div>
  @endif
</div>
