<?php

use App\Models\PengaturanAplikasi;
use App\Services\PengaturanAplikasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {});

test('konfigurasi aktif mengembalikan default saat belum ada data', function () {
    $konfigurasi = app(PengaturanAplikasiService::class)->konfigurasiAktif();

    expect($konfigurasi)->toHaveKeys([
        'timezone',
        'locale_default',
        'batas_upload_kb',
        'pagination_default',
        'otp_mode',
        'otp_inactive_days',
        'otp_failed_attempts',
        'otp_failed_window_minutes',
    ])
        ->and($konfigurasi['pagination_default'])->toBeGreaterThan(0);
});

test('simpan pengaturan aplikasi membuat record aktif baru', function () {
    $service = app(PengaturanAplikasiService::class);

    PengaturanAplikasi::query()->create([
        'timezone' => 'Asia/Jakarta',
        'locale_default' => 'id',
        'batas_upload_kb' => 10240,
        'pagination_default' => 10,
        'otp_mode' => 'always',
        'otp_inactive_days' => 30,
        'otp_failed_attempts' => 3,
        'otp_failed_window_minutes' => 15,
        'is_active' => true,
    ]);

    $pengaturanBaru = $service->simpan([
        'timezone' => 'UTC',
        'locale_default' => 'en',
        'batas_upload_kb' => 20480,
        'pagination_default' => 25,
        'otp_mode' => 'adaptive',
        'otp_inactive_days' => 45,
        'otp_failed_attempts' => 4,
        'otp_failed_window_minutes' => 20,
    ]);

    expect($pengaturanBaru->is_active)->toBeTrue()
        ->and(PengaturanAplikasi::query()->where('is_active', true)->count())->toBe(1)
        ->and(PengaturanAplikasi::query()->where('timezone', 'Asia/Jakarta')->first()?->is_active)->toBeFalse();
});

test('terapkan konfigurasi runtime mengubah config aplikasi', function () {
    $service = app(PengaturanAplikasiService::class);

    $service->simpan([
        'timezone' => 'UTC',
        'locale_default' => 'en',
        'batas_upload_kb' => 12345,
        'pagination_default' => 17,
        'otp_mode' => 'adaptive',
        'otp_inactive_days' => 60,
        'otp_failed_attempts' => 5,
        'otp_failed_window_minutes' => 25,
    ]);

    $service->terapkanKonfigurasiRuntime();

    expect(config('app.timezone'))->toBe('UTC')
        ->and(config('app.locale'))->toBe('en')
        ->and((int) config('app_runtime.batas_upload_kb'))->toBe(12345)
        ->and((int) config('app_runtime.pagination_default'))->toBe(17)
        ->and((string) config('app_runtime.otp_mode'))->toBe('adaptive')
        ->and((int) config('app_runtime.otp_inactive_days'))->toBe(60)
        ->and((int) config('app_runtime.otp_failed_attempts'))->toBe(5)
        ->and((int) config('app_runtime.otp_failed_window_minutes'))->toBe(25);
});
