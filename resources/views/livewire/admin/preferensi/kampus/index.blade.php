<?php

use App\Models\Kampus;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editId = null;

    public ?string $no_id = null;
    public ?string $nama = null;
    public ?string $alamat = null;
    public ?string $kota = null;
    public ?string $kode_id = null;
    public ?string $telepon = null;
    public ?string $wa = null;
    public ?string $fax = null;
    public ?string $aktif = null;
    public bool $def = false;
    public bool $is_aktif = true;

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/kampus', 'dapat_buat')) abort(403);

        $this->reset(['editId', 'no_id', 'nama', 'alamat', 'kota', 'kode_id', 'telepon', 'wa', 'fax', 'aktif', 'def', 'is_aktif']);
        $this->showModal = true;
    }

    public function edit($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/kampus', 'dapat_ubah')) abort(403);

        $item = Kampus::findOrFail($id);
        $this->editId = $item->kampus_id;
        $this->no_id = $item->no_id;
        $this->nama = $item->nama;
        $this->alamat = $item->alamat;
        $this->kota = $item->kota;
        $this->kode_id = $item->kode_id;
        $this->telepon = $item->telepon;
        $this->wa = $item->wa;
        $this->fax = $item->fax;
        $this->aktif = $item->aktif;
        $this->def = (bool)$item->def;
        $this->is_aktif = ($item->na === 'N');
        $this->showModal = true;
    }

    public function simpan(): void
    {
        if ($this->editId) {
            if (! auth()->user()?->bisaMenu('/admin/preferensi/kampus', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('/admin/preferensi/kampus', 'dapat_buat')) abort(403);
        }

        $rules = [
            'no_id' => 'nullable',
            'nama' => 'nullable',
            'alamat' => 'nullable',
            'kota' => 'nullable',
            'kode_id' => 'nullable',
            'telepon' => 'nullable',
            'wa' => 'nullable',
            'fax' => 'nullable',
            'aktif' => 'nullable',
            'def' => 'boolean',
            'is_aktif' => 'boolean'
        ];

        $data = $this->validate($rules);

        $payload = [
            'no_id' => ($data['no_id'] === '' ? null : $data['no_id']),
            'nama' => ($data['nama'] === '' ? null : $data['nama']),
            'alamat' => ($data['alamat'] === '' ? null : $data['alamat']),
            'kota' => ($data['kota'] === '' ? null : $data['kota']),
            'kode_id' => ($data['kode_id'] === '' ? null : $data['kode_id']),
            'telepon' => ($data['telepon'] === '' ? null : $data['telepon']),
            'wa' => ($data['wa'] === '' ? null : $data['wa']),
            'fax' => ($data['fax'] === '' ? null : $data['fax']),
            'aktif' => ($data['aktif'] === '' ? null : $data['aktif']),
            'def' => empty($data['def']) ? 0 : 1, ? 1 : 0,
            'na' => empty($data['is_aktif']) ? 'Y' : 'N',
        ];

        if ($this->editId) {
            $item = Kampus::findOrFail($this->editId);
            $item->update($payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            $item = Kampus::create($payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editId', 'no_id', 'nama', 'alamat', 'kota', 'kode_id', 'telepon', 'wa', 'fax', 'aktif', 'def', 'is_aktif']);
        $this->resetPage();
    }

    public function hapus($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/kampus', 'dapat_hapus')) abort(403);

        $item = Kampus::findOrFail($id);
        $item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        $this->resetPage();
    }

    public function with(): array
    {
        $data = Kampus::query()
            ->when($this->search, function ($query) {
                // $query->where('kampus_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('kampus_id', 'desc')
            ->paginate(10);

        return [
            'rows' => $data
        ];
    }
};
?>

@section('title', 'Kelola Kampus')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola Kampus</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>Kampus</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
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
            <th>No Id</th>
            <th>Nama</th>
            <th>Alamat</th>
            <th>Kota</th>
            <th>Kode Id</th>

            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td>{{ $row->no_id }}</td>
              <td>{{ $row->nama }}</td>
              <td>{{ $row->alamat }}</td>
              <td>{{ $row->kota }}</td>
              <td>{{ $row->kode_id }}</td>

              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ $row->kampus_id }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && $wire.hapus('{{ $row->kampus_id }}'))">
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
            <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah' : 'Tambah' }} Kampus</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
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
                  <label class="form-label fw-semibold">Alamat</label>
                  <input wire:model="alamat" type="text" class="form-control @error('alamat') is-invalid @enderror">
                  @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kota</label>
                  <input wire:model="kota" type="text" class="form-control @error('kota') is-invalid @enderror">
                  @error('kota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kode Id</label>
                  <input wire:model="kode_id" type="text" class="form-control @error('kode_id') is-invalid @enderror">
                  @error('kode_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Telepon</label>
                  <input wire:model="telepon" type="text" class="form-control @error('telepon') is-invalid @enderror">
                  @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Wa</label>
                  <input wire:model="wa" type="text" class="form-control @error('wa') is-invalid @enderror">
                  @error('wa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Fax</label>
                  <input wire:model="fax" type="text" class="form-control @error('fax') is-invalid @enderror">
                  @error('fax') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Aktif</label>
                  <input wire:model="aktif" type="text" class="form-control @error('aktif') is-invalid @enderror">
                  @error('aktif') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Def</label>
                  <div class="form-check mt-2">
                    <input wire:model="def" type="checkbox" class="form-check-input" id="check_def">
                    <label class="form-check-label" for="check_def">Def</label>
                  </div>
                  @error('def') <div class="invalid-feedback">{{ $message }}</div> @enderror
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