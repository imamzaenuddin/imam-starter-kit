<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

@section('title', __('messages.delete_account_title'))

<section>
    <hr class="my-4 w-50" />
    <div class="mb-5">
        <h5 class="mb-2">{{ __('messages.delete_account_title') }}</h5>
        <p class="text-muted">{{ __('messages.delete_account_subtitle') }}</p>
    </div>

    <!-- Button to open the modal -->
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
        {{ __('messages.delete_account_title') }}
    </button>

    <!-- Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">{{ __('messages.delete_account_confirm_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('messages.delete_account_warning') }}</p>

                    <form wire:submit="deleteUser" class="space-y-3">
                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('messages.password') }}</label>
                            <input type="password" id="password" wire:model="password" class="form-control" required />
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                            <button type="submit" class="btn btn-danger">{{ __('messages.delete_account_title') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
