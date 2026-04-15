<?php

use App\Models\User;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('halaman two factor dapat diakses user login', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/settings/two-factor')->assertOk();
});

test('guest tidak dapat mengakses halaman two factor', function () {
    $this->get('/settings/two-factor')->assertRedirect('/login');
});

test('user dapat mengaktifkan 2fa untuk akun sendiri', function () {
    $user = User::factory()->create([
        'two_factor_enabled' => false,
        'two_factor_confirmed_at' => null,
    ]);

    $this->actingAs($user);

    Volt::test('settings.two-factor')
        ->call('aktifkan')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->two_factor_enabled)->toBeTrue()
        ->and($user->two_factor_confirmed_at)->not->toBeNull();
});

test('user dapat menonaktifkan 2fa untuk akun sendiri', function () {
    $user = User::factory()->create([
        'two_factor_enabled' => true,
        'two_factor_confirmed_at' => now(),
    ]);

    $this->actingAs($user);

    Volt::test('settings.two-factor')
        ->call('nonaktifkan')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->two_factor_enabled)->toBeFalse()
        ->and($user->two_factor_confirmed_at)->toBeNull();
});

test('user tidak dapat mengubah 2fa akun lain meski memanipulasi state komponen', function () {
    $aktor = User::factory()->create([
        'two_factor_enabled' => false,
        'two_factor_confirmed_at' => null,
    ]);

    $target = User::factory()->create([
        'two_factor_enabled' => false,
        'two_factor_confirmed_at' => null,
    ]);

    $this->actingAs($aktor);

    Volt::test('settings.two-factor')
        ->set('pemilikAkunId', $target->id)
        ->call('aktifkan')
        ->assertForbidden();

    $aktor->refresh();
    $target->refresh();

    expect($aktor->two_factor_enabled)->toBeFalse()
        ->and($target->two_factor_enabled)->toBeFalse();
});
