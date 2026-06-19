<?php

use App\Models\Pmbgrade;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editId = null;

    public ?string $grade_nilai = null;
    public ?string $kode_id = null;
    public ?string $nilai_ujian_min = null;
    public ?string $nilai_ujian_max = null;
    public ?string $keterangan = null;
    public bool $is_aktif = true;

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/pmbgrade', 'dapat_buat')) abort(403);

        $this->reset(['editId', 'grade_nilai', 'kode_id', 'nilai_ujian_min', 'nilai_ujian_max', 'keterangan', 'is_aktif']);
        $this->showModal = true;
    }

    public function edit($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/pmbgrade', 'dapat_ubah')) abort(403);

        $item = Pmbgrade::findOrFail($id);
        $this->editId = $item->kode_id;
        $this->grade_nilai = $item->grade_nilai;
        $this->kode_id = $item->kode_id;
        $this->nilai_ujian_min = $item->nilai_ujian_min;
        $this->nilai_ujian_max = $item->nilai_ujian_max;
        $this->keterangan = $item->keterangan;
        $this->is_aktif = ($item->na === 'N');
        $this->showModal = true;
    }

    public function simpan(): void
    {
        if ($this->editId) {
            if (! auth()->user()?->bisaMenu('/admin/preferensi/pmbgrade', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('/admin/preferensi/pmbgrade', 'dapat_buat')) abort(403);
        }

        $rules = [
            'grade_nilai' => 'nullable',
            'kode_id' => 'required|string|max:255',
            'nilai_ujian_min' => 'nullable',
            'nilai_ujian_max' => 'nullable',
            'keterangan' => 'nullable',
            'is_aktif' => 'boolean'
        ];

        $data = $this->validate($rules);

        $payload = [
            'grade_nilai' => ($data['grade_nilai'] === '' ? null : $data['grade_nilai']),
            'kode_id' => ($data['kode_id'] === '' ? null : $data['kode_id']),
            'nilai_ujian_min' => ($data['nilai_ujian_min'] === '' ? null : $data['nilai_ujian_min']),
            'nilai_ujian_max' => ($data['nilai_ujian_max'] === '' ? null : $data['nilai_ujian_max']),
            'keterangan' => ($data['keterangan'] === '' ? null : $data['keterangan']),
            'na' => empty($data['is_aktif']) ? 'Y' : 'N',
        ];

        if ($this->editId) {
            $item = Pmbgrade::findOrFail($this->editId);
            $item->update($payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            $item = Pmbgrade::create($payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editId', 'grade_nilai', 'kode_id', 'nilai_ujian_min', 'nilai_ujian_max', 'keterangan', 'is_aktif']);
        $this->resetPage();
    }

    public function hapus($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/pmbgrade', 'dapat_hapus')) abort(403);

        $item = Pmbgrade::findOrFail($id);
        $item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        $this->resetPage();
    }

    public function with(): array
    {
        $data = Pmbgrade::query()
            ->when($this->search, function ($query) {
                // $query->where('kode_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('kode_id', 'desc')
            ->paginate(10);

        return [
            'rows' => $data
        ];
    }
};
?>

@section('title', 'Kelola Pmbgrade')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola Pmbgrade</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>Pmbgrade</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
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
            <th>Grade Nilai</th>
            <th>Kode Id</th>
            <th>Nilai Ujian Min</th>
            <th>Nilai Ujian Max</th>
            <th>Keterangan</th>

            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td>{{ $row->grade_nilai }}</td>
              <td>{{ $row->kode_id }}</td>
              <td>{{ $row->nilai_ujian_min }}</td>
              <td>{{ $row->nilai_ujian_max }}</td>
              <td>{{ $row->keterangan }}</td>

              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ $row->kode_id }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && $wire.hapus('{{ $row->kode_id }}'))">
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
            <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah' : 'Tambah' }} Pmbgrade</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Grade Nilai</label>
                  <input wire:model="grade_nilai" type="text" class="form-control @error('grade_nilai') is-invalid @enderror">
                  @error('grade_nilai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kode Id</label>
                  <input wire:model="kode_id" type="text" class="form-control @error('kode_id') is-invalid @enderror">
                  @error('kode_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nilai Ujian Min</label>
                  <input wire:model="nilai_ujian_min" type="text" class="form-control @error('nilai_ujian_min') is-invalid @enderror">
                  @error('nilai_ujian_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nilai Ujian Max</label>
                  <input wire:model="nilai_ujian_max" type="text" class="form-control @error('nilai_ujian_max') is-invalid @enderror">
                  @error('nilai_ujian_max') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Keterangan</label>
                  <input wire:model="keterangan" type="text" class="form-control @error('keterangan') is-invalid @enderror">
                  @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
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