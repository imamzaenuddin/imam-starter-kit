<?php

use App\Models\Berita;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

new #[Layout('components.layouts.app')] class extends Component {
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    // Form fields
    public ?int $editId = null;
    public bool $showModal = false;

    public string $judul = '';
    public string $ringkasan = '';
    public string $isi = '';
    public $fotoUpload = null;
    public ?string $fotoPath = null;
    public string $kategori = 'Berita';
    public string $penulis = '';
    public ?string $tanggalTerbit = null;
    public bool $isPublished = true;
    public bool $isFeatured = false;

    public function mount(): void
    {
        if (!auth()->user()?->bisaMenu('/manajemen-konten/berita', 'dapat_lihat')) {
            abort(403);
        }
    }

    public function buka(): void
    {
        if (!auth()->user()?->bisaMenu('/manajemen-konten/berita', 'dapat_buat')) {
            abort(403);
        }

        $this->resetForm();
        $this->tanggalTerbit = date('Y-m-d');
        $this->penulis = auth()->user()->name;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        if (!auth()->user()?->bisaMenu('/manajemen-konten/berita', 'dapat_ubah')) {
            abort(403);
        }

        $berita = Berita::findOrFail($id);

        $this->editId = $berita->id;
        $this->judul = $berita->judul;
        $this->ringkasan = $berita->ringkasan ?? '';
        $this->isi = $berita->isi;
        $this->fotoPath = $berita->foto;
        $this->fotoUpload = null;
        $this->kategori = $berita->kategori;
        $this->penulis = $berita->penulis ?? '';
        $this->tanggalTerbit = $berita->tanggal_terbit ? $berita->tanggal_terbit->format('Y-m-d') : null;
        $this->isPublished = $berita->is_published;
        $this->isFeatured = $berita->is_featured;

        $this->showModal = true;
    }

    public function simpan(): void
    {
        $izin = $this->editId ? 'dapat_ubah' : 'dapat_buat';
        if (!auth()->user()?->bisaMenu('/manajemen-konten/berita', $izin)) {
            abort(403);
        }

        $data = $this->validate([
            'judul'         => 'required|string|max:255',
            'ringkasan'     => 'nullable|string|max:500',
            'isi'           => 'required|string',
            'fotoUpload'    => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
            'kategori'      => 'required|in:Berita,Pengumuman,Kegiatan',
            'penulis'       => 'nullable|string|max:100',
            'tanggalTerbit' => 'nullable|date',
            'isPublished'   => 'boolean',
            'isFeatured'    => 'boolean',
        ]);

        $slug = Str::slug($data['judul']);
        
        // Cek keunikan slug
        $count = Berita::where('slug', $slug)->where('id', '!=', $this->editId ?? 0)->count();
        if ($count > 0) {
            $slug = $slug . '-' . time();
        }

        $payload = [
            'judul'          => $data['judul'],
            'slug'           => $slug,
            'ringkasan'      => $data['ringkasan'] ?: null,
            'isi'            => $data['isi'],
            'foto'           => $this->fotoPath,
            'kategori'       => $data['kategori'],
            'penulis'        => $data['penulis'] ?: auth()->user()->name,
            'tanggal_terbit' => $data['tanggalTerbit'] ?: date('Y-m-d'),
            'is_published'   => $data['isPublished'],
            'is_featured'    => $data['isFeatured'],
            'created_by'     => auth()->id(),
        ];

        if ($this->fotoUpload) {
            if ($this->fotoPath && Storage::disk('public')->exists($this->fotoPath)) {
                Storage::disk('public')->delete($this->fotoPath);
            }
            $payload['foto'] = $this->fotoUpload->store('berita', 'public');
        }

        if ($this->editId) {
            $berita = Berita::findOrFail($this->editId);
            $berita->update($payload);
            app(LogAktivitasService::class)->catatManual('Berita & Artikel', 'Mengubah berita: ' . $berita->judul, '/manajemen-konten/berita', ['berita_id' => $berita->id]);
            session()->flash('sukses', 'Berita berhasil diperbarui.');
        } else {
            $berita = Berita::create($payload);
            app(LogAktivitasService::class)->catatManual('Berita & Artikel', 'Menambahkan berita baru: ' . $berita->judul, '/manajemen-konten/berita', ['berita_id' => $berita->id]);
            session()->flash('sukses', 'Berita baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function togglePublish(int $id): void
    {
        if (!auth()->user()?->bisaMenu('/manajemen-konten/berita', 'dapat_ubah')) {
            abort(403);
        }

        $berita = Berita::findOrFail($id);
        $berita->update(['is_published' => !$berita->is_published]);

        app(LogAktivitasService::class)->catatManual('Berita & Artikel', 'Mengubah status publikasi berita: ' . $berita->judul, '/manajemen-konten/berita', ['berita_id' => $berita->id]);
    }

    public function toggleFeatured(int $id): void
    {
        if (!auth()->user()?->bisaMenu('/manajemen-konten/berita', 'dapat_ubah')) {
            abort(403);
        }

        $berita = Berita::findOrFail($id);
        $berita->update(['is_featured' => !$berita->is_featured]);

        app(LogAktivitasService::class)->catatManual('Berita & Artikel', 'Mengubah status featured berita: ' . $berita->judul, '/manajemen-konten/berita', ['berita_id' => $berita->id]);
    }

    public function hapus(int $id): void
    {
        if (!auth()->user()?->bisaMenu('/manajemen-konten/berita', 'dapat_hapus')) {
            abort(403);
        }

        $berita = Berita::findOrFail($id);
        if ($berita->foto && Storage::disk('public')->exists($berita->foto)) {
            Storage::disk('public')->delete($berita->foto);
        }

        app(LogAktivitasService::class)->catatManual('Berita & Artikel', 'Menghapus berita: ' . $berita->judul, '/manajemen-konten/berita', ['berita_id' => $berita->id]);
        $berita->delete();
        session()->flash('sukses', 'Berita berhasil dihapus.');
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editId', 'judul', 'ringkasan', 'isi', 'fotoUpload', 'fotoPath',
            'kategori', 'penulis', 'tanggalTerbit', 'isPublished', 'isFeatured'
        ]);
        $this->kategori = 'Berita';
        $this->isPublished = true;
        $this->isFeatured = false;
    }

    public function with(): array
    {
        return [
            'beritas' => Berita::query()
                ->when($this->search, function ($q) {
                    $q->where('judul', 'like', '%' . $this->search . '%')
                      ->orWhere('penulis', 'like', '%' . $this->search . '%')
                      ->orWhere('kategori', 'like', '%' . $this->search . '%');
                })
                ->orderByDesc('tanggal_terbit')
                ->orderByDesc('id')
                ->paginate((int) config('app_runtime.pagination_default', 10)),
        ];
    }
};
?>
@section('title', 'Kelola Berita & Artikel')

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1e293b">Berita & Artikel</h4>
            <p class="text-muted mb-0" style="font-size:.875rem">Kelola artikel publikasi, kegiatan, dan pengumuman untuk halaman utama.</p>
        </div>
        @if (auth()->user()?->bisaMenu('/manajemen-konten/berita', 'dapat_buat'))
            <button class="btn btn-primary" wire:click="buka">
                <i class="bx bx-plus me-1"></i> Tambah Berita
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
            <input wire:model.live.debounce.300ms="search" type="search" class="form-control" placeholder="Cari berdasarkan judul, kategori, atau penulis...">
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Foto</th>
                        <th>Judul Berita</th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Tanggal Terbit</th>
                        <th class="text-center">Publish</th>
                        <th class="text-center">Featured</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($beritas as $item)
                        <tr>
                            <td>{{ $beritas->firstItem() + $loop->index }}</td>
                            <td>
                                @if ($item->foto)
                                    <img src="{{ asset('storage/' . $item->foto) }}" alt="Foto berita" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="bx bx-image-alt text-muted" style="font-size: 1.25rem;"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-wrap" style="max-width: 250px;">{{ $item->judul }}</div>
                                <small class="text-muted d-block mt-1"><i class="bx bx-show me-1"></i>{{ $item->views }} views</small>
                            </td>
                            <td>
                                @if($item->kategori === 'Berita')
                                    <span class="badge bg-label-primary">Berita</span>
                                @elseif($item->kategori === 'Pengumuman')
                                    <span class="badge bg-label-warning">Pengumuman</span>
                                @else
                                    <span class="badge bg-label-success">Kegiatan</span>
                                @endif
                            </td>
                            <td>{{ $item->penulis }}</td>
                            <td>{{ $item->tanggal_terbit ? $item->tanggal_terbit->format('d M Y') : '-' }}</td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox" role="switch" 
                                           {{ !auth()->user()?->bisaMenu('/manajemen-konten/berita', 'dapat_ubah') ? 'disabled' : '' }}
                                           wire:click="togglePublish({{ $item->id }})" {{ $item->is_published ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-icon {{ $item->is_featured ? 'btn-warning' : 'btn-outline-secondary' }}" 
                                        {{ !auth()->user()?->bisaMenu('/manajemen-konten/berita', 'dapat_ubah') ? 'disabled' : '' }}
                                        wire:click="toggleFeatured({{ $item->id }})" title="Tampilkan di Halaman Utama">
                                    <i class="bx bxs-star"></i>
                                </button>
                            </td>
                            <td class="text-center">
                                @if (auth()->user()?->bisaMenu('/manajemen-konten/berita', 'dapat_ubah'))
                                    <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit({{ $item->id }})" title="Edit">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                @endif
                                @if (auth()->user()?->bisaMenu('/manajemen-konten/berita', 'dapat_hapus'))
                                    <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                                            @click="Swal.fire({
                                                title: 'Apakah Anda yakin?',
                                                text: 'Berita ini akan dihapus secara permanen!',
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
                            <td colspan="9" class="text-center py-4 text-muted">Belum ada data berita.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $beritas->links() }}</div>
    </div>

    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.45)">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah Berita' : 'Tambah Berita' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="simpan">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Judul Berita <span class="text-danger">*</span></label>
                                    <input wire:model="judul" type="text" class="form-control @error('judul') is-invalid @enderror" placeholder="Masukkan judul berita yang menarik...">
                                    @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                                    <select wire:model="kategori" class="form-select @error('kategori') is-invalid @enderror">
                                        <option value="Berita">Berita</option>
                                        <option value="Pengumuman">Pengumuman</option>
                                        <option value="Kegiatan">Kegiatan</option>
                                    </select>
                                    @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tanggal Terbit</label>
                                    <input wire:model="tanggalTerbit" type="date" class="form-control @error('tanggalTerbit') is-invalid @enderror">
                                    @error('tanggalTerbit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Penulis</label>
                                    <input wire:model="penulis" type="text" class="form-control @error('penulis') is-invalid @enderror" placeholder="Masukkan nama penulis...">
                                    @error('penulis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Foto Cover</label>
                                    <input wire:model="fotoUpload" type="file" class="form-control @error('fotoUpload') is-invalid @enderror" accept="image/*">
                                    @error('fotoUpload') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Preview Foto</label>
                                    <div class="border rounded p-2 d-flex align-items-center gap-2" style="min-height:80px">
                                        @if ($fotoUpload)
                                            <img src="{{ $fotoUpload->temporaryUrl() }}" alt="Preview" class="rounded" style="height:60px;width:60px;object-fit:cover">
                                            <span class="text-muted small">File baru terpilih</span>
                                        @elseif ($fotoPath)
                                            <img src="{{ asset('storage/' . $fotoPath) }}" alt="Cover" class="rounded" style="height:60px;width:60px;object-fit:cover">
                                            <span class="text-muted small">Foto tersimpan saat ini</span>
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                <i class="bx bx-image-alt text-muted" style="font-size: 1.5rem;"></i>
                                            </div>
                                            <span class="text-muted small">Belum ada foto terpilih</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Ringkasan Singkat</label>
                                    <textarea wire:model="ringkasan" rows="2" class="form-control @error('ringkasan') is-invalid @enderror" placeholder="Tulis ringkasan singkat untuk tampilan kartu berita..."></textarea>
                                    @error('ringkasan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Isi Berita/Konten <span class="text-danger">*</span></label>
                                    <textarea wire:model="isi" rows="6" class="form-control @error('isi') is-invalid @enderror" placeholder="Masukkan konten lengkap artikel (format HTML diperbolehkan)..."></textarea>
                                    @error('isi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mt-3">
                                    <div class="form-check">
                                        <input wire:model="isPublished" type="checkbox" class="form-check-input" id="beritaPublish">
                                        <label class="form-check-label fw-semibold" for="beritaPublish">Publikasikan Langsung</label>
                                    </div>
                                </div>

                                <div class="col-md-6 mt-3">
                                    <div class="form-check">
                                        <input wire:model="isFeatured" type="checkbox" class="form-check-input" id="beritaFeatured">
                                        <label class="form-check-label fw-semibold" for="beritaFeatured">Jadikan Featured (Tampil di Halaman Utama)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModal', false)">Batal</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpan">
                                <span wire:loading.remove wire:target="simpan">Simpan Konten</span>
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
