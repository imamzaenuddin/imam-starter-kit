<?php

use App\Models\Statuskerja;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editId = null;

    public ?string $status_kerja_id = null;
    public bool $nama = false;
    public bool $def = false;
    public bool $is_aktif = true;
    public bool $nilai_ujian_min = false;

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/statuskerja', 'dapat_buat')) abort(403);

        $this->reset(['editId', 'status_kerja_id', 'nama', 'def', 'is_aktif', 'nilai_ujian_min']);
        $this->showModal = true;
    }

    public function edit($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/statuskerja', 'dapat_ubah')) abort(403);

        $item = Statuskerja::findOrFail($id);
        $this->editId = $item->status_kerja_id;
        $this->status_kerja_id = $item->status_kerja_id;
        $this->nama = (bool)$item->nama;
        $this->def = (bool)$item->def;
        $this->is_aktif = ($item->na === 'N');
        $this->nilai_ujian_min = (bool)$item->nilai_ujian_min;
        $this->showModal = true;
    }

    public function simpan(): void
    {
        if ($this->editId) {
            if (! auth()->user()?->bisaMenu('/admin/referensi/statuskerja', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('/admin/referensi/statuskerja', 'dapat_buat')) abort(403);
        }

        $rules = [
            'status_kerja_id' => 'required|string|max:255',
            'nama' => 'boolean',
            'def' => 'boolean',
            'is_aktif' => 'boolean',
            'nilai_ujian_min' => 'boolean'
        ];

        $data = $this->validate($rules);

        $payload = [
            'status_kerja_id' => ($data['status_kerja_id'] === '' ? null : $data['status_kerja_id']),
            'nama' => $data['nama'] ? 1 : 0,
            'def' => empty($data['def']) ? 0 : 1, ? 1 : 0,
            'na' => empty($data['is_aktif']) ? 'Y' : 'N',
            'nilai_ujian_min' => $data['nilai_ujian_min'] ? 1 : 0
        ];

        if ($this->editId) {
            $item = Statuskerja::findOrFail($this->editId);
            $item->update($payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            $item = Statuskerja::create($payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editId', 'status_kerja_id', 'nama', 'def', 'is_aktif', 'nilai_ujian_min']);
        $this->resetPage();
    }

    public function hapus($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/statuskerja', 'dapat_hapus')) abort(403);

        $item = Statuskerja::findOrFail($id);
        $item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        $this->resetPage();
    }

    public function with(): array
    {
        $data = Statuskerja::query()
            ->when($this->search, function ($query) {
                // $query->where('status_kerja_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('status_kerja_id', 'desc')
            ->paginate(10);

        return [
            'rows' => $data
        ];
    }
};
?>

@section('title', 'Kelola Statuskerja')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola Statuskerja</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>Statuskerja</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
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
            <th>Status Kerja Id</th>
            <th>Nama</th>
            <th>Def</th>
            <th>Na</th>
            <th>Nilai Ujian Min</th>

            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td>{{ $row->status_kerja_id }}</td>
              <td>{{ $row->nama }}</td>
              <td>{{ $row->def }}</td>
              <td>
                @if ($row->na === 'N')
                  <span class="badge bg-label-success">Aktif</span>
                @else
                  <span class="badge bg-label-danger">Tidak Aktif</span>
                @endif
              </td>
              <td>{{ $row->nilai_ujian_min }}</td>

              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ $row->status_kerja_id }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && $wire.hapus('{{ $row->status_kerja_id }}'))">
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
            <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah' : 'Tambah' }} Statuskerja</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Status Kerja Id</label>
                  <input wire:model="status_kerja_id" type="text" class="form-control @error('status_kerja_id') is-invalid @enderror">
                  @error('status_kerja_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nama</label>
                  <div class="form-check mt-2">
                    <input wire:model="nama" type="checkbox" class="form-check-input" id="check_nama">
                    <label class="form-check-label" for="check_nama">Nama</label>
                  </div>
                  @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nilai Ujian Min</label>
                  <div class="form-check mt-2">
                    <input wire:model="nilai_ujian_min" type="checkbox" class="form-check-input" id="check_nilai_ujian_min">
                    <label class="form-check-label" for="check_nilai_ujian_min">Nilai Ujian Min</label>
                  </div>
                  @error('nilai_ujian_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
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