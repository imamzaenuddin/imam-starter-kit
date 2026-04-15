<?php

use App\Models\Level;
use App\Models\Menu;
use App\Models\User;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function buatUserDenganIzinPemeliharaan(string $email): User
{
    $level = Level::create([
        'nama_level' => 'Level Test ' . uniqid(),
        'deskripsi' => 'Level untuk test pemeliharaan',
        'is_active' => true,
    ]);

    $menu = Menu::firstOrCreate(
        ['url' => '/admin/pemeliharaan'],
        [
            'nama' => 'Pemeliharaan',
            'icon' => 'bx bx-wrench',
            'urutan' => 23,
            'is_active' => true,
        ]
    );

    $level->menus()->syncWithoutDetaching([
        $menu->id => [
            'dapat_lihat' => true,
            'dapat_buat' => false,
            'dapat_ubah' => true,
            'dapat_hapus' => false,
            'dapat_backup' => false,
            'dapat_restore' => false,
            'dapat_hapus_backup' => false,
        ],
    ]);

    return User::factory()->create([
        'email' => $email,
        'level_id' => $level->id,
    ]);
}

test('preferensi migration tersimpan dan termuat ulang dari session', function () {
    $user = buatUserDenganIzinPemeliharaan('pref-1@example.com');

    $this->actingAs($user);

    Volt::test('admin.pemeliharaan.index')
        ->call('setFilterMigration', 'semua')
        ->set('perPageMigration', 50)
        ->call('urutkanMigration', 'status')
        ->assertSet('filterMigration', 'semua')
        ->assertSet('perPageMigration', 50)
        ->assertSet('sortMigrationBy', 'status');

    Volt::test('admin.pemeliharaan.index')
        ->assertSet('filterMigration', 'semua')
        ->assertSet('perPageMigration', 50)
        ->assertSet('sortMigrationBy', 'status');
});

test('reset pengaturan migration kembali ke default cerdas', function () {
    $user = buatUserDenganIzinPemeliharaan('pref-2@example.com');

    $this->actingAs($user);

    Volt::test('admin.pemeliharaan.index')
        ->call('setFilterMigration', 'semua')
        ->set('searchMigration', 'create_users')
        ->set('perPageMigration', 100)
        ->call('urutkanMigration', 'batch')
        ->call('resetPengaturanMigration')
        ->assertSet('filterMigration', 'pending')
        ->assertSet('searchMigration', '')
        ->assertSet('perPageMigration', 20)
        ->assertSet('sortMigrationBy', 'nama')
        ->assertSet('sortMigrationDir', 'asc');
});

test('preferensi migration terisolasi per user', function () {
    $userA = buatUserDenganIzinPemeliharaan('pref-a@example.com');
    $userB = buatUserDenganIzinPemeliharaan('pref-b@example.com');

    $this->actingAs($userA);

    Volt::test('admin.pemeliharaan.index')
        ->call('setFilterMigration', 'semua')
        ->set('perPageMigration', 100)
        ->call('urutkanMigration', 'batch');

    $this->actingAs($userB);

    Volt::test('admin.pemeliharaan.index')
        ->assertSet('filterMigration', 'pending')
        ->assertSet('perPageMigration', 20)
        ->assertSet('sortMigrationBy', 'nama')
        ->assertSet('sortMigrationDir', 'asc');
});

test('default sort mengikuti filter migration yang dipilih', function () {
    $user = buatUserDenganIzinPemeliharaan('pref-sort@example.com');

    $this->actingAs($user);

    Volt::test('admin.pemeliharaan.index')
        ->call('setFilterMigration', 'semua')
        ->assertSet('sortMigrationBy', 'batch')
        ->assertSet('sortMigrationDir', 'desc')
        ->call('setFilterMigration', 'pending')
        ->assertSet('sortMigrationBy', 'nama')
        ->assertSet('sortMigrationDir', 'asc');
});

test('export csv migration mengikuti filter dan dapat diunduh', function () {
    $user = buatUserDenganIzinPemeliharaan('pref-export@example.com');

    $this->actingAs($user);

    Volt::test('admin.pemeliharaan.index')
        ->call('setFilterMigration', 'semua')
        ->set('searchMigration', '000045')
        ->call('exportMigrationCsv')
        ->assertFileDownloaded();
});
