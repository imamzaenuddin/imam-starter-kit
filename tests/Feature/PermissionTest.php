<?php

use App\Models\Level;
use App\Models\Menu;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('user can check menu permission with bisaMenu', function () {
    $level = Level::create([
        'nama_level' => 'Test Level',
        'deskripsi' => 'A test level',
        'is_active' => true,
    ]);

    $menu = Menu::create([
        'nama' => 'Test Menu',
        'url' => '/test/menu',
        'icon' => 'bx bx-test',
        'urutan' => 1,
        'is_active' => true,
    ]);

    $user = User::factory()->create(['level_id' => $level->id]);

    $level->menus()->attach($menu->id, [
        'dapat_lihat' => true,
        'dapat_buat' => true,
        'dapat_ubah' => false,
        'dapat_hapus' => false,
    ]);

    expect($user->bisaMenu($menu->url, 'dapat_lihat'))->toBeTrue();
    expect($user->bisaMenu($menu->url, 'dapat_buat'))->toBeTrue();
    expect($user->bisaMenu($menu->url, 'dapat_ubah'))->toBeFalse();
    expect($user->bisaMenu($menu->url, 'dapat_hapus'))->toBeFalse();
});

test('user without menu assignment cannot access menu', function () {
    $level1 = Level::create([
        'nama_level' => 'Admin',
        'deskripsi' => 'Admin level',
        'is_active' => true,
    ]);

    $level2 = Level::create([
        'nama_level' => 'User',
        'deskripsi' => 'User level',
        'is_active' => true,
    ]);

    $menu = Menu::create([
        'nama' => 'Admin Menu',
        'url' => '/admin/users',
        'icon' => 'bx bx-user',
        'urutan' => 1,
        'is_active' => true,
    ]);

    $level1->menus()->attach($menu->id, [
        'dapat_lihat' => true,
        'dapat_buat' => true,
        'dapat_ubah' => true,
        'dapat_hapus' => true,
    ]);

    $user = User::factory()->create(['level_id' => $level2->id]);

    expect($user->bisaMenu($menu->url, 'dapat_lihat'))->toBeFalse();
});

test('superadmin can have access to assigned menus', function () {
    $superadmin = Level::where('nama_level', 'Superadmin')->first()
        ?? Level::create([
            'nama_level' => 'Superadmin',
            'deskripsi' => 'Full access',
            'is_active' => true,
        ]);

    $user = User::factory()->create(['level_id' => $superadmin->id]);

    $menu = Menu::create([
        'nama' => 'Test Menu',
        'url' => '/test',
        'icon' => 'bx bx-test',
        'urutan' => 1,
        'is_active' => true,
    ]);

    $superadmin->menus()->attach($menu->id, [
        'dapat_lihat' => true,
        'dapat_buat' => true,
        'dapat_ubah' => true,
        'dapat_hapus' => true,
    ]);

    expect($user->bisaMenu($menu->url, 'dapat_lihat'))->toBeTrue();
    expect($user->bisaMenu($menu->url, 'dapat_buat'))->toBeTrue();
    expect($user->bisaMenu($menu->url, 'dapat_ubah'))->toBeTrue();
    expect($user->bisaMenu($menu->url, 'dapat_hapus'))->toBeTrue();
});
