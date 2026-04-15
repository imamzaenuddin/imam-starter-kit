<?php

use App\Models\Level;
use App\Models\Menu;
use App\Models\User;
use App\Services\BackupRestoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

// ============ SETUP FIXTURE ============

function buatLevelDenganAksiSensitif(string $namaLevel, bool $backup, bool $restore, bool $hapusBackup): Level
{
    $level = Level::firstOrCreate(
        ['nama_level' => $namaLevel],
        ['deskripsi' => $namaLevel . ' level', 'is_active' => true]
    );

    $menu = Menu::firstOrCreate(
        ['url' => '/admin/backup-restore'],
        [
            'nama' => 'Backup & Restore',
            'icon' => 'bx bx-data',
            'urutan' => 99,
            'is_active' => true,
        ]
    );

    DB::table('m_level_menu')->updateOrInsert(
        ['level_id' => $level->id, 'menu_id' => $menu->id],
        [
            'dapat_lihat' => true,
            'dapat_buat' => false,
            'dapat_ubah' => false,
            'dapat_hapus' => false,
            'dapat_backup' => $backup,
            'dapat_restore' => $restore,
            'dapat_hapus_backup' => $hapusBackup,
        ]
    );

    return $level;
}

// ============ ITEM 2: ROLE & PERMISSION GRANULAR ============

test('user dengan izin dapat_backup bisa melakukan aksi backup', function () {
    $level = buatLevelDenganAksiSensitif('admin-backup', true, false, false);
    $user = User::factory()->create(['level_id' => $level->id]);

    expect($user->bisaAksiSensitif('/admin/backup-restore', 'backup'))->toBeTrue();
});

test('user tanpa izin dapat_backup tidak bisa melakukan aksi backup', function () {
    $level = buatLevelDenganAksiSensitif('admin-norestore', false, false, false);
    $user = User::factory()->create(['level_id' => $level->id]);

    expect($user->bisaAksiSensitif('/admin/backup-restore', 'backup'))->toBeFalse();
});

test('user dengan izin dapat_restore bisa melakukan restore', function () {
    $level = buatLevelDenganAksiSensitif('admin-restore', false, true, false);
    $user = User::factory()->create(['level_id' => $level->id]);

    expect($user->bisaAksiSensitif('/admin/backup-restore', 'restore'))->toBeTrue();
});

test('user tanpa izin dapat_restore tidak bisa restore', function () {
    $level = buatLevelDenganAksiSensitif('admin-norestore2', true, false, false);
    $user = User::factory()->create(['level_id' => $level->id]);

    expect($user->bisaAksiSensitif('/admin/backup-restore', 'restore'))->toBeFalse();
});

test('user dengan izin dapat_hapus_backup bisa menghapus file backup', function () {
    $level = buatLevelDenganAksiSensitif('admin-hapus', false, false, true);
    $user = User::factory()->create(['level_id' => $level->id]);

    expect($user->bisaAksiSensitif('/admin/backup-restore', 'hapus_backup'))->toBeTrue();
});

test('user tanpa level tidak memiliki izin aksi sensitif apapun', function () {
    $user = User::factory()->create(['level_id' => null]);

    expect($user->bisaAksiSensitif('/admin/backup-restore', 'backup'))->toBeFalse();
    expect($user->bisaAksiSensitif('/admin/backup-restore', 'restore'))->toBeFalse();
    expect($user->bisaAksiSensitif('/admin/backup-restore', 'hapus_backup'))->toBeFalse();
});

test('aksi tidak dikenal selalu mengembalikan false', function () {
    $level = buatLevelDenganAksiSensitif('admin-unknown', true, true, true);
    $user = User::factory()->create(['level_id' => $level->id]);

    expect($user->bisaAksiSensitif('/admin/backup-restore', 'unknown_action'))->toBeFalse();
});

test('superadmin dengan semua izin bisa backup restore dan hapus', function () {
    $level = buatLevelDenganAksiSensitif('superadmin', true, true, true);
    $user = User::factory()->create(['level_id' => $level->id]);

    expect($user->bisaAksiSensitif('/admin/backup-restore', 'backup'))->toBeTrue();
    expect($user->bisaAksiSensitif('/admin/backup-restore', 'restore'))->toBeTrue();
    expect($user->bisaAksiSensitif('/admin/backup-restore', 'hapus_backup'))->toBeTrue();
});

