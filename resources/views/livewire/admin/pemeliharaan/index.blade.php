<?php

use App\Services\LogAktivitasService;
use App\Services\PemeliharaanService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {

    // ─── State ───────────────────────────────────────────────────────────────

    /** @var array<int,array{label:string,perintah:string,output:string,sukses:bool}> */
    public array $hasilCache = [];

    /** @var array<int,array{nama:string,status:string,batch:int|null}> */
    public array $daftarMigration = [];

    /** @var array{total:int,run:int,pending:int} */
    public array $ringkasanMigration = [
        'total' => 0,
        'run' => 0,
        'pending' => 0,
    ];

    public string $filterMigration = 'pending';
    public bool $migrationRunDibatasi = false;
    public int $migrationMaksRun = 50;
    public string $searchMigration = '';
    public int $perPageMigration = 20;
    public int $halamanMigration = 1;
    public string $sortMigrationBy = 'nama';
    public string $sortMigrationDir = 'asc';
    public string $migrationRefreshedAt = '-';

    /** @var array<int,array{nama:string,status:string,batch:int|null}> */
    public array $daftarMigrationTampil = [];

    public int $totalHasilMigration = 0;
    public int $totalHalamanMigration = 1;

    public string $outputMigration = '';

    public bool $showMigrationDetail = false;
    public bool $migrationSudahDijalankan = false;
    public bool $cacheSudahDibersihkan = false;

    // ─── Mount ───────────────────────────────────────────────────────────────

    public function mount(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pemeliharaan', 'dapat_lihat')) {
            abort(403);
        }

        $this->muatPreferensiMigrationDariSession();

        $this->muatRingkasanMigration();
    }

    // ─── Computed ─────────────────────────────────────────────────────────────

    public function with(): array
    {
        $service = app(PemeliharaanService::class);

        return [
            'infoVersi'      => $service->infoVersi(),
            'ringkasanStatus' => $service->ringkasanStatus(),
            'jumlahPending'  => $service->jumlahPendingMigration(),
        ];
    }

    // ─── Actions ─────────────────────────────────────────────────────────────

    public function lihatMigration(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pemeliharaan', 'dapat_lihat')) {
            abort(403);
        }

        $data = app(PemeliharaanService::class)->dataMigrationEfektif($this->filterMigration, $this->migrationMaksRun);
        $this->daftarMigration = $data['daftar'];
        $this->ringkasanMigration = $data['ringkasan'];
        $this->migrationRunDibatasi = $data['dibatasi_run'];
        $this->migrationRefreshedAt = now()->format('d/m/Y H:i:s');
        $this->sinkronkanTabelMigration();
        $this->showMigrationDetail = true;
    }

    public function setFilterMigration(string $filter): void
    {
        if (! in_array($filter, ['pending', 'semua'], true)) {
            return;
        }

        $this->filterMigration = $filter;
        $this->terapkanSortDefaultPerFilter($filter);
        $this->halamanMigration = 1;
        $this->simpanPreferensiMigrationKeSession();

        if ($this->showMigrationDetail) {
            $this->lihatMigration();
        }
    }

    public function updatedSearchMigration(): void
    {
        $this->halamanMigration = 1;
        $this->sinkronkanTabelMigration();
    }

    public function updatedPerPageMigration(): void
    {
        if (! in_array($this->perPageMigration, [10, 20, 50, 100], true)) {
            $this->perPageMigration = 20;
        }

        $this->halamanMigration = 1;
        $this->simpanPreferensiMigrationKeSession();
        $this->sinkronkanTabelMigration();
    }

    public function halamanMigrationBerikutnya(): void
    {
        if ($this->halamanMigration < $this->totalHalamanMigration) {
            $this->halamanMigration++;
            $this->sinkronkanTabelMigration();
        }
    }

    public function halamanMigrationSebelumnya(): void
    {
        if ($this->halamanMigration > 1) {
            $this->halamanMigration--;
            $this->sinkronkanTabelMigration();
        }
    }

    public function urutkanMigration(string $kolom): void
    {
        if (! in_array($kolom, ['nama', 'batch', 'status'], true)) {
            return;
        }

        if ($this->sortMigrationBy === $kolom) {
            $this->sortMigrationDir = $this->sortMigrationDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortMigrationBy = $kolom;
            $this->sortMigrationDir = 'asc';
        }

        $this->halamanMigration = 1;
        $this->simpanPreferensiMigrationKeSession();
        $this->sinkronkanTabelMigration();
    }

    public function resetPengaturanMigration(): void
    {
        $this->filterMigration = 'pending';
        $this->searchMigration = '';
        $this->perPageMigration = 20;
        $this->halamanMigration = 1;

        $this->terapkanSortDefaultPerFilter($this->filterMigration);
        $this->simpanPreferensiMigrationKeSession();

        if ($this->showMigrationDetail) {
            $this->lihatMigration();
            return;
        }

        $this->sinkronkanTabelMigration();
    }

    public function salinPerintahMigrationStatus(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pemeliharaan', 'dapat_lihat')) {
            abort(403);
        }

        $this->dispatch('salin-teks', teks: 'php artisan migrate:status', pesan: __('messages.pemeliharaan_copy_command_success'));
    }

    public function exportMigrationCsv()
    {
        if (! auth()->user()?->bisaMenu('/admin/pemeliharaan', 'dapat_lihat')) {
            abort(403);
        }

        $data = app(PemeliharaanService::class)->dataMigrationEfektif($this->filterMigration, 0);
        $rows = $this->filterDataMigration(collect($data['daftar']))->values();

        $namaFile = 'daftar-migration-' . now()->format('Ymd_His') . '.csv';

        app(LogAktivitasService::class)->catatManual(
            __('messages.pemeliharaan_module_name'),
            __('messages.pemeliharaan_log_export_migration'),
            '/admin/pemeliharaan',
            ['aksi' => 'export_migration_csv', 'jumlah' => $rows->count()]
        );

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['nama_migration', 'status', 'batch']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['nama'],
                    $row['status'],
                    $row['batch'] ?? '',
                ]);
            }

            fclose($handle);
        }, $namaFile, ['Content-Type' => 'text/csv']);
    }

    public function jalankanMigration(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pemeliharaan', 'dapat_ubah')) {
            abort(403);
        }

        $this->outputMigration = app(PemeliharaanService::class)->jalankanMigration();
        $this->migrationSudahDijalankan = true;

        // Re-load daftar migration setelah dijalankan
        $this->lihatMigration();

        app(LogAktivitasService::class)->catatManual(
            __('messages.pemeliharaan_module_name'),
            __('messages.pemeliharaan_log_migration'),
            '/admin/pemeliharaan',
            ['aksi' => 'jalankan_migration', 'output' => substr($this->outputMigration, 0, 500)]
        );

        $this->dispatch('notifikasi', jenis: 'success', pesan: __('messages.pemeliharaan_migration_success'));
    }

    public function bersihkanCache(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pemeliharaan', 'dapat_ubah')) {
            abort(403);
        }

        $this->hasilCache = app(PemeliharaanService::class)->bersihkanCache();
        $this->cacheSudahDibersihkan = true;

        app(LogAktivitasService::class)->catatManual(
            __('messages.pemeliharaan_module_name'),
            __('messages.pemeliharaan_log_clear_cache'),
            '/admin/pemeliharaan',
            ['aksi' => 'bersihkan_cache']
        );

        $this->dispatch('notifikasi', jenis: 'success', pesan: __('messages.pemeliharaan_cache_success'));
    }

    public function tutupDetailMigration(): void
    {
        $this->showMigrationDetail = false;
    }

    private function muatRingkasanMigration(): void
    {
        $data = app(PemeliharaanService::class)->dataMigrationEfektif('semua', $this->migrationMaksRun);
        $this->ringkasanMigration = $data['ringkasan'];
    }

    private function sinkronkanTabelMigration(): void
    {
        $filtered = $this->filterDataMigration(collect($this->daftarMigration));
        $sorted = $this->urutkanDataMigration($filtered)->values();

        $this->totalHasilMigration = $sorted->count();
        $this->totalHalamanMigration = max(1, (int) ceil($this->totalHasilMigration / $this->perPageMigration));

        if ($this->halamanMigration > $this->totalHalamanMigration) {
            $this->halamanMigration = $this->totalHalamanMigration;
        }

        $offset = ($this->halamanMigration - 1) * $this->perPageMigration;
        $this->daftarMigrationTampil = $sorted
            ->slice($offset, $this->perPageMigration)
            ->values()
            ->all();
    }

    private function filterDataMigration(Collection $items): Collection
    {
        $keyword = trim(mb_strtolower($this->searchMigration));

        return $items->filter(function ($item) use ($keyword) {
            if ($keyword === '') {
                return true;
            }

            return str_contains(mb_strtolower((string) ($item['nama'] ?? '')), $keyword)
                || str_contains(mb_strtolower((string) ($item['status'] ?? '')), $keyword)
                || str_contains((string) ($item['batch'] ?? ''), $keyword);
        });
    }

    private function urutkanDataMigration(Collection $items): Collection
    {
        $kolom = $this->sortMigrationBy;
        $desc = $this->sortMigrationDir === 'desc';

        return $items->sortBy(function ($item) use ($kolom) {
            if ($kolom === 'batch') {
                return (int) ($item['batch'] ?? 0);
            }

            if ($kolom === 'status') {
                return ($item['status'] ?? 'pending') === 'pending' ? 0 : 1;
            }

            return (string) ($item['nama'] ?? '');
        }, SORT_NATURAL, $desc);
    }

    private function terapkanSortDefaultPerFilter(string $filter): void
    {
        if ($filter === 'semua') {
            $this->sortMigrationBy = 'batch';
            $this->sortMigrationDir = 'desc';
            return;
        }

        $this->sortMigrationBy = 'nama';
        $this->sortMigrationDir = 'asc';
    }

    private function simpanPreferensiMigrationKeSession(): void
    {
        session()->put($this->kunciSessionMigration('filter'), $this->filterMigration);
        session()->put($this->kunciSessionMigration('per_page'), $this->perPageMigration);
        session()->put($this->kunciSessionMigration('sort_by'), $this->sortMigrationBy);
        session()->put($this->kunciSessionMigration('sort_dir'), $this->sortMigrationDir);
    }

    private function muatPreferensiMigrationDariSession(): void
    {
        $filter = (string) session()->get($this->kunciSessionMigration('filter'), 'pending');
        $perPage = (int) session()->get($this->kunciSessionMigration('per_page'), 20);
        $sortBy = (string) session()->get($this->kunciSessionMigration('sort_by'), '');
        $sortDir = (string) session()->get($this->kunciSessionMigration('sort_dir'), '');

        if (! in_array($filter, ['pending', 'semua'], true)) {
            $filter = 'pending';
        }

        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $this->filterMigration = $filter;
        $this->perPageMigration = $perPage;

        if (in_array($sortBy, ['nama', 'batch', 'status'], true) && in_array($sortDir, ['asc', 'desc'], true)) {
            $this->sortMigrationBy = $sortBy;
            $this->sortMigrationDir = $sortDir;
            return;
        }

        $this->terapkanSortDefaultPerFilter($this->filterMigration);
    }

    private function kunciSessionMigration(string $suffix): string
    {
        $userId = (int) (auth()->id() ?? 0);
        return "pemeliharaan.migration.{$userId}.{$suffix}";
    }
};
?>

