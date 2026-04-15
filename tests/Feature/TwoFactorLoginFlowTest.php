<?php

use App\Models\Level;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Level::firstOrCreate(
        ['nama_level' => 'superadmin'],
        ['deskripsi' => 'Superadmin level', 'is_active' => true]
    );

    Level::firstOrCreate(
        ['nama_level' => 'admin'],
        ['deskripsi' => 'Admin level', 'is_active' => true]
    );
});

test('2fa service kirim kode login dan simpan di cache', function () {
    $user = User::factory()->create([
        'email' => 'superadmin@test.id',
    ]);

    $service = app(TwoFactorService::class);
    $service->kirimKodeLogin($user);

    $cacheKode = Cache::get($service->keyKode($user->id));
    $cacheAttempt = Cache::get($service->keyAttempt($user->id));

    expect($cacheKode)->toBeArray()
        ->and($cacheKode)->toHaveKeys(['hash', 'expires_at'])
        ->and($cacheAttempt)->toBe(0);
});

test('2fa verify kode gagal dengan kode yang salah', function () {
    $user = User::factory()->create();

    $service = app(TwoFactorService::class);
    $service->kirimKodeLogin($user);

    $valid = $service->verifyKode($user, '000000');

    expect($valid)->toBeFalse()
        ->and((int) Cache::get($service->keyAttempt($user->id), 0))->toBe(1);
});

test('2fa aktifkan untuk user mengubah setting', function () {
    $user = User::factory()->create([
        'two_factor_enabled' => false,
        'two_factor_confirmed_at' => null,
    ]);

    $service = app(TwoFactorService::class);
    $service->aktifkanUntuk($user);

    $user->refresh();

    expect($user->two_factor_enabled)->toBeTrue()
        ->and($user->two_factor_confirmed_at)->not->toBeNull();
});

test('2fa nonaktifkan untuk user menghapus setting', function () {
    $user = User::factory()->create([
        'two_factor_enabled' => true,
        'two_factor_confirmed_at' => now(),
    ]);

    $service = app(TwoFactorService::class);
    $service->nonaktifkanUntuk($user);

    $user->refresh();

    expect($user->two_factor_enabled)->toBeFalse()
        ->and($user->two_factor_confirmed_at)->toBeNull();
});

test('user isSuperadmin method bekerja dengan benar', function () {
    $superadminLevel = Level::where('nama_level', 'superadmin')->first();
    $adminLevel = Level::where('nama_level', 'admin')->first();

    $superadmin = User::factory()->create(['level_id' => $superadminLevel->id]);
    $admin = User::factory()->create(['level_id' => $adminLevel->id]);

    expect($superadmin->isSuperadmin())->toBeTrue()
        ->and($admin->isSuperadmin())->toBeFalse();
});

test('rate limiting 2fa setelah 5 percobaan gagal', function () {
    $user = User::factory()->create();

    $service = app(TwoFactorService::class);
    $service->kirimKodeLogin($user);

    // Coba 5 kali dengan kode salah
    for ($i = 0; $i < 5; $i++) {
        $service->verifyKode($user, '000000');
    }

    $attempts = Cache::get($service->keyAttempt($user->id));
    expect($attempts)->toBe(5);
});

test('kode 2fa expired setelah cache dihapus', function () {
    $user = User::factory()->create();

    $service = app(TwoFactorService::class);
    $service->kirimKodeLogin($user);

    // Simulate cache expiry
    Cache::forget($service->keyKode($user->id));

    $valid = $service->verifyKode($user, '123456');

    expect($valid)->toBeFalse();
});;
