<?php

use App\Models\Modekuliah;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editId = null;

    public ?string $id_legacy = null;
    public ?string $mode_kuliah_id = null;
    public ?string $mode_kuliah_kode = null;
    public ?string $nama = null;
    public ?string $keterangan = null;
    public ?string $dokumentasi = null;
    public ?string $bukti_dokumentasi = null;
    public ?string $link = null;
    public bool $bukti_link = false;
    public bool $is_aktif = true;

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/modekuliah', 'dapat_buat')) abort(403);

        $this->reset(['editId', 'id_legacy', 'mode_kuliah_id', 'mode_kuliah_kode', 'nama', 'keterangan', 'dokumentasi', 'bukti_dokumentasi', 'link', 'bukti_link', 'is_aktif']);
        $this->showModal = true;
    }

    public function edit($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/modekuliah', 'dapat_ubah')) abort(403);

        $item = Modekuliah::findOrFail($id);
        $this->editId = $item->mode_kuliah_id;
        $this->id_legacy = $item->id_legacy;
        $this->mode_kuliah_id = $item->mode_kuliah_id;
        $this->mode_kuliah_kode = $item->mode_kuliah_kode;
        $this->nama = $item->nama;
        $this->keterangan = $item->keterangan;
        $this->dokumentasi = $item->dokumentasi;
        $this->bukti_dokumentasi = $item->bukti_dokumentasi;
        $this->link = $item->link;
        $this->bukti_link = (bool)$item->bukti_link;
        $this->is_aktif = ($item->na === 'N');
        $this->showModal = true;
    }

    public function simpan(): void
    {
        if ($this->editId) {
            if (! auth()->user()?->bisaMenu('/admin/referensi/modekuliah', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('/admin/referensi/modekuliah', 'dapat_buat')) abort(403);
        }

        $rules = [
            'id_legacy' => 'nullable',
            'mode_kuliah_id' => 'required|string|max:255',
            'mode_kuliah_kode' => 'nullable',
            'nama' => 'nullable',
            'keterangan' => 'nullable',
            'dokumentasi' => 'nullable',
            'bukti_dokumentasi' => 'nullable',
            'link' => 'nullable',
            'bukti_link' => 'boolean',
            'is_aktif' => 'boolean'
        ];

        $data = $this->validate($rules);

        $payload = [
            'id_legacy' => ($data['id_legacy'] === '' ? null : $data['id_legacy']),
            'mode_kuliah_id' => ($data['mode_kuliah_id'] === '' ? null : $data['mode_kuliah_id']),
            'mode_kuliah_kode' => ($data['mode_kuliah_kode'] === '' ? null : $data['mode_kuliah_kode']),
            'nama' => ($data['nama'] === '' ? null : $data['nama']),
            'keterangan' => ($data['keterangan'] === '' ? null : $data['keterangan']),
            'dokumentasi' => ($data['dokumentasi'] === '' ? null : $data['dokumentasi']),
            'bukti_dokumentasi' => ($data['bukti_dokumentasi'] === '' ? null : $data['bukti_dokumentasi']),
            'link' => ($data['link'] === '' ? null : $data['link']),
            'bukti_link' => $data['bukti_link'] ? 1 : 0,
            'na' => empty($data['is_aktif']) ? 'Y' : 'N',
        ];

        if ($this->editId) {
            $item = Modekuliah::findOrFail($this->editId);
            $item->update($payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            $item = Modekuliah::create($payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editId', 'id_legacy', 'mode_kuliah_id', 'mode_kuliah_kode', 'nama', 'keterangan', 'dokumentasi', 'bukti_dokumentasi', 'link', 'bukti_link', 'is_aktif']);
        $this->resetPage();
    }

    public function hapus($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/modekuliah', 'dapat_hapus')) abort(403);

        $item = Modekuliah::findOrFail($id);
        $item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        $this->resetPage();
    }

    public function with(): array
    {
        $data = Modekuliah::query()
            ->when($this->search, function ($query) {
                // $query->where('mode_kuliah_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('mode_kuliah_id', 'desc')
            ->paginate(10);

        return [
            'rows' => $data
        ];
    }
};
?>

@section('title', 'Kelola Modekuliah')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola Modekuliah</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>Modekuliah</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
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
            <th>Id Legacy</th>
            <th>Mode Kuliah Id</th>
            <th>Mode Kuliah Kode</th>
            <th>Nama</th>
            <th>Keterangan</th>

            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td>{{ $row->id_legacy }}</td>
              <td>{{ $row->mode_kuliah_id }}</td>
              <td>{{ $row->mode_kuliah_kode }}</td>
              <td>{{ $row->nama }}</td>
              <td>{{ $row->keterangan }}</td>

              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ $row->mode_kuliah_id }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && $wire.hapus('{{ $row->mode_kuliah_id }}'))">
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
            <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah' : 'Tambah' }} Modekuliah</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Id Legacy</label>
                  <input wire:model="id_legacy" type="text" class="form-control @error('id_legacy') is-invalid @enderror">
                  @error('id_legacy') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Mode Kuliah Id</label>
                  <input wire:model="mode_kuliah_id" type="text" class="form-control @error('mode_kuliah_id') is-invalid @enderror">
                  @error('mode_kuliah_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Mode Kuliah Kode</label>
                  <input wire:model="mode_kuliah_kode" type="text" class="form-control @error('mode_kuliah_kode') is-invalid @enderror">
                  @error('mode_kuliah_kode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nama</label>
                  <input wire:model="nama" type="text" class="form-control @error('nama') is-invalid @enderror">
                  @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Keterangan</label>
                  <input wire:model="keterangan" type="text" class="form-control @error('keterangan') is-invalid @enderror">
                  @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Dokumentasi</label>
                  <input wire:model="dokumentasi" type="text" class="form-control @error('dokumentasi') is-invalid @enderror">
                  @error('dokumentasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Bukti Dokumentasi</label>
                  <input wire:model="bukti_dokumentasi" type="text" class="form-control @error('bukti_dokumentasi') is-invalid @enderror">
                  @error('bukti_dokumentasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Link</label>
                  <input wire:model="link" type="text" class="form-control @error('link') is-invalid @enderror">
                  @error('link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Bukti Link</label>
                  <div class="form-check mt-2">
                    <input wire:model="bukti_link" type="checkbox" class="form-check-input" id="check_bukti_link">
                    <label class="form-check-label" for="check_bukti_link">Bukti Link</label>
                  </div>
                  @error('bukti_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
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