// ============ ITEM 3: BACKUP SCHEDULER ============

test('artisan command backup:jalan terdaftar di aplikasi', function () {
    $commands = Artisan::all();
    expect(array_key_exists('backup:jalan', $commands))->toBeTrue();
});

test('backup scheduler dikonfigurasi di console.php', function () {
    // Verifikasi bahwa schedule sudah dikonfigurasi dengan memeriksa
    // keberadaan PengaturanBackupService dan konfigurasi default
    $konfigurasi = app(\App\Services\PengaturanBackupService::class)->konfigurasiScheduler();

    expect($konfigurasi)->toHaveKeys([
        'jadwal_harian_tipe',
        'jadwal_harian_jam',
        'jadwal_mingguan_tipe',
        'jadwal_mingguan_hari',
        'jadwal_mingguan_jam',
        'retensi_hari',
    ]);

    expect((int) $konfigurasi['retensi_hari'])->toBeGreaterThan(0);
});

test('konfigurasi retensi default adalah 30 hari', function () {
    $konfigurasi = app(\App\Services\PengaturanBackupService::class)->konfigurasiScheduler();
    expect((int) $konfigurasi['retensi_hari'])->toBe(30);
});

test('tipe backup yang valid adalah full transaksi dan master', function () {
    $service = app(BackupRestoreService::class);
    $pilihan = array_keys($service->pilihanTipe());

    expect($pilihan)->toContain('full');
    expect($pilihan)->toContain('transaksi');
    expect($pilihan)->toContain('master');
});

// ============ ITEM 4: RESTORE SAFETY MODE ============

test('PengaturanBackupService memiliki opsi restore auto backup', function () {
    $konfigurasi = app(\App\Services\PengaturanBackupService::class)->konfigurasiScheduler();

    expect($konfigurasi)->toHaveKey('restore_auto_backup');
    expect($konfigurasi)->toHaveKey('restore_auto_backup_tipe');
});

test('restore auto backup aktif secara default', function () {
    $konfigurasi = app(\App\Services\PengaturanBackupService::class)->konfigurasiScheduler();

    expect((bool) $konfigurasi['restore_auto_backup'])->toBeTrue();
});

test('konfigurasi restore lock timeout default lebih dari 0', function () {
    $konfigurasi = app(\App\Services\PengaturanBackupService::class)->konfigurasiScheduler();

    expect($konfigurasi)->toHaveKey('restore_lock_timeout_detik');
    expect((int) $konfigurasi['restore_lock_timeout_detik'])->toBeGreaterThan(0);
});

test('BackupRestoreService memiliki method restoreAman untuk safety mode', function () {
    $service = app(BackupRestoreService::class);

    expect(method_exists($service, 'restoreAman'))->toBeTrue();
});

test('BackupRestoreService memiliki method hapusBackupKadaluarsa untuk retensi', function () {
    $service = app(BackupRestoreService::class);

    expect(method_exists($service, 'hapusBackupKadaluarsa'))->toBeTrue();
});

test('BackupRestoreService memiliki method riwayatBackup untuk list file', function () {
    $service = app(BackupRestoreService::class);
    $riwayat = $service->riwayatBackup();

    expect($riwayat)->toBeArray();
});

// ============ AKSESIBILITAS HALAMAN ============

test('guest tidak bisa akses halaman backup-restore', function () {
    $response = $this->get('/admin/backup-restore');
    $response->assertRedirect('/login');
});

test('user tanpa izin mendapat 403 saat akses halaman backup-restore', function () {
    $level = Level::firstOrCreate(
        ['nama_level' => 'anggota'],
        ['deskripsi' => 'Anggota biasa', 'is_active' => true]
    );
    $user = User::factory()->create(['level_id' => $level->id]);

    $response = $this->actingAs($user)->get('/admin/backup-restore');

    // Tidak ada menu-level mapping = abort 403
    expect($response->getStatusCode())->toBe(403);
});
