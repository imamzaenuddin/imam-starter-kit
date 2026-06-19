<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CrudGeneratorService
{
    public function generate(string $table, array $fieldsConfig): string
    {
        // 1. Prepare Names
        $tableName = $table; // e.g. m_prodi
        $baseName = Str::camel(str_replace('m_', '', $tableName)); // prodi
        $modelName = Str::studly(str_replace('m_', '', $tableName)); // Prodi
        $routePath = 'admin/referensi/' . Str::kebab($baseName); // admin/referensi/prodi
        $viewPath = 'admin.referensi.' . Str::kebab($baseName) . '.index'; // admin.referensi.prodi.index

        // Determine Primary Key
        $columns = Schema::getColumnListing($tableName);
        $primaryKey = 'id';
        if (!in_array('id', $columns)) {
            // Find something ending with _id or id
            foreach ($columns as $col) {
                if (str_ends_with($col, 'id')) {
                    $primaryKey = $col;
                    break;
                }
            }
        }
        $keyType = Schema::getColumnType($tableName, $primaryKey) === 'integer' || Schema::getColumnType($tableName, $primaryKey) === 'bigint' ? 'int' : 'string';

        // 2. Generate Model
        $this->generateModel($tableName, $modelName, $columns, $primaryKey, $keyType);

        // 3. Generate Volt Component
        $this->generateVoltComponent($tableName, $modelName, $baseName, $fieldsConfig, $primaryKey, $viewPath);

        // 4. Append Route
        $this->appendRoute($routePath, $viewPath);

        // 5. Daftarkan Menu & Akses Superadmin
        $this->registerMenu($modelName, '/' . $routePath);

        return '/' . $routePath;
    }

    private function generateModel(string $tableName, string $modelName, array $columns, string $primaryKey, string $keyType)
    {
        $fillable = array_filter($columns, function ($col) use ($primaryKey) {
            return !in_array($col, ['created_at', 'updated_at', 'deleted_at']);
        });

        $fillableString = implode(",\n        ", array_map(fn($col) => "'$col'", $fillable));

        $pkBlock = "";
        if ($primaryKey !== 'id' || $keyType === 'string') {
            $pkBlock .= "    protected \$primaryKey = '$primaryKey';\n";
            if ($keyType === 'string') {
                $pkBlock .= "    public \$incrementing = false;\n";
                $pkBlock .= "    protected \$keyType = 'string';\n";
            }
        }

        $softDeletes = in_array('deleted_at', $columns) ? "use SoftDeletes;" : "";
        $softDeletesImport = in_array('deleted_at', $columns) ? "use Illuminate\Database\Eloquent\SoftDeletes;" : "";

        $stub = <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
$softDeletesImport

class $modelName extends Model
{
    $softDeletes

    protected \$table = '$tableName';
$pkBlock
    protected \$fillable = [
        $fillableString
    ];
}
PHP;
        File::put(app_path('Models/' . $modelName . '.php'), $stub);
    }

    private function generateVoltComponent(string $tableName, string $modelName, string $baseName, array $fieldsConfig, string $primaryKey, string $viewPath)
    {
        $dir = resource_path('views/livewire/' . str_replace('.', '/', $viewPath));
        if (!File::exists(dirname($dir))) {
            File::makeDirectory(dirname($dir), 0755, true);
        }

        // Generate properties
        $properties = [];
        $resets = [];
        $rules = [];
        $payloads = [];
        $formHtml = "";

        foreach ($fieldsConfig as $col => $type) {
            // Exclude PK from form if it's auto-incrementing integer id
            if ($col === 'id' && $primaryKey === 'id') continue;
            if ($type === 'abaikan') continue;

            // Property type
            $propType = '?string';
            $default = "null";
            
            if ($col === 'na') {
                $properties[] = "public bool \$is_aktif = true;";
                $resets[] = "'is_aktif'";
                continue;
            }

            if ($type === 'checkbox' || $type === 'toggle') {
                $propType = 'bool';
                $default = "false";
            }
            $properties[] = "public $propType \$$col = $default;";
            $resets[] = "'$col'";
            
            // Payload
            if ($col === 'na') {
                $payloads[] = "'na' => \$data['is_aktif'] ? 'N' : 'Y'";
            } elseif ($type === 'checkbox' || $type === 'toggle') {
                $payloads[] = "'$col' => \$data['$col'] ? 1 : 0";
            } else {
                $payloads[] = "'$col' => (\$data['$col'] === '' ? null : \$data['$col'])";
            }

            // Rules basic
            $rule = "'nullable'";
            if ($type === 'checkbox' || $type === 'toggle') {
                $rule = "'boolean'";
            } elseif ($col === $primaryKey) {
                $rule = "'required|string|max:255'";
            }
            $rules[] = "'$col' => $rule";
            

            // Form HTML
            $label = Str::title(str_replace('_', ' ', $col));
            $formHtml .= "                <div class=\"col-md-12 mb-3\">\n";
            $formHtml .= "                  <label class=\"form-label fw-semibold\">$label</label>\n";
            
            if ($type === 'text') {
                $formHtml .= "                  <input wire:model=\"$col\" type=\"text\" class=\"form-control @error('$col') is-invalid @enderror\">\n";
            } elseif ($type === 'textarea') {
                $formHtml .= "                <div class=\"col-md-12 mb-3\">\n";
                $formHtml .= "                  <label class=\"form-label fw-semibold\">$label</label>\n";
                $formHtml .= "                  <textarea wire:model=\"$col\" class=\"form-control @error('$col') is-invalid @enderror\" rows=\"3\"></textarea>\n";
            } elseif ($type === 'select') {
                $formHtml .= "                <div class=\"col-md-12 mb-3\">\n";
                $formHtml .= "                  <label class=\"form-label fw-semibold\">$label</label>\n";
                $formHtml .= "                  <select wire:model=\"$col\" class=\"form-select @error('$col') is-invalid @enderror\">\n";
                $formHtml .= "                    <option value=\"\">Pilih $label</option>\n";
                $formHtml .= "                  </select>\n";
            } elseif ($col === 'na') {
                $formHtml .= <<<BLADE
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold d-block">Status Aktif</label>
                  <div class="form-check form-switch mt-2">
                    <input wire:model="is_aktif" class="form-check-input" type="checkbox" id="is_aktif">
                    <label class="form-check-label" for="is_aktif">Aktif</label>
                  </div>
                  @error('is_aktif') <div class="invalid-feedback">{{ \$message }}</div> @enderror
                </div>
BLADE;
            } elseif ($type === 'toggle') {
                $formHtml .= <<<BLADE
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold d-block">$label</label>
                  <div class="form-check form-switch mt-2">
                    <input wire:model="$col" class="form-check-input" type="checkbox" id="toggle_$col">
                    <label class="form-check-label" for="toggle_$col">$label</label>
                  </div>
                  @error('$col') <div class="invalid-feedback">{{ \$message }}</div> @enderror
                </div>
BLADE;
            } elseif ($type === 'checkbox') {
                $formHtml .= "                <div class=\"col-md-12 mb-3\">\n";
                $formHtml .= "                  <div class=\"form-check mt-2\">\n";
                $formHtml .= "                    <input wire:model=\"$col\" type=\"checkbox\" class=\"form-check-input\" id=\"check_$col\">\n";
                $formHtml .= "                    <label class=\"form-check-label\" for=\"check_$col\">$label</label>\n";
                $formHtml .= "                  </div>\n";
            } elseif ($type === 'radio') {
                $formHtml .= "                  <div class=\"form-check\">\n";
                $formHtml .= "                    <input wire:model=\"$col\" type=\"radio\" value=\"1\" class=\"form-check-input\">\n";
                $formHtml .= "                    <label class=\"form-check-label\">Option 1</label>\n";
                $formHtml .= "                  </div>\n";
            } elseif ($type === 'multiselect') {
                $formHtml .= "                  <select wire:model=\"$col\" class=\"form-select @error('$col') is-invalid @enderror\" multiple>\n";
                $formHtml .= "                    <option value=\"\">Multi Select (Sesuaikan manual)</option>\n";
                $formHtml .= "                  </select>\n";
            }
            $formHtml .= "                  @error('$col') <div class=\"invalid-feedback\">{{ \$message }}</div> @enderror\n";
            $formHtml .= "                </div>\n";
        }

        $propStr = implode("\n    ", $properties);
        $resetStr = implode(", ", $resets);
        
        // Validation rules
        $rules = [];
        foreach ($fieldsConfig as $col => $type) {
            if ($col === 'id' && $primaryKey === 'id') continue;
            if ($type === 'abaikan') continue;

            if ($col === 'na') {
                $rules[] = "'is_aktif' => 'boolean'";
            } else {
                $rules[] = "'$col' => 'nullable'";
            }
        }
        $ruleStr = implode(",\n            ", $rules);
        $payloadStr = implode(",\n            ", $payloads);

        // Edit assignments
        $editAssignments = [];
        foreach ($fieldsConfig as $col => $type) {
            if ($col === 'id' && $primaryKey === 'id') continue;
            if ($type === 'abaikan') continue;
            
            if ($col === 'na') {
                $editAssignments[] = "\$this->is_aktif = (\$item->na === 'N');";
            } elseif ($type === 'checkbox' || $type === 'toggle') {
                $editAssignments[] = "\$this->$col = (bool)\$item->$col;";
            } else {
                $editAssignments[] = "\$this->$col = \$item->$col ?? '';";
            }
        }
        $editAssignStr = implode("\n        ", $editAssignments);

        // Table headers
        $tableHeaders = "";
        $tableRows = "";
        $i = 0;
        foreach ($fieldsConfig as $col => $type) {
            if ($type === 'abaikan') continue;
            if ($i++ > 4) break; // Limit 5 columns in table
            $label = Str::title(str_replace('_', ' ', $col));
            $tableHeaders .= "            <th>$label</th>\n";
            
            if ($col === 'na') {
                $tableRows .= "              <td>\n";
                $tableRows .= "                @if (\$row->na === 'N')\n";
                $tableRows .= "                  <span class=\"badge bg-label-success\">Aktif</span>\n";
                $tableRows .= "                @else\n";
                $tableRows .= "                  <span class=\"badge bg-label-danger\">Tidak Aktif</span>\n";
                $tableRows .= "                @endif\n";
                $tableRows .= "              </td>\n";
            } elseif ($type === 'toggle' || $type === 'checkbox') {
                $tableRows .= "              <td>\n";
                $tableRows .= "                @if (\$row->$col)\n";
                $tableRows .= "                  <span class=\"badge bg-label-success\">Ya</span>\n";
                $tableRows .= "                @else\n";
                $tableRows .= "                  <span class=\"badge bg-label-secondary\">Tidak</span>\n";
                $tableRows .= "                @endif\n";
                $tableRows .= "              </td>\n";
            } else {
                $tableRows .= "              <td>{{ \$row->$col }}</td>\n";
            }
        }

        $routeBase = '/admin/referensi/' . Str::kebab($baseName);

        $stub = <<<BLADE
<?php

use App\Models\\$modelName;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string \$search = '';
    public bool \$showModal = false;
    public ?string \$editId = null;

    $propStr

    protected \$queryString = ['search'];

    public function updatingSearch(): void
    {
        \$this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('$routeBase', 'dapat_buat')) abort(403);

        \$this->reset(['editId', $resetStr]);
        \$this->showModal = true;
    }

    public function edit(\$id = null): void
    {
        if (! auth()->user()?->bisaMenu('$routeBase', 'dapat_ubah')) abort(403);

        \$item = $modelName::findOrFail(\$id);
        \$this->editId = \$item->$primaryKey;
        $editAssignStr
        \$this->showModal = true;
    }

    public function simpan(): void
    {
        if (\$this->editId) {
            if (! auth()->user()?->bisaMenu('$routeBase', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('$routeBase', 'dapat_buat')) abort(403);
        }

        \$rules = [
            $ruleStr
        ];

        \$data = \$this->validate(\$rules);

        \$payload = [
            $payloadStr
        ];

        if (\$this->editId) {
            \$item = $modelName::findOrFail(\$this->editId);
            \$item->update(\$payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            \$item = $modelName::create(\$payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        \$this->showModal = false;
        \$this->reset(['editId', $resetStr]);
        \$this->resetPage();
    }

    public function hapus(\$id = null): void
    {
        if (! auth()->user()?->bisaMenu('$routeBase', 'dapat_hapus')) abort(403);

        \$item = $modelName::findOrFail(\$id);
        \$item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        \$this->resetPage();
    }

    public function with(): array
    {
        \$data = $modelName::query()
            ->when(\$this->search, function (\$query) {
                // \$query->where('$primaryKey', 'like', '%' . \$this->search . '%');
            })
            ->orderBy('$primaryKey', 'desc')
            ->paginate(10);

        return [
            'rows' => \$data
        ];
    }
};
?>

@section('title', 'Kelola $modelName')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola $modelName</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>$modelName</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
    </div>
    <button class="btn btn-primary" wire:click="buka">
      <i class="bx bx-plus me-1"></i> Tambah Baru
    </button>
  </div>

  @if (session('sukses'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {!! session('sukses') !!}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
      <div class="input-group input-group-merge">
        <span class="input-group-text"><i class="bx bx-search text-muted"></i></span>
        <input wire:model.live.debounce.300ms="search" type="search" class="form-control" placeholder="Cari data...">
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
$tableHeaders
            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse (\$rows as \$row)
            <tr>
$tableRows
              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ \$row->$primaryKey }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && \$wire.hapus('{{ \$row->$primaryKey }}'))">
                    <i class="bx bx-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="text-center py-5 text-muted">Tidak ada data ditemukan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if (\$rows->hasPages())
      <div class="card-footer border-top">{{ \$rows->links() }}</div>
    @endif
  </div>

  @if (\$showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold">{{ \$editId ? 'Ubah' : 'Tambah' }} $modelName</h5>
            <button type="button" class="btn-close" wire:click="\$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
$formHtml
              </div>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-outline-secondary" wire:click="\$set('showModal', false)">Batal</button>
              <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="simpan"><i class="bx bx-save me-1"></i>Simpan</span>
                <span wire:loading wire:target="simpan"><span class="spinner-border spinner-border-sm me-1"></span>...</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
</div>
BLADE;

        File::put($dir . '.blade.php', $stub);
    }

    private function appendRoute(string $routePath, string $viewPath)
    {
        $routesFile = base_path('routes/web.php');
        $content = File::get($routesFile);

        $routeLine = "Volt::route('/$routePath', '$viewPath')->name('admin.referensi." . str_replace('/', '.', str_replace('admin/referensi/', '', $routePath)) . "');";

        if (!str_contains($content, $routeLine)) {
            // Find a good place to inject. E.g. after middleware('auth')
            $content .= "\n// Auto-generated by CRUD Generator\n" . "Route::middleware(['auth'])->group(function () {\n    $routeLine\n});\n";
            File::put($routesFile, $content);
        }
    }

    private function registerMenu(string $namaMenu, string $url)
    {
        $menu = \App\Models\Menu::where('url', $url)->first();
        if (!$menu) {
            // Cari Parent Menu (Otomatis deteksi berdasarkan nama rute)
            $parentId = null;
            if (str_contains($url, 'referensi')) {
                $parent = \App\Models\Menu::where('nama', 'Referensi')->first();
                if ($parent) $parentId = $parent->id;
            }

            $urutan = \App\Models\Menu::where('parent_id', $parentId)->max('urutan') + 1;

            $menu = \App\Models\Menu::create([
                'nama' => $namaMenu,
                'url' => $url,
                'icon' => 'bx-table', // Default icon
                'parent_id' => $parentId,
                'urutan' => $urutan,
                'is_active' => true,
            ]);
        }

        // Berikan Full Access ke Superadmin (Level ID 1)
        if ($menu) {
            $menu->levels()->syncWithoutDetaching([
                1 => [
                    'dapat_buat' => true,
                    'dapat_lihat' => true,
                    'dapat_ubah' => true,
                    'dapat_hapus' => true,
                    'dapat_backup' => false,
                    'dapat_restore' => false,
                    'dapat_hapus_backup' => false,
                ]
            ]);
        }
    }
}
