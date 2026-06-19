<?php

use App\Models\Jenistransportasi;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editId = null;

    public ?string $jenis_transportasi_id = null;
    public ?string $nama = null;
    public bool $is_aktif = true;
    public ?string $kode_id = null;

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/jenistransportasi', 'dapat_buat')) abort(403);

        $this->reset(['editId', 'jenis_transportasi_id', 'nama', 'is_aktif', 'kode_id']);
        $this->showModal = true;
    }

    public function edit($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/jenistransportasi', 'dapat_ubah')) abort(403);

        $item = Jenistransportasi::findOrFail($id);
        $this->editId = $item->jenis_transportasi_id;
        $this->jenis_transportasi_id = $item->jenis_transportasi_id ?? '';
        $this->nama = $item->nama ?? '';
        $this->is_aktif = ($item->na === 'N');
        $this->kode_id = $item->kode_id ?? '';
        $this->showModal = true;
    }

    public function simpan(): void
    {
        if ($this->editId) {
            if (! auth()->user()?->bisaMenu('/admin/referensi/jenistransportasi', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('/admin/referensi/jenistransportasi', 'dapat_buat')) abort(403);
        }

        $rules = [
            'jenis_transportasi_id' => 'nullable',
            'nama' => 'nullable',
            'is_aktif' => 'boolean',
            'kode_id' => 'nullable'
        ];

        $data = $this->validate($rules);

        $payload = [
            'jenis_transportasi_id' => ($data['jenis_transportasi_id'] === '' ? null : $data['jenis_transportasi_id']),
            'nama' => ($data['nama'] === '' ? null : $data['nama']),
            'kode_id' => ($data['kode_id'] === '' ? null : $data['kode_id'])
        ];

        if ($this->editId) {
            $item = Jenistransportasi::findOrFail($this->editId);
            $item->update($payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            $item = Jenistransportasi::create($payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editId', 'jenis_transportasi_id', 'nama', 'is_aktif', 'kode_id']);
        $this->resetPage();
    }

    public function hapus($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/jenistransportasi', 'dapat_hapus')) abort(403);

        $item = Jenistransportasi::findOrFail($id);
        $item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        $this->resetPage();
    }

    public function with(): array
    {
        $data = Jenistransportasi::query()
            ->when($this->search, function ($query) {
                // $query->where('jenis_transportasi_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('jenis_transportasi_id', 'desc')
            ->paginate(10);

        return [
            'rows' => $data
        ];
    }
};
?>

@section('title', 'Kelola Jenistransportasi')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola Jenistransportasi</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>Jenistransportasi</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
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
            <th>Jenis Transportasi Id</th>
            <th>Nama</th>
            <th>Na</th>
            <th>Kode Id</th>

            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td>{{ $row->jenis_transportasi_id }}</td>
              <td>{{ $row->nama }}</td>
              <td>
                @if ($row->na === 'N')
                  <span class="badge bg-label-success">Aktif</span>
                @else
                  <span class="badge bg-label-danger">Tidak Aktif</span>
                @endif
              </td>
              <td>{{ $row->kode_id }}</td>

              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ $row->jenis_transportasi_id }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && $wire.hapus('{{ $row->jenis_transportasi_id }}'))">
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
            <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah' : 'Tambah' }} Jenistransportasi</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Jenis Transportasi Id</label>
                  <input wire:model="jenis_transportasi_id" type="text" class="form-control @error('jenis_transportasi_id') is-invalid @enderror">
                  @error('jenis_transportasi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nama</label>
                  <input wire:model="nama" type="text" class="form-control @error('nama') is-invalid @enderror">
                  @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kode Id</label>
                  <input wire:model="kode_id" type="text" class="form-control @error('kode_id') is-invalid @enderror">
                  @error('kode_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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