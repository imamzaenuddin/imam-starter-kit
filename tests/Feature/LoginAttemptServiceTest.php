<?php

use App\Models\LoginAttempt;
use App\Models\User;
use App\Services\LoginAttemptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(LoginAttemptService::class);
});

test('service mencatat login gagal dengan email', function () {
    $request = Request::create('/login', 'POST', [], [], [], ['REMOTE_ADDR' => '10.10.10.1']);
    $request->headers->set('User-Agent', 'Pest Test Agent');

    $this->service->catat('gagal', 'user@example.com', $request, null, 'Salah password');

    $data = LoginAttempt::query()->first();

    expect($data)->not->toBeNull()
        ->and($data->status)->toBe('gagal')
        ->and($data->email)->toBe('user@example.com')
        ->and($data->ip_address)->toBe('10.10.10.1')
        ->and($data->alasan)->toBe('Salah password');
});

test('service mencatat login sukses dengan relasi user', function () {
    $user = User::factory()->create(['email' => 'sukses@example.com']);
    $request = Request::create('/login', 'POST', [], [], [], ['REMOTE_ADDR' => '192.168.0.1']);

    $this->service->catat('sukses', $user->email, $request, $user, 'Login berhasil');

    $data = LoginAttempt::query()->first();

    expect($data->user_id)->toBe($user->id)
        ->and($data->status)->toBe('sukses')
        ->and($data->user)->not->toBeNull();
});

test('service menyimpan metadata lockout', function () {
    $request = Request::create('/login', 'POST', [], [], [], ['REMOTE_ADDR' => '172.16.1.99']);

    $this->service->catat('lockout', 'lockout@example.com', $request, null, 'Terlalu banyak percobaan', [
        'detik_tersisa' => 60,
    ]);

    $data = LoginAttempt::query()->first();

    expect($data->status)->toBe('lockout')
        ->and($data->metadata)->toBeArray()
        ->and($data->metadata['detik_tersisa'])->toBe(60);
});
