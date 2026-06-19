<?php

use App\Models\Statusaplikan;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editId = null;

    public ?string $status_aplikan_id = null;
    public ?string $urutan = null;
    public ?string $nama = null;
    public ?string $status_aplikan_before = null;
    public ?string $status_aplikan_after = null;
    public ?string $keterangan = null;

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/statusaplikan', 'dapat_buat')) abort(403);

        $this->reset(['editId', 'status_aplikan_id', 'urutan', 'nama', 'status_aplikan_before', 'status_aplikan_after', 'keterangan']);
        $this->showModal = true;
    }

    public function edit($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/statusaplikan', 'dapat_ubah')) abort(403);

        $item = Statusaplikan::findOrFail($id);
        $this->editId = $item->status_aplikan_id;
        $this->status_aplikan_id = $item->status_aplikan_id;
        $this->urutan = $item->urutan;
        $this->nama = $item->nama;
        $this->status_aplikan_before = $item->status_aplikan_before;
        $this->status_aplikan_after = $item->status_aplikan_after;
        $this->keterangan = $item->keterangan;
        $this->showModal = true;
    }

    public function simpan(): void
    {
        if ($this->editId) {
            if (! auth()->user()?->bisaMenu('/admin/preferensi/statusaplikan', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('/admin/preferensi/statusaplikan', 'dapat_buat')) abort(403);
        }

        $rules = [
            'status_aplikan_id' => 'required|string|max:255',
            'urutan' => 'nullable',
            'nama' => 'nullable',
            'status_aplikan_before' => 'nullable',
            'status_aplikan_after' => 'nullable',
            'keterangan' => 'nullable'
        ];

        $data = $this->validate($rules);

        $payload = [
            'status_aplikan_id' => ($data['status_aplikan_id'] === '' ? null : $data['status_aplikan_id']),
            'urutan' => ($data['urutan'] === '' ? null : $data['urutan']),
            'nama' => ($data['nama'] === '' ? null : $data['nama']),
            'status_aplikan_before' => ($data['status_aplikan_before'] === '' ? null : $data['status_aplikan_before']),
            'status_aplikan_after' => ($data['status_aplikan_after'] === '' ? null : $data['status_aplikan_after']),
            'keterangan' => ($data['keterangan'] === '' ? null : $data['keterangan'])
        ];

        if ($this->editId) {
            $item = Statusaplikan::findOrFail($this->editId);
            $item->update($payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            $item = Statusaplikan::create($payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editId', 'status_aplikan_id', 'urutan', 'nama', 'status_aplikan_before', 'status_aplikan_after', 'keterangan']);
        $this->resetPage();
    }

    public function hapus($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/statusaplikan', 'dapat_hapus')) abort(403);

        $item = Statusaplikan::findOrFail($id);
        $item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        $this->resetPage();
    }

    public function with(): array
    {
        $data = Statusaplikan::query()
            ->when($this->search, function ($query) {
                // $query->where('status_aplikan_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('status_aplikan_id', 'desc')
            ->paginate(10);

        return [
            'rows' => $data
        ];
    }
};
?>

@section('title', 'Kelola Statusaplikan')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola Statusaplikan</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>Statusaplikan</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
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
            <th>Status Aplikan Id</th>
            <th>Urutan</th>
            <th>Nama</th>
            <th>Status Aplikan Before</th>
            <th>Status Aplikan After</th>

            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td>{{ $row->status_aplikan_id }}</td>
              <td>{{ $row->urutan }}</td>
              <td>{{ $row->nama }}</td>
              <td>{{ $row->status_aplikan_before }}</td>
              <td>{{ $row->status_aplikan_after }}</td>

              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ $row->status_aplikan_id }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && $wire.hapus('{{ $row->status_aplikan_id }}'))">
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
    @if ($rows->hasPages())
      <div class="card-footer border-top">{{ $rows->links() }}</div>
    @endif
  </div>

  @if ($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah' : 'Tambah' }} Statusaplikan</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Status Aplikan Id</label>
                  <input wire:model="status_aplikan_id" type="text" class="form-control @error('status_aplikan_id') is-invalid @enderror">
                  @error('status_aplikan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Urutan</label>
                  <input wire:model="urutan" type="text" class="form-control @error('urutan') is-invalid @enderror">
                  @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nama</label>
                  <input wire:model="nama" type="text" class="form-control @error('nama') is-invalid @enderror">
                  @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Status Aplikan Before</label>
                  <input wire:model="status_aplikan_before" type="text" class="form-control @error('status_aplikan_before') is-invalid @enderror">
                  @error('status_aplikan_before') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Status Aplikan After</label>
                  <input wire:model="status_aplikan_after" type="text" class="form-control @error('status_aplikan_after') is-invalid @enderror">
                  @error('status_aplikan_after') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Keterangan</label>
                  <input wire:model="keterangan" type="text" class="form-control @error('keterangan') is-invalid @enderror">
                  @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

              </div>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModal', false)">Batal</button>
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