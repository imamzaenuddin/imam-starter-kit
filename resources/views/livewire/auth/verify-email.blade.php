<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
            return;
        }

        Auth::user()->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

@section('title', __('messages.verify_email_page_title'))

@section('page-style')
@vite([
    'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection

<div>
    <h4 class="mb-1">{{ __('messages.verify_email_title') }} 📧</h4>
    <p class="mb-6">{{ __('messages.verify_email_subtitle') }}</p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success mb-4">
            {{ __('messages.verification_link_sent') }}
        </div>
    @endif

    <div class="text-center mb-6">
        <button wire:click="sendVerification" class="btn btn-primary d-grid w-100 mb-3">
            {{ __('messages.resend_verification_email') }}
        </button>

        <button wire:click="logout" class="btn btn-link">
            {{ __('messages.log_out') }}
        </button>
    </div>
</div>
