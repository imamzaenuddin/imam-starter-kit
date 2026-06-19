<?php

use App\Services\MigrasiDatabaseService;
use App\Services\LogAktivitasService;
use App\Models\MigrasiPemetaanTabel;
use App\Models\LogMigrasi;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {

    public string $tabAktif       = 'koneksi';
    public array  $statusKoneksi  = [];
    public string $pesanFlash     = '';
    public string $tipeFlash      = '';

    // Tab Scan
    public string $filterKlasifikasi = '';
    public string $cariTabel         = '';
    public bool   $sedangScan        = false;
    public int    $halamanScan       = 1;
    public int    $perHalaman        = 25;

    // Tab Preview Field
    public string $tabelPreview   = '';
    public array  $previewField   = [];

    // Modal ubah klasifikasi
    public ?int   $editId         = null;
    public string $editKlasifikasi = 'abaikan';
    public string $editTabelBaru   = '';

    // Modal konfirmasi impor
    public string $konfirmasiEntitas = '';
    public int    $konfirmFase       = 2;
    public string $konfirmasiLabel   = '';

    // Modal Konfigurasi CRUD
    public ?int   $crudMappingId     = null;
    public string $crudTabel         = '';
    public array  $crudFields        = [];
    public array  $crudFieldTypes    = [];

    // Modal Konfirmasi Buat Tabel
    public ?int   $konfirmTabelId    = null;
    public string $konfirmTabelNama  = '';
    public bool   $buatFileMigration = true;
    public bool   $buatFileSeeder    = false;

    public function mount(): void
    {
        if (! auth()->user()->bisaMenu('/admin/migrasi-database', 'dapat_lihat')) {
            abort(403);
        }
        $this->statusKoneksi = app(MigrasiDatabaseService::class)->testKoneksiLegacy();
    }

    // ── Koneksi ─────────────────────────────────────────────────
    public function testKoneksi(): void
    {
        $this->statusKoneksi = app(MigrasiDatabaseService::class)->testKoneksiLegacy();
    }

    // ── Scan Tabel ───────────────────────────────────────────────
    public function scanTabelLegacy(): void
    {
        if (! auth()->user()->bisaMenu('/admin/migrasi-database', 'dapat_buat')) {
            abort(403);
        }

        $svc  = app(MigrasiDatabaseService::class);
        $hasil = $svc->scanTabelLegacy(auth()->id());

        if ($hasil['status'] === 'ok') {
            $this->flash('success',
                "✅ Scan selesai: <strong>{$hasil['total']}</strong> tabel ditemukan. " .
                "Baru: <strong>{$hasil['baru']}</strong>, Diperbarui: <strong>{$hasil['diperbarui']}</strong>."
            );
        } else {
            $this->flash('danger', "❌ Scan gagal: {$hasil['pesan']}");
        }
    }

    public function ubahKlasifikasi(int $id): void
    {
        if (! auth()->user()->bisaMenu('/admin/migrasi-database', 'dapat_ubah')) {
            abort(403);
        }

        $row = MigrasiPemetaanTabel::find($id);
        if (! $row) return;

        $this->editId           = $id;
        $this->editKlasifikasi  = $row->klasifikasi;
        $this->editTabelBaru    = $row->tabel_baru
            ?? app(MigrasiDatabaseService::class)->suggestNamaTabelBaru($row->tabel_legacy, $row->klasifikasi);

        // $this->js() adalah cara terpercaya di Livewire v3 untuk trigger Bootstrap modal
        $this->js("(function(){ var m = new bootstrap.Modal(document.getElementById('modalEditKlasifikasi')); m.show(); })();");
    }

    public function simpanKlasifikasi(): void
    {
        if (! auth()->user()->bisaMenu('/admin/migrasi-database', 'dapat_ubah')) {
            abort(403);
        }

        $row = MigrasiPemetaanTabel::find($this->editId);
        if (! $row) return;

        // Auto-suggest nama tabel baru jika kosong atau belum diisi manual
        if (empty($this->editTabelBaru) || $this->editTabelBaru === $row->tabel_baru) {
            $this->editTabelBaru = app(MigrasiDatabaseService::class)
                ->suggestNamaTabelBaru($row->tabel_legacy, $this->editKlasifikasi);
        }

        $row->update([
            'klasifikasi' => $this->editKlasifikasi,
            'tabel_baru'  => $this->editKlasifikasi !== 'abaikan' ? $this->editTabelBaru : null,
        ]);

        $this->js("bootstrap.Modal.getInstance(document.getElementById('modalEditKlasifikasi'))?.hide();");
        $this->flash('success', "✅ Klasifikasi <strong>{$row->tabel_legacy}</strong> diubah ke <em>{$this->editKlasifikasi}</em>.");
        $this->editId = null;
    }

    public function batalEditKlasifikasi(): void
    {
        $this->editId = null;
        $this->js("bootstrap.Modal.getInstance(document.getElementById('modalEditKlasifikasi'))?.hide();");
    }

    // ── Preview Field ────────────────────────────────────────────
    public function previewPemetaanField(string $tabel): void
    {
        $this->tabelPreview = $tabel;
        
        $row = MigrasiPemetaanTabel::where('tabel_legacy', $tabel)->first();
        if ($row && !empty($row->pemetaan_field)) {
            $this->previewField = $row->pemetaan_field;
        } else {
            $this->previewField = app(MigrasiDatabaseService::class)->buildPemetaanField($tabel);
        }
        
        $this->tabAktif = 'preview';
    }

    public function simpanPemetaanField(): void
    {
        if (! $this->tabelPreview) return;

        $errors = [];
        foreach ($this->previewField as $i => $field) {
            if ($field['abaikan'] ?? false) continue;
            
            $tipeBaru = $field['tipe_baru'] ?? '';
            $kolom = $field['legacy'];
            
            // Cek jika tipe baru adalah integer tapi data legacy mengandung huruf
            if (str_contains(strtolower($tipeBaru), 'integer') || strtolower($tipeBaru) === 'id') {
                try {
                    $hasNonNumeric = \Illuminate\Support\Facades\DB::connection('legacy')
                        ->table($this->tabelPreview)
                        ->whereRaw("`$kolom` REGEXP '[^0-9.-]'")
                        ->whereNotNull($kolom)
                        ->where($kolom, '!=', '')
                        ->exists();
                        
                    if ($hasNonNumeric) {
                        $errors[] = "Kolom <strong>$kolom</strong> ditolak! Anda memilih tipe <em>$tipeBaru</em>, namun database lama mengandung data huruf/karakter non-angka.";
                    }
                } catch (\Throwable $e) {
                    // Abaikan jika REGEXP tidak disupport atau kolom error
                }
            }
        }
        
        if (!empty($errors)) {
            $this->flash('danger', implode('<br>', $errors));
            return;
        }

        $row = MigrasiPemetaanTabel::where('tabel_legacy', $this->tabelPreview)->first();
        $row?->update(['pemetaan_field' => $this->previewField]);

        $this->flash('success', "✅ Pemetaan field untuk <strong>{$this->tabelPreview}</strong> berhasil divalidasi dan disimpan.");
    }

    public function updateFieldBaru(int $index, string $nilai): void
    {
        $this->previewField[$index]['baru'] = $nilai;
    }

    public function updatedEditKlasifikasi(string $val): void
    {
        $row = MigrasiPemetaanTabel::find($this->editId);
        if ($row) {
            $this->editTabelBaru = app(MigrasiDatabaseService::class)->suggestNamaTabelBaru($row->tabel_legacy, $val);
        }
    }

    public function bukaKonfirmasiBuatTabel(int $id): void
    {
        if (! auth()->user()->bisaMenu('/admin/migrasi-database', 'dapat_ubah')) {
            abort(403);
        }

        $row = MigrasiPemetaanTabel::find($id);
        if (! $row || empty($row->tabel_baru)) return;

        $this->konfirmTabelId = $id;
        $this->konfirmTabelNama = $row->tabel_baru;
        $this->buatFileMigration = true;
        $this->buatFileSeeder = str_starts_with($row->tabel_baru, 'm_');

        $this->js("(function(){ var m = new bootstrap.Modal(document.getElementById('modalKonfirmasiBuatTabel')); m.show(); })();");
    }

    public function eksekusiBuatTabel(): void
    {
        if (! auth()->user()->bisaMenu('/admin/migrasi-database', 'dapat_ubah')) {
            abort(403);
        }

        $res = app(MigrasiDatabaseService::class)->jalankanMigrasi(
            $this->konfirmTabelId, 
            $this->buatFileMigration, 
            $this->buatFileSeeder
        );
        
        $this->js("bootstrap.Modal.getInstance(document.getElementById('modalKonfirmasiBuatTabel'))?.hide();");

        if ($res['status'] === 'ok') {
            $this->flash('success', $res['pesan']);
        } else {
            $this->flash('danger', $res['pesan']);
        }
    }

    public function mulaiImpor(int $id): void
    {
        if (! auth()->user()->bisaMenu('/admin/migrasi-database', 'dapat_buat')) {
            abort(403);
        }

        $row = MigrasiPemetaanTabel::find($id);
        if (! $row) return;

        // Buat record LogMigrasi terlebih dahulu
        $log = LogMigrasi::create([
            'fase'           => $row->klasifikasi === 'master' ? 2 : 3,
            'entitas'        => $row->tabel_legacy,
            'tabel_legacy'   => $row->tabel_legacy,
            'tabel_target'   => $row->tabel_baru,
            'status'         => 'pending',
            'total_legacy'   => $row->jml_baris_legacy,
            'total_imported' => 0,
            'total_skipped'  => 0,
            'total_error'    => 0,
            'started_at'     => now(),
            'user_id'        => auth()->id(),
        ]);

        $row->update(['status_impor' => 'running']);

        // Dispatch Job ke Queue
        \App\Jobs\JalankanImporEntitas::dispatch($log->id, $row->tabel_legacy);

        $this->flash('success', "🚀 Proses impor untuk <strong>{$row->tabel_legacy}</strong> telah dimasukkan ke dalam antrean (Queue Job).");
    }

    // ── Flash ────────────────────────────────────────────────────
    private function flash(string $tipe, string $pesan): void
    {
        $this->tipeFlash  = $tipe;
        $this->pesanFlash = $pesan;
    }

    public function tutupFlash(): void { $this->pesanFlash = ''; $this->tipeFlash = ''; }

    // ── Polling ──────────────────────────────────────────────────
    // Reset halaman saat filter/pencarian berubah
    public function updatedFilterKlasifikasi(): void { $this->halamanScan = 1; }
    public function updatedCariTabel(): void         { $this->halamanScan = 1; }

    #[On('refresh-status')]
    public function refreshStatus(): void {}

    // ── Data ─────────────────────────────────────────────────────
    // ── Konfigurasi & Generate CRUD ────────────────────────────────
    public function bukaKonfigurasiCrud(int $id): void
    {
        if (! auth()->user()->bisaMenu('/admin/migrasi-database', 'dapat_buat')) {
            abort(403);
        }

        $row = MigrasiPemetaanTabel::find($id);
        if (! $row || empty($row->tabel_baru)) return;

        $this->crudMappingId = $id;
        $this->crudTabel = $row->tabel_baru;
        
        $columnsInfo = \Illuminate\Support\Facades\Schema::getColumns($row->tabel_baru);
        $fields = [];
        $fieldTypes = [];
        foreach ($columnsInfo as $colInfo) {
            $col = $colInfo['name'];
            if (in_array($col, ['created_at', 'updated_at', 'deleted_at'])) continue;
            
            $type = 'text'; // Default
            if ($col === 'na' || $col === 'is_locked') {
                $type = 'toggle';
            } elseif (str_contains($col, 'tanggal') || str_contains($col, 'date') || str_contains($col, 'time')) {
                $type = 'text'; // TODO: or date/datetime if preferred
            }

            $fields[$col] = $type;
            $fieldTypes[$col] = $colInfo['type'];
        }
        $this->crudFields = $fields;
        $this->crudFieldTypes = $fieldTypes;

        $this->js("(function(){ var m = new bootstrap.Modal(document.getElementById('modalKonfigurasiCrud')); m.show(); })();");
    }

    public function generateCrud(): void
    {
        if (! auth()->user()->bisaMenu('/admin/migrasi-database', 'dapat_buat')) {
            abort(403);
        }

        try {
            $svc = app(\App\Services\CrudGeneratorService::class);
            $routePath = $svc->generate($this->crudTabel, $this->crudFields);
            
            $this->js("bootstrap.Modal.getInstance(document.getElementById('modalKonfigurasiCrud'))?.hide();");
            $this->flash('success', "✅ CRUD untuk tabel <strong>{$this->crudTabel}</strong> berhasil di-generate! Rute dapat diakses pada <strong>{$routePath}</strong>");
        } catch (\Exception $e) {
            $this->flash('danger', "❌ Gagal generate CRUD: " . $e->getMessage());
        }
    }

    public function with(): array
    {
        $svc = app(MigrasiDatabaseService::class);

        // Ambil semua, lalu slice untuk pagination manual
        $semua = $svc->getTabelTerscan($this->filterKlasifikasi, $this->cariTabel);
        $total = $semua->count();
        $offset = ($this->halamanScan - 1) * $this->perHalaman;
        $tabelHalaman = $semua->slice($offset, $this->perHalaman)->values();
        $totalHalaman = (int) ceil($total / $this->perHalaman);

        return [
            'tabelTerscan'   => $tabelHalaman,
            'totalTabelScan' => $total,
            'totalHalaman'   => $totalHalaman,
            'ringkasanScan'  => $svc->getRingkasanScan(),
            'riwayat'        => $svc->getRiwayat(25),
            'adaBerjalan'    => $svc->adaYangSedangBerjalan(),
            'koneksiOk'      => ($this->statusKoneksi['status'] ?? '') === 'ok',
        ];
    }

};
?>

