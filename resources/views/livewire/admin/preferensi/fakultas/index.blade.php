<?php

use App\Models\Fakultas;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editId = null;

    public ?string $fakultas_id = null;
    public ?string $id_perguruan_tinggi = null;
    public ?string $nama = null;
    public ?string $nama_ins = null;
    public ?string $kode_pti = null;
    public ?string $status_pt = null;
    public ?string $kode_ptid = null;
    public ?string $header = null;
    public ?string $footer = null;
    public ?string $file_kartu_pegawai1 = null;
    public ?string $file_kartu_pegawai2 = null;
    public ?string $akreditasi = null;
    public ?string $no_skbanpt = null;
    public ?string $live = null;
    public ?string $id__sp = null;
    public ?string $feeder_url = null;
    public ?string $feeder_port = null;
    public ?string $feeder_username = null;
    public ?string $feeder_password = null;
    public ?string $port = null;
    public ?string $kode_id = null;
    public ?string $pejabat = null;
    public ?string $jabatan = null;
    public ?string $keterangan = null;
    public ?string $alamat = null;
    public ?string $provinsi = null;
    public ?string $start_no_fakultas = null;
    public ?string $no_fakultas = null;
    public bool $is_aktif = true;

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/fakultas', 'dapat_buat')) abort(403);

        $this->reset(['editId', 'fakultas_id', 'id_perguruan_tinggi', 'nama', 'nama_ins', 'kode_pti', 'status_pt', 'kode_ptid', 'header', 'footer', 'file_kartu_pegawai1', 'file_kartu_pegawai2', 'akreditasi', 'no_skbanpt', 'live', 'id__sp', 'feeder_url', 'feeder_port', 'feeder_username', 'feeder_password', 'port', 'kode_id', 'pejabat', 'jabatan', 'keterangan', 'alamat', 'provinsi', 'start_no_fakultas', 'no_fakultas', 'is_aktif']);
        $this->showModal = true;
    }

    public function edit($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/fakultas', 'dapat_ubah')) abort(403);

        $item = Fakultas::findOrFail($id);
        $this->editId = $item->fakultas_id;
        $this->fakultas_id = $item->fakultas_id;
        $this->id_perguruan_tinggi = $item->id_perguruan_tinggi;
        $this->nama = $item->nama;
        $this->nama_ins = $item->nama_ins;
        $this->kode_pti = $item->kode_pti;
        $this->status_pt = $item->status_pt;
        $this->kode_ptid = $item->kode_ptid;
        $this->header = $item->header;
        $this->footer = $item->footer;
        $this->file_kartu_pegawai1 = $item->file_kartu_pegawai1;
        $this->file_kartu_pegawai2 = $item->file_kartu_pegawai2;
        $this->akreditasi = $item->akreditasi;
        $this->no_skbanpt = $item->no_skbanpt;
        $this->live = $item->live;
        $this->id__sp = $item->id__sp;
        $this->feeder_url = $item->feeder_url;
        $this->feeder_port = $item->feeder_port;
        $this->feeder_username = $item->feeder_username;
        $this->feeder_password = $item->feeder_password;
        $this->port = $item->port;
        $this->kode_id = $item->kode_id;
        $this->pejabat = $item->pejabat;
        $this->jabatan = $item->jabatan;
        $this->keterangan = $item->keterangan;
        $this->alamat = $item->alamat;
        $this->provinsi = $item->provinsi;
        $this->start_no_fakultas = $item->start_no_fakultas;
        $this->no_fakultas = $item->no_fakultas;
        $this->is_aktif = ($item->na === 'N');
        $this->showModal = true;
    }

    public function simpan(): void
    {
        if ($this->editId) {
            if (! auth()->user()?->bisaMenu('/admin/preferensi/fakultas', 'dapat_ubah')) abort(403);
        } else {
            if (! auth()->user()?->bisaMenu('/admin/preferensi/fakultas', 'dapat_buat')) abort(403);
        }

        $rules = [
            'fakultas_id' => 'required|string|max:255',
            'id_perguruan_tinggi' => 'nullable',
            'nama' => 'nullable',
            'nama_ins' => 'nullable',
            'kode_pti' => 'nullable',
            'status_pt' => 'nullable',
            'kode_ptid' => 'nullable',
            'header' => 'nullable',
            'footer' => 'nullable',
            'file_kartu_pegawai1' => 'nullable',
            'file_kartu_pegawai2' => 'nullable',
            'akreditasi' => 'nullable',
            'no_skbanpt' => 'nullable',
            'live' => 'nullable',
            'id__sp' => 'nullable',
            'feeder_url' => 'nullable',
            'feeder_port' => 'nullable',
            'feeder_username' => 'nullable',
            'feeder_password' => 'nullable',
            'port' => 'nullable',
            'kode_id' => 'nullable',
            'pejabat' => 'nullable',
            'jabatan' => 'nullable',
            'keterangan' => 'nullable',
            'alamat' => 'nullable',
            'provinsi' => 'nullable',
            'start_no_fakultas' => 'nullable',
            'no_fakultas' => 'nullable',
            'is_aktif' => 'boolean'
        ];

        $data = $this->validate($rules);

        $payload = [
            'fakultas_id' => ($data['fakultas_id'] === '' ? null : $data['fakultas_id']),
            'id_perguruan_tinggi' => ($data['id_perguruan_tinggi'] === '' ? null : $data['id_perguruan_tinggi']),
            'nama' => ($data['nama'] === '' ? null : $data['nama']),
            'nama_ins' => ($data['nama_ins'] === '' ? null : $data['nama_ins']),
            'kode_pti' => ($data['kode_pti'] === '' ? null : $data['kode_pti']),
            'status_pt' => ($data['status_pt'] === '' ? null : $data['status_pt']),
            'kode_ptid' => ($data['kode_ptid'] === '' ? null : $data['kode_ptid']),
            'header' => ($data['header'] === '' ? null : $data['header']),
            'footer' => ($data['footer'] === '' ? null : $data['footer']),
            'file_kartu_pegawai1' => ($data['file_kartu_pegawai1'] === '' ? null : $data['file_kartu_pegawai1']),
            'file_kartu_pegawai2' => ($data['file_kartu_pegawai2'] === '' ? null : $data['file_kartu_pegawai2']),
            'akreditasi' => ($data['akreditasi'] === '' ? null : $data['akreditasi']),
            'no_skbanpt' => ($data['no_skbanpt'] === '' ? null : $data['no_skbanpt']),
            'live' => ($data['live'] === '' ? null : $data['live']),
            'id__sp' => ($data['id__sp'] === '' ? null : $data['id__sp']),
            'feeder_url' => ($data['feeder_url'] === '' ? null : $data['feeder_url']),
            'feeder_port' => ($data['feeder_port'] === '' ? null : $data['feeder_port']),
            'feeder_username' => ($data['feeder_username'] === '' ? null : $data['feeder_username']),
            'feeder_password' => ($data['feeder_password'] === '' ? null : $data['feeder_password']),
            'port' => ($data['port'] === '' ? null : $data['port']),
            'kode_id' => ($data['kode_id'] === '' ? null : $data['kode_id']),
            'pejabat' => ($data['pejabat'] === '' ? null : $data['pejabat']),
            'jabatan' => ($data['jabatan'] === '' ? null : $data['jabatan']),
            'keterangan' => ($data['keterangan'] === '' ? null : $data['keterangan']),
            'alamat' => ($data['alamat'] === '' ? null : $data['alamat']),
            'provinsi' => ($data['provinsi'] === '' ? null : $data['provinsi']),
            'start_no_fakultas' => ($data['start_no_fakultas'] === '' ? null : $data['start_no_fakultas']),
            'no_fakultas' => ($data['no_fakultas'] === '' ? null : $data['no_fakultas']),
            'na' => empty($data['is_aktif']) ? 'Y' : 'N',
        ];

        if ($this->editId) {
            $item = Fakultas::findOrFail($this->editId);
            $item->update($payload);
            session()->flash('sukses', 'Data berhasil diperbarui.');
        } else {
            $item = Fakultas::create($payload);
            session()->flash('sukses', 'Data baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editId', 'fakultas_id', 'id_perguruan_tinggi', 'nama', 'nama_ins', 'kode_pti', 'status_pt', 'kode_ptid', 'header', 'footer', 'file_kartu_pegawai1', 'file_kartu_pegawai2', 'akreditasi', 'no_skbanpt', 'live', 'id__sp', 'feeder_url', 'feeder_port', 'feeder_username', 'feeder_password', 'port', 'kode_id', 'pejabat', 'jabatan', 'keterangan', 'alamat', 'provinsi', 'start_no_fakultas', 'no_fakultas', 'is_aktif']);
        $this->resetPage();
    }

    public function hapus($id = null): void
    {
        if (! auth()->user()?->bisaMenu('/admin/preferensi/fakultas', 'dapat_hapus')) abort(403);

        $item = Fakultas::findOrFail($id);
        $item->delete();

        session()->flash('sukses', 'Data berhasil dihapus.');
        $this->resetPage();
    }

    public function with(): array
    {
        $data = Fakultas::query()
            ->when($this->search, function ($query) {
                // $query->where('fakultas_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('fakultas_id', 'desc')
            ->paginate(10);

        return [
            'rows' => $data
        ];
    }
};
?>

@section('title', 'Kelola Fakultas')

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bx bx-table me-2 text-primary"></i>Kelola Fakultas</h4>
      <p class="text-muted small mb-0">Halaman manajemen sentral untuk data <strong>Fakultas</strong>. Anda dapat melakukan pencarian, penambahan, hingga modifikasi data di sini.</p>
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
            <th>Fakultas Id</th>
            <th>Id Perguruan Tinggi</th>
            <th>Nama</th>
            <th>Nama Ins</th>
            <th>Kode Pti</th>

            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td>{{ $row->fakultas_id }}</td>
              <td>{{ $row->id_perguruan_tinggi }}</td>
              <td>{{ $row->nama }}</td>
              <td>{{ $row->nama_ins }}</td>
              <td>{{ $row->kode_pti }}</td>

              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit('{{ $row->fakultas_id }}')" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-text-danger" title="Hapus"
                          @click="Swal.fire({
                            title: 'Hapus Data?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
                          }).then(r => r.isConfirmed && $wire.hapus('{{ $row->fakultas_id }}'))">
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
            <h5 class="modal-title fw-bold">{{ $editId ? 'Ubah' : 'Tambah' }} Fakultas</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Fakultas Id</label>
                  <input wire:model="fakultas_id" type="text" class="form-control @error('fakultas_id') is-invalid @enderror">
                  @error('fakultas_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Id Perguruan Tinggi</label>
                  <input wire:model="id_perguruan_tinggi" type="text" class="form-control @error('id_perguruan_tinggi') is-invalid @enderror">
                  @error('id_perguruan_tinggi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nama</label>
                  <input wire:model="nama" type="text" class="form-control @error('nama') is-invalid @enderror">
                  @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Nama Ins</label>
                  <input wire:model="nama_ins" type="text" class="form-control @error('nama_ins') is-invalid @enderror">
                  @error('nama_ins') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kode Pti</label>
                  <input wire:model="kode_pti" type="text" class="form-control @error('kode_pti') is-invalid @enderror">
                  @error('kode_pti') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Status Pt</label>
                  <input wire:model="status_pt" type="text" class="form-control @error('status_pt') is-invalid @enderror">
                  @error('status_pt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kode Ptid</label>
                  <input wire:model="kode_ptid" type="text" class="form-control @error('kode_ptid') is-invalid @enderror">
                  @error('kode_ptid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Header</label>
                  <input wire:model="header" type="text" class="form-control @error('header') is-invalid @enderror">
                  @error('header') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Footer</label>
                  <input wire:model="footer" type="text" class="form-control @error('footer') is-invalid @enderror">
                  @error('footer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">File Kartu Pegawai1</label>
                  <input wire:model="file_kartu_pegawai1" type="text" class="form-control @error('file_kartu_pegawai1') is-invalid @enderror">
                  @error('file_kartu_pegawai1') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">File Kartu Pegawai2</label>
                  <input wire:model="file_kartu_pegawai2" type="text" class="form-control @error('file_kartu_pegawai2') is-invalid @enderror">
                  @error('file_kartu_pegawai2') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Akreditasi</label>
                  <input wire:model="akreditasi" type="text" class="form-control @error('akreditasi') is-invalid @enderror">
                  @error('akreditasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">No Skbanpt</label>
                  <input wire:model="no_skbanpt" type="text" class="form-control @error('no_skbanpt') is-invalid @enderror">
                  @error('no_skbanpt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Live</label>
                  <input wire:model="live" type="text" class="form-control @error('live') is-invalid @enderror">
                  @error('live') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Id  Sp</label>
                  <input wire:model="id__sp" type="text" class="form-control @error('id__sp') is-invalid @enderror">
                  @error('id__sp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Feeder Url</label>
                  <input wire:model="feeder_url" type="text" class="form-control @error('feeder_url') is-invalid @enderror">
                  @error('feeder_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Feeder Port</label>
                  <input wire:model="feeder_port" type="text" class="form-control @error('feeder_port') is-invalid @enderror">
                  @error('feeder_port') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Feeder Username</label>
                  <input wire:model="feeder_username" type="text" class="form-control @error('feeder_username') is-invalid @enderror">
                  @error('feeder_username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Feeder Password</label>
                  <input wire:model="feeder_password" type="text" class="form-control @error('feeder_password') is-invalid @enderror">
                  @error('feeder_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Port</label>
                  <input wire:model="port" type="text" class="form-control @error('port') is-invalid @enderror">
                  @error('port') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Kode Id</label>
                  <input wire:model="kode_id" type="text" class="form-control @error('kode_id') is-invalid @enderror">
                  @error('kode_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Pejabat</label>
                  <input wire:model="pejabat" type="text" class="form-control @error('pejabat') is-invalid @enderror">
                  @error('pejabat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Jabatan</label>
                  <input wire:model="jabatan" type="text" class="form-control @error('jabatan') is-invalid @enderror">
                  @error('jabatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Keterangan</label>
                  <input wire:model="keterangan" type="text" class="form-control @error('keterangan') is-invalid @enderror">
                  @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Alamat</label>
                  <input wire:model="alamat" type="text" class="form-control @error('alamat') is-invalid @enderror">
                  @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Provinsi</label>
                  <input wire:model="provinsi" type="text" class="form-control @error('provinsi') is-invalid @enderror">
                  @error('provinsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Start No Fakultas</label>
                  <input wire:model="start_no_fakultas" type="text" class="form-control @error('start_no_fakultas') is-invalid @enderror">
                  @error('start_no_fakultas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">No Fakultas</label>
                  <input wire:model="no_fakultas" type="text" class="form-control @error('no_fakultas') is-invalid @enderror">
                  @error('no_fakultas') <div class="invalid-feedback">{{ $message }}</div> @enderror
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