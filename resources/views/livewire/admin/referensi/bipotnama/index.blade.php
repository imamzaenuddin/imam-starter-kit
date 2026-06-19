<?php

use App\Models\Bipotnama;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editId = null;

    public ?string $kode_id = null;
    public ?string $rekening_id = null;
    public ?string $urutan = null;
    public ?string $nama = null;
    public ?string $singkatan = null;
    public ?string $trx_id = null;
    public ?string $baris = null;
    public ?string $detil = null;
    public ?string $def_jumlah = null;
    public ?string $def_besar = null;
    public ?string $diskon = null;
    public bool $kena_denda = false;
    public bool $dipotong_beasiswa = false;
    public ?string $catatan = null;
    public bool $is_aktif = true;
    public bool $pb = false;

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/bipotnama', 'dapat_buat')) abort(403);

        $this->reset(['editId', 'kode_id', 'rekening_id', 'urutan', 'nama', 'singkatan', 'trx_id', 'baris', 'detil', 'def_jumlah', 'def_besar', 'diskon', 'kena_denda', 'dipotong_beasiswa', 'catatan', 'is_aktif', 'pb']);
        $this->showModal = true;
    }

    public function edit($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/bipotnama', 'dapat_ubah')) abort(403);

        $item = Bipotnama::findOrFail($id);
        $this->editId = $item->bipot_nama_id;
        $this->kode_id = $item->kode_id ?? '';
        $this->rekening_id = $item->rekening_id ?? '';
        $this->urutan = $item->urutan ?? '';
        $this->nama = $item->nama ?? '';
        $this->singkatan = $item->singkatan ?? '';
        $this->trx_id = $item->trx_id ?? '';
        $this->baris = $item->baris ?? '';
        $this->detil = $item->detil ?? '';
        $this->def_jumlah = $item->def_jumlah ?? '';
        $this->def_besar = $item->def_besar ?? '';
        $this->diskon = $item->diskon ?? '';
        $this->kena_denda = (bool)$item->kena_denda;
        $this->dipotong_beasiswa = (bool)$item->dipotong_beasiswa;
        $this->catatan = $item->catatan ?? '';
        $this->is_aktif = ($item->na === 'N');
        $this->pb = (bool)$item->pb;
        $this->showModal = true;
    }

    public function simpan(): void
    {
        if ($this->editId) {
            if (! auth()->user()?->bisaMenu('/admin/referensi/bipotnama', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('/admin/referensi/bipotnama', 'dapat_buat')) abort(403);
        }

        $rules = [
            'kode_id' => 'nullable',
            'rekening_id' => 'nullable',
            'urutan' => 'nullable',
            'nama' => 'nullable',
            'singkatan' => 'nullable',
            'trx_id' => 'nullable',
            'baris' => 'nullable',
            'detil' => 'nullable',
            'def_jumlah' => 'nullable',
            'def_besar' => 'nullable',
            'diskon' => 'nullable',
            'kena_denda' => 'nullable',
            'dipotong_beasiswa' => 'nullable',
            'catatan' => 'nullable',
            'is_aktif' => 'boolean',
            'pb' => 'nullable'
        ];

        $data = $this->validate($rules);

        $payload = [
            'kode_id' => ($data['kode_id'] === '' ? null : $data['kode_id']),
            'rekening_id' => ($data['rekening_id'] === '' ? null : $data['rekening_id']),
            'urutan' => ($data['urutan'] === '' ? null : $data['urutan']),
            'nama' => ($data['nama'] === '' ? null : $data['nama']),
            'singkatan' => ($data['singkatan'] === '' ? null : $data['singkatan']),
            'trx_id' => ($data['trx_id'] === '' ? null : $data['trx_id']),
            'baris' => ($data['baris'] === '' ? null : $data['baris']),
            'detil' => ($data['detil'] === '' ? null : $data['detil']),
            'def_jumlah' => ($data['def_jumlah'] === '' ? null : $data['def_jumlah']),
            'def_besar' => ($data['def_besar'] === '' ? null : $data['def_besar']),
            'diskon' => ($data['diskon'] === '' ? null : $data['diskon']),
            'kena_denda' => $data['kena_denda'] ? 1 : 0,
            'dipotong_beasiswa' => $data['dipotong_beasiswa'] ? 1 : 0,
            'catatan' => ($data['catatan'] === '' ? null : $data['catatan']),
            'pb' => $data['pb'] ? 1 : 0
        ];

        if ($this->editId) {
            $item = Bipotnama::findOrFail($this->editId);
            $item->update($payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            $item = Bipotnama::create($payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editId', 'kode_id', 'rekening_id', 'urutan', 'nama', 'singkatan', 'trx_id', 'baris', 'detil', 'def_jumlah', 'def_besar', 'diskon', 'kena_denda', 'dipotong_beasiswa', 'catatan', 'is_aktif', 'pb']);
        $this->resetPage();
    }

    public function hapus($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/bipotnama', 'dapat_hapus')) abort(403);

        $item = Bipotnama::findOrFail($id);
        $item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        $this->resetPage();
    }

    public function with(): array
    {
        $data = Bipotnama::query()
            ->when($this->search, function ($query) {
                // $query->where('bipot_nama_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('bipot_nama_id', 'desc')
            ->paginate(10);

        return [
            'rows' => $data
        ];
    }
};
?>

@section('title', 'Kelola Bipotnama')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola Bipotnama</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>Bipotnama</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
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
            <th>Kode Id</th>
            <th>Rekening Id</th>
            <th>Urutan</th>
            <th>Nama</th>
            <th>Singkatan</th>

            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td>{{ $row->kode_id }}</td>
              <td>{{ $row->rekening_id }}</td>
              <td>{{ $row->urutan }}</td>
              <td>{{ $row->nama }}</td>
              <td>{{ $row->singkatan }}</td>

              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ $row->bipot_nama_id }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && $wire.hapus('{{ $row->bipot_nama_id }}'))">
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
            <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah' : 'Tambah' }} Bipotnama</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kode Id</label>
                  <input wire:model="kode_id" type="text" class="form-control @error('kode_id') is-invalid @enderror">
                  @error('kode_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Rekening Id</label>
                  <input wire:model="rekening_id" type="text" class="form-control @error('rekening_id') is-invalid @enderror">
                  @error('rekening_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                  <label class="form-label fw-semibold">Singkatan</label>
                  <input wire:model="singkatan" type="text" class="form-control @error('singkatan') is-invalid @enderror">
                  @error('singkatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Trx Id</label>
                  <input wire:model="trx_id" type="text" class="form-control @error('trx_id') is-invalid @enderror">
                  @error('trx_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Baris</label>
                  <input wire:model="baris" type="text" class="form-control @error('baris') is-invalid @enderror">
                  @error('baris') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Detil</label>
                  <input wire:model="detil" type="text" class="form-control @error('detil') is-invalid @enderror">
                  @error('detil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Def Jumlah</label>
                  <input wire:model="def_jumlah" type="text" class="form-control @error('def_jumlah') is-invalid @enderror">
                  @error('def_jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Def Besar</label>
                  <input wire:model="def_besar" type="text" class="form-control @error('def_besar') is-invalid @enderror">
                  @error('def_besar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Diskon</label>
                  <input wire:model="diskon" type="text" class="form-control @error('diskon') is-invalid @enderror">
                  @error('diskon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kena Denda</label>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold d-block">Kena Denda</label>
                  <div class="form-check form-switch mt-2">
                    <input wire:model="kena_denda" class="form-check-input" type="checkbox" id="toggle_kena_denda">
                    <label class="form-check-label" for="toggle_kena_denda">Kena Denda</label>
                  </div>
                  @error('kena_denda') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>                  @error('kena_denda') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Dipotong Beasiswa</label>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold d-block">Dipotong Beasiswa</label>
                  <div class="form-check form-switch mt-2">
                    <input wire:model="dipotong_beasiswa" class="form-check-input" type="checkbox" id="toggle_dipotong_beasiswa">
                    <label class="form-check-label" for="toggle_dipotong_beasiswa">Dipotong Beasiswa</label>
                  </div>
                  @error('dipotong_beasiswa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>                  @error('dipotong_beasiswa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Catatan</label>
                  <input wire:model="catatan" type="text" class="form-control @error('catatan') is-invalid @enderror">
                  @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Pb</label>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold d-block">Pb</label>
                  <div class="form-check form-switch mt-2">
                    <input wire:model="pb" class="form-check-input" type="checkbox" id="toggle_pb">
                    <label class="form-check-label" for="toggle_pb">Pb</label>
                  </div>
                  @error('pb') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>                  @error('pb') <div class="invalid-feedback">{{ $message }}</div> @enderror
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