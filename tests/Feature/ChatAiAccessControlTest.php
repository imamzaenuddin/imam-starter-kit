<?php

use App\Models\ChatAiSumber;
use App\Models\Level;
use App\Models\LogAktivitas;
use App\Models\Menu;
use App\Models\User;
use App\Services\ChatAiAnalisisService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buatUserDenganAksesChatAi(string $namaLevel, string $email): User
{
    $level = Level::query()->firstOrCreate(
        ['nama_level' => $namaLevel],
        ['deskripsi' => $namaLevel.' level', 'is_active' => true]
    );

    $menuLaporan = Menu::query()->firstOrCreate(
        ['nama' => 'Laporan', 'parent_id' => null],
        ['url' => null, 'icon' => 'bx bx-bar-chart-alt-2', 'urutan' => 30, 'is_active' => true]
    );

    $menuChatAi = Menu::query()->firstOrCreate(
        ['nama' => 'Chat Asisten Analitik', 'parent_id' => $menuLaporan->id],
        ['url' => '/laporan/chat-ai', 'icon' => 'bx bx-bot', 'urutan' => 33, 'is_active' => true]
    );

    $level->menus()->syncWithoutDetaching([
        $menuLaporan->id => [
            'dapat_lihat' => true,
            'dapat_buat' => false,
            'dapat_ubah' => false,
            'dapat_hapus' => false,
            'dapat_backup' => false,
            'dapat_restore' => false,
            'dapat_hapus_backup' => false,
        ],
        $menuChatAi->id => [
            'dapat_lihat' => true,
            'dapat_buat' => false,
            'dapat_ubah' => false,
            'dapat_hapus' => false,
            'dapat_backup' => false,
            'dapat_restore' => false,
            'dapat_hapus_backup' => false,
        ],
    ]);

    return User::factory()->create([
        'name' => $namaLevel.' User',
        'email' => $email,
        'level_id' => $level->id,
        'is_active' => true,
    ]);
}

test('non superadmin tidak menerima sumber chat ai bertanda data personal', function () {
    $admin = buatUserDenganAksesChatAi('Admin', 'admin-chat@test.id');

    ChatAiSumber::query()->create([
        'nama' => 'Data Personal User',
        'sumber_data' => 'users',
        'tipe_data' => 'daftar',
        'tipe_query' => 'count',
        'kolom_tampil' => ['name', 'email'],
        'batas_data' => 5,
        'is_data_personal' => true,
        'is_active' => true,
        'urutan' => 1,
    ]);

    $hasil = app(ChatAiAnalisisService::class)->analisa('tampilkan data user', $admin);

    $namaSumber = collect(data_get($hasil, 'konteks.konteks_dinamis', []))
        ->pluck('nama')
        ->all();

    expect($namaSumber)->not->toContain('Data Personal User');
});

test('superadmin dapat menerima sumber chat ai bertanda data personal', function () {
    $superadmin = buatUserDenganAksesChatAi('Superadmin', 'superadmin-chat@test.id');

    ChatAiSumber::query()->create([
        'nama' => 'Data Personal User',
        'sumber_data' => 'users',
        'tipe_data' => 'daftar',
        'tipe_query' => 'count',
        'kolom_tampil' => ['name', 'email'],
        'batas_data' => 5,
        'is_data_personal' => true,
        'is_active' => true,
        'urutan' => 1,
    ]);

    $hasil = app(ChatAiAnalisisService::class)->analisa('tampilkan data user', $superadmin);

    $namaSumber = collect(data_get($hasil, 'konteks.konteks_dinamis', []))
        ->pluck('nama')
        ->all();

    expect($namaSumber)->toContain('Data Personal User');
});

