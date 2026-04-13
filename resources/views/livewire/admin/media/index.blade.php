<?php

use App\Models\Media;
use App\Services\MediaService;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithFileUploads, WithPagination;

    public ?string $kategori = null;
    public ?string $search = '';
    public ?TemporaryUploadedFile $file = null;
    public string $deskripsi = '';
    public bool $showUploadForm = false;

    public function with(): array
    {
        $query = Media::untukUser(auth()->id())->terbaru();

        if ($this->kategori && in_array($this->kategori, MediaService::kategoriTersedia())) {
            $query->kategori($this->kategori);
        }

        if ($this->search) {
            $query->where('nama_asli', 'like', '%' . $this->search . '%');
        }

        return [
            'media' => $query->paginate(15),
            'kategoriTersedia' => MediaService::kategoriTersedia(),
            'totalUkuran' => app(MediaService::class)->totalUkuranUser(auth()->id()),
            'totalUkuranFormat' => $this->formatUkuran((int) app(MediaService::class)->totalUkuranUser(auth()->id())),
        ];
    }

    private function formatUkuran(int $bytes): string
    {
        $bytes = max(0, $bytes);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = (float) $bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function uploadFile(): void
    {
        // Validasi input
        $this->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'kategori' => 'required|in:' . implode(',', MediaService::kategoriTersedia()),
        ]);

        try {
            $service = app(MediaService::class);

            // Cek quota
            if (!$service->tersediaQuota(auth()->id())) {
                $this->addError('file', __('messages.media_quota_exceeded'));
                return;
            }

            // Upload
            $media = $service->upload(
                $this->file,
                auth()->id(),
                $this->kategori,
                $this->deskripsi ?: null
            );

            // Log aktivitas
            app(LogAktivitasService::class)->catatManual(
                __('messages.media_module_name'),
                __('messages.media_log_upload', ['nama' => $media->nama_asli]),
                '/admin/media',
                ['media_id' => $media->id]
            );

            // Reset form
            $this->reset('file', 'deskripsi', 'showUploadForm');
            $this->dispatch('notifikasi:baru', [
                'tipe' => 'success',
                'pesan' => __('messages.media_upload_success'),
            ]);
        } catch (\Exception $e) {
            $this->addError('file', $e->getMessage());
        }
    }

    public function download(Media $media): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        try {
            return app(MediaService::class)->download($media, auth()->id());
        } catch (\Exception $e) {
            $this->dispatch('notifikasi:baru', [
                'tipe' => 'error',
                'pesan' => $e->getMessage(),
            ]);
            return response()->noContent();
        }
    }

    public function hapus(Media $media): void
    {
        try {
            $namaFile = $media->nama_asli;

            app(MediaService::class)->delete($media, auth()->id());

            // Log aktivitas
            app(LogAktivitasService::class)->catatManual(
                __('messages.media_module_name'),
                __('messages.media_log_delete', ['nama' => $namaFile]),
                '/admin/media',
                ['media_id' => $media->id]
            );

            $this->dispatch('notifikasi:baru', [
                'tipe' => 'success',
                'pesan' => __('messages.media_delete_success'),
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notifikasi:baru', [
                'tipe' => 'error',
                'pesan' => $e->getMessage(),
            ]);
        }
    }

    public function filterKategori(string $kategori): void
    {
        $this->kategori = $this->kategori === $kategori ? null : $kategori;
        $this->resetPage();
    }
};
?>
@section('title', __('messages.admin_media_title'))
<div>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">{{ __('messages.admin_media_heading') }}</h5>
            <button
                type="button"
                class="btn btn-sm btn-primary"
                wire:click="$toggle('showUploadForm')"
            >
                <i class="bx bx-cloud-upload me-1"></i>
                {{ __('messages.media_upload') }}
            </button>
        </div>

        @if ($showUploadForm)
            <div class="card-body border-bottom">
                <form wire:submit="uploadFile" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.media_select_file') }}</label>
                        <input
                            type="file"
                            class="form-control @error('file') is-invalid @enderror"
                            wire:model="file"
                            accept="*/*"
                        />
                        @error('file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.media_select_category') }}</label>
                        <select
                            class="form-select @error('kategori') is-invalid @enderror"
                            wire:model="kategori"
                        >
                            <option value="">{{ __('messages.media_select_category') }}</option>
                            @foreach ($kategoriTersedia as $kat)
                                <option value="{{ $kat }}">
                                    {{ __('messages.media_category_' . $kat) }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ __('messages.media_description') }}</label>
                        <textarea
                            class="form-control"
                            wire:model="deskripsi"
                            rows="2"
                            placeholder="{{ __('messages.media_description_placeholder') }}"
                        ></textarea>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button
                            type="submit"
                            class="btn btn-success"
                            wire:loading.attr="disabled"
                            wire:target="uploadFile"
                        >
                            <span wire:loading.remove wire:target="uploadFile">
                                {{ __('messages.save') }}
                            </span>
                            <span wire:loading wire:target="uploadFile">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                {{ __('messages.uploading') }}
                            </span>
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            wire:click="$toggle('showUploadForm')"
                        >
                            {{ __('messages.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input
                        type="text"
                        class="form-control form-control-sm"
                        placeholder="{{ __('messages.search') }}..."
                        wire:model.live="search"
                    />
                </div>
                <div class="col-md-6 d-flex gap-2 justify-content-end">
                    @foreach ($kategoriTersedia as $kat)
                        <button
                            type="button"
                            class="btn btn-sm {{ $kategori === $kat ? 'btn-primary' : 'btn-outline-primary' }}"
                            wire:click="filterKategori('{{ $kat }}')"
                        >
                            {{ __('messages.media_category_' . $kat) }}
                        </button>
                    @endforeach
                </div>
            </div>

            @if ($media->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('messages.media_file_name') }}</th>
                                <th>{{ __('messages.media_file_category') }}</th>
                                <th>{{ __('messages.media_file_size') }}</th>
                                <th>{{ __('messages.media_file_date') }}</th>
                                <th class="text-center">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($media as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if ($item->isImage())
                                                <i class="bx bx-image me-2 text-primary"></i>
                                            @elseif ($item->isDocument())
                                                <i class="bx bx-file me-2 text-warning"></i>
                                            @else
                                                <i class="bx bx-file me-2 text-secondary"></i>
                                            @endif
                                            <div>
                                                <div class="fw-semibold">{{ \Str::limit($item->nama_asli, 30) }}</div>
                                                @if ($item->deskripsi)
                                                    <small class="text-muted">{{ \Str::limit($item->deskripsi, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">
                                            {{ __('messages.media_category_' . $item->kategori) }}
                                        </span>
                                    </td>
                                    <td>{{ $item->ukuran_format }}</td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $item->updated_at->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button
                                                class="btn btn-sm btn-icon btn-text-secondary dropdown-toggle hide-arrow"
                                                type="button"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false"
                                            >
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a
                                                    class="dropdown-item"
                                                    href="javascript:void(0);"
                                                    wire:click="download({{ $item->id }})"
                                                >
                                                    <i class="bx bx-download me-1"></i>
                                                    {{ __('messages.media_download') }}
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a
                                                    class="dropdown-item text-danger"
                                                    href="javascript:void(0);"
                                                    wire:click="hapus({{ $item->id }})"
                                                    wire:confirm="{{ __('messages.media_confirm_delete') }}"
                                                >
                                                    <i class="bx bx-trash me-1"></i>
                                                    {{ __('messages.delete') }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $media->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bx bx-image-alt text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">{{ __('messages.media_no_files') }}</p>
                </div>
            @endif
        </div>

        <div class="card-footer">
            <small class="text-muted">
                {{ __('messages.media_total_size') }}: <strong>{{ $totalUkuranFormat }}</strong>
            </small>
        </div>
    </div>
</div>
