<?php

use App\Services\BackupRestoreService;
use App\Services\PengaturanBackupService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('backup:jalan {tipe=full} {--retensi=}', function (BackupRestoreService $service) {
    $tipe = strtolower((string) $this->argument('tipe'));
    $pilihan = array_keys($service->pilihanTipe());

    if (! in_array($tipe, $pilihan, true)) {
        $this->error('Tipe backup tidak valid. Gunakan: full | transaksi | master');

        return 1;
    }

    $hasil = $service->buatBackup($tipe);
    $this->info('Backup berhasil: ' . $hasil['nama_file']);

    $retensi = $this->option('retensi');
    $retensiHari = is_numeric($retensi)
        ? (int) $retensi
        : (int) config('backup.retensi_hari', 30);

    $terhapus = $service->hapusBackupKadaluarsa($retensiHari);
    $this->info('Retensi backup: ' . $terhapus . ' file lama dihapus.');

    return 0;
})->purpose('Menjalankan backup database (full/transaksi/master) + retensi file lama');

$konfigurasiBackup = app(PengaturanBackupService::class)->konfigurasiScheduler();

Schedule::command('backup:jalan ' . ($konfigurasiBackup['jadwal_harian_tipe'] ?? 'transaksi'))
    ->dailyAt((string) ($konfigurasiBackup['jadwal_harian_jam'] ?? '01:00'))
    ->withoutOverlapping()
    ->onSuccess(function () {
        Log::info('Backup harian berhasil dijalankan oleh scheduler.');
    })
    ->onFailure(function () {
        Log::error('Backup harian gagal dijalankan oleh scheduler.');
    });

$hariMap = [
    'sunday' => 0,
    'monday' => 1,
    'tuesday' => 2,
    'wednesday' => 3,
    'thursday' => 4,
    'friday' => 5,
    'saturday' => 6,
];

$hariMingguan = strtolower((string) ($konfigurasiBackup['jadwal_mingguan_hari'] ?? 'sunday'));
$hariMingguanIndex = $hariMap[$hariMingguan] ?? 0;

Schedule::command('backup:jalan ' . ($konfigurasiBackup['jadwal_mingguan_tipe'] ?? 'full'))
    ->weeklyOn($hariMingguanIndex, (string) ($konfigurasiBackup['jadwal_mingguan_jam'] ?? '02:00'))
    ->withoutOverlapping()
    ->onSuccess(function () {
        Log::info('Backup mingguan berhasil dijalankan oleh scheduler.');
    })
    ->onFailure(function () {
        Log::error('Backup mingguan gagal dijalankan oleh scheduler.');
    });
