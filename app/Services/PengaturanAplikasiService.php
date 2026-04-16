<?php

namespace App\Services;

use App\Models\Bahasa;
use App\Models\PengaturanAplikasi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class PengaturanAplikasiService
{
  public function cacheKey(): string
  {
    return 'pengaturan_aplikasi_runtime';
  }

  public function konfigurasiDefault(): array
  {
    return [
      'timezone' => config('app.timezone', 'Asia/Jakarta'),
      'locale_default' => config('app.locale', 'id'),
      'batas_upload_kb' => 10240,
      'pagination_default' => 10,
      'otp_mode' => 'always',
      'otp_inactive_days' => 30,
      'otp_failed_attempts' => 3,
      'otp_failed_window_minutes' => 15,
      'chat_ai_konteks' => ['total_pengguna', 'total_level', 'level_aktif', 'total_menu', 'menu_aktif', 'aktivitas_7_hari', 'modul_teratas'],
    ];
  }

  public function konfigurasiAktif(): array
  {
    $default = $this->konfigurasiDefault();

    if (! Schema::hasTable('m_pengaturan_aplikasi')) {
      return $default;
    }

    return Cache::remember($this->cacheKey(), now()->addMinutes(30), function () use ($default) {
      $pengaturan = PengaturanAplikasi::query()
        ->where('is_active', true)
        ->latest('id')
        ->first();

      if (! $pengaturan) {
        return $default;
      }

      return array_merge($default, [
        'timezone' => (string) ($pengaturan->timezone ?: $default['timezone']),
        'locale_default' => (string) ($pengaturan->locale_default ?: $default['locale_default']),
        'batas_upload_kb' => (int) ($pengaturan->batas_upload_kb ?: $default['batas_upload_kb']),
        'pagination_default' => (int) ($pengaturan->pagination_default ?: $default['pagination_default']),
        'otp_mode' => (string) ($pengaturan->otp_mode ?: $default['otp_mode']),
        'otp_inactive_days' => (int) ($pengaturan->otp_inactive_days ?? $default['otp_inactive_days']),
        'otp_failed_attempts' => (int) ($pengaturan->otp_failed_attempts ?? $default['otp_failed_attempts']),
        'otp_failed_window_minutes' => (int) ($pengaturan->otp_failed_window_minutes ?? $default['otp_failed_window_minutes']),
        'chat_ai_konteks' => is_array($pengaturan->chat_ai_konteks) && count($pengaturan->chat_ai_konteks) > 0
          ? $pengaturan->chat_ai_konteks
          : $default['chat_ai_konteks'],
      ]);
    });
  }

  public function simpan(array $data): PengaturanAplikasi
  {
    if (! Schema::hasTable('m_pengaturan_aplikasi')) {
      throw new \RuntimeException('Tabel pengaturan aplikasi belum tersedia. Jalankan migrasi terlebih dahulu.');
    }

    PengaturanAplikasi::query()->update(['is_active' => false]);

    $pengaturan = PengaturanAplikasi::query()->create([
      'timezone' => $data['timezone'],
      'locale_default' => $data['locale_default'],
      'batas_upload_kb' => $data['batas_upload_kb'],
      'pagination_default' => $data['pagination_default'],
      'otp_mode' => $data['otp_mode'],
      'otp_inactive_days' => $data['otp_inactive_days'],
      'otp_failed_attempts' => $data['otp_failed_attempts'],
      'otp_failed_window_minutes' => $data['otp_failed_window_minutes'],
      'chat_ai_konteks' => $data['chat_ai_konteks'] ?? $this->konfigurasiDefault()['chat_ai_konteks'],
      'is_active' => true,
    ]);

    if (Schema::hasTable('m_bahasa')) {
      $bahasa = Bahasa::query()
        ->where('kode', $data['locale_default'])
        ->where('is_active', true)
        ->first();

      if ($bahasa) {
        Bahasa::query()->update(['is_default' => false]);
        $bahasa->update(['is_default' => true]);
      }
    }

    $this->refreshCache();

    return $pengaturan;
  }

  public function terapkanKonfigurasiRuntime(): void
  {
    $konfigurasi = array_merge($this->konfigurasiDefault(), $this->konfigurasiAktif());

    Config::set('app.timezone', $konfigurasi['timezone']);
    Config::set('app.locale', $konfigurasi['locale_default']);
    Config::set('app_runtime.batas_upload_kb', $konfigurasi['batas_upload_kb']);
    Config::set('app_runtime.pagination_default', $konfigurasi['pagination_default']);
    Config::set('app_runtime.otp_mode', $konfigurasi['otp_mode']);
    Config::set('app_runtime.otp_inactive_days', $konfigurasi['otp_inactive_days']);
    Config::set('app_runtime.otp_failed_attempts', $konfigurasi['otp_failed_attempts']);
    Config::set('app_runtime.otp_failed_window_minutes', $konfigurasi['otp_failed_window_minutes']);

    date_default_timezone_set($konfigurasi['timezone']);
  }

  public function refreshCache(): array
  {
    Cache::forget($this->cacheKey());

    return $this->konfigurasiAktif();
  }
}
