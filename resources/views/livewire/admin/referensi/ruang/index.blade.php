<?php

use App\Models\Ruang;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editId = null;

    public ?string $nama = null;
    public ?string $kampus_id = null;
    public ?string $rfid_device_id = null;
    public ?string $lantai = null;
    public ?string $prodi_id = null;
    public ?string $ruang_kuliah = null;
    public ?string $kapasitas = null;
    public ?string $kapasitas_ujian = null;
    public ?string $kolom_ujian = null;
    public ?string $untuk_usm = null;
    public ?string $keterangan = null;
    public bool $is_aktif = true;

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/ruang', 'dapat_buat')) abort(403);

        $this->reset(['editId', 'nama', 'kampus_id', 'rfid_device_id', 'lantai', 'prodi_id', 'ruang_kuliah', 'kapasitas', 'kapasitas_ujian', 'kolom_ujian', 'untuk_usm', 'keterangan', 'is_aktif']);
        $this->showModal = true;
    }

    public function edit($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/ruang', 'dapat_ubah')) abort(403);

        $item = Ruang::findOrFail($id);
        $this->editId = $item->ruang_id;
        $this->nama = $item->nama ?? '';
        $this->kampus_id = $item->kampus_id ?? '';
        $this->rfid_device_id = $item->rfid_device_id ?? '';
        $this->lantai = $item->lantai ?? '';
        $this->prodi_id = $item->prodi_id ?? '';
        $this->ruang_kuliah = $item->ruang_kuliah ?? '';
        $this->kapasitas = $item->kapasitas ?? '';
        $this->kapasitas_ujian = $item->kapasitas_ujian ?? '';
        $this->kolom_ujian = $item->kolom_ujian ?? '';
        $this->untuk_usm = $item->untuk_usm ?? '';
        $this->keterangan = $item->keterangan ?? '';
        $this->is_aktif = ($item->na === 'N');
        $this->showModal = true;
    }

    public function simpan(): void
    {
        if ($this->editId) {
            if (! auth()->user()?->bisaMenu('/admin/referensi/ruang', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('/admin/referensi/ruang', 'dapat_buat')) abort(403);
        }

        $rules = [
            'nama' => 'nullable',
            'kampus_id' => 'nullable',
            'rfid_device_id' => 'nullable',
            'lantai' => 'nullable',
            'prodi_id' => 'nullable',
            'ruang_kuliah' => 'nullable',
            'kapasitas' => 'nullable',
            'kapasitas_ujian' => 'nullable',
            'kolom_ujian' => 'nullable',
            'untuk_usm' => 'nullable',
            'keterangan' => 'nullable',
            'is_aktif' => 'boolean'
        ];

        $data = $this->validate($rules);

        $payload = [
            'nama' => ($data['nama'] === '' ? null : $data['nama']),
            'kampus_id' => ($data['kampus_id'] === '' ? null : $data['kampus_id']),
            'rfid_device_id' => ($data['rfid_device_id'] === '' ? null : $data['rfid_device_id']),
            'lantai' => ($data['lantai'] === '' ? null : $data['lantai']),
            'prodi_id' => ($data['prodi_id'] === '' ? null : $data['prodi_id']),
            'ruang_kuliah' => ($data['ruang_kuliah'] === '' ? null : $data['ruang_kuliah']),
            'kapasitas' => ($data['kapasitas'] === '' ? null : $data['kapasitas']),
            'kapasitas_ujian' => ($data['kapasitas_ujian'] === '' ? null : $data['kapasitas_ujian']),
            'kolom_ujian' => ($data['kolom_ujian'] === '' ? null : $data['kolom_ujian']),
            'untuk_usm' => ($data['untuk_usm'] === '' ? null : $data['untuk_usm']),
            'keterangan' => ($data['keterangan'] === '' ? null : $data['keterangan'])
        ];

        if ($this->editId) {
            $item = Ruang::findOrFail($this->editId);
            $item->update($payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            $item = Ruang::create($payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editId', 'nama', 'kampus_id', 'rfid_device_id', 'lantai', 'prodi_id', 'ruang_kuliah', 'kapasitas', 'kapasitas_ujian', 'kolom_ujian', 'untuk_usm', 'keterangan', 'is_aktif']);
        $this->resetPage();
    }

    public function hapus($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/ruang', 'dapat_hapus')) abort(403);

        $item = Ruang::findOrFail($id);
        $item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        $this->resetPage();
    }

    public function with(): array
    {
        $data = Ruang::query()
            ->when($this->search, function ($query) {
                // $query->where('ruang_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('ruang_id', 'desc')
            ->paginate(10);

        return [
            'rows' => $data
        ];
    }
};
?>

@section('title', 'Kelola Ruang')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola Ruang</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>Ruang</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
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
            <th>Nama</th>
            <th>Kampus Id</th>
            <th>Rfid Device Id</th>
            <th>Lantai</th>
            <th>Prodi Id</th>

            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td>{{ $row->nama }}</td>
              <td>{{ $row->kampus_id }}</td>
              <td>{{ $row->rfid_device_id }}</td>
              <td>{{ $row->lantai }}</td>
              <td>{{ $row->prodi_id }}</td>

              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ $row->ruang_id }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && $wire.hapus('{{ $row->ruang_id }}'))">
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
            <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah' : 'Tambah' }} Ruang</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nama</label>
                  <input wire:model="nama" type="text" class="form-control @error('nama') is-invalid @enderror">
                  @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kampus Id</label>
                  <input wire:model="kampus_id" type="text" class="form-control @error('kampus_id') is-invalid @enderror">
                  @error('kampus_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Rfid Device Id</label>
                  <input wire:model="rfid_device_id" type="text" class="form-control @error('rfid_device_id') is-invalid @enderror">
                  @error('rfid_device_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Lantai</label>
                  <input wire:model="lantai" type="text" class="form-control @error('lantai') is-invalid @enderror">
                  @error('lantai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Prodi Id</label>
                  <input wire:model="prodi_id" type="text" class="form-control @error('prodi_id') is-invalid @enderror">
                  @error('prodi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Ruang Kuliah</label>
                  <input wire:model="ruang_kuliah" type="text" class="form-control @error('ruang_kuliah') is-invalid @enderror">
                  @error('ruang_kuliah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kapasitas</label>
                  <input wire:model="kapasitas" type="text" class="form-control @error('kapasitas') is-invalid @enderror">
                  @error('kapasitas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kapasitas Ujian</label>
                  <input wire:model="kapasitas_ujian" type="text" class="form-control @error('kapasitas_ujian') is-invalid @enderror">
                  @error('kapasitas_ujian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kolom Ujian</label>
                  <input wire:model="kolom_ujian" type="text" class="form-control @error('kolom_ujian') is-invalid @enderror">
                  @error('kolom_ujian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Untuk Usm</label>
                  <input wire:model="untuk_usm" type="text" class="form-control @error('untuk_usm') is-invalid @enderror">
                  @error('untuk_usm') <div class="invalid-feedback">{{ $message }}</div> @enderror
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