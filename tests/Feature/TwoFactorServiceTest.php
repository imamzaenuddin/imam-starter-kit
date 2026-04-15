<?php

use App\Models\Level;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Services\PengaturanAplikasiService;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Level::firstOrCreate(
        ['nama_level' => 'superadmin'],
        ['deskripsi' => 'Superadmin level', 'is_active' => true]
    );
});

test('kirim kode login menyimpan kode hash di cache', function () {
    $service = app(TwoFactorService::class);

    $user = User::factory()->create([
        'email' => 'superadmin@example.com',
    ]);

    $service->kirimKodeLogin($user);

    $cacheKode = Cache::get($service->keyKode($user->id));
    $cacheAttempt = Cache::get($service->keyAttempt($user->id));

    expect($cacheKode)->toBeArray()
        ->and($cacheKode)->toHaveKeys(['hash', 'expires_at'])
        ->and($cacheAttempt)->toBe(0);
});

test('verify kode gagal jika kode tidak cocok', function () {
    $service = app(TwoFactorService::class);

    $user = User::factory()->create();
    $service->kirimKodeLogin($user);

    $valid = $service->verifyKode($user, '000000');

    expect($valid)->toBeFalse()
        ->and((int) Cache::get($service->keyAttempt($user->id), 0))->toBe(1);
});

test('aktifkan dan nonaktifkan 2fa memperbarui kolom user', function () {
    $service = app(TwoFactorService::class);

    $user = User::factory()->create([
        'two_factor_enabled' => false,
        'two_factor_confirmed_at' => null,
    ]);

    $service->aktifkanUntuk($user);

    $user->refresh();

    expect($user->two_factor_enabled)->toBeTrue()
        ->and($user->two_factor_confirmed_at)->not->toBeNull();

    $service->nonaktifkanUntuk($user);

    $user->refresh();

    expect($user->two_factor_enabled)->toBeFalse()
        ->and($user->two_factor_confirmed_at)->toBeNull();
});

test('mode otp always selalu meminta otp untuk superadmin yang aktif 2fa', function () {
    $service = app(TwoFactorService::class);

    $level = Level::where('nama_level', 'superadmin')->first();
    $user = User::factory()->create([
        'level_id' => $level->id,
        'two_factor_enabled' => true,
        'last_login_at' => now(),
    ]);

    app(PengaturanAplikasiService::class)->simpan([
        'timezone' => 'Asia/Jakarta',
        'locale_default' => 'id',
        'batas_upload_kb' => 10240,
        'pagination_default' => 10,
        'otp_mode' => 'always',
        'otp_inactive_days' => 30,
        'otp_failed_attempts' => 3,
        'otp_failed_window_minutes' => 15,
    ]);

    expect($service->wajibSaatLogin($user, $user->email))->toBeTrue();
});

test('mode otp adaptive tidak meminta otp jika login masih baru dan tidak ada gagal', function () {
    $service = app(TwoFactorService::class);

    $level = Level::where('nama_level', 'superadmin')->first();
    $user = User::factory()->create([
        'level_id' => $level->id,
        'two_factor_enabled' => true,
        'last_login_at' => now()->subDays(2),
    ]);

    app(PengaturanAplikasiService::class)->simpan([
        'timezone' => 'Asia/Jakarta',
        'locale_default' => 'id',
        'batas_upload_kb' => 10240,
        'pagination_default' => 10,
        'otp_mode' => 'adaptive',
        'otp_inactive_days' => 30,
        'otp_failed_attempts' => 3,
        'otp_failed_window_minutes' => 15,
    ]);

    expect($service->wajibSaatLogin($user, $user->email))->toBeFalse();
});

test('mode otp adaptive meminta otp jika user sudah lama tidak login', function () {
    $service = app(TwoFactorService::class);

    $level = Level::where('nama_level', 'superadmin')->first();
    $user = User::factory()->create([
        'level_id' => $level->id,
        'two_factor_enabled' => true,
        'last_login_at' => now()->subDays(45),
    ]);

    app(PengaturanAplikasiService::class)->simpan([
        'timezone' => 'Asia/Jakarta',
        'locale_default' => 'id',
        'batas_upload_kb' => 10240,
        'pagination_default' => 10,
        'otp_mode' => 'adaptive',
        'otp_inactive_days' => 30,
        'otp_failed_attempts' => 3,
        'otp_failed_window_minutes' => 15,
    ]);

    expect($service->wajibSaatLogin($user, $user->email))->toBeTrue();
});

