<?php

use App\Models\Notifikasi;
use App\Models\User;
use App\Services\NotifikasiService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('notification service can create notifications', function () {
    $user = User::factory()->create();

    $service = app(NotifikasiService::class);
    $notifikasi = $service->buat(
        $user->id,
        'Test Notification',
        'This is a test notification message',
        'info'
    );

    expect($notifikasi)->toHaveCount(1);
    expect($notifikasi->first()->judul)->toBe('Test Notification');
    expect($notifikasi->first()->user_id)->toBe($user->id);
    expect($notifikasi->first()->dibaca)->toBeFalse();
});

test('notification service can mark notification as read', function () {
    $user = User::factory()->create();
    $service = app(NotifikasiService::class);

    $notifikasi = $service->buat(
        $user->id,
        'Test',
        'Message',
        'info'
    )->first();

    expect($notifikasi->dibaca)->toBeFalse();

    $service->tandaiDibaca($notifikasi->id);

    $notifikasiRefresh = Notifikasi::find($notifikasi->id);
    expect($notifikasiRefresh->dibaca)->toBeTrue();
    expect($notifikasiRefresh->read_at)->not()->toBeNull();
});

test('notification model can scope unread notifications', function () {
    $user = User::factory()->create();

    Notifikasi::create([
        'user_id' => $user->id,
        'judul' => 'Notification 1',
        'pesan' => 'Message 1',
        'tipe' => 'info',
        'dibaca' => false,
    ]);

    Notifikasi::create([
        'user_id' => $user->id,
        'judul' => 'Notification 2',
        'pesan' => 'Message 2',
        'tipe' => 'info',
        'dibaca' => true,
    ]);

    $belumDibaca = Notifikasi::belumDibaca()->count();

    expect($belumDibaca)->toBe(1);
});

test('notification service can create backup complete notifications', function () {
    $user = User::factory()->create();
    $service = app(NotifikasiService::class);

    $notifikasi = $service->backupSelesai(
        [$user->id],
        [
            'tipe' => 'FULL',
            'ukuran' => '250 MB',
            'waktu' => '45 seconds',
        ]
    );

    expect($notifikasi)->toHaveCount(1);
    expect($notifikasi->first()->tipe)->toBe('backup_selesai');
    expect($notifikasi->first()->path_terkait)->toBe('/admin/backup-restore');
});

test('notification service can count unread notifications', function () {
    $user = User::factory()->create();
    $service = app(NotifikasiService::class);

    $service->buat(
        $user->id,
        'Test 1',
        'Message 1',
        'info'
    );

    $service->buat(
        $user->id,
        'Test 2',
        'Message 2',
        'info'
    );

    $jumlah = $service->hitungBelumDibaca($user->id);

    expect($jumlah)->toBe(2);
});

test('notification service can mark all user notifications as read', function () {
    $user = User::factory()->create();
    $service = app(NotifikasiService::class);

    $service->buat($user->id, 'Test 1', 'Message 1', 'info');
    $service->buat($user->id, 'Test 2', 'Message 2', 'info');
    $service->buat($user->id, 'Test 3', 'Message 3', 'info');

    expect($service->hitungBelumDibaca($user->id))->toBe(3);

    $service->tandaiSemuaDibaca($user->id);

    expect($service->hitungBelumDibaca($user->id))->toBe(0);
});

test('notification service can delete old notifications', function () {
    $user = User::factory()->create();

    // Create old read notification (should be deleted)
    $oldNotif = new Notifikasi([
        'user_id' => $user->id,
        'judul' => 'Old Notification',
        'pesan' => 'This is old',
        'tipe' => 'info',
        'dibaca' => true,
        'read_at' => now()->subDays(35),
    ]);
    $oldNotif->created_at = now()->subDays(35);
    $oldNotif->updated_at = now()->subDays(35);
    $oldNotif->save();

    // Create recent read notification (should NOT be deleted)
    $recentNotif = new Notifikasi([
        'user_id' => $user->id,
        'judul' => 'Recent Notification',
        'pesan' => 'This is recent',
        'tipe' => 'info',
        'dibaca' => true,
        'read_at' => now()->subDays(5),
    ]);
    $recentNotif->created_at = now()->subDays(5);
    $recentNotif->updated_at = now()->subDays(5);
    $recentNotif->save();

    $totalBefore = Notifikasi::count();

    $service = app(NotifikasiService::class);
    $terhapus = $service->hapusLama(30);

    $totalAfter = Notifikasi::count();

    expect($terhapus)->toBe(1);
    expect($totalAfter)->toBe($totalBefore - 1);
});

test('notification model can scope by user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Notifikasi::create([
        'user_id' => $user1->id,
        'judul' => 'User 1 Notification',
        'pesan' => 'Message',
        'tipe' => 'info',
    ]);

    Notifikasi::create([
        'user_id' => $user2->id,
        'judul' => 'User 2 Notification',
        'pesan' => 'Message',
        'tipe' => 'info',
    ]);

    $user1Notif = Notifikasi::untukUser($user1->id)->count();
    $user2Notif = Notifikasi::untukUser($user2->id)->count();

    expect($user1Notif)->toBe(1);
    expect($user2Notif)->toBe(1);
});
