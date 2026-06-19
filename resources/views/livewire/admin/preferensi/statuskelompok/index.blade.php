<?php

use App\Models\Statuskelompok;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editId = null;

    public ?string $program_id = null;
    public ?string $nama = null;
    public ?string $jam_mulai = null;
    public ?string $jam_selesai = null;
    public ?string $def = null;
    public ?string $keterangan = null;
    public bool $is_aktif = true;

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/statuskelompok', 'dapat_buat')) abort(403);

        $this->reset(['editId', 'program_id', 'nama', 'jam_mulai', 'jam_selesai', 'def', 'keterangan', 'is_aktif']);
        $this->showModal = true;
    }

    public function edit($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/statuskelompok', 'dapat_ubah')) abort(403);

        $item = Statuskelompok::findOrFail($id);
        $this->editId = $item->status_kelompok_id;
        $this->program_id = $item->program_id;
        $this->nama = $item->nama;
        $this->jam_mulai = $item->jam_mulai;
        $this->jam_selesai = $item->jam_selesai;
        $this->def = (bool)$item->def;
        $this->keterangan = $item->keterangan;
        $this->is_aktif = ($item->na === 'N');
        $this->showModal = true;
    }

    public function simpan(): void
    {
        if ($this->editId) {
            if (! auth()->user()?->bisaMenu('/admin/preferensi/statuskelompok', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('/admin/preferensi/statuskelompok', 'dapat_buat')) abort(403);
        }

        $rules = [
            'program_id' => 'nullable',
            'nama' => 'nullable',
            'jam_mulai' => 'nullable',
            'jam_selesai' => 'nullable',
            'def' => 'boolean',
            'keterangan' => 'nullable',
            'is_aktif' => 'boolean'
        ];

        $data = $this->validate($rules);

        $payload = [
            'program_id' => ($data['program_id'] === '' ? null : $data['program_id']),
            'nama' => ($data['nama'] === '' ? null : $data['nama']),
            'jam_mulai' => ($data['jam_mulai'] === '' ? null : $data['jam_mulai']),
            'jam_selesai' => ($data['jam_selesai'] === '' ? null : $data['jam_selesai']),
            'def' => empty($data['def']) ? 0 : 1,
            'keterangan' => ($data['keterangan'] === '' ? null : $data['keterangan']),
            'na' => empty($data['is_aktif']) ? 'Y' : 'N',
        ];

        if ($this->editId) {
            $item = Statuskelompok::findOrFail($this->editId);
            $item->update($payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            $item = Statuskelompok::create($payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editId', 'program_id', 'nama', 'jam_mulai', 'jam_selesai', 'def', 'keterangan', 'is_aktif']);
        $this->resetPage();
    }

    public function hapus($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/statuskelompok', 'dapat_hapus')) abort(403);

        $item = Statuskelompok::findOrFail($id);
        $item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        $this->resetPage();
    }

    public function with(): array
    {
        $data = Statuskelompok::query()
            ->when($this->search, function ($query) {
                // $query->where('status_kelompok_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('status_kelompok_id', 'desc')
            ->paginate(10);

        return [
            'rows' => $data
        ];
    }
};
?>

@section('title', 'Kelola Statuskelompok')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola Statuskelompok</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>Statuskelompok</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
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
            <th>Program Id</th>
            <th>Nama</th>
            <th>Jam Mulai</th>
            <th>Jam Selesai</th>
            <th>Def</th>

            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td>{{ $row->program_id }}</td>
              <td>{{ $row->nama }}</td>
              <td>{{ $row->jam_mulai }}</td>
              <td>{{ $row->jam_selesai }}</td>
              <td>{{ $row->def }}</td>

              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ $row->status_kelompok_id }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && $wire.hapus('{{ $row->status_kelompok_id }}'))">
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
            <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah' : 'Tambah' }} Statuskelompok</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Program Id</label>
                  <input wire:model="program_id" type="text" class="form-control @error('program_id') is-invalid @enderror">
                  @error('program_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nama</label>
                  <input wire:model="nama" type="text" class="form-control @error('nama') is-invalid @enderror">
                  @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Jam Mulai</label>
                  <input wire:model="jam_mulai" type="text" class="form-control @error('jam_mulai') is-invalid @enderror">
                  @error('jam_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Jam Selesai</label>
                  <input wire:model="jam_selesai" type="text" class="form-control @error('jam_selesai') is-invalid @enderror">
                  @error('jam_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Def</label>
                  <div class="form-check form-switch mt-2">
                    <input wire:model="def" class="form-check-input" type="checkbox" id="def_{{ str()->random(4) }}">
                    <label class="form-check-label" for="def_{{ str()->random(4) }}">Def / Default</label>
                  </div>
                  @error('def') <div class="invalid-feedback">{{ $message }}</div> @enderror
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