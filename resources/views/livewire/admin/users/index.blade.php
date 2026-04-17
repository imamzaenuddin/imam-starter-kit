<?php

use App\Models\Level;
use App\Models\User;
use App\Services\LogAktivitasService;
use App\Services\NotifikasiService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $name = '';
    public string $email = '';
    public ?int $levelId = null;
    public bool $isActive = true;
    public string $password = '';
    public string $password_confirmation = '';

    public ?int $editId = null;
    public bool $showModal = false;

    public ?int $resetUserId = null;
    public string $resetPasswordBaru = '';
    public string $resetPasswordKonfirmasi = '';
    public bool $showModalResetPassword = false;

    public function mount(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/users', 'dapat_lihat')) {
            abort(403);
        }
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/users', 'dapat_buat')) {
            abort(403);
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        if (! auth()->user()?->bisaMenu('/admin/users', 'dapat_ubah')) {
            abort(403);
        }

        $user = User::findOrFail($id);

        $this->editId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->levelId = $user->level_id;
        $this->isActive = (bool) ($user->is_active ?? true);
        $this->password = '';
        $this->password_confirmation = '';
        $this->showModal = true;
    }

    public function simpan(): void
    {
        $izin = $this->editId ? 'dapat_ubah' : 'dapat_buat';
        if (! auth()->user()?->bisaMenu('/admin/users', $izin)) {
            abort(403);
        }

        $data = $this->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:t_user,email,' . ($this->editId ?? 'NULL'),
            'levelId' => 'required|exists:m_level,id',
            'isActive' => 'boolean',
            'password' => ($this->editId ? 'nullable' : 'required') . '|string|min:8|confirmed',
        ]);

        if ($this->editId) {
            $user = User::findOrFail($this->editId);

            if ((int) $user->id === (int) auth()->id() && ! $data['isActive']) {
                $this->addError('isActive', __('messages.user_error_cannot_deactivate_self'));
                return;
            }

            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'level_id' => $data['levelId'],
                'is_active' => (bool) $data['isActive'],
            ];

            if (! empty($data['password'])) {
                $payload['password'] = $data['password'];
            }

            $user->update($payload);

            app(LogAktivitasService::class)->catatManual(
                __('messages.user_management_module_name'),
                __('messages.user_management_log_update', ['nama' => $user->name]),
                '/admin/users',
                ['user_id' => $user->id]
            );

            $this->kirimNotifikasiPerubahanPengguna('Update user: ' . $user->name);
        } else {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'level_id' => $data['levelId'],
                'is_active' => (bool) $data['isActive'],
                'password' => $data['password'],
            ]);

            app(LogAktivitasService::class)->catatManual(
                __('messages.user_management_module_name'),
                __('messages.user_management_log_add', ['nama' => $user->name]),
                '/admin/users',
                ['user_id' => $user->id]
            );

            $this->kirimNotifikasiPerubahanPengguna('Tambah user: ' . $user->name);
        }

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function bukaResetPassword(int $id): void
    {
        if (! auth()->user()?->bisaMenu('/admin/users', 'dapat_ubah')) {
            abort(403);
        }

        $this->resetUserId = $id;
        $this->resetPasswordBaru = '';
        $this->resetPasswordKonfirmasi = '';
        $this->resetValidation();
        $this->showModalResetPassword = true;
    }

    public function simpanResetPassword(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/users', 'dapat_ubah')) {
            abort(403);
        }

        $data = $this->validate([
            'resetUserId' => 'required|exists:t_user,id',
            'resetPasswordBaru' => 'required|string|min:8|same:resetPasswordKonfirmasi',
            'resetPasswordKonfirmasi' => 'required|string|min:8',
        ]);

        $user = User::findOrFail((int) $data['resetUserId']);
        $user->update(['password' => $data['resetPasswordBaru']]);

        app(LogAktivitasService::class)->catatManual(
            __('messages.user_management_module_name'),
            __('messages.user_management_log_reset_password', ['nama' => $user->name]),
            '/admin/users',
            ['user_id' => $user->id]
        );

        $this->kirimNotifikasiPerubahanPengguna('Reset password user: ' . $user->name);

        $this->showModalResetPassword = false;
        $this->resetUserId = null;
        $this->resetPasswordBaru = '';
        $this->resetPasswordKonfirmasi = '';
    }

    public function hapus(int $id): void
    {
        if (! auth()->user()?->bisaMenu('/admin/users', 'dapat_hapus')) {
            abort(403);
        }

        if ((int) $id === (int) auth()->id()) {
            $this->addError('search', __('messages.user_error_cannot_delete_self'));
            return;
        }

        $user = User::findOrFail($id);

        app(LogAktivitasService::class)->catatManual(
            __('messages.user_management_module_name'),
            __('messages.user_management_log_delete', ['nama' => $user->name]),
            '/admin/users',
            ['user_id' => $user->id]
        );

        $this->kirimNotifikasiPerubahanPengguna('Hapus user: ' . $user->name);

        $user->delete();
        $this->resetPage();
    }

      private function kirimNotifikasiPerubahanPengguna(string $aksi): void
      {
        $userId = auth()->id();
        if (! $userId) {
          return;
        }

        app(NotifikasiService::class)->perubahanData([(int) $userId], __('messages.user_management_module_name'), $aksi);
        $this->dispatch('notifikasi:baru');
      }

    public function with(): array
    {
        return [
            'users' => User::query()
                ->with('level:id,nama_level')
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->orderBy('name')
              ->paginate((int) config('app_runtime.pagination_default', 10)),
            'levels' => Level::query()->where('is_active', true)->orderBy('nama_level')->get(),
        ];
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'email', 'levelId', 'isActive', 'password', 'password_confirmation', 'editId']);
        $this->isActive = true;
        $this->resetValidation();
    }
};
?>
@section('title', __('messages.admin_manage_user_title'))

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1" style="color:#1e293b">{{ __('messages.admin_manage_user_heading') }}</h4>
      <p class="text-muted mb-0" style="font-size:.875rem">{{ __('messages.admin_manage_user_subheading') }}</p>
    </div>
    <button class="btn btn-primary" wire:click="buka">
      <i class="bx bx-plus me-1"></i> {{ __('messages.add_user') }}
    </button>
  </div>

  <div class="card mb-4">
    <div class="card-body py-3">
      <input wire:model.live.debounce.300ms="search" type="search" class="form-control" placeholder="{{ __('messages.search_user_placeholder') }}">
      @error('search') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>{{ __('messages.user') }}</th>
            <th>{{ __('messages.level') }}</th>
            <th>{{ __('messages.status') }}</th>
            <th class="text-center">{{ __('messages.action') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($users as $user)
            <tr>
              <td>{{ $users->firstItem() + $loop->index }}</td>
              <td>
                <div class="fw-semibold">{{ $user->name }}</div>
                <small class="text-muted">{{ $user->email }}</small>
              </td>
              <td>{{ $user->level?->nama_level ?? '-' }}</td>
              <td>
                @if ($user->is_active)
                  <span class="badge bg-label-success">{{ __('messages.active') }}</span>
                @else
                  <span class="badge bg-label-secondary">{{ __('messages.inactive') }}</span>
                @endif
              </td>
              <td class="text-center">
                <button class="btn btn-sm btn-icon btn-text-warning" wire:click="bukaResetPassword({{ $user->id }})" title="{{ __('messages.user_reset_password') }}">
                  <i class="bx bx-key"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit({{ $user->id }})" title="{{ __('messages.edit') }}">
                  <i class="bx bx-edit-alt"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-text-danger"
                        @click="Swal.fire({
                          title: '{{ __('messages.confirm_delete') }}',
                          text: '{{ __('messages.confirm_delete_user', ['nama' => addslashes($user->name)]) }}',
                          icon: 'warning',
                          showCancelButton: true,
                          confirmButtonText: '{{ __('messages.yes_delete') }}',
                          cancelButtonText: '{{ __('messages.cancel') }}',
                        }).then(r => r.isConfirmed && $wire.hapus({{ $user->id }}))"
                        title="{{ __('messages.delete') }}">
                  <i class="bx bx-trash"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">{{ __('messages.no_user_data') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $users->links() }}</div>
  </div>

  @if ($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.45)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $editId ? __('messages.edit_user') : __('messages.add_user') }}</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
          </div>
          <form wire:submit="simpan">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('messages.enter_your_name') }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.email') }} <span class="text-danger">*</span></label>
                <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="{{ __('messages.enter_your_email') }}">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.level') }} <span class="text-danger">*</span></label>
                <select wire:model="levelId" class="form-select @error('levelId') is-invalid @enderror">
                  <option value="">{{ __('messages.select_level_option') }}</option>
                  @foreach ($levels as $level)
                    <option value="{{ $level->id }}">{{ $level->nama_level }}</option>
                  @endforeach
                </select>
                @error('levelId') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.password') }} {{ $editId ? '(' . __('messages.user_password_optional_on_edit') . ')' : '' }}</label>
                <input wire:model="password" type="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.confirm_password') }}</label>
                <input wire:model="password_confirmation" type="password" class="form-control" autocomplete="new-password">
              </div>
              <div class="form-check">
                <input wire:model="isActive" type="checkbox" class="form-check-input" id="userActiveCheck">
                <label class="form-check-label" for="userActiveCheck">{{ __('messages.user_active_status') }}</label>
              </div>
              @error('isActive') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModal', false)">{{ __('messages.cancel') }}</button>
              <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpan">
                <span wire:loading.remove wire:target="simpan">{{ __('messages.save') }}</span>
                <span wire:loading wire:target="simpan" style="display:none">{{ __('messages.saving') }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif

  @if ($showModalResetPassword)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.45)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ __('messages.user_reset_password') }}</h5>
            <button type="button" class="btn-close" wire:click="$set('showModalResetPassword', false)"></button>
          </div>
          <form wire:submit="simpanResetPassword">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.new_password') }} <span class="text-danger">*</span></label>
                <input wire:model="resetPasswordBaru" type="password" class="form-control @error('resetPasswordBaru') is-invalid @enderror" autocomplete="new-password">
                @error('resetPasswordBaru') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="mb-0">
                <label class="form-label fw-semibold">{{ __('messages.confirm_password') }} <span class="text-danger">*</span></label>
                <input wire:model="resetPasswordKonfirmasi" type="password" class="form-control @error('resetPasswordKonfirmasi') is-invalid @enderror" autocomplete="new-password">
                @error('resetPasswordKonfirmasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModalResetPassword', false)">{{ __('messages.cancel') }}</button>
              <button type="submit" class="btn btn-warning" wire:loading.attr="disabled" wire:target="simpanResetPassword">
                <span wire:loading.remove wire:target="simpanResetPassword">{{ __('messages.user_save_new_password') }}</span>
                <span wire:loading wire:target="simpanResetPassword" style="display:none">{{ __('messages.saving') }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
</div>
