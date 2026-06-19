<?php

use App\Models\Statusmhsw;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editId = null;

    public ?string $jenis_keluar_id = null;
    public ?string $nama = null;
    public ?string $nilai = null;
    public bool $status_semester = false;
    public bool $keluar = false;
    public bool $status_kembali = false;
    public bool $def = false;
    public bool $lulus = false;
    public bool $is_aktif = true;

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/statusmhsw', 'dapat_buat')) abort(403);

        $this->reset(['editId', 'jenis_keluar_id', 'nama', 'nilai', 'status_semester', 'keluar', 'status_kembali', 'def', 'lulus', 'is_aktif']);
        $this->showModal = true;
    }

    public function edit($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/statusmhsw', 'dapat_ubah')) abort(403);

        $item = Statusmhsw::findOrFail($id);
        $this->editId = $item->status_mhsw_id;
        $this->jenis_keluar_id = $item->jenis_keluar_id ?? '';
        $this->nama = $item->nama ?? '';
        $this->nilai = $item->nilai ?? '';
        $this->status_semester = (bool)$item->status_semester;
        $this->keluar = (bool)$item->keluar;
        $this->status_kembali = (bool)$item->status_kembali;
        $this->def = (bool)$item->def;
        $this->lulus = (bool)$item->lulus;
        $this->is_aktif = ($item->na === 'N');
        $this->showModal = true;
    }

    public function simpan(): void
    {
        if ($this->editId) {
            if (! auth()->user()?->bisaMenu('/admin/referensi/statusmhsw', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('/admin/referensi/statusmhsw', 'dapat_buat')) abort(403);
        }

        $rules = [
            'jenis_keluar_id' => 'nullable',
            'nama' => 'nullable',
            'nilai' => 'nullable',
            'status_semester' => 'nullable',
            'keluar' => 'nullable',
            'status_kembali' => 'nullable',
            'def' => 'nullable',
            'lulus' => 'nullable',
            'is_aktif' => 'boolean'
        ];

        $data = $this->validate($rules);

        $payload = [
            'jenis_keluar_id' => ($data['jenis_keluar_id'] === '' ? null : $data['jenis_keluar_id']),
            'nama' => ($data['nama'] === '' ? null : $data['nama']),
            'nilai' => ($data['nilai'] === '' ? null : $data['nilai']),
            'status_semester' => $data['status_semester'] ? 1 : 0,
            'keluar' => $data['keluar'] ? 1 : 0,
            'status_kembali' => $data['status_kembali'] ? 1 : 0,
            'def' => $data['def'] ? 1 : 0,
            'lulus' => $data['lulus'] ? 1 : 0
        ];

        if ($this->editId) {
            $item = Statusmhsw::findOrFail($this->editId);
            $item->update($payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            $item = Statusmhsw::create($payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editId', 'jenis_keluar_id', 'nama', 'nilai', 'status_semester', 'keluar', 'status_kembali', 'def', 'lulus', 'is_aktif']);
        $this->resetPage();
    }

    public function hapus($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/statusmhsw', 'dapat_hapus')) abort(403);

        $item = Statusmhsw::findOrFail($id);
        $item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        $this->resetPage();
    }

    public function with(): array
    {
        $data = Statusmhsw::query()
            ->when($this->search, function ($query) {
                // $query->where('status_mhsw_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('status_mhsw_id', 'desc')
            ->paginate(10);

        return [
            'rows' => $data
        ];
    }
};
?>

@section('title', 'Kelola Statusmhsw')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola Statusmhsw</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>Statusmhsw</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
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
            <th>Jenis Keluar Id</th>
            <th>Nama</th>
            <th>Nilai</th>
            <th>Status Semester</th>
            <th>Keluar</th>

            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td>{{ $row->jenis_keluar_id }}</td>
              <td>{{ $row->nama }}</td>
              <td>{{ $row->nilai }}</td>
              <td>
                @if ($row->status_semester)
                  <span class="badge bg-label-success">Ya</span>
                @else
                  <span class="badge bg-label-secondary">Tidak</span>
                @endif
              </td>
              <td>
                @if ($row->keluar)
                  <span class="badge bg-label-success">Ya</span>
                @else
                  <span class="badge bg-label-secondary">Tidak</span>
                @endif
              </td>

              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ $row->status_mhsw_id }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && $wire.hapus('{{ $row->status_mhsw_id }}'))">
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
            <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah' : 'Tambah' }} Statusmhsw</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Jenis Keluar Id</label>
                  <input wire:model="jenis_keluar_id" type="text" class="form-control @error('jenis_keluar_id') is-invalid @enderror">
                  @error('jenis_keluar_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nama</label>
                  <input wire:model="nama" type="text" class="form-control @error('nama') is-invalid @enderror">
                  @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nilai</label>
                  <input wire:model="nilai" type="text" class="form-control @error('nilai') is-invalid @enderror">
                  @error('nilai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Status Semester</label>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold d-block">Status Semester</label>
                  <div class="form-check form-switch mt-2">
                    <input wire:model="status_semester" class="form-check-input" type="checkbox" id="toggle_status_semester">
                    <label class="form-check-label" for="toggle_status_semester">Status Semester</label>
                  </div>
                  @error('status_semester') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>                  @error('status_semester') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Keluar</label>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold d-block">Keluar</label>
                  <div class="form-check form-switch mt-2">
                    <input wire:model="keluar" class="form-check-input" type="checkbox" id="toggle_keluar">
                    <label class="form-check-label" for="toggle_keluar">Keluar</label>
                  </div>
                  @error('keluar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>                  @error('keluar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Status Kembali</label>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold d-block">Status Kembali</label>
                  <div class="form-check form-switch mt-2">
                    <input wire:model="status_kembali" class="form-check-input" type="checkbox" id="toggle_status_kembali">
                    <label class="form-check-label" for="toggle_status_kembali">Status Kembali</label>
                  </div>
                  @error('status_kembali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>                  @error('status_kembali') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                  <label class="form-label fw-semibold">Lulus</label>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold d-block">Lulus</label>
                  <div class="form-check form-switch mt-2">
                    <input wire:model="lulus" class="form-check-input" type="checkbox" id="toggle_lulus">
                    <label class="form-check-label" for="toggle_lulus">Lulus</label>
                  </div>
                  @error('lulus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>                  @error('lulus') <div class="invalid-feedback">{{ $message }}</div> @enderror
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