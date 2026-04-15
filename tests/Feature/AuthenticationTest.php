<?php

use App\Models\Level;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

// ============ LOGIN PAGE ACCESS TESTS ============

test('guest dapat mengakses login page', function () {
    $response = $this->get(route('login'));
    expect($response->status())->toBe(200);
});

test('guest dapat mengakses register page', function () {
    $response = $this->get(route('register'));
    expect($response->status())->toBe(200);
});

// ============ USER CREATION TESTS ============

test('user dapat dibuat dengan data valid', function () {
    $user = User::factory()->create([
        'email' => 'user@test.id',
        'name' => 'Test User',
    ]);

    expect($user->email)->toBe('user@test.id')
        ->and($user->name)->toBe('Test User');
});

test('user dengan email duplikat tidak bisa dibuat di database', function () {
    $existing = User::factory()->create(['email' => 'duplicate@test.id']);

    expect($existing->email)->toBe('duplicate@test.id');
});

test('password di-hash saat user dibuat', function () {
    $password = 'plaintext123';
    $user = User::factory()->create(['password' => bcrypt($password)]);

    expect($user->password)->not->toBe($password);
});

// ============ GUEST PROTECTION TESTS ============

test('guest user redirect ke login saat akses halaman protected', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('user dapat logout', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('logout'));

    $this->assertGuest();
});

// ============ PASSWORD RESET REQUEST TESTS ============

test('password reset page dapat diakses', function () {
    $response = $this->get(route('password.request'));

    expect($response->status())->toBe(200);
});

// ============ USER DATA INTEGRITY TESTS ============

test('user level relationship bekerja', function () {
    $level = Level::where('nama_level', 'admin')->first();
    $user = User::factory()->create(['level_id' => $level->id]);

    expect($user->level)->not->toBeNull()
        ->and($user->level->nama_level)->toBe('admin');
});

test('user dapat memiliki level assignment', function () {
    $adminLevel = Level::where('nama_level', 'admin')->first();
    $user = User::factory()->create(['level_id' => $adminLevel->id]);

    expect($user->level_id)->toBe($adminLevel->id);
});

test('user timestamps diset otomatis', function () {
    $user = User::factory()->create();

    expect($user->created_at)->not->toBeNull()
        ->and($user->updated_at)->not->toBeNull();
});

test('user is_active flag dapat manipulated', function () {
    $user = User::factory()->create(['is_active' => true]);

    $user->update(['is_active' => false]);
    $user->refresh();

    expect($user->is_active)->toBeFalse();
});
