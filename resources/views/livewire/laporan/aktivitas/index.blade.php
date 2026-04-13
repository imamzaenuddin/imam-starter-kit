<?php

use App\Models\LogAktivitas;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $filterModul = '';
    public string $filterUser = '';
    public string $filterMetode = '';
    public string $filterIp = '';
    public string $filterStatus = '';
    public string $dariTanggal = '';
    public string $sampaiTanggal = '';

    public function mount(): void
    {
        if (! auth()->user()?->bisaMenu('/laporan/aktivitas', 'dapat_lihat')) {
            abort(403);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterModul(): void
    {
        $this->resetPage();
    }

    public function updatingFilterUser(): void
    {
        $this->resetPage();
    }

    public function updatingFilterMetode(): void
    {
        $this->resetPage();
    }

    public function updatingFilterIp(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingDariTanggal(): void
    {
        $this->resetPage();
    }

    public function updatingSampaiTanggal(): void
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset([
            'search',
            'filterModul',
            'filterUser',
            'filterMetode',
            'filterIp',
            'filterStatus',
            'dariTanggal',
            'sampaiTanggal',
        ]);
        $this->resetPage();
    }

    public function exportCsv()
    {
        if (! auth()->user()?->bisaMenu('/laporan/aktivitas', 'dapat_lihat')) {
            abort(403);
        }

        $filename = __('messages.activity_log_export_filename_prefix') . '_' . now()->format('Ymd_His') . '.csv';
        $rows = $this->queryLogAktivitas()->limit(5000)->get();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                __('messages.activity_log_csv_time'),
                __('messages.activity_log_csv_user_name'),
                __('messages.activity_log_csv_user_email'),
                __('messages.activity_log_csv_module'),
                __('messages.activity_log_csv_activity'),
                __('messages.activity_log_csv_method'),
                __('messages.activity_log_csv_url'),
                __('messages.activity_log_csv_ip_address'),
                __('messages.activity_log_csv_status_code'),
            ]);

            foreach ($rows as $log) {
                fputcsv($handle, [
                    $log->created_at?->format('d/m/Y H:i:s'),
                    $log->user?->name ?? 'Sistem',
                    $log->user?->email ?? '-',
                    $log->modul ?? '-',
                    $log->aktivitas,
                    strtoupper((string) ($log->metode ?? '-')),
                    $log->url ?? '-',
                    $log->ip_address ?? '-',
                    (string) data_get($log->metadata, 'status_code', '-'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function with(): array
    {
        return [
            'modulList' => LogAktivitas::query()
                ->select('modul')
                ->whereNotNull('modul')
                ->distinct()
                ->orderBy('modul')
                ->pluck('modul'),

            'userList' => User::query()
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),

            'logs' => $this->queryLogAktivitas()->paginate(15),
        ];
    }

    private function queryLogAktivitas()
    {
        return LogAktivitas::query()
            ->with('user')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('aktivitas', 'like', '%' . $this->search . '%')
                        ->orWhere('modul', 'like', '%' . $this->search . '%')
                        ->orWhere('url', 'like', '%' . $this->search . '%')
                        ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($uq) {
                            $uq->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filterModul, fn ($q) => $q->where('modul', $this->filterModul))
            ->when($this->filterUser, fn ($q) => $q->where('user_id', (int) $this->filterUser))
            ->when($this->filterMetode, fn ($q) => $q->where('metode', strtoupper($this->filterMetode)))
            ->when($this->filterIp, fn ($q) => $q->where('ip_address', 'like', '%' . $this->filterIp . '%'))
            ->when($this->filterStatus, fn ($q) => $q->where('metadata->status_code', (int) $this->filterStatus))
            ->when($this->dariTanggal, fn ($q) => $q->whereDate('created_at', '>=', $this->dariTanggal))
            ->when($this->sampaiTanggal, fn ($q) => $q->whereDate('created_at', '<=', $this->sampaiTanggal))
            ->latest();
    }
};
?>
@section('title', __('messages.activity_log_title'))

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ __('messages.activity_log_heading') }}</h4>
            <p class="text-muted mb-0">{{ __('messages.activity_log_subheading') }}</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">{{ __('messages.search') }}</label>
                    <input wire:model.live.debounce.300ms="search" type="search" class="form-control"
                           placeholder="{{ __('messages.search_activity_placeholder') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('messages.module') }}</label>
                    <select wire:model.live="filterModul" class="form-select">
                        <option value="">{{ __('messages.all_modules') }}</option>
                        @foreach ($modulList as $modul)
                            <option value="{{ $modul }}">{{ $modul }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('messages.activity_log_filter_user') }}</label>
                    <select wire:model.live="filterUser" class="form-select">
                        <option value="">{{ __('messages.activity_log_all_users') }}</option>
                        @foreach ($userList as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">{{ __('messages.activity_log_filter_method') }}</label>
                    <select wire:model.live="filterMetode" class="form-select">
                        <option value="">{{ __('messages.activity_log_all') }}</option>
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="PATCH">PATCH</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('messages.activity_log_filter_ip') }}</label>
                    <input wire:model.live.debounce.300ms="filterIp" type="text" class="form-control" placeholder="{{ __('messages.activity_log_ip_placeholder') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('messages.activity_log_filter_status') }}</label>
                    <select wire:model.live="filterStatus" class="form-select">
                        <option value="">{{ __('messages.activity_log_all') }}</option>
                        <option value="200">200</option>
                        <option value="201">201</option>
                        <option value="204">204</option>
                        <option value="302">302</option>
                        <option value="401">401</option>
                        <option value="403">403</option>
                        <option value="404">404</option>
                        <option value="500">500</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('messages.from_date') }}</label>
                    <input wire:model.live="dariTanggal" type="date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('messages.until_date') }}</label>
                    <input wire:model.live="sampaiTanggal" type="date" class="form-control">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary w-100" wire:click="resetFilter">
                        <i class="bx bx-reset"></i>
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100" wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv">
                        <span wire:loading.remove wire:target="exportCsv"><i class="bx bx-download me-1"></i>{{ __('messages.activity_log_export_csv') }}</span>
                        <span wire:loading wire:target="exportCsv" style="display:none">{{ __('messages.processing') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.time') }}</th>
                        <th>{{ __('messages.user') }}</th>
                        <th>{{ __('messages.module') }}</th>
                        <th>{{ __('messages.activity') }}</th>
                        <th>{{ __('messages.request') }}</th>
                        <th>{{ __('messages.activity_log_filter_ip') }}</th>
                        <th>{{ __('messages.activity_log_filter_status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $logs->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="fw-semibold">{{ $log->created_at?->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $log->created_at?->format('H:i:s') }}</small>
                            </td>
                            <td>
                                <div>{{ $log->user?->name ?? __('messages.system') }}</div>
                                <small class="text-muted">{{ $log->user?->email ?? '-' }}</small>
                            </td>
                            <td>
                                @if ($log->modul)
                                    <span class="badge bg-label-primary">{{ $log->modul }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $log->aktivitas }}</td>
                            <td>
                                <div>
                                    @if ($log->metode)
                                        <span class="badge bg-label-info me-1">{{ strtoupper($log->metode) }}</span>
                                    @endif
                                    <small class="text-muted">{{ $log->url ?? '-' }}</small>
                                </div>
                            </td>
                            <td><small class="text-muted">{{ $log->ip_address ?? '-' }}</small></td>
                            <td>
                                @php $statusCode = data_get($log->metadata, 'status_code'); @endphp
                                @if ($statusCode)
                                    <span class="badge bg-label-secondary">{{ $statusCode }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">{{ __('messages.no_activity_log_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $logs->links() }}</div>
    </div>
</div>