test('sumber dinamis mengikuti mapping level yang dipilih', function () {
    $superadmin = buatUserDenganAksesChatAi('Superadmin', 'superadmin-level@test.id');
    $admin = buatUserDenganAksesChatAi('Admin', 'admin-level@test.id');

    $sumber = ChatAiSumber::query()->create([
        'nama' => 'Khusus Superadmin',
        'sumber_data' => 'levels',
        'tipe_data' => 'statistik',
        'tipe_query' => 'count',
        'batas_data' => 10,
        'is_data_personal' => false,
        'is_active' => true,
        'urutan' => 1,
    ]);

    $sumber->levels()->sync([$superadmin->level_id]);

    $hasilAdmin = app(ChatAiAnalisisService::class)->analisa('ringkasan data', $admin);
    $hasilSuperadmin = app(ChatAiAnalisisService::class)->analisa('ringkasan data', $superadmin);

    $namaSumberAdmin = collect(data_get($hasilAdmin, 'konteks.konteks_dinamis', []))->pluck('nama')->all();
    $namaSumberSuperadmin = collect(data_get($hasilSuperadmin, 'konteks.konteks_dinamis', []))->pluck('nama')->all();

    expect($namaSumberAdmin)->not->toContain('Khusus Superadmin')
        ->and($namaSumberSuperadmin)->toContain('Khusus Superadmin');
});

test('kolom email otomatis dihilangkan untuk non superadmin pada sumber users', function () {
    $admin = buatUserDenganAksesChatAi('Admin', 'admin-email@test.id');

    $sumber = ChatAiSumber::query()->create([
        'nama' => 'Daftar User Umum',
        'sumber_data' => 'users',
        'tipe_data' => 'daftar',
        'tipe_query' => 'count',
        'kolom_tampil' => ['name', 'email'],
        'batas_data' => 3,
        'is_data_personal' => false,
        'is_active' => true,
        'urutan' => 1,
    ]);

    $sumber->levels()->sync([$admin->level_id]);

    $hasil = app(ChatAiAnalisisService::class)->analisa('data user terbaru', $admin);

    $item = collect(data_get($hasil, 'konteks.konteks_dinamis', []))
        ->firstWhere('nama', 'Daftar User Umum');

    expect($item)->not->toBeNull();

    $rows = data_get($item, 'hasil', []);

    expect($rows)->not->toBeEmpty();

    foreach ($rows as $row) {
        expect(array_key_exists('email', $row))->toBeFalse();
    }
});

test('audit log redaksi kolom personal tercatat untuk admin non superadmin', function () {
    $admin = buatUserDenganAksesChatAi('Admin', 'admin-audit@test.id');

    $sumber = ChatAiSumber::query()->create([
        'nama' => 'Audit Redaksi Email',
        'sumber_data' => 'users',
        'tipe_data' => 'daftar',
        'tipe_query' => 'count',
        'kolom_tampil' => ['name', 'email'],
        'batas_data' => 3,
        'is_data_personal' => false,
        'is_active' => true,
        'urutan' => 1,
    ]);

    $sumber->levels()->sync([$admin->level_id]);

    app(ChatAiAnalisisService::class)->analisa('audit redaksi kolom personal', $admin);

    $log = LogAktivitas::query()
        ->where('user_id', $admin->id)
        ->where('modul', 'Chat AI Keamanan')
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and((array) data_get($log, 'metadata.kolom_disensor', []))->toContain('email');
});

test('hasil analisa menyertakan ringkasan redaksi otomatis', function () {
    $admin = buatUserDenganAksesChatAi('Admin', 'admin-ringkasan@test.id');

    $sumber = ChatAiSumber::query()->create([
        'nama' => 'Ringkasan Redaksi Email',
        'sumber_data' => 'users',
        'tipe_data' => 'daftar',
        'tipe_query' => 'count',
        'kolom_tampil' => ['name', 'email'],
        'batas_data' => 3,
        'is_data_personal' => false,
        'is_active' => true,
        'urutan' => 1,
    ]);

    $sumber->levels()->sync([$admin->level_id]);

    $hasil = app(ChatAiAnalisisService::class)->analisa('cek ringkasan redaksi', $admin);

    expect(data_get($hasil, 'ringkasan_redaksi.ada_redaksi'))->toBeTrue()
        ->and((int) data_get($hasil, 'ringkasan_redaksi.jumlah_sumber_teredaksi'))->toBeGreaterThan(0)
        ->and((array) data_get($hasil, 'ringkasan_redaksi.kolom_disensor', []))->toContain('email');
});
