<?php

namespace App\Mail;

use App\Models\User;
use App\Services\IdentitasService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendTwoFactorOtp extends Mailable
{
    use SerializesModels;

    public function __construct(
        public User $user,
        public string $kode,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            to: [new Address($this->user->email, $this->user->name)],
            subject: __('messages.two_factor_email_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two-factor-otp',
            with: [
                'user' => $this->user,
                'kode' => $this->kode,
                'appName' => app(IdentitasService::class)->aktif()?->nama_aplikasi ?? config('app.name'),
                'expiresIn' => '5 menit',
            ],
        );
    }
}
