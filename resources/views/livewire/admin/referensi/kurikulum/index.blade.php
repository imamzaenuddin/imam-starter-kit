<?php

use App\Models\Kurikulum;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editId = null;

    public ?string $kurikulum_dikti_id = null;
    public ?string $kurikulum_kode = null;
    public ?string $sk_kurikulum = null;
    public ?string $nama = null;
    public ?string $kode_id = null;
    public ?string $prodi_id = null;
    public ?string $tahun_id = null;
    public ?string $tgl_mulai = null;
    public ?string $tgl_selesai = null;
    public ?string $sesi = null;
    public ?string $jml_sesi = null;
    public ?string $sks_wajib = null;
    public ?string $sks_pilihan = null;
    public ?string $total_sks = null;
    public bool $final_dosen = false;
    public ?string $error_code = null;
    public ?string $error_desc = null;
    public bool $is_aktif = true;

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/kurikulum', 'dapat_buat')) abort(403);

        $this->reset(['editId', 'kurikulum_dikti_id', 'kurikulum_kode', 'sk_kurikulum', 'nama', 'kode_id', 'prodi_id', 'tahun_id', 'tgl_mulai', 'tgl_selesai', 'sesi', 'jml_sesi', 'sks_wajib', 'sks_pilihan', 'total_sks', 'final_dosen', 'error_code', 'error_desc', 'is_aktif']);
        $this->showModal = true;
    }

    public function edit($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/kurikulum', 'dapat_ubah')) abort(403);

        $item = Kurikulum::findOrFail($id);
        $this->editId = $item->kurikulum_id;
        $this->kurikulum_dikti_id = $item->kurikulum_dikti_id ?? '';
        $this->kurikulum_kode = $item->kurikulum_kode ?? '';
        $this->sk_kurikulum = $item->sk_kurikulum ?? '';
        $this->nama = $item->nama ?? '';
        $this->kode_id = $item->kode_id ?? '';
        $this->prodi_id = $item->prodi_id ?? '';
        $this->tahun_id = $item->tahun_id ?? '';
        $this->tgl_mulai = $item->tgl_mulai ?? '';
        $this->tgl_selesai = $item->tgl_selesai ?? '';
        $this->sesi = $item->sesi ?? '';
        $this->jml_sesi = $item->jml_sesi ?? '';
        $this->sks_wajib = $item->sks_wajib ?? '';
        $this->sks_pilihan = $item->sks_pilihan ?? '';
        $this->total_sks = $item->total_sks ?? '';
        $this->final_dosen = (bool)$item->final_dosen;
        $this->error_code = $item->error_code ?? '';
        $this->error_desc = $item->error_desc ?? '';
        $this->is_aktif = ($item->na === 'N');
        $this->showModal = true;
    }

    public function simpan(): void
    {
        if ($this->editId) {
            if (! auth()->user()?->bisaMenu('/admin/referensi/kurikulum', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('/admin/referensi/kurikulum', 'dapat_buat')) abort(403);
        }

        $rules = [
            'kurikulum_dikti_id' => 'nullable',
            'kurikulum_kode' => 'nullable',
            'sk_kurikulum' => 'nullable',
            'nama' => 'nullable',
            'kode_id' => 'nullable',
            'prodi_id' => 'nullable',
            'tahun_id' => 'nullable',
            'tgl_mulai' => 'nullable',
            'tgl_selesai' => 'nullable',
            'sesi' => 'nullable',
            'jml_sesi' => 'nullable',
            'sks_wajib' => 'nullable',
            'sks_pilihan' => 'nullable',
            'total_sks' => 'nullable',
            'final_dosen' => 'nullable',
            'error_code' => 'nullable',
            'error_desc' => 'nullable',
            'is_aktif' => 'boolean'
        ];

        $data = $this->validate($rules);

        $payload = [
            'kurikulum_dikti_id' => ($data['kurikulum_dikti_id'] === '' ? null : $data['kurikulum_dikti_id']),
            'kurikulum_kode' => ($data['kurikulum_kode'] === '' ? null : $data['kurikulum_kode']),
            'sk_kurikulum' => ($data['sk_kurikulum'] === '' ? null : $data['sk_kurikulum']),
            'nama' => ($data['nama'] === '' ? null : $data['nama']),
            'kode_id' => ($data['kode_id'] === '' ? null : $data['kode_id']),
            'prodi_id' => ($data['prodi_id'] === '' ? null : $data['prodi_id']),
            'tahun_id' => ($data['tahun_id'] === '' ? null : $data['tahun_id']),
            'tgl_mulai' => ($data['tgl_mulai'] === '' ? null : $data['tgl_mulai']),
            'tgl_selesai' => ($data['tgl_selesai'] === '' ? null : $data['tgl_selesai']),
            'sesi' => ($data['sesi'] === '' ? null : $data['sesi']),
            'jml_sesi' => ($data['jml_sesi'] === '' ? null : $data['jml_sesi']),
            'sks_wajib' => ($data['sks_wajib'] === '' ? null : $data['sks_wajib']),
            'sks_pilihan' => ($data['sks_pilihan'] === '' ? null : $data['sks_pilihan']),
            'total_sks' => ($data['total_sks'] === '' ? null : $data['total_sks']),
            'final_dosen' => $data['final_dosen'] ? 1 : 0,
            'error_code' => ($data['error_code'] === '' ? null : $data['error_code']),
            'error_desc' => ($data['error_desc'] === '' ? null : $data['error_desc'])
        ];

        if ($this->editId) {
            $item = Kurikulum::findOrFail($this->editId);
            $item->update($payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            $item = Kurikulum::create($payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editId', 'kurikulum_dikti_id', 'kurikulum_kode', 'sk_kurikulum', 'nama', 'kode_id', 'prodi_id', 'tahun_id', 'tgl_mulai', 'tgl_selesai', 'sesi', 'jml_sesi', 'sks_wajib', 'sks_pilihan', 'total_sks', 'final_dosen', 'error_code', 'error_desc', 'is_aktif']);
        $this->resetPage();
    }

    public function hapus($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/referensi/kurikulum', 'dapat_hapus')) abort(403);

        $item = Kurikulum::findOrFail($id);
        $item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        $this->resetPage();
    }

    public function with(): array
    {
        $data = Kurikulum::query()
            ->when($this->search, function ($query) {
                // $query->where('kurikulum_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('kurikulum_id', 'desc')
            ->paginate(10);

        return [
            'rows' => $data
        ];
    }
};
?>

@section('title', 'Kelola Kurikulum')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola Kurikulum</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>Kurikulum</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
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
            <th>Kurikulum Dikti Id</th>
            <th>Kurikulum Kode</th>
            <th>Sk Kurikulum</th>
            <th>Nama</th>
            <th>Kode Id</th>

            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td>{{ $row->kurikulum_dikti_id }}</td>
              <td>{{ $row->kurikulum_kode }}</td>
              <td>{{ $row->sk_kurikulum }}</td>
              <td>{{ $row->nama }}</td>
              <td>{{ $row->kode_id }}</td>

              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ $row->kurikulum_id }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && $wire.hapus('{{ $row->kurikulum_id }}'))">
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
            <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah' : 'Tambah' }} Kurikulum</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kurikulum Dikti Id</label>
                  <input wire:model="kurikulum_dikti_id" type="text" class="form-control @error('kurikulum_dikti_id') is-invalid @enderror">
                  @error('kurikulum_dikti_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kurikulum Kode</label>
                  <input wire:model="kurikulum_kode" type="text" class="form-control @error('kurikulum_kode') is-invalid @enderror">
                  @error('kurikulum_kode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Sk Kurikulum</label>
                  <input wire:model="sk_kurikulum" type="text" class="form-control @error('sk_kurikulum') is-invalid @enderror">
                  @error('sk_kurikulum') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nama</label>
                  <input wire:model="nama" type="text" class="form-control @error('nama') is-invalid @enderror">
                  @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kode Id</label>
                  <input wire:model="kode_id" type="text" class="form-control @error('kode_id') is-invalid @enderror">
                  @error('kode_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Prodi Id</label>
                  <input wire:model="prodi_id" type="text" class="form-control @error('prodi_id') is-invalid @enderror">
                  @error('prodi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Tahun Id</label>
                  <input wire:model="tahun_id" type="text" class="form-control @error('tahun_id') is-invalid @enderror">
                  @error('tahun_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Tgl Mulai</label>
                  <input wire:model="tgl_mulai" type="text" class="form-control @error('tgl_mulai') is-invalid @enderror">
                  @error('tgl_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Tgl Selesai</label>
                  <input wire:model="tgl_selesai" type="text" class="form-control @error('tgl_selesai') is-invalid @enderror">
                  @error('tgl_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Sesi</label>
                  <input wire:model="sesi" type="text" class="form-control @error('sesi') is-invalid @enderror">
                  @error('sesi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Jml Sesi</label>
                  <input wire:model="jml_sesi" type="text" class="form-control @error('jml_sesi') is-invalid @enderror">
                  @error('jml_sesi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Sks Wajib</label>
                  <input wire:model="sks_wajib" type="text" class="form-control @error('sks_wajib') is-invalid @enderror">
                  @error('sks_wajib') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Sks Pilihan</label>
                  <input wire:model="sks_pilihan" type="text" class="form-control @error('sks_pilihan') is-invalid @enderror">
                  @error('sks_pilihan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Total Sks</label>
                  <input wire:model="total_sks" type="text" class="form-control @error('total_sks') is-invalid @enderror">
                  @error('total_sks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Final Dosen</label>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold d-block">Final Dosen</label>
                  <div class="form-check form-switch mt-2">
                    <input wire:model="final_dosen" class="form-check-input" type="checkbox" id="toggle_final_dosen">
                    <label class="form-check-label" for="toggle_final_dosen">Final Dosen</label>
                  </div>
                  @error('final_dosen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>                  @error('final_dosen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Error Code</label>
                  <input wire:model="error_code" type="text" class="form-control @error('error_code') is-invalid @enderror">
                  @error('error_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Error Desc</label>
                  <input wire:model="error_desc" type="text" class="form-control @error('error_desc') is-invalid @enderror">
                  @error('error_desc') <div class="invalid-feedback">{{ $message }}</div> @enderror
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