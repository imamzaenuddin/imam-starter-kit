<?php

use App\Models\LogAktivitas;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $filterModul = '';
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
        $this->reset(['search', 'filterModul', 'dariTanggal', 'sampaiTanggal']);
        $this->resetPage();
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

            'logs' => LogAktivitas::query()
                ->with('user')
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('aktivitas', 'like', '%' . $this->search . '%')
                          ->orWhere('modul', 'like', '%' . $this->search . '%')
                          ->orWhere('url', 'like', '%' . $this->search . '%')
                          ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%' . $this->search . '%'));
                    });
                })
                ->when($this->filterModul, fn ($q) => $q->where('modul', $this->filterModul))
                ->when($this->dariTanggal, fn ($q) => $q->whereDate('created_at', '>=', $this->dariTanggal))
                ->when($this->sampaiTanggal, fn ($q) => $q->whereDate('created_at', '<=', $this->sampaiTanggal))
                ->latest()
                ->paginate(15),
        ];
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
                <div class="col-md-4">
                    <label class="form-label">{{ __('messages.search') }}</label>
                    <input wire:model.live.debounce.300ms="search" type="search" class="form-control"
                           placeholder="{{ __('messages.search_activity_placeholder') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('messages.module') }}</label>
                    <select wire:model.live="filterModul" class="form-select">
                        <option value="">{{ __('messages.all_modules') }}</option>
                        @foreach ($modulList as $modul)
                            <option value="{{ $modul }}">{{ $modul }}</option>
                        @endforeach
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
                        <th>IP</th>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">{{ __('messages.no_activity_log_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $logs->links() }}</div>
    </div>
</div>
