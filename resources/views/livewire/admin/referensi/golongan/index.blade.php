<?php

use App\Models\Golongan;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editId = null;

    public ?string $kategori_id = null;
    public ?string $prodi_id = null;
    public ?string $pangkat = null;
    public ?string $nama = null;
    public bool $def = false;
    public ?string $tunjangan_fungsional = null;
    public ?string $tunjangan_sks = null;
    public ?string $tunjangan_transport = null;
    public ?string $tunjangan_tetap = null;
    public bool $is_aktif = true;

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/golongan', 'dapat_buat')) abort(403);

        $this->reset(['editId', 'kategori_id', 'prodi_id', 'pangkat', 'nama', 'def', 'tunjangan_fungsional', 'tunjangan_sks', 'tunjangan_transport', 'tunjangan_tetap', 'is_aktif']);
        $this->showModal = true;
    }

    public function edit($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/golongan', 'dapat_ubah')) abort(403);

        $item = Golongan::findOrFail($id);
        $this->editId = $item->golongan_id;
        $this->kategori_id = $item->kategori_id ?? '';
        $this->prodi_id = $item->prodi_id ?? '';
        $this->pangkat = $item->pangkat ?? '';
        $this->nama = $item->nama ?? '';
        $this->def = (bool)$item->def;
        $this->tunjangan_fungsional = $item->tunjangan_fungsional ?? '';
        $this->tunjangan_sks = $item->tunjangan_sks ?? '';
        $this->tunjangan_transport = $item->tunjangan_transport ?? '';
        $this->tunjangan_tetap = $item->tunjangan_tetap ?? '';
        $this->is_aktif = ($item->na === 'N');
        $this->showModal = true;
    }

    public function simpan(): void
    {
        if ($this->editId) {
            if (! auth()->user()?->bisaMenu('/admin/referensi/golongan', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('/admin/referensi/golongan', 'dapat_buat')) abort(403);
        }

        $rules = [
            'kategori_id' => 'nullable',
            'prodi_id' => 'nullable',
            'pangkat' => 'nullable',
            'nama' => 'nullable',
            'def' => 'nullable',
            'tunjangan_fungsional' => 'nullable',
            'tunjangan_sks' => 'nullable',
            'tunjangan_transport' => 'nullable',
            'tunjangan_tetap' => 'nullable',
            'is_aktif' => 'boolean'
        ];

        $data = $this->validate($rules);

        $payload = [
            'kategori_id' => ($data['kategori_id'] === '' ? null : $data['kategori_id']),
            'prodi_id' => ($data['prodi_id'] === '' ? null : $data['prodi_id']),
            'pangkat' => ($data['pangkat'] === '' ? null : $data['pangkat']),
            'nama' => ($data['nama'] === '' ? null : $data['nama']),
            'def' => $data['def'] ? 1 : 0,
            'tunjangan_fungsional' => ($data['tunjangan_fungsional'] === '' ? null : $data['tunjangan_fungsional']),
            'tunjangan_sks' => ($data['tunjangan_sks'] === '' ? null : $data['tunjangan_sks']),
            'tunjangan_transport' => ($data['tunjangan_transport'] === '' ? null : $data['tunjangan_transport']),
            'tunjangan_tetap' => ($data['tunjangan_tetap'] === '' ? null : $data['tunjangan_tetap'])
        ];

        if ($this->editId) {
            $item = Golongan::findOrFail($this->editId);
            $item->update($payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            $item = Golongan::create($payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editId', 'kategori_id', 'prodi_id', 'pangkat', 'nama', 'def', 'tunjangan_fungsional', 'tunjangan_sks', 'tunjangan_transport', 'tunjangan_tetap', 'is_aktif']);
        $this->resetPage();
    }

    public function hapus($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/golongan', 'dapat_hapus')) abort(403);

        $item = Golongan::findOrFail($id);
        $item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        $this->resetPage();
    }

    public function with(): array
    {
        $data = Golongan::query()
            ->when($this->search, function ($query) {
                // $query->where('golongan_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('golongan_id', 'desc')
            ->paginate(10);

        return [
            'rows' => $data
        ];
    }
};
?>

@section('title', 'Kelola Golongan')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola Golongan</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>Golongan</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
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
            <th>Kategori Id</th>
            <th>Prodi Id</th>
            <th>Pangkat</th>
            <th>Nama</th>
            <th>Def</th>

            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td>{{ $row->kategori_id }}</td>
              <td>{{ $row->prodi_id }}</td>
              <td>{{ $row->pangkat }}</td>
              <td>{{ $row->nama }}</td>
              <td>
                @if ($row->def)
                  <span class="badge bg-label-success">Ya</span>
                @else
                  <span class="badge bg-label-secondary">Tidak</span>
                @endif
              </td>

              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ $row->golongan_id }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && $wire.hapus('{{ $row->golongan_id }}'))">
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
            <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah' : 'Tambah' }} Golongan</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kategori Id</label>
                  <input wire:model="kategori_id" type="text" class="form-control @error('kategori_id') is-invalid @enderror">
                  @error('kategori_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Prodi Id</label>
                  <input wire:model="prodi_id" type="text" class="form-control @error('prodi_id') is-invalid @enderror">
                  @error('prodi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Pangkat</label>
                  <input wire:model="pangkat" type="text" class="form-control @error('pangkat') is-invalid @enderror">
                  @error('pangkat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nama</label>
                  <input wire:model="nama" type="text" class="form-control @error('nama') is-invalid @enderror">
                  @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Def</label>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold d-block">Def</label>
                  <div class="form-check form-switch mt-2">
                    <input wire:model="def" class="form-check-input" type="checkbox" id="toggle_def">
                    <label class="form-check-label" for="toggle_def">Def</label>
                  </div>
                  @error('def') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>                  @error('def') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Tunjangan Fungsional</label>
                  <input wire:model="tunjangan_fungsional" type="text" class="form-control @error('tunjangan_fungsional') is-invalid @enderror">
                  @error('tunjangan_fungsional') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Tunjangan Sks</label>
                  <input wire:model="tunjangan_sks" type="text" class="form-control @error('tunjangan_sks') is-invalid @enderror">
                  @error('tunjangan_sks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Tunjangan Transport</label>
                  <input wire:model="tunjangan_transport" type="text" class="form-control @error('tunjangan_transport') is-invalid @enderror">
                  @error('tunjangan_transport') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Tunjangan Tetap</label>
                  <input wire:model="tunjangan_tetap" type="text" class="form-control @error('tunjangan_tetap') is-invalid @enderror">
                  @error('tunjangan_tetap') <div class="invalid-feedback">{{ $message }}</div> @enderror
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