@section('title', 'Migrasi Database')

<div>
  {{-- Flash --}}
  @if($pesanFlash)
  <div class="alert alert-{{ $tipeFlash }} alert-dismissible fade show" role="alert">
    {!! $pesanFlash !!}
    <button type="button" class="btn-close" wire:click="tutupFlash"></button>
  </div>
  @endif

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">
        <i class="bx bx-transfer-alt me-2 text-primary"></i>Migrasi Database
      </h4>
      <p class="text-muted mb-0">
        ETL Control Panel — Transfer data dari DB Legacy
        <code>{{ $statusKoneksi['database'] ?? '?' }}</code> ke DB Baru
        <code>{{ config('database.connections.mysql.database') }}</code>
      </p>
    </div>
    <div class="d-flex gap-2 align-items-center">
      @if($adaBerjalan)
        <span class="badge bg-primary d-flex align-items-center gap-1">
          <span class="spinner-grow spinner-grow-sm"></span> Ada proses berjalan
        </span>
      @endif
      <button class="btn btn-sm btn-outline-secondary" wire:click="testKoneksi">
        <i class="bx bx-refresh me-1"></i>Refresh
      </button>
    </div>
  </div>

  {{-- KPI --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md bg-label-{{ $koneksiOk ? 'success' : 'danger' }} rounded">
            <i class="bx bx-{{ $koneksiOk ? 'check-shield' : 'x-circle' }} fs-4"></i>
          </div>
          <div>
            <div class="text-muted small">Koneksi Legacy</div>
            <div class="fw-bold text-{{ $koneksiOk ? 'success' : 'danger' }}">
              {{ $koneksiOk ? ($statusKoneksi['database'] ?? '?') : 'Gagal' }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md bg-label-info rounded">
            <i class="bx bx-table fs-4"></i>
          </div>
          <div>
            <div class="text-muted small">Total Terscan</div>
            <div class="fw-bold">{{ $ringkasanScan['total'] }} tabel</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md bg-label-primary rounded">
            <i class="bx bx-layer fs-4"></i>
          </div>
          <div>
            <div class="text-muted small">Master / Transaksi</div>
            <div class="fw-bold">
              {{ $ringkasanScan['master'] }} <span class="text-muted">/</span> {{ $ringkasanScan['transaksi'] }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md bg-label-success rounded">
            <i class="bx bx-check-double fs-4"></i>
          </div>
          <div>
            <div class="text-muted small">Selesai Diimpor</div>
            <div class="fw-bold">{{ $ringkasanScan['done'] }} tabel</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Tab Nav --}}
  <div class="card shadow-sm">
    <div class="card-header p-0">
      <ul class="nav nav-tabs card-header-tabs flex-nowrap overflow-auto" role="tablist">
        <li class="nav-item">
          <button class="nav-link {{ $tabAktif === 'koneksi' ? 'active' : '' }} px-4 py-3"
                  wire:click="$set('tabAktif','koneksi')">
            <i class="bx bx-link me-1"></i>Koneksi
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link {{ $tabAktif === 'scan' ? 'active' : '' }} px-4 py-3"
                  wire:click="$set('tabAktif','scan')">
            <i class="bx bx-search me-1"></i>Scan &amp; Klasifikasi
            @if($ringkasanScan['total'] > 0)
              <span class="badge bg-info ms-1 rounded-pill">{{ $ringkasanScan['total'] }}</span>
            @endif
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link {{ $tabAktif === 'preview' ? 'active' : '' }} px-4 py-3"
                  wire:click="$set('tabAktif','preview')">
            <i class="bx bx-columns me-1"></i>Preview Mapping Field
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link {{ $tabAktif === 'log' ? 'active' : '' }} px-4 py-3"
                  wire:click="$set('tabAktif','log')">
            <i class="bx bx-history me-1"></i>Log
          </button>
        </li>
      </ul>
    </div>

    <div class="card-body p-0">

      {{-- ══ TAB: KONEKSI ══ --}}
      @if($tabAktif === 'koneksi')
      <div class="p-4">
        <div class="row g-4">
          <div class="col-md-5">
            <h6 class="fw-semibold mb-3">Status Koneksi Database Legacy</h6>
            @if($koneksiOk)
              <div class="alert alert-success border-0">
                <i class="bx bx-check-shield me-2 fs-5"></i>
                <strong>Koneksi Berhasil</strong>
                <div class="mt-2 small">
                  <div><strong>Database:</strong> {{ $statusKoneksi['database'] }}</div>
                  <div><strong>Host:</strong> {{ $statusKoneksi['host'] }}</div>
                  <div><strong>Versi:</strong> {{ $statusKoneksi['versi'] }}</div>
                  <div><strong>Jumlah Tabel:</strong> {{ $statusKoneksi['jml_tabel'] }}</div>
                </div>
              </div>
            @else
              <div class="alert alert-danger border-0">
                <i class="bx bx-error-circle me-2 fs-5"></i>
                <strong>Koneksi Gagal</strong>
                <div class="mt-2 small">{{ $statusKoneksi['pesan'] ?? 'Unknown error' }}</div>
              </div>
            @endif
            <button class="btn btn-outline-primary" wire:click="testKoneksi" wire:loading.attr="disabled">
              <span wire:loading.remove wire:target="testKoneksi"><i class="bx bx-refresh me-1"></i>Test Ulang</span>
              <span wire:loading wire:target="testKoneksi"><span class="spinner-border spinner-border-sm me-1"></span>Mengetes...</span>
            </button>
          </div>
          <div class="col-md-7">
            <h6 class="fw-semibold mb-3">Konfigurasi Dual-Database</h6>
            <div class="table-responsive">
              <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                  <tr><th>Parameter</th><th>DB Target (Baru)</th><th>DB Legacy (Lama)</th></tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="text-muted small">Koneksi</td>
                    <td><code>mysql</code></td>
                    <td><code>legacy</code></td>
                  </tr>
                  <tr>
                    <td class="text-muted small">Database</td>
                    <td><code>{{ config('database.connections.mysql.database') }}</code></td>
                    <td><code>{{ config('database.connections.legacy.database') }}</code></td>
                  </tr>
                  <tr>
                    <td class="text-muted small">Host</td>
                    <td><code>{{ config('database.connections.mysql.host') }}</code></td>
                    <td><code>{{ config('database.connections.legacy.host') }}</code></td>
                  </tr>
                  <tr>
                    <td class="text-muted small">Status</td>
                    <td><span class="badge bg-success">Aktif</span></td>
                    <td>
                      <span class="badge {{ $koneksiOk ? 'bg-success' : 'bg-danger' }}">
                        {{ $koneksiOk ? 'Terhubung' : 'Error' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="alert alert-info border-0 small mb-0">
              <i class="bx bx-info-circle me-1"></i>
              Edit <code>DB_LEGACY_*</code> di <code>.env</code> untuk mengubah sumber legacy.
              Setelah itu klik <strong>Scan &amp; Klasifikasi</strong>.
            </div>
          </div>
        </div>
      </div>
      @endif

      {{-- ══ TAB: SCAN & KLASIFIKASI ══ --}}
      @if($tabAktif === 'scan')
      <div class="p-4">
        {{-- Action Bar --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
          <div>
            <h6 class="fw-semibold mb-1">Scan &amp; Klasifikasi Tabel Legacy</h6>
            <p class="text-muted small mb-0">
              Sistem akan membaca semua tabel, lalu Anda menentukan: <strong>Master</strong> (→ <code>m_*</code>) atau <strong>Transaksi</strong> (→ <code>t_*</code>).
            </p>
          </div>
          <button class="btn btn-primary" wire:click="scanTabelLegacy"
                  wire:loading.attr="disabled" wire:target="scanTabelLegacy"
                  @if(! $koneksiOk) disabled @endif>
            <span wire:loading.remove wire:target="scanTabelLegacy">
              <i class="bx bx-scan me-1"></i>Scan Tabel Legacy
            </span>
            <span wire:loading wire:target="scanTabelLegacy">
              <span class="spinner-border spinner-border-sm me-1"></span>Scanning...
            </span>
          </button>
        </div>

        {{-- Info konversi field --}}
        <div class="alert alert-primary border-0 small mb-3">
          <i class="bx bx-code-alt me-1"></i>
          <strong>Aturan Konversi Nama Field:</strong>
          Field di legacy DB akan otomatis dikonversi ke <strong>snake_case</strong> standar Laravel.
          Contoh: <code>ProdiID</code> → <code>prodi_id</code> | <code>NmProdi</code> → <code>nm_prodi</code> | <code>KdFakultas</code> → <code>kd_fakultas</code>
        </div>

        @if($ringkasanScan['total'] === 0)
          <div class="text-center py-5">
            <i class="bx bx-search fs-1 text-muted d-block mb-3"></i>
            <p class="text-muted">Belum ada tabel terscan. Klik <strong>"Scan Tabel Legacy"</strong> untuk memulai.</p>
          </div>
        @else
          {{-- Ringkasan badge --}}
          <div class="d-flex flex-wrap gap-2 mb-3">
            <button wire:click="$set('filterKlasifikasi', '')"
                    class="btn btn-sm {{ $filterKlasifikasi === '' ? 'btn-dark' : 'btn-outline-secondary' }}">
              Semua ({{ $ringkasanScan['total'] }})
            </button>
            <button wire:click="$set('filterKlasifikasi', 'master')"
                    class="btn btn-sm {{ $filterKlasifikasi === 'master' ? 'btn-primary' : 'btn-outline-primary' }}">
              <i class="bx bx-layer me-1"></i>Master ({{ $ringkasanScan['master'] }})
            </button>
            <button wire:click="$set('filterKlasifikasi', 'transaksi')"
                    class="btn btn-sm {{ $filterKlasifikasi === 'transaksi' ? 'btn-warning' : 'btn-outline-warning' }}">
              <i class="bx bx-git-branch me-1"></i>Transaksi ({{ $ringkasanScan['transaksi'] }})
            </button>
            <button wire:click="$set('filterKlasifikasi', 'abaikan')"
                    class="btn btn-sm {{ $filterKlasifikasi === 'abaikan' ? 'btn-secondary' : 'btn-outline-secondary' }}">
              Abaikan ({{ $ringkasanScan['abaikan'] }})
            </button>
            <div class="ms-auto">
              <input type="text" wire:model.live.debounce.300ms="cariTabel"
                     class="form-control form-control-sm" placeholder="Cari nama tabel...">
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Tabel Legacy</th>
                  <th class="text-center">Rows</th>
                  <th class="text-center">Kolom</th>
                  <th class="text-center">Klasifikasi</th>
                  <th>Nama Tabel Baru</th>
                  <th class="text-center">Status Impor</th>
                  <th class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($tabelTerscan as $i => $row)
                <tr>
                  <td class="text-muted">{{ $i + 1 }}</td>
                  <td>
                    <code class="fw-semibold">{{ $row->tabel_legacy }}</code>
                  </td>
                  <td class="text-center">
                    <span class="{{ $row->jml_baris_legacy > 1000 ? 'text-danger fw-semibold' : ($row->jml_baris_legacy > 100 ? 'text-warning' : 'text-success') }}">
                      {{ number_format($row->jml_baris_legacy) }}
                    </span>
                  </td>
                  <td class="text-center text-muted">{{ $row->jml_kolom_legacy }}</td>
                  <td class="text-center">
                    <span class="badge {{ $row->klasifikasiBadge() }}">
                      {{ $row->klasifikasiLabel() }}
                    </span>
                  </td>
                  <td>
                    @if($row->tabel_baru)
                      <code class="text-success-emphasis">{{ $row->tabel_baru }}</code>
                    @else
                      <span class="text-muted fst-italic">—</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <span class="badge {{ $row->statusImporBadge() }}">
                      {{ $row->status_impor }}
                    </span>
                  </td>
                  <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                      <button class="btn btn-sm btn-outline-primary"
                              wire:click="ubahKlasifikasi({{ $row->id }})"
                              title="Ubah Klasifikasi">
                        <i class="bx bx-edit"></i>
                      </button>
                      @if($row->klasifikasi !== 'abaikan')
                        <button class="btn btn-sm btn-outline-secondary"
                                wire:click="previewPemetaanField('{{ $row->tabel_legacy }}')"
                                title="Preview Mapping Field">
                          <i class="bx bx-columns"></i>
                        </button>
                        
                        @if($row->tabel_baru)
                          @if(\Illuminate\Support\Facades\Schema::hasTable($row->tabel_baru))
                            <button class="btn btn-sm btn-success" disabled title="Tabel Sudah Dibuat">
                              <i class="bx bx-check-double"></i>
                            </button>
                            
                            @if(str_starts_with($row->tabel_baru, 'm_'))
                              <button class="btn btn-sm btn-primary"
                                      wire:click="bukaKonfigurasiCrud({{ $row->id }})"
                                      title="Buat Form CRUD">
                                <i class="bx bx-layout"></i>
                              </button>
                            @endif
                            
                            @if($row->status_impor === 'running')
                              <button class="btn btn-sm btn-warning" disabled title="Mengimpor Data...">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                              </button>
                            @else
                              <button class="btn btn-sm btn-outline-warning"
                                      wire:click="mulaiImpor({{ $row->id }})"
                                      title="Jalankan Impor Data">
                                <i class="bx bx-play"></i>
                              </button>
                            @endif
                          @else
                            <button class="btn btn-sm btn-outline-success"
                                    wire:click="bukaKonfirmasiBuatTabel({{ $row->id }})"
                                    title="Buat Tabel di DB Baru">
                              <i class="bx bx-plus-circle"></i>
                            </button>
                          @endif
                        @endif
                      @endif
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">
                    Tidak ada tabel yang cocok.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          @if($totalHalaman > 1)
          <div class="d-flex justify-content-between align-items-center mt-3 px-3 py-2 border-top">
            <div class="text-muted small">
              Menampilkan halaman <strong>{{ $halamanScan }}</strong> dari <strong>{{ $totalHalaman }}</strong>
            </div>
            <nav>
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item {{ $halamanScan <= 1 ? 'disabled' : '' }}">
                  <button class="page-link" wire:click="$set('halamanScan', {{ $halamanScan - 1 }})" type="button">Sebelumnya</button>
                </li>
                
                @php
                  $range = 2; 
                  $startPage = max(1, $halamanScan - $range);
                  $endPage = min($totalHalaman, $halamanScan + $range);
                @endphp
                
                @if($startPage > 1)
                  <li class="page-item {{ $halamanScan === 1 ? 'active' : '' }}">
                    <button class="page-link" wire:click="$set('halamanScan', 1)" type="button">1</button>
                  </li>
                  @if($startPage > 2)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                  @endif
                @endif
                
                @for($p = $startPage; $p <= $endPage; $p++)
                  <li class="page-item {{ $halamanScan === $p ? 'active' : '' }}">
                    <button class="page-link" wire:click="$set('halamanScan', {{ $p }})" type="button">{{ $p }}</button>
                  </li>
                @endfor
                
                @if($endPage < $totalHalaman)
                  @if($endPage < $totalHalaman - 1)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                  @endif
                  <li class="page-item {{ $halamanScan === $totalHalaman ? 'active' : '' }}">
                    <button class="page-link" wire:click="$set('halamanScan', {{ $totalHalaman }})" type="button">{{ $totalHalaman }}</button>
                  </li>
                @endif
                
                <li class="page-item {{ $halamanScan >= $totalHalaman ? 'disabled' : '' }}">
                  <button class="page-link" wire:click="$set('halamanScan', {{ $halamanScan + 1 }})" type="button">Berikutnya</button>
                </li>
              </ul>
            </nav>
          </div>
          @endif
        @endif
      </div>
      @endif

      {{-- ══ TAB: PREVIEW MAPPING FIELD ══ --}}
      @if($tabAktif === 'preview')
      <div class="p-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h6 class="fw-semibold mb-1">Preview Pemetaan Field</h6>
            <p class="text-muted small mb-0">
              Field nama legacy dikonversi otomatis ke <strong>snake_case</strong> standar Laravel.
              Anda dapat mengubah nama field baru sebelum menyimpan.
            </p>
          </div>
          @if($tabelPreview)
            <div class="d-flex flex-column align-items-end">
              <button class="btn btn-success mb-1" wire:click="simpanPemetaanField">
                <span wire:loading.remove wire:target="simpanPemetaanField"><i class="bx bx-save me-1"></i>Simpan Pemetaan</span>
                <span wire:loading wire:target="simpanPemetaanField"><span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...</span>
              </button>
              @if($pesanFlash && $tabAktif === 'preview')
                <small class="text-{{ $tipeFlash }} fw-bold"><i class="bx bx-info-circle"></i> {!! strip_tags($pesanFlash) !!}</small>
              @endif
            </div>
          @endif
        </div>

        @if(! $tabelPreview)
          <div class="text-center py-5">
            <i class="bx bx-columns fs-1 text-muted d-block mb-3"></i>
            <p class="text-muted">Klik ikon <i class="bx bx-columns"></i> pada tabel di tab <strong>Scan &amp; Klasifikasi</strong> untuk melihat mapping field.</p>
          </div>
        @else
          <div class="alert alert-info border-0 small mb-3">
            <i class="bx bx-info-circle me-1"></i>
            Tabel: <code class="fw-bold">{{ $tabelPreview }}</code>
            → Tabel baru: <code class="fw-bold text-success">
              {{ MigrasiPemetaanTabel::where('tabel_legacy', $tabelPreview)->value('tabel_baru') ?? '?' }}
            </code>
          </div>

          <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Field Legacy (Asli)</th>
                  <th>Tipe Legacy</th>
                  <th>Key</th>
                  <th>Field Baru <span class="badge bg-success ms-1">snake_case</span></th>
                  <th>Tipe Migration Laravel</th>
                  <th class="text-center">Abaikan?</th>
                </tr>
              </thead>
              <tbody>
                @foreach($previewField as $i => $field)
                <tr class="{{ ($field['abaikan'] ?? false) ? 'table-secondary text-decoration-line-through text-muted' : '' }}">
                  <td class="text-muted">{{ $i + 1 }}</td>
                  <td><code class="{{ ($field['abaikan'] ?? false) ? 'text-muted' : 'text-warning-emphasis' }}">{{ $field['legacy'] }}</code></td>
                  <td><small class="text-muted">{{ $field['tipe'] }}</small></td>
                  <td>
                    <select class="form-select form-select-sm" wire:model="previewField.{{ $i }}.key" {{ ($field['abaikan'] ?? false) ? 'disabled' : '' }}>
                      <option value="">-</option>
                      <option value="PRI">PK (Primary)</option>
                      <option value="MUL">FK/Index (MUL)</option>
                      <option value="UNI">Unique</option>
                    </select>
                  </td>
                  <td>
                    <input type="text" class="form-control form-control-sm"
                           wire:model="previewField.{{ $i }}.baru"
                           {{ ($field['abaikan'] ?? false) ? 'disabled' : '' }}>
                  </td>
                  <td>
                    <input type="text" class="form-control form-control-sm font-monospace text-success-emphasis"
                           wire:model="previewField.{{ $i }}.tipe_baru"
                           {{ ($field['abaikan'] ?? false) ? 'disabled' : '' }}>
                  </td>
                  <td class="text-center">
                    <div class="form-check form-switch d-flex justify-content-center m-0">
                      <input class="form-check-input" type="checkbox" wire:model="previewField.{{ $i }}.abaikan">
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
      @endif

      {{-- ══ TAB: LOG ══ --}}
      @if($tabAktif === 'log')
      <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-semibold mb-0">Log Eksekusi Migrasi</h6>
          <small class="text-muted">Auto-refresh setiap 5 detik</small>
        </div>

        @if($riwayat->isEmpty())
          <div class="text-center py-5 text-muted">
            <i class="bx bx-history fs-1 d-block mb-2"></i>Belum ada riwayat eksekusi
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
              <thead class="table-light">
                <tr>
                  <th>Waktu</th>
                  <th>Entitas</th>
                  <th>Legacy → Baru</th>
                  <th class="text-center">Status</th>
                  <th class="text-center">Imported</th>
                  <th class="text-center">Skip</th>
                  <th class="text-center">Error</th>
                  <th class="text-center">Durasi</th>
                </tr>
              </thead>
              <tbody>
                @foreach($riwayat as $log)
                <tr>
                  <td class="text-muted" style="white-space:nowrap;">{{ $log->created_at->format('d/m H:i:s') }}</td>
                  <td>
                    <span class="fw-semibold">{{ $log->entitas }}</span>
                    @if($log->isRunning())
                      <span class="spinner-border spinner-border-sm text-primary ms-1" style="width:10px;height:10px;"></span>
                    @endif
                  </td>
                  <td>
                    <code class="text-warning-emphasis">{{ $log->tabel_legacy }}</code>
                    <i class="bx bx-right-arrow-alt text-muted mx-1"></i>
                    <code class="text-success-emphasis">{{ $log->tabel_target }}</code>
                  </td>
                  <td class="text-center"><span class="badge {{ $log->statusBadgeClass() }}">{{ $log->statusLabel() }}</span></td>
                  <td class="text-center text-success fw-semibold">{{ number_format($log->total_imported) }}</td>
                  <td class="text-center text-warning">{{ number_format($log->total_skipped) }}</td>
                  <td class="text-center text-danger">{{ number_format($log->total_error) }}</td>
                  <td class="text-center text-muted">{{ $log->durasiLabel() }}</td>
                </tr>
                @if($log->isError() && $log->pesan_error)
                <tr class="table-danger">
                  <td colspan="8" class="py-1 ps-4 small">
                    <i class="bx bx-error me-1"></i>{{ $log->pesan_error }}
                  </td>
                </tr>
                @endif
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
      @endif

    </div>
  </div>

  {{-- Modal: Ubah Klasifikasi --}}
  <div class="modal fade" id="modalEditKlasifikasi" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold">
            <i class="bx bx-edit me-2 text-primary"></i>Ubah Klasifikasi Tabel
          </h5>
          <button type="button" class="btn-close" wire:click="batalEditKlasifikasi"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Klasifikasi</label>
            <div class="d-flex gap-3">
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" wire:model="editKlasifikasi" value="master" id="klMaster">
                <label class="form-check-label" for="klMaster">
                  <span class="badge bg-primary me-1">m_*</span> Master Data
                </label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" wire:model="editKlasifikasi" value="transaksi" id="klTransaksi">
                <label class="form-check-label" for="klTransaksi">
                  <span class="badge bg-warning text-dark me-1">t_*</span> Transaksi
                </label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" wire:model="editKlasifikasi" value="abaikan" id="klAbaikan">
                <label class="form-check-label" for="klAbaikan">
                  <span class="badge bg-secondary me-1">—</span> Abaikan
                </label>
              </div>
            </div>
          </div>
          @if($editKlasifikasi !== 'abaikan')
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Tabel Baru</label>
            <input type="text" class="form-control" wire:model="editTabelBaru"
                   placeholder="contoh: m_prodis">
            <div class="form-text">
              Prefix otomatis: <code>{{ $editKlasifikasi === 'master' ? 'm_' : 't_' }}</code>.
              Nama menggunakan <strong>snake_case</strong>.
            </div>
          </div>
          @endif
          <div class="alert alert-warning border-0 small mb-0">
            <i class="bx bx-info-circle me-1"></i>
            <strong>Master</strong>: Data referensi yang jarang berubah (contoh: agama, prodi, status).<br>
            <strong>Transaksi</strong>: Data operasional yang terus bertambah (contoh: mahasiswa, krs, nilai).
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-outline-secondary" wire:click="batalEditKlasifikasi">Batal</button>
          <button type="button" class="btn btn-primary" wire:click="simpanKlasifikasi">
            <span wire:loading.remove wire:target="simpanKlasifikasi"><i class="bx bx-save me-1"></i>Simpan</span>
            <span wire:loading wire:target="simpanKlasifikasi"><span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...</span>
          </button>
        </div>
      </div>
    </div>
  </div>
  {{-- Modal: Konfirmasi Buat Tabel --}}
  <div class="modal fade" id="modalKonfirmasiBuatTabel" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">
            <i class="bx bx-table me-2 text-primary"></i>Konfirmasi Pembuatan Tabel
          </h5>
          <button type="button" class="btn-close" onclick="bootstrap.Modal.getInstance(document.getElementById('modalKonfirmasiBuatTabel'))?.hide()"></button>
        </div>
        <div class="modal-body pt-3">
          <p class="mb-4">Tabel <code class="fw-bold">{{ $konfirmTabelNama }}</code> akan diciptakan ke dalam skema *database* baru Anda.</p>
          
          <div class="card border bg-light shadow-none mb-3">
            <div class="card-body p-3">
              <h6 class="fw-semibold mb-3">Opsi Kode Generator:</h6>
              
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="checkMigration" wire:model="buatFileMigration">
                <label class="form-check-label fw-medium" for="checkMigration">Simpan File Migration</label>
                <div class="text-muted small mt-1">Sistem akan men-_generate_ file <code>..._create_table.php</code> ke folder <code>database/migrations/</code>. Jika tidak dicentang, tabel tetap dibuat di *database* namun tanpa peninggalan file.</div>
              </div>

              <hr class="my-3">

              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="checkSeeder" wire:model="buatFileSeeder">
                <label class="form-check-label fw-medium" for="checkSeeder">Buat & Tarik Data ke Seeder</label>
                <div class="text-muted small mt-1">Sistem akan menyalin data dari tabel *legacy* dan menulisnya sebagai kumpulan *array statis* ke dalam <code>database/seeders/{{ Str::studly(str_replace('m_', '', $konfirmTabelNama)) }}Seeder.php</code>. (Direkomendasikan khusus untuk data master).</div>
              </div>

            </div>
          </div>
          
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" onclick="bootstrap.Modal.getInstance(document.getElementById('modalKonfirmasiBuatTabel'))?.hide()">Batal</button>
          <button type="button" class="btn btn-primary" wire:click="eksekusiBuatTabel">
            <span wire:loading.remove wire:target="eksekusiBuatTabel"><i class="bx bx-play-circle me-1"></i>Jalankan Eksekusi</span>
            <span wire:loading wire:target="eksekusiBuatTabel"><span class="spinner-border spinner-border-sm me-1"></span>Mengeksekusi...</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal: Konfigurasi Form CRUD --}}
  <div class="modal fade" id="modalKonfigurasiCrud" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">
            <i class="bx bx-layout me-2 text-primary"></i>Konfigurasi Form CRUD
          </h5>
          <button type="button" class="btn-close" onclick="bootstrap.Modal.getInstance(document.getElementById('modalKonfigurasiCrud'))?.hide()"></button>
        </div>
        <div class="modal-body pt-3">
          <p class="text-muted mb-4">Pilih tipe input form (HTML) untuk setiap kolom pada tabel <code class="fw-bold">{{ $crudTabel }}</code>.</p>
          
          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
              <thead class="table-light">
                <tr>
                  <th>Nama Kolom</th>
                  <th>Tipe Data (DB)</th>
                  <th style="width: 250px;">Tipe Input Form</th>
                </tr>
              </thead>
              <tbody>
                @foreach($crudFields as $col => $type)
                <tr>
                  <td><code>{{ $col }}</code></td>
                  <td><span class="badge bg-label-info font-monospace" style="text-transform: lowercase;">{{ $crudFieldTypes[$col] ?? 'unknown' }}</span></td>
                  <td>
                    <select wire:model="crudFields.{{ $col }}" class="form-select form-select-sm">
                      <option value="text">Teks Input (Input)</option>
                      <option value="textarea">Teks Area (Textarea)</option>
                      <option value="select">Pilihan Tunggal (Select)</option>
                      <option value="checkbox">Kotak Centang (Checkbox Klasik)</option>
                      <option value="toggle">Sakelar (Toggle Switch Modern)</option>
                      <option value="radio">Tombol Radio (Radio Button)</option>
                      <option value="multiselect">Pilihan Ganda (Multi Select)</option>
                      <option value="abaikan" class="text-danger fw-semibold">Abaikan (Sembunyikan)</option>
                    </select>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="alert alert-info border-0 small mt-3">
            <i class="bx bx-info-circle me-1"></i> Kolom <strong>na</strong> (jika ada) otomatis di-set sebagai <em>Checkbox</em> (Status Aktif).
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" onclick="bootstrap.Modal.getInstance(document.getElementById('modalKonfigurasiCrud'))?.hide()">Batal</button>
          <button type="button" class="btn btn-primary" wire:click="generateCrud">
            <span wire:loading.remove wire:target="generateCrud"><i class="bx bx-cog me-1"></i>Generate Kode</span>
            <span wire:loading wire:target="generateCrud"><span class="spinner-border spinner-border-sm me-1"></span>Memproses...</span>
          </button>
        </div>
      </div>
    </div>
  </div>

</div>{{-- END ROOT DIV --}}

@push('scripts')
<script>
  document.addEventListener('livewire:initialized', () => {
    Livewire.on('buka-modal-edit', () => {
      new bootstrap.Modal(document.getElementById('modalEditKlasifikasi')).show();
    });
    Livewire.on('tutup-modal-edit', () => {
      bootstrap.Modal.getInstance(document.getElementById('modalEditKlasifikasi'))?.hide();
    });
    setInterval(() => Livewire.dispatch('refresh-status'), 5000);
  });
</script>
@endpush
