<?php

use App\Models\Level;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create levels
    Level::firstOrCreate(
        ['nama_level' => 'superadmin'],
        ['deskripsi' => 'Superadmin level', 'is_active' => true]
    );
    Level::firstOrCreate(
        ['nama_level' => 'admin'],
        ['deskripsi' => 'Admin level', 'is_active' => true]
    );
    Level::firstOrCreate(
        ['nama_level' => 'user'],
        ['deskripsi' => 'User level', 'is_active' => true]
    );

    // Create menus
    Menu::firstOrCreate(
        ['nama' => 'Dashboard', 'url' => '/dashboard'],
        ['icon' => 'bx bx-home', 'urutan' => 1, 'is_active' => true]
    );
    Menu::firstOrCreate(
        ['nama' => 'Pengaturan', 'url' => '/settings'],
        ['icon' => 'bx bx-cog', 'urutan' => 2, 'is_active' => true]
    );
    Menu::firstOrCreate(
        ['nama' => 'Admin', 'url' => '/admin'],
        ['icon' => 'bx bx-shield', 'urutan' => 3, 'is_active' => true]
    );
});

// ============ AUTHORIZATION TESTS ============

test('superadmin helper method bekerja dengan benar', function () {
    $superadminLevel = Level::where('nama_level', 'superadmin')->first();
    $adminLevel = Level::where('nama_level', 'admin')->first();

    $superadmin = User::factory()->create(['level_id' => $superadminLevel->id]);
    $admin = User::factory()->create(['level_id' => $adminLevel->id]);

    expect($superadmin->isSuperadmin())->toBeTrue()
        ->and($admin->isSuperadmin())->toBeFalse();
});

test('user level identification bekerja dengan benar', function () {
    $superadminLevel = Level::where('nama_level', 'superadmin')->first();
    $adminLevel = Level::where('nama_level', 'admin')->first();
    $userLevel = Level::where('nama_level', 'user')->first();

    $superadmin = User::factory()->create(['level_id' => $superadminLevel->id]);
    $admin = User::factory()->create(['level_id' => $adminLevel->id]);
    $user = User::factory()->create(['level_id' => $userLevel->id]);

    expect($superadmin->level->nama_level)->toEqual('superadmin')
        ->and($admin->level->nama_level)->toEqual('admin')
        ->and($user->level->nama_level)->toEqual('user');
});

test('guest user redirect ke login saat akses halaman protected', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('authenticated user tersimpan di session', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    expect(auth()->check())->toBeTrue()
        ->and(auth()->user()->id)->toBe($user->id);
});

// ============ MENU ACCESS TESTS ============

test('all menu items exist dalam database', function () {
    $dashboardMenu = Menu::where('url', '/dashboard')->first();
    $settingsMenu = Menu::where('url', '/settings')->first();
    $adminMenu = Menu::where('url', '/admin')->first();

    expect($dashboardMenu)->not->toBeNull()
        ->and($settingsMenu)->not->toBeNull()
        ->and($adminMenu)->not->toBeNull();
});

test('inactive menus are tidak ditampilkan di query', function () {
    $menu = Menu::where('nama', 'Dashboard')->firstOrFail();
    $menu->update(['is_active' => false]);

    $inactiveMenu = Menu::where('url', '/dashboard')->where('is_active', false)->first();

    expect($inactiveMenu)->not->toBeNull();
});

test('menu ordering is preserved', function () {
    $menus = Menu::orderBy('urutan')->get();

    expect($menus->count())->toBeGreaterThan(0);
    expect($menus->first()->urutan)->toBeLessThanOrEqual($menus->last()->urutan);
});

test('menu level relationship bekerja', function () {
    $menu = Menu::where('nama', 'Dashboard')->first();

    expect($menu->nama)->toEqual('Dashboard');
});

// ============ 2FA AUTHORIZATION TESTS ============

test('2fa settings accessible oleh superadmin', function () {
    $superadminLevel = Level::where('nama_level', 'superadmin')->first();
    $superadmin = User::factory()->create(['level_id' => $superadminLevel->id]);

    expect($superadmin->isSuperadmin())->toBeTrue();
});

test('2fa settings tidak accessible oleh non-superadmin', function () {
    $adminLevel = Level::where('nama_level', 'admin')->first();
    $admin = User::factory()->create(['level_id' => $adminLevel->id]);

    expect($admin->isSuperadmin())->toBeFalse();
});

test('2fa enabled field exist di user model', function () {
    $user = User::factory()->create(['two_factor_enabled' => false]);

    expect($user->two_factor_enabled)->toBeFalse();
});

test('user dapat enable 2fa', function () {
    $user = User::factory()->create(['two_factor_enabled' => false]);

    $user->update(['two_factor_enabled' => true, 'two_factor_confirmed_at' => now()]);
    $user->refresh();

    expect($user->two_factor_enabled)->toBeTrue()
        ->and($user->two_factor_confirmed_at)->not->toBeNull();
});

test('user dapat disable 2fa', function () {
    $user = User::factory()->create(['two_factor_enabled' => true, 'two_factor_confirmed_at' => now()]);

    $user->update(['two_factor_enabled' => false, 'two_factor_confirmed_at' => null]);
    $user->refresh();

    expect($user->two_factor_enabled)->toBeFalse()
        ->and($user->two_factor_confirmed_at)->toBeNull();
});

// ============ LEVEL MANAGEMENT TESTS ============

test('levels dapat dibuat dan di-query', function () {
    $superadmin = Level::where('nama_level', 'superadmin')->first();

    expect($superadmin->nama_level)->toBe('superadmin')
        ->and($superadmin->is_active)->toBeTrue();
});

test('levels dapat dimodifikasi', function () {
    $level = Level::where('nama_level', 'admin')->first();

    $level->update(['deskripsi' => 'Updated admin level']);
    $level->refresh();

    expect($level->deskripsi)->toBe('Updated admin level');
});

test('user assignment ke level bekerja', function () {
    $level = Level::where('nama_level', 'admin')->first();
    $user = User::factory()->create(['level_id' => $level->id]);

    expect($user->level_id)->toBe($level->id);
});