test('mode otp adaptive meminta otp jika percobaan login gagal melebihi batas', function () {
    $service = app(TwoFactorService::class);

    $level = Level::where('nama_level', 'superadmin')->first();
    $user = User::factory()->create([
        'level_id' => $level->id,
        'two_factor_enabled' => true,
        'last_login_at' => now()->subDays(1),
        'email' => 'superadmin@example.com',
    ]);

    app(PengaturanAplikasiService::class)->simpan([
        'timezone' => 'Asia/Jakarta',
        'locale_default' => 'id',
        'batas_upload_kb' => 10240,
        'pagination_default' => 10,
        'otp_mode' => 'adaptive',
        'otp_inactive_days' => 30,
        'otp_failed_attempts' => 3,
        'otp_failed_window_minutes' => 15,
    ]);

    LoginAttempt::query()->create([
        'user_id' => $user->id,
        'email' => $user->email,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test-agent',
        'status' => 'gagal',
        'alasan' => 'Password salah 1',
        'metadata' => [],
    ]);

    LoginAttempt::query()->create([
        'user_id' => $user->id,
        'email' => $user->email,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test-agent',
        'status' => 'gagal',
        'alasan' => 'Password salah 2',
        'metadata' => [],
    ]);

    LoginAttempt::query()->create([
        'user_id' => $user->id,
        'email' => $user->email,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test-agent',
        'status' => 'lockout',
        'alasan' => 'Terlalu banyak percobaan login',
        'metadata' => [],
    ]);

    expect($service->wajibSaatLogin($user, $user->email))->toBeTrue();
});

test('mode otp always juga meminta otp untuk non-superadmin jika 2fa aktif', function () {
    $service = app(TwoFactorService::class);

    $user = User::factory()->create([
        'two_factor_enabled' => true,
        'last_login_at' => now(),
    ]);

    app(PengaturanAplikasiService::class)->simpan([
        'timezone' => 'Asia/Jakarta',
        'locale_default' => 'id',
        'batas_upload_kb' => 10240,
        'pagination_default' => 10,
        'otp_mode' => 'always',
        'otp_inactive_days' => 30,
        'otp_failed_attempts' => 3,
        'otp_failed_window_minutes' => 15,
    ]);

    expect($service->wajibSaatLogin($user, $user->email))->toBeTrue();
});

test('otp tidak wajib jika user belum mengaktifkan 2fa', function () {
    $service = app(TwoFactorService::class);

    $user = User::factory()->create([
        'two_factor_enabled' => false,
        'last_login_at' => now()->subDays(100),
    ]);

    app(PengaturanAplikasiService::class)->simpan([
        'timezone' => 'Asia/Jakarta',
        'locale_default' => 'id',
        'batas_upload_kb' => 10240,
        'pagination_default' => 10,
        'otp_mode' => 'always',
        'otp_inactive_days' => 30,
        'otp_failed_attempts' => 3,
        'otp_failed_window_minutes' => 15,
    ]);

    expect($service->wajibSaatLogin($user, $user->email))->toBeFalse();
});

test('catat login berhasil memperbarui last_login_at', function () {
    $service = app(TwoFactorService::class);

    $user = User::factory()->create([
        'last_login_at' => null,
    ]);

    $service->catatLoginBerhasil($user);

    $user->refresh();

    expect($user->last_login_at)->not->toBeNull();
});

test('bolehKelola2fa true jika aktor dan target adalah akun yang sama', function () {
    $service = app(TwoFactorService::class);
    $user = User::factory()->create();

    expect($service->bolehKelola2fa($user, $user))->toBeTrue();
});

test('bolehKelola2fa false jika aktor dan target berbeda akun', function () {
    $service = app(TwoFactorService::class);
    $aktor = User::factory()->create();
    $target = User::factory()->create();

    expect($service->bolehKelola2fa($aktor, $target))->toBeFalse();
});