@section('title', __('messages.pemeliharaan_page_title'))

<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">{{ __('messages.pemeliharaan_page_heading') }}</h4>
            <p class="text-muted mb-0 small">{{ __('messages.pemeliharaan_page_subheading') }}</p>
        </div>
    </div>

    {{-- Notifikasi event --}}
    <div
        x-data="{ show: false, jenis: 'success', pesan: '' }"
        x-on:notifikasi.window="
            pesan = $event.detail.pesan;
            jenis = $event.detail.jenis;
            show = true;
            setTimeout(() => show = false, 4000);
        "
        x-on:salin-teks.window="
            navigator.clipboard.writeText($event.detail.teks).then(() => {
                window.dispatchEvent(new CustomEvent('notifikasi', {
                    detail: { jenis: 'success', pesan: ($event.detail.pesan || 'Perintah disalin.') }
                }));
            });
        "
        x-show="show"
        x-transition
        class="alert mb-3"
        :class="jenis === 'success' ? 'alert-success' : 'alert-danger'"
        style="display:none"
    >
        <i class="bx me-1" :class="jenis === 'success' ? 'bx-check-circle' : 'bx-error-circle'"></i>
        <span x-text="pesan"></span>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         BARIS 1 — Info Versi & Status Sistem
    ════════════════════════════════════════════════════════ --}}
    <div class="row g-4 mb-4">

        {{-- Kartu Versi --}}
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bx bx-code-block text-primary fs-5"></i>
                    <h5 class="card-title mb-0">{{ __('messages.pemeliharaan_version_title') }}</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <tbody>
                            <tr>
                                <td class="ps-4 text-muted" style="width:40%">Laravel</td>
                                <td><span class="badge bg-label-primary">v{{ $infoVersi['laravel'] }}</span></td>
                            </tr>
                            <tr>
                                <td class="ps-4 text-muted">PHP</td>
                                <td><span class="badge bg-label-info">v{{ $infoVersi['php'] }}</span></td>
                            </tr>
                            <tr>
                                <td class="ps-4 text-muted">Livewire / Volt</td>
                                <td><span class="badge bg-label-success">v{{ $infoVersi['livewire'] }}</span></td>
                            </tr>
                            <tr>
                                <td class="ps-4 text-muted">Bootstrap</td>
                                <td><span class="badge bg-label-warning">v{{ $infoVersi['bootstrap'] }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Kartu Status Sistem --}}
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bx bx-pulse text-primary fs-5"></i>
                    <h5 class="card-title mb-0">{{ __('messages.pemeliharaan_status_title') }}</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <tbody>
                            <tr>
                                <td class="ps-4 text-muted" style="width:55%">{{ __('messages.pemeliharaan_db_connection') }}</td>
                                <td>
                                    @if($ringkasanStatus['db_terhubung'])
                                        <span class="badge bg-label-success"><i class="bx bx-check me-1"></i>{{ __('messages.pemeliharaan_connected') }}</span>
                                    @else
                                        <span class="badge bg-label-danger"><i class="bx bx-x me-1"></i>{{ __('messages.pemeliharaan_not_connected') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 text-muted">{{ __('messages.pemeliharaan_pending_migration') }}</td>
                                <td>
                                    @if($jumlahPending === 0)
                                        <span class="badge bg-label-success">{{ __('messages.pemeliharaan_up_to_date') }}</span>
                                    @else
                                        <span class="badge bg-label-warning">{{ $jumlahPending }} {{ __('messages.pemeliharaan_pending') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 text-muted">{{ __('messages.pemeliharaan_storage_write') }}</td>
                                <td>
                                    @if($ringkasanStatus['storage_writable'])
                                        <span class="badge bg-label-success"><i class="bx bx-check me-1"></i>{{ __('messages.pemeliharaan_writable') }}</span>
                                    @else
                                        <span class="badge bg-label-danger"><i class="bx bx-lock me-1"></i>{{ __('messages.pemeliharaan_not_writable') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 text-muted">{{ __('messages.pemeliharaan_environment') }}</td>
                                <td>
                                    <span class="badge {{ $ringkasanStatus['env'] === 'production' ? 'bg-label-danger' : 'bg-label-info' }}">
                                        {{ $ringkasanStatus['env'] }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 text-muted">{{ __('messages.pemeliharaan_debug_mode') }}</td>
                                <td>
                                    @if($ringkasanStatus['debug_mode'])
                                        <span class="badge bg-label-warning"><i class="bx bx-bug me-1"></i>ON</span>
                                    @else
                                        <span class="badge bg-label-secondary">OFF</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         BARIS 2 — Aksi Pemeliharaan
    ════════════════════════════════════════════════════════ --}}
    <div class="row g-4 mb-4">

        {{-- Kartu Database Migration --}}
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bx bx-data text-primary fs-5"></i>
                    <h5 class="card-title mb-0">{{ __('messages.pemeliharaan_migration_title') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('messages.pemeliharaan_migration_desc') }}</p>

                    @if($migrationSudahDijalankan && $outputMigration)
                        <div class="alert alert-success py-2 px-3 mb-3 small">
                            <pre class="mb-0" style="white-space:pre-wrap;font-size:0.78rem">{{ $outputMigration }}</pre>
                        </div>
                    @endif

                    @if($jumlahPending > 0)
                        <div class="alert alert-warning py-2 px-3 mb-3 small">
                            <i class="bx bx-info-circle me-1"></i>
                            {{ $jumlahPending }} {{ __('messages.pemeliharaan_migration_pending_info') }}
                        </div>
                    @endif

                    <div class="d-flex gap-2 flex-wrap">
                        <button
                            wire:click="setFilterMigration('pending')"
                            class="btn btn-sm {{ $filterMigration === 'pending' ? 'btn-outline-primary' : 'btn-outline-secondary' }}"
                        >
                            {{ __('messages.pemeliharaan_migration_filter_pending') }}
                        </button>

                        <button
                            wire:click="setFilterMigration('semua')"
                            class="btn btn-sm {{ $filterMigration === 'semua' ? 'btn-outline-primary' : 'btn-outline-secondary' }}"
                        >
                            {{ __('messages.pemeliharaan_migration_filter_all') }}
                        </button>

                        <button
                            wire:click="resetPengaturanMigration"
                            class="btn btn-sm btn-outline-secondary"
                        >
                            <i class="bx bx-reset me-1"></i>{{ __('messages.pemeliharaan_migration_reset_btn') }}
                        </button>

                        @if(auth()->user()?->bisaMenu('/admin/pemeliharaan', 'dapat_lihat'))
                            <button
                                wire:click="lihatMigration"
                                wire:loading.attr="disabled"
                                wire:target="lihatMigration"
                                class="btn btn-outline-secondary btn-sm"
                            >
                                <span wire:loading.remove wire:target="lihatMigration">
                                    <i class="bx bx-list-ul me-1"></i>{{ __('messages.pemeliharaan_migration_list_btn') }}
                                </span>
                                <span wire:loading wire:target="lihatMigration" style="display:none">
                                    <span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.pemeliharaan_loading') }}
                                </span>
                            </button>

                            <button
                                wire:click="salinPerintahMigrationStatus"
                                class="btn btn-outline-secondary btn-sm"
                            >
                                <i class="bx bx-copy me-1"></i>{{ __('messages.pemeliharaan_copy_command_btn') }}
                            </button>

                            <button
                                wire:click="exportMigrationCsv"
                                wire:loading.attr="disabled"
                                wire:target="exportMigrationCsv"
                                class="btn btn-outline-success btn-sm"
                            >
                                <span wire:loading.remove wire:target="exportMigrationCsv">
                                    <i class="bx bx-download me-1"></i>{{ __('messages.pemeliharaan_migration_export_btn') }}
                                </span>
                                <span wire:loading wire:target="exportMigrationCsv" style="display:none">
                                    <span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.pemeliharaan_running') }}
                                </span>
                            </button>
                        @endif

                        @if(auth()->user()?->bisaMenu('/admin/pemeliharaan', 'dapat_ubah'))
                            <button
                                type="button"
                                @click="Swal.fire({
                                    title: 'Konfirmasi',
                                    text: '{{ __('messages.pemeliharaan_migration_confirm') }}',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Ya, Lanjutkan',
                                    cancelButtonText: 'Batal',
                                }).then(r => r.isConfirmed && $wire.jalankanMigration())"
                                wire:loading.attr="disabled"
                                wire:target="jalankanMigration"
                                class="btn btn-primary btn-sm"
                            >
                                <span wire:loading.remove wire:target="jalankanMigration">
                                    <i class="bx bx-play me-1"></i>{{ __('messages.pemeliharaan_migration_run_btn') }}
                                </span>
                                <span wire:loading wire:target="jalankanMigration" style="display:none">
                                    <span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.pemeliharaan_running') }}
                                </span>
                            </button>
                        @endif
                    </div>

                    {{-- Detail daftar migration --}}
                    @if($showMigrationDetail)
                        <div class="mt-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <small class="text-muted fw-semibold">{{ __('messages.pemeliharaan_migration_detail') }}</small>
                                <button wire:click="tutupDetailMigration" class="btn btn-sm btn-icon btn-text-secondary p-0">
                                    <i class="bx bx-x fs-5"></i>
                                </button>
                            </div>

                            <small class="text-muted d-block mb-2">
                                {{ __('messages.pemeliharaan_migration_last_refresh', ['waktu' => $migrationRefreshedAt]) }}
                            </small>

                            <div class="d-flex gap-2 flex-wrap mb-2">
                                <span class="badge bg-label-primary">{{ __('messages.pemeliharaan_migration_total') }}: {{ $ringkasanMigration['total'] }}</span>
                                <span class="badge bg-label-success">run: {{ $ringkasanMigration['run'] }}</span>
                                <span class="badge bg-label-warning">pending: {{ $ringkasanMigration['pending'] }}</span>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-12 col-md-8">
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        wire:model.live.debounce.300ms="searchMigration"
                                        placeholder="{{ __('messages.pemeliharaan_migration_search_placeholder') }}"
                                    >
                                </div>
                                <div class="col-6 col-md-2">
                                    <select class="form-select form-select-sm" wire:model.live="perPageMigration">
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2 d-flex align-items-center">
                                    <small class="text-muted">{{ __('messages.pemeliharaan_migration_total_filtered') }}: {{ $totalHasilMigration }}</small>
                                </div>
                            </div>

                            @if($migrationRunDibatasi && $filterMigration === 'semua')
                                <div class="alert alert-info py-2 px-3 mb-2 small">
                                    <i class="bx bx-info-circle me-1"></i>
                                    {{ __('messages.pemeliharaan_migration_limited_info', ['maks' => $migrationMaksRun]) }}
                                </div>
                            @endif

                            <div class="table-responsive" style="max-height:320px;overflow-y:auto">
                                <table class="table table-sm table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="small">
                                                <button type="button" wire:click="urutkanMigration('nama')" class="btn btn-sm btn-text-secondary p-0">
                                                    {{ __('messages.pemeliharaan_migration_file') }}
                                                    <i class="bx {{ $sortMigrationBy === 'nama' && $sortMigrationDir === 'asc' ? 'bx-chevron-up' : 'bx-chevron-down' }}"></i>
                                                </button>
                                            </th>
                                            <th class="small text-center" style="width:90px">
                                                <button type="button" wire:click="urutkanMigration('batch')" class="btn btn-sm btn-text-secondary p-0">
                                                    Batch
                                                    <i class="bx {{ $sortMigrationBy === 'batch' && $sortMigrationDir === 'asc' ? 'bx-chevron-up' : 'bx-chevron-down' }}"></i>
                                                </button>
                                            </th>
                                            <th class="small text-center" style="width:120px">
                                                <button type="button" wire:click="urutkanMigration('status')" class="btn btn-sm btn-text-secondary p-0">
                                                    {{ __('messages.status') }}
                                                    <i class="bx {{ $sortMigrationBy === 'status' && $sortMigrationDir === 'asc' ? 'bx-chevron-up' : 'bx-chevron-down' }}"></i>
                                                </button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($daftarMigrationTampil as $m)
                                            <tr>
                                                <td class="small text-break" style="font-size:0.73rem;font-family:monospace">{{ $m['nama'] }}</td>
                                                <td class="text-center small">{{ $m['batch'] ?? '-' }}</td>
                                                <td class="text-center">
                                                    @if($m['status'] === 'run')
                                                        <span class="badge bg-label-success" style="font-size:0.65rem">run</span>
                                                    @else
                                                        <span class="badge bg-label-warning" style="font-size:0.65rem">pending</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($totalHalamanMigration > 1)
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">
                                        {{ __('messages.pemeliharaan_migration_page_info', ['page' => $halamanMigration, 'total' => $totalHalamanMigration]) }}
                                    </small>
                                    <div class="btn-group">
                                        <button
                                            class="btn btn-sm btn-outline-secondary"
                                            wire:click="halamanMigrationSebelumnya"
                                            wire:loading.attr="disabled"
                                            wire:target="halamanMigrationSebelumnya"
                                            @disabled($halamanMigration <= 1)
                                        >
                                            <i class="bx bx-chevron-left"></i>
                                        </button>
                                        <button
                                            class="btn btn-sm btn-outline-secondary"
                                            wire:click="halamanMigrationBerikutnya"
                                            wire:loading.attr="disabled"
                                            wire:target="halamanMigrationBerikutnya"
                                            @disabled($halamanMigration >= $totalHalamanMigration)
                                        >
                                            <i class="bx bx-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($showMigrationDetail && !count($daftarMigrationTampil))
                        <div class="alert alert-success py-2 px-3 mt-3 small">
                            <i class="bx bx-check-circle me-1"></i>
                            {{ __('messages.pemeliharaan_migration_empty') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kartu Bersihkan Cache --}}
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bx bx-refresh text-primary fs-5"></i>
                    <h5 class="card-title mb-0">{{ __('messages.pemeliharaan_cache_title') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('messages.pemeliharaan_cache_desc') }}</p>

                    @if(auth()->user()?->bisaMenu('/admin/pemeliharaan', 'dapat_ubah'))
                        <button
                            type="button"
                            @click="Swal.fire({
                                title: 'Konfirmasi',
                                text: '{{ __('messages.pemeliharaan_cache_confirm') }}',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Lanjutkan',
                                cancelButtonText: 'Batal',
                            }).then(r => r.isConfirmed && $wire.bersihkanCache())"
                            wire:loading.attr="disabled"
                            wire:target="bersihkanCache"
                            class="btn btn-primary btn-sm mb-3"
                        >
                            <span wire:loading.remove wire:target="bersihkanCache">
                                <i class="bx bx-trash me-1"></i>{{ __('messages.pemeliharaan_cache_clear_btn') }}
                            </span>
                            <span wire:loading wire:target="bersihkanCache" style="display:none">
                                <span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.pemeliharaan_running') }}
                            </span>
                        </button>
                    @endif

                    @if($cacheSudahDibersihkan && count($hasilCache))
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small">{{ __('messages.pemeliharaan_cache_label') }}</th>
                                        <th class="small" style="width:100px">{{ __('messages.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hasilCache as $h)
                                        <tr>
                                            <td>
                                                <div class="small">{{ $h['label'] }}</div>
                                                <div class="text-muted" style="font-size:0.7rem;font-family:monospace">{{ $h['perintah'] }}</div>
                                                @if($h['output'] && $h['output'] !== 'Selesai.')
                                                    <div class="text-muted" style="font-size:0.7rem">{{ $h['output'] }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($h['sukses'])
                                                    <span class="badge bg-label-success"><i class="bx bx-check me-1"></i>OK</span>
                                                @else
                                                    <span class="badge bg-label-danger"><i class="bx bx-x me-1"></i>Gagal</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         BARIS 3 — Panduan Update Manual (Informasi)
    ════════════════════════════════════════════════════════ --}}
    <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="bx bx-book-open text-primary fs-5"></i>
            <h5 class="card-title mb-0">{{ __('messages.pemeliharaan_guide_title') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">

                {{-- Laravel --}}
                <div class="col-12 col-md-4">
                    <div class="d-flex gap-2 align-items-start mb-2">
                        <span class="badge bg-label-primary p-2"><i class="bx bxl-laravel fs-5"></i></span>
                        <div>
                            <div class="fw-semibold small">Update Laravel</div>
                            <div class="text-muted" style="font-size:0.78rem">{{ __('messages.pemeliharaan_guide_laravel_desc') }}</div>
                        </div>
                    </div>
                    <pre class="bg-light rounded p-2 mb-0 small" style="font-size:0.72rem;white-space:pre-wrap">composer update laravel/framework
php artisan migrate --force
php artisan optimize:clear</pre>
                </div>

                {{-- Bootstrap --}}
                <div class="col-12 col-md-4">
                    <div class="d-flex gap-2 align-items-start mb-2">
                        <span class="badge bg-label-warning p-2"><i class="bx bxl-bootstrap fs-5"></i></span>
                        <div>
                            <div class="fw-semibold small">Update Bootstrap / Sneat</div>
                            <div class="text-muted" style="font-size:0.78rem">{{ __('messages.pemeliharaan_guide_bootstrap_desc') }}</div>
                        </div>
                    </div>
                    <pre class="bg-light rounded p-2 mb-0 small" style="font-size:0.72rem;white-space:pre-wrap">npm update bootstrap
npm run build</pre>
                </div>

                {{-- Semua Paket --}}
                <div class="col-12 col-md-4">
                    <div class="d-flex gap-2 align-items-start mb-2">
                        <span class="badge bg-label-success p-2"><i class="bx bx-package fs-5"></i></span>
                        <div>
                            <div class="fw-semibold small">{{ __('messages.pemeliharaan_guide_all_title') }}</div>
                            <div class="text-muted" style="font-size:0.78rem">{{ __('messages.pemeliharaan_guide_all_desc') }}</div>
                        </div>
                    </div>
                    <pre class="bg-light rounded p-2 mb-0 small" style="font-size:0.72rem;white-space:pre-wrap">composer update
npm update
npm run build
php artisan migrate --force
php artisan optimize:clear</pre>
                </div>

            </div>
        </div>
    </div>

</div>
