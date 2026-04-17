<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PemeliharaanService
{
    /**
     * Kembalikan informasi versi sistem yang terpasang.
     */
    public function infoVersi(): array
    {
        $laravelVersion = app()->version();
        $phpVersion = PHP_VERSION;

        $livewireVersion = $this->versiPaket('livewire/volt');
        $bootstrapVersion = $this->versiNpm('bootstrap');

        return [
            'laravel' => $laravelVersion,
            'php' => $phpVersion,
            'livewire' => $livewireVersion,
            'bootstrap' => $bootstrapVersion,
        ];
    }

    /**
     * Kembalikan data migration yang lebih efektif untuk ditampilkan di UI.
     *
     * @return array{ringkasan: array{total:int,run:int,pending:int}, daftar: array<int,array{nama:string,status:string,batch:int|null}>, dibatasi_run: bool, maks_run: int}
     */
    public function dataMigrationEfektif(string $filter = 'pending', int $maksRun = 50): array
    {
        $migrationsTable = [];

        if (Schema::hasTable('migrations')) {
            $migrationsTable = DB::table('migrations')
                ->select('migration', 'batch')
                ->get()
                ->keyBy('migration')
                ->map(fn ($item) => ['batch' => (int) $item->batch])
                ->all();
        }

        $pending = [];
        $run = [];

        $files = glob(database_path('migrations/*.php')) ?: [];

        foreach ($files as $file) {
            $nama = pathinfo($file, PATHINFO_FILENAME);
            $isRun = array_key_exists($nama, $migrationsTable);

            $baris = [
                'nama' => $nama,
                'status' => $isRun ? 'run' : 'pending',
                'batch' => $isRun ? (int) ($migrationsTable[$nama]['batch'] ?? 0) : null,
            ];

            if ($isRun) {
                $run[] = $baris;
            } else {
                $pending[] = $baris;
            }
        }

        // Pending diurutkan ascending agar sesuai urutan eksekusi migration.
        usort($pending, fn ($a, $b) => strcmp($a['nama'], $b['nama']));

        // Run diurutkan descending agar migration terbaru ada di atas.
        usort($run, fn ($a, $b) => strcmp($b['nama'], $a['nama']));

        $total = count($pending) + count($run);
        $runAsli = count($run);
        $dibatasiRun = false;

        if ($filter === 'pending') {
            $daftar = $pending;
        } else {
            if ($maksRun > 0 && $runAsli > $maksRun) {
                $run = array_slice($run, 0, $maksRun);
                $dibatasiRun = true;
            }

            // Pending tetap di atas, run di bawah agar tindakan cepat lebih jelas.
            $daftar = array_merge($pending, $run);
        }

        return [
            'ringkasan' => [
                'total' => $total,
                'run' => $runAsli,
                'pending' => count($pending),
            ],
            'daftar' => $daftar,
            'dibatasi_run' => $dibatasiRun,
            'maks_run' => $maksRun,
        ];
    }

    /**
     * Kompatibilitas untuk pemanggilan lama.
     */
    public function statusMigration(): array
    {
        return $this->dataMigrationEfektif('semua')['daftar'];
    }

    /**
     * Hitung jumlah migration yang belum dijalankan.
     */
    public function jumlahPendingMigration(): int
    {
        return $this->dataMigrationEfektif('pending')['ringkasan']['pending'];
    }

    /**
     * Jalankan semua migration yang pending.
     * Kembalikan output Artisan.
     */
    public function jalankanMigration(): string
    {
        Artisan::call('migrate', ['--force' => true]);

        return Artisan::output();
    }

    /**
     * Bersihkan semua cache aplikasi (config, view, route, cache, event).
     * Kembalikan ringkasan.
     */
    public function bersihkanCache(): array
    {
        $hasil = [];

        $perintah = [
            'config:clear' => 'Cache konfigurasi',
            'cache:clear' => 'Cache aplikasi',
            'route:clear' => 'Cache route',
            'view:clear' => 'Cache view',
            'event:clear' => 'Cache event',
        ];

        foreach ($perintah as $cmd => $label) {
            try {
                Artisan::call($cmd);
                $output = trim(Artisan::output());
                $hasil[] = [
                    'label' => $label,
                    'perintah' => 'php artisan '.$cmd,
                    'output' => $output ?: 'Selesai.',
                    'sukses' => true,
                ];
            } catch (\Throwable $e) {
                $hasil[] = [
                    'label' => $label,
                    'perintah' => 'php artisan '.$cmd,
                    'output' => $e->getMessage(),
                    'sukses' => false,
                ];
            }
        }

        return $hasil;
    }

    /**
     * Kembalikan ringkasan status sistem secara keseluruhan.
     */
    public function ringkasanStatus(): array
    {
        $pendingMigration = $this->jumlahPendingMigration();
        $dbTerhubung = $this->cekKoneksiDb();

        return [
            'db_terhubung' => $dbTerhubung,
            'pending_migration' => $pendingMigration,
            'storage_writable' => is_writable(storage_path()),
            'env' => app()->environment(),
            'debug_mode' => config('app.debug'),
        ];
    }

    // ─── Private helpers ────────────────────────────────────────────────────

    private function versiPaket(string $paket): string
    {
        $lockPath = base_path('composer.lock');
        if (! file_exists($lockPath)) {
            return 'N/A';
        }

        $lock = json_decode(file_get_contents($lockPath), true);
        foreach (array_merge($lock['packages'] ?? [], $lock['packages-dev'] ?? []) as $pkg) {
            if ($pkg['name'] === $paket) {
                return ltrim($pkg['version'] ?? 'N/A', 'v');
            }
        }

        return 'N/A';
    }

    private function versiNpm(string $paket): string
    {
        $lockPath = base_path('package-lock.json');
        if (file_exists($lockPath)) {
            $lock = json_decode(file_get_contents($lockPath), true);
            $ver = data_get($lock, "packages.node_modules/{$paket}.version");
            if ($ver) {
                return ltrim($ver, 'v');
            }
        }

        $pkgPath = base_path('package.json');
        if (file_exists($pkgPath)) {
            $pkg = json_decode(file_get_contents($pkgPath), true);
            $ver = data_get($pkg, "dependencies.{$paket}")
                ?? data_get($pkg, "devDependencies.{$paket}");
            if ($ver) {
                return ltrim($ver, '^~v');
            }
        }

        return 'N/A';
    }

    private function cekKoneksiDb(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
