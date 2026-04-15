<?php

use App\Models\LoginAttempt;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $tanggalDari = '';
    public string $tanggalSampai = '';

    public function mount(): void
    {
        if (! auth()->user()?->bisaMenu('/laporan/login-attempts', 'dapat_lihat')) {
            abort(403);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTanggalDari(): void
    {
        $this->resetPage();
    }

    public function updatedTanggalSampai(): void
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset(['search', 'statusFilter', 'tanggalDari', 'tanggalSampai']);
        $this->resetPage();
    }

    public function with(): array
    {
        $query = LoginAttempt::query()
            ->with('user:id,name,email')
            ->when($this->search, function ($q) {
                $keyword = trim($this->search);

                $q->where(function ($sq) use ($keyword) {
                    $sq->where('email', 'like', '%' . $keyword . '%')
                        ->orWhere('ip_address', 'like', '%' . $keyword . '%')
                        ->orWhere('alasan', 'like', '%' . $keyword . '%')
                        ->orWhereHas('user', function ($uq) use ($keyword) {
                            $uq->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('email', 'like', '%' . $keyword . '%');
                        });
                });
            })
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->tanggalDari !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->tanggalDari))
            ->when($this->tanggalSampai !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->tanggalSampai))
            ->latest();

        return [
            'loginAttempts' => $query->paginate((int) config('app_runtime.pagination_default', 10)),
            'statusList' => [
                'sukses' => __('messages.login_attempt_status_success'),
                'gagal' => __('messages.login_attempt_status_failed'),
                'lockout' => __('messages.login_attempt_status_lockout'),
            ],
        ];
    }
};
?>

@section('title', __('messages.login_attempt_title'))

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ __('messages.login_attempt_heading') }}</h4>
            <p class="text-muted mb-0">{{ __('messages.login_attempt_subheading') }}</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ __('messages.search') }}</label>
                    <input type="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('messages.login_attempt_search_placeholder') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('messages.status') }}</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">{{ __('messages.all') }}</option>
                        @foreach ($statusList as $kode => $label)
                            <option value="{{ $kode }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('messages.date_from') }}</label>
                    <input type="date" wire:model.live="tanggalDari" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('messages.date_to') }}</label>
                    <input type="date" wire:model.live="tanggalSampai" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary w-100" wire:click="resetFilter">{{ __('messages.reset') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('messages.time') }}</th>
                        <th>{{ __('messages.user') }}</th>
                        <th>Email</th>
                        <th>IP</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.description') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($loginAttempts as $item)
                        <tr>
                            <td>{{ $item->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $item->user?->name ?: '-' }}</td>
                            <td>{{ $item->email ?: '-' }}</td>
                            <td>{{ $item->ip_address ?: '-' }}</td>
                            <td>
                                @if ($item->status === 'sukses')
                                    <span class="badge bg-label-success">{{ __('messages.login_attempt_status_success') }}</span>
                                @elseif ($item->status === 'lockout')
                                    <span class="badge bg-label-warning">{{ __('messages.login_attempt_status_lockout') }}</span>
                                @else
                                    <span class="badge bg-label-danger">{{ __('messages.login_attempt_status_failed') }}</span>
                                @endif
                            </td>
                            <td>{{ $item->alasan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">{{ __('messages.login_attempt_no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $loginAttempts->links() }}</div>
    </div>
</div>
