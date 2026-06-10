<?php

use App\Models\Notifikasi;
use App\Services\NotifikasiService;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public int $jumlahBelumDibaca = 0;
    public array $daftarNotifikasi = [];

    public function mount(): void
    {
        $this->muatNotifikasi();
    }

    #[On('notifikasi:baru')]
    public function muatNotifikasi(): void
    {
        $userId = auth()->id();

        if ($userId) {
            $this->daftarNotifikasi = app(NotifikasiService::class)
                ->ambilBelumDibaca($userId, 10)
                ->map(fn($n) => [
                    'id' => $n->id,
                    'judul' => $n->judul,
                    'pesan' => $n->pesan,
                    'tipe' => $n->tipe,
                    'path_terkait' => $n->path_terkait,
                    'created_at' => $n->created_at->diffForHumans(),
                ])
                ->toArray();

            $this->jumlahBelumDibaca = app(NotifikasiService::class)
                ->hitungBelumDibaca($userId);
        }
    }

    public function tandaiDibaca(int $notifikasiId): void
    {
        app(NotifikasiService::class)->tandaiDibaca($notifikasiId);
        $this->muatNotifikasi();
    }

    public function tandaiSemuaDibaca(): void
    {
        $userId = auth()->id();

        if ($userId) {
            app(NotifikasiService::class)->tandaiSemuaDibaca($userId);
            $this->muatNotifikasi();
        }
    }

    public function hapus(int $notifikasiId): void
    {
        app(NotifikasiService::class)->hapus($notifikasiId);
        $this->muatNotifikasi();
    }

    private function getBadgeClass(string $tipe): string
    {
        return match ($tipe) {
            'backup_selesai' => 'badge-success',
            'restore_selesai' => 'badge-success',
            'restore_gagal' => 'badge-danger',
            'perubahan_data' => 'badge-info',
            'aktivitas_penting' => 'badge-warning',
            'peringatan' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    private function getIconClass(string $tipe): string
    {
        return match ($tipe) {
            'backup_selesai' => 'bx bx-check-circle text-success',
            'restore_selesai' => 'bx bx-check-circle text-success',
            'restore_gagal' => 'bx bx-x-circle text-danger',
            'perubahan_data' => 'bx bx-info-circle text-info',
            'aktivitas_penting' => 'bx bx-bell-plus text-warning',
            'peringatan' => 'bx bx-alert text-danger',
            default => 'bx bx-bell text-secondary',
        };
    }
};
?>
<div class="dropdown" x-init="$watch('$refs.toggle.getAttribute(\'aria-expanded\')', value => $dispatch('notifikasi-dropdown', { opened: value === 'true' }))" style="position: relative;">
  <a href="javascript:void(0)" class="nav-link dropdown-toggle hide-arrow position-relative" id="notifikasiDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" x-ref="toggle">
    <i class="bx bx-bell"></i>
    @if ($jumlahBelumDibaca > 0)
      <span class="badge bg-danger badge-center rounded-pill" style="position: absolute; top: 0; right: 0; padding: 0.25rem 0.5rem; font-size: 0.65rem;">
        {{ min($jumlahBelumDibaca, 99) }}{{ $jumlahBelumDibaca > 99 ? '+' : '' }}
      </span>
    @endif
  </a>

  <!-- Dropdown menu -->
  <div class="dropdown-menu dropdown-menu-lg-end animated--grow-in" aria-labelledby="notifikasiDropdown" style="min-width: 350px; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);">
    <div class="dropdown-header d-flex justify-content-between align-items-center border-bottom">
      <h6 class="m-0 fw-600">{{ __('messages.notifikasi') }}</h6>
      @if ($jumlahBelumDibaca > 0)
        <button type="button" class="btn btn-sm btn-link p-0" wire:click="tandaiSemuaDibaca">
          <small>{{ __('messages.notifikasi_baca_semua') }}</small>
        </button>
      @endif
    </div>

    @if (count($daftarNotifikasi) > 0)
      <div class="dropdown-divider m-0"></div>
      <div class="list-group list-group-flush" style="max-height: 350px; overflow-y: auto;">
        @foreach ($daftarNotifikasi as $notif)
          <a href="{{ $notif['path_terkait'] ?? '#' }}" class="list-group-item list-group-item-action p-3" style="background-color: transparent; cursor: {{ $notif['path_terkait'] ? 'pointer' : 'default' }};" @if (!$notif['path_terkait']) onclick="return false;" @endif>
            <div class="d-flex align-items-start">
              <div class="me-2" style="font-size: 1.25rem;">
                <i class="{{ $this->getIconClass($notif['tipe']) }}"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-1 small">{{ $notif['judul'] }}</h6>
                <p class="mb-1 small text-muted">{{ Str::limit($notif['pesan'], 60) }}</p>
                <small class="text-muted">{{ $notif['created_at'] }}</small>
              </div>
              <div class="ms-auto">
                <button type="button" class="btn btn-sm btn-ghost p-1" wire:click.stop="hapus({{ $notif['id'] }})" title="{{ __('messages.notifikasi_hapus') }}" style="background: none; border: none; cursor: pointer;">
                  <i class="bx bx-x"></i>
                </button>
              </div>
            </div>
          </a>
        @endforeach
      </div>
      <div class="dropdown-divider m-0"></div>
      <a href="{{ route('laporan.aktivitas', absolute: false) }}" class="dropdown-item text-center small py-2">
        {{ __('messages.notifikasi_lihat_semua') }}
      </a>
    @else
      <div class="text-center py-4">
        <p class="text-muted mb-0">{{ __('messages.notifikasi_belum_ada') }}</p>
      </div>
    @endif
  </div>
</div>
