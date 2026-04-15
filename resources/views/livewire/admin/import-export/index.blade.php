<?php

use App\Services\ImportExportMasterService;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('components.layouts.app')] class extends Component {
    use WithFileUploads;

    public string $entitasExport = 'levels';
    public string $entitasImport = 'levels';
    public ?TemporaryUploadedFile $fileImport = null;
    public array $hasilImport = [];

    public function mount(): void
    {
        if (! auth()->user()->bisaMenu('/admin/import-export', 'dapat_lihat')) {
            abort(403);
        }
    }

    public function exportCsv()
    {
        if (! auth()->user()->bisaMenu('/admin/import-export', 'dapat_lihat')) {
            abort(403);
        }

        $this->validate([
            'entitasExport' => 'required|string|in:levels,menus',
        ]);

        app(LogAktivitasService::class)->catatManual(
            __('messages.menu_import_export_master'),
            __('messages.import_export_log_export', ['entitas' => $this->entitasExport]),
            '/admin/import-export',
            ['entitas' => $this->entitasExport]
        );

        return $this->service()->exportCsv($this->entitasExport);
    }

    public function importCsv(): void
    {
        if (! auth()->user()->bisaMenu('/admin/import-export', 'dapat_buat')) {
            abort(403);
        }

        $this->validate([
            'entitasImport' => 'required|string|in:levels,menus',
            'fileImport' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $hasil = $this->service()->importCsv($this->entitasImport, $this->fileImport->getRealPath());
        $this->hasilImport = $hasil;

        app(LogAktivitasService::class)->catatManual(
            __('messages.menu_import_export_master'),
            __('messages.import_export_log_import', ['entitas' => $this->entitasImport]),
            '/admin/import-export',
            [
                'entitas' => $this->entitasImport,
                'jumlah_total' => $hasil['jumlah_total'] ?? 0,
                'jumlah_berhasil' => $hasil['jumlah_berhasil'] ?? 0,
                'jumlah_gagal' => $hasil['jumlah_gagal'] ?? 0,
            ]
        );

        $this->fileImport = null;
    }

    public function with(): array
    {
        return [
            'opsiEntitas' => $this->service()->entitasTersedia(),
        ];
    }

    private function service(): ImportExportMasterService
    {
        return app(ImportExportMasterService::class);
    }
};
?>
@section('title', __('messages.import_export_title'))

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">{{ __('messages.import_export_title') }}</h4>
      <p class="text-muted mb-0">{{ __('messages.import_export_subtitle') }}</p>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('messages.export_data') }}</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">{{ __('messages.select_master_data') }}</label>
            <select wire:model="entitasExport" class="form-select @error('entitasExport') is-invalid @enderror">
              @foreach ($opsiEntitas as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
              @endforeach
            </select>
            @error('entitasExport') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <button type="button" class="btn btn-primary" wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv">
            <span class="d-flex align-items-center justify-content-center gap-2">
              <span wire:loading.remove wire:target="exportCsv"><i class="bx bx-download me-1"></i>{{ __('messages.export_csv') }}</span>
              <span wire:loading wire:target="exportCsv" style="display:none">
                <span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.processing') }}
              </span>
            </span>
          </button>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('messages.import_data') }}</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">{{ __('messages.select_master_data') }}</label>
            <select wire:model="entitasImport" class="form-select @error('entitasImport') is-invalid @enderror">
              @foreach ($opsiEntitas as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
              @endforeach
            </select>
            @error('entitasImport') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">{{ __('messages.select_csv_file') }}</label>
            <input type="file" wire:model="fileImport" accept=".csv,.txt" class="form-control @error('fileImport') is-invalid @enderror">
            <div class="form-text">{{ __('messages.import_csv_hint') }}</div>
            @error('fileImport') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <button type="button" class="btn btn-success" wire:click="importCsv" wire:loading.attr="disabled" wire:target="importCsv,fileImport">
            <span class="d-flex align-items-center justify-content-center gap-2">
              <span wire:loading.remove wire:target="importCsv,fileImport"><i class="bx bx-upload me-1"></i>{{ __('messages.import_csv') }}</span>
              <span wire:loading wire:target="importCsv,fileImport" style="display:none">
                <span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.processing') }}
              </span>
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>

  @if (! empty($hasilImport))
    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('messages.import_result') }}</h5>
      </div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <div class="border rounded p-3">
              <div class="text-muted small">{{ __('messages.total_rows') }}</div>
              <div class="fw-bold fs-5">{{ $hasilImport['jumlah_total'] ?? 0 }}</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="border rounded p-3">
              <div class="text-muted small">{{ __('messages.success_rows') }}</div>
              <div class="fw-bold fs-5 text-success">{{ $hasilImport['jumlah_berhasil'] ?? 0 }}</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="border rounded p-3">
              <div class="text-muted small">{{ __('messages.failed_rows') }}</div>
              <div class="fw-bold fs-5 text-danger">{{ $hasilImport['jumlah_gagal'] ?? 0 }}</div>
            </div>
          </div>
        </div>

        @if (! empty($hasilImport['error_baris']))
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>{{ __('messages.row_number') }}</th>
                  <th>{{ __('messages.error_message') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($hasilImport['error_baris'] as $error)
                  <tr>
                    <td>{{ $error['baris'] ?? '-' }}</td>
                    <td>{{ $error['pesan'] ?? '-' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  @endif
</div>
