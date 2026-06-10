<?php

use App\Models\KontenSlider;
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

    // Form fields
    public ?int $editId = null;
    public bool $showModal = false;

    public string $judul = '';
    public string $subjudul = '';
    public $fotoUpload = null;
    public ?string $fotoPath = null;
    public string $warnaLatar = '#2563eb';
    public string $labelTombol = '';
    public string $urlTombol = '';
    public bool $isActive = true;
    public int $urutan = 1;

    /** Preset warna latar overlay slider */
    public array $warnaPreset = [
        '#2563eb', '#1e40af', '#7c3aed', '#5b21b6', '#10b981', '#065f46',
        '#f59e0b', '#b45309', '#ef4444', '#991b1b', '#0f172a', '#1e293b',
    ];

    public function mount(): void
    {
        if (!auth()->user()?->bisaMenu('/manajemen-konten/slider', 'dapat_lihat')) {
            abort(403);
        }
    }

    public function buka(): void
    {
        if (!auth()->user()?->bisaMenu('/manajemen-konten/slider', 'dapat_buat')) {
            abort(403);
        }

        $this->resetForm();
        // Set default order
        $this->urutan = KontenSlider::max('urutan') + 1;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        if (!auth()->user()?->bisaMenu('/manajemen-konten/slider', 'dapat_ubah')) {
            abort(403);
        }

        $slider = KontenSlider::findOrFail($id);

        $this->editId = $slider->id;
        $this->judul = $slider->judul;
        $this->subjudul = $slider->subjudul ?? '';
        $this->fotoPath = $slider->foto;
        $this->fotoUpload = null;
        $this->warnaLatar = $slider->warna_latar ?? '#2563eb';
        $this->labelTombol = $slider->label_tombol ?? '';
        $this->urlTombol = $slider->url_tombol ?? '';
        $this->isActive = $slider->is_active;
        $this->urutan = $slider->urutan;

        $this->showModal = true;
    }

    public function simpan(): void
    {
        $izin = $this->editId ? 'dapat_ubah' : 'dapat_buat';
        if (!auth()->user()?->bisaMenu('/manajemen-konten/slider', $izin)) {
            abort(403);
        }

        $rules = [
            'judul'       => 'required|string|max:255',
            'subjudul'    => 'nullable|string|max:255',
            'fotoUpload'  => $this->editId ? 'nullable|image|max:3072|mimes:jpg,jpeg,png,webp' : 'required|image|max:3072|mimes:jpg,jpeg,png,webp',
            'warnaLatar'  => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'labelTombol' => 'nullable|string|max:50',
            'urlTombol'   => 'nullable|string|max:255',
            'isActive'    => 'boolean',
            'urutan'      => 'required|integer|min:1|max:100',
        ];

        $data = $this->validate($rules);

        $payload = [
            'judul'        => $data['judul'],
            'subjudul'     => $data['subjudul'] ?: null,
            'foto'         => $this->fotoPath,
            'warna_latar'  => $data['warnaLatar'] ?: '#2563eb',
            'label_tombol' => $data['labelTombol'] ?: null,
            'url_tombol'   => $data['urlTombol'] ?: null,
            'is_active'    => $data['isActive'],
            'urutan'       => $data['urutan'],
            'created_by'   => auth()->id(),
        ];

        if ($this->fotoUpload) {
            // Hapus gambar lama jika ada
            if ($this->fotoPath && Storage::disk('public')->exists($this->fotoPath)) {
                Storage::disk('public')->delete($this->fotoPath);
            }
            $payload['foto'] = $this->fotoUpload->store('slider', 'public');
        }

        if ($this->editId) {
            $slider = KontenSlider::findOrFail($this->editId);
            $slider->update($payload);
            app(LogAktivitasService::class)->catatManual('Slider Utama', 'Mengubah slide: ' . $slider->judul, '/manajemen-konten/slider', ['slider_id' => $slider->id]);
            session()->flash('sukses', 'Slider berhasil diperbarui.');
        } else {
            $slider = KontenSlider::create($payload);
            app(LogAktivitasService::class)->catatManual('Slider Utama', 'Menambahkan slide baru: ' . $slider->judul, '/manajemen-konten/slider', ['slider_id' => $slider->id]);
            session()->flash('sukses', 'Slider baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        if (!auth()->user()?->bisaMenu('/manajemen-konten/slider', 'dapat_ubah')) {
            abort(403);
        }

        $slider = KontenSlider::findOrFail($id);
        $slider->update(['is_active' => !$slider->is_active]);

        app(LogAktivitasService::class)->catatManual('Slider Utama', 'Mengubah status aktif slide: ' . $slider->judul, '/manajemen-konten/slider', ['slider_id' => $slider->id]);
    }

    public function hapus(int $id): void
    {
        if (!auth()->user()?->bisaMenu('/manajemen-konten/slider', 'dapat_hapus')) {
            abort(403);
        }

        $slider = KontenSlider::findOrFail($id);
        if ($slider->foto && Storage::disk('public')->exists($slider->foto)) {
            Storage::disk('public')->delete($slider->foto);
        }

        app(LogAktivitasService::class)->catatManual('Slider Utama', 'Menghapus slide: ' . $slider->judul, '/manajemen-konten/slider', ['slider_id' => $slider->id]);
        $slider->delete();
        session()->flash('sukses', 'Slider berhasil dihapus.');
        $this->resetPage();
    }

    public function pilihWarnaLatar(string $warna): void
    {
        $this->warnaLatar = $warna;
    }

    private function resetForm(): void
    {
        $this->reset([
            'editId', 'judul', 'subjudul', 'fotoUpload', 'fotoPath',
            'warnaLatar', 'labelTombol', 'urlTombol', 'isActive', 'urutan'
        ]);
        $this->warnaLatar = '#2563eb';
        $this->isActive = true;
        $this->urutan = 1;
    }

    public function with(): array
    {
        return [
            'sliders' => KontenSlider::query()
                ->when($this->search, function ($q) {
                    $q->where('judul', 'like', '%' . $this->search . '%')
                      ->orWhere('subjudul', 'like', '%' . $this->search . '%');
                })
                ->orderBy('urutan')
                ->orderByDesc('id')
                ->paginate((int) config('app_runtime.pagination_default', 10)),
        ];
    }
};
?>
@section('title', 'Kelola Slider Hero Halaman Utama')

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1e293b">Slider Halaman Utama</h4>
            <p class="text-muted mb-0" style="font-size:.875rem">Kelola gambar slide promosi, informasi utama, dan slogan interaktif untuk karosel landing page.</p>
        </div>
        @if (auth()->user()?->bisaMenu('/manajemen-konten/slider', 'dapat_buat'))
            <button class="btn btn-primary" wire:click="buka">
                <i class="bx bx-plus me-1"></i> Tambah Slider
            </button>
        @endif
    </div>

    @if (session('sukses'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body py-3">
            <input wire:model.live.debounce.300ms="search" type="search" class="form-control" placeholder="Cari berdasarkan judul atau subjudul...">
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Urutan</th>
                        <th>Foto Slide</th>
                        <th>Info Slider</th>
                        <th>Tombol Aksi</th>
                        <th class="text-center">Status Aktif</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sliders as $item)
                        <tr>
                            <td><span class="badge bg-label-primary"># {{ $item->urutan }}</span></td>
                            <td>
                                <div class="position-relative" style="width: 120px; height: 60px;">
                                    <img src="{{ asset('storage/' . $item->foto) }}" alt="Slider image" class="rounded w-100 h-100" style="object-fit: cover;">
                                    <div class="position-absolute bottom-0 end-0 p-1 rounded-circle border" 
                                         style="background: {{ $item->warna_latar }}; width: 14px; height: 14px;" title="Warna latar overlay: {{ $item->warna_latar }}"></div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-wrap" style="max-width: 300px;">{{ $item->judul }}</div>
                                @if ($item->subjudul)
                                    <small class="text-muted d-block text-wrap" style="max-width: 300px;">{{ $item->subjudul }}</small>
                                @endif
                            </td>
                            <td>
                                @if ($item->label_tombol && $item->url_tombol)
                                    <a href="{{ $item->url_tombol }}" target="_blank" class="btn btn-xs btn-outline-primary">{{ $item->label_tombol }}</a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox" role="switch" 
                                           {{ !auth()->user()?->bisaMenu('/manajemen-konten/slider', 'dapat_ubah') ? 'disabled' : '' }}
                                           wire:click="toggleActive({{ $item->id }})" {{ $item->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="text-center">
                                @if (auth()->user()?->bisaMenu('/manajemen-konten/slider', 'dapat_ubah'))
                                    <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit({{ $item->id }})" title="Edit">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                @endif
                                @if (auth()->user()?->bisaMenu('/manajemen-konten/slider', 'dapat_hapus'))
                                    <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                                            @click="Swal.fire({
                                                title: 'Apakah Anda yakin?',
                                                text: 'Slide ini akan dihapus secara permanen!',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonText: 'Ya, Hapus!',
                                                cancelButtonText: 'Batal'
                                            }).then(r => r.isConfirmed && $wire.hapus({{ $item->id }}))">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data slider.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $sliders->links() }}</div>
    </div>

    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.45)">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah Slider' : 'Tambah Slider' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="simpan">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Judul Utama Slide <span class="text-danger">*</span></label>
                                    <input wire:model="judul" type="text" class="form-control @error('judul') is-invalid @enderror" placeholder="Masukkan slogan/judul promosi slide...">
                                    @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Subjudul / Deskripsi Pendek</label>
                                    <input wire:model="subjudul" type="text" class="form-control @error('subjudul') is-invalid @enderror" placeholder="Masukkan deskripsi pelengkap slide...">
                                    @error('subjudul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Urutan Tampilan <span class="text-danger">*</span></label>
                                    <input wire:model="urutan" type="number" min="1" max="100" class="form-control @error('urutan') is-invalid @enderror">
                                    @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Upload Gambar Slide <span class="text-danger">{{ $editId ? '' : '*' }}</span></label>
                                    <input wire:model="fotoUpload" type="file" class="form-control @error('fotoUpload') is-invalid @enderror" accept="image/*">
                                    @error('fotoUpload') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Preview Gambar</label>
                                    <div class="border rounded p-2 d-flex align-items-center gap-2" style="min-height:90px">
                                        @if ($fotoUpload)
                                            <img src="{{ $fotoUpload->temporaryUrl() }}" alt="Preview" class="rounded" style="height:70px;width:140px;object-fit:cover">
                                            <span class="text-muted small">File baru terpilih</span>
                                        @elseif ($fotoPath)
                                            <img src="{{ asset('storage/' . $fotoPath) }}" alt="Slide" class="rounded" style="height:70px;width:140px;object-fit:cover">
                                            <span class="text-muted small">Gambar tersimpan saat ini</span>
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 140px; height: 70px;">
                                                <i class="bx bx-image-alt text-muted" style="font-size: 1.8rem;"></i>
                                            </div>
                                            <span class="text-muted small">Belum ada gambar terpilih</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Warna Latar Overlay (Hex Color) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text p-1">
                                            <input wire:model.live="warnaLatar" type="color" class="form-control form-control-color border-0 p-0" title="Pilih warna latar overlay" style="width:28px;height:28px;min-width:28px">
                                        </span>
                                        <input wire:model.live="warnaLatar" type="text" class="form-control @error('warnaLatar') is-invalid @enderror" placeholder="#2563eb">
                                        @error('warnaLatar') <div class="invalid-feedback">Format warna hex harus valid (contoh: #2563eb)</div> @enderror
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        @foreach ($warnaPreset as $warna)
                                            <button type="button" class="btn btn-sm p-0 border rounded-circle"
                                                    wire:click="pilihWarnaLatar('{{ $warna }}')"
                                                    title="Gunakan warna preset: {{ $warna }}"
                                                    style="width:20px;height:20px;background:{{ $warna }}"></button>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Status Aktif</label>
                                    <div class="form-check mt-2">
                                        <input wire:model="isActive" type="checkbox" class="form-check-input" id="sliderAktif">
                                        <label class="form-check-label fw-semibold" for="sliderAktif">Tampilkan slider ini ke publik</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Label Tombol Aksi</label>
                                    <input wire:model="labelTombol" type="text" class="form-control @error('labelTombol') is-invalid @enderror" placeholder="Contoh: Mulai Sekarang, Pelajari...">
                                    @error('labelTombol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">URL Tombol Aksi</label>
                                    <input wire:model="urlTombol" type="text" class="form-control @error('urlTombol') is-invalid @enderror" placeholder="Contoh: /login, #berita, https://...">
                                    @error('urlTombol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModal', false)">Batal</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpan">
                                <span wire:loading.remove wire:target="simpan">Simpan Slider</span>
                                <span wire:loading wire:target="simpan">
                                    <span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
