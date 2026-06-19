<?php

use App\Models\Posisipegawai;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editId = null;

    public ?string $posisi_pegawai_id = null;
    public ?string $no_id = null;
    public ?string $nama = null;
    public ?string $def = null;
    public ?string $honor_mengajar = null;
    public bool $is_aktif = true;

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/posisipegawai', 'dapat_buat')) abort(403);

        $this->reset(['editId', 'posisi_pegawai_id', 'no_id', 'nama', 'def', 'honor_mengajar', 'is_aktif']);
        $this->showModal = true;
    }

    public function edit($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/posisipegawai', 'dapat_ubah')) abort(403);

        $item = Posisipegawai::findOrFail($id);
        $this->editId = $item->posisi_pegawai_id;
        $this->posisi_pegawai_id = $item->posisi_pegawai_id;
        $this->no_id = $item->no_id;
        $this->nama = $item->nama;
        $this->def = (bool)$item->def;
        $this->honor_mengajar = $item->honor_mengajar;
        $this->is_aktif = ($item->na === 'N');
        $this->showModal = true;
    }

    public function simpan(): void
    {
        if ($this->editId) {
            if (! auth()->user()?->bisaMenu('/admin/preferensi/posisipegawai', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('/admin/preferensi/posisipegawai', 'dapat_buat')) abort(403);
        }

        $rules = [
            'posisi_pegawai_id' => 'required|string|max:255',
            'no_id' => 'nullable',
            'nama' => 'nullable',
            'def' => 'boolean',
            'honor_mengajar' => 'nullable',
            'is_aktif' => 'boolean'
        ];

        $data = $this->validate($rules);

        $payload = [
            'posisi_pegawai_id' => ($data['posisi_pegawai_id'] === '' ? null : $data['posisi_pegawai_id']),
            'no_id' => ($data['no_id'] === '' ? null : $data['no_id']),
            'nama' => ($data['nama'] === '' ? null : $data['nama']),
            'def' => empty($data['def']) ? 0 : 1,
            'honor_mengajar' => ($data['honor_mengajar'] === '' ? null : $data['honor_mengajar']),
            'na' => empty($data['is_aktif']) ? 'Y' : 'N',
        ];

        if ($this->editId) {
            $item = Posisipegawai::findOrFail($this->editId);
            $item->update($payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            $item = Posisipegawai::create($payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editId', 'posisi_pegawai_id', 'no_id', 'nama', 'def', 'honor_mengajar', 'is_aktif']);
        $this->resetPage();
    }

    public function hapus($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/posisipegawai', 'dapat_hapus')) abort(403);

        $item = Posisipegawai::findOrFail($id);
        $item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        $this->resetPage();
    }

    public function with(): array
    {
        $data = Posisipegawai::query()
            ->when($this->search, function ($query) {
                // $query->where('posisi_pegawai_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('posisi_pegawai_id', 'desc')
            ->paginate(10);

        return [
            'rows' => $data
        ];
    }
};
?>

@section('title', 'Kelola Posisipegawai')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola Posisipegawai</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>Posisipegawai</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
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
            <th>Posisi Pegawai Id</th>
            <th>No Id</th>
            <th>Nama</th>
            <th>Def</th>
            <th>Honor Mengajar</th>

            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td>{{ $row->posisi_pegawai_id }}</td>
              <td>{{ $row->no_id }}</td>
              <td>{{ $row->nama }}</td>
              <td>{{ $row->def }}</td>
              <td>{{ $row->honor_mengajar }}</td>

              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ $row->posisi_pegawai_id }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && $wire.hapus('{{ $row->posisi_pegawai_id }}'))">
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
            <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah' : 'Tambah' }} Posisipegawai</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Posisi Pegawai Id</label>
                  <input wire:model="posisi_pegawai_id" type="text" class="form-control @error('posisi_pegawai_id') is-invalid @enderror">
                  @error('posisi_pegawai_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">No Id</label>
                  <input wire:model="no_id" type="text" class="form-control @error('no_id') is-invalid @enderror">
                  @error('no_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nama</label>
                  <input wire:model="nama" type="text" class="form-control @error('nama') is-invalid @enderror">
                  @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Def</label>
                  <input wire:model="def" type="text" class="form-control @error('def') is-invalid @enderror">
                  @error('def') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Honor Mengajar</label>
                  <input wire:model="honor_mengajar" type="text" class="form-control @error('honor_mengajar') is-invalid @enderror">
                  @error('honor_mengajar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Na</label>
                  <select wire:model="na" class="form-select">
                    <option value="N">Aktif</option>
                    <option value="Y">Tidak Aktif (NA)</option>
                  </select>
                  @error('is_aktif') <div class="invalid-feedback">{{ $message }}</div> @enderror
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