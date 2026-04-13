<?php

namespace App\Services;

use App\Models\PengaturanEmail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class PengaturanEmailService
{
  public function konfigurasiAktif(): ?PengaturanEmail
  {
    if (! Schema::hasTable('pengaturan_email')) {
      return null;
    }

    return PengaturanEmail::query()
      ->where('is_active', true)
      ->latest('id')
      ->first();
  }

  public function terapkanKonfigurasiRuntime(?PengaturanEmail $pengaturan = null): void
  {
    $pengaturan ??= $this->konfigurasiAktif();

    if (! $pengaturan) {
      return;
    }

    Config::set('mail.default', $pengaturan->mailer ?: 'smtp');

    Config::set('mail.mailers.smtp.transport', 'smtp');
    Config::set('mail.mailers.smtp.host', $pengaturan->host);
    Config::set('mail.mailers.smtp.port', (int) $pengaturan->port);
    Config::set('mail.mailers.smtp.encryption', $pengaturan->enkripsi ?: null);
    Config::set('mail.mailers.smtp.username', $pengaturan->username ?: null);
    Config::set('mail.mailers.smtp.password', $pengaturan->password ?: null);
    Config::set('mail.mailers.smtp.timeout', 20);

    Config::set('mail.from.address', $pengaturan->from_address);
    Config::set('mail.from.name', $pengaturan->from_name);

    if ($pengaturan->reply_to) {
      Config::set('mail.reply_to.address', $pengaturan->reply_to);
      Config::set('mail.reply_to.name', $pengaturan->from_name);
    }
  }
}
