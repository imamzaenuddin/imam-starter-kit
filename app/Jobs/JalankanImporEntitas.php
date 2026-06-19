<?php

namespace App\Jobs;

use App\Models\LogMigrasi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class JalankanImporEntitas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Timeout maksimal: 2 jam untuk data besar
     */
    public int $timeout = 7200;

    /**
     * Tidak retry otomatis — migrasi harus dijalankan ulang manual jika gagal
     */
    public int $tries = 1;

    public function __construct(
        public readonly int    $logMigrasiId,
        public readonly string $entitas,
    ) {}

    public function handle(): void
    {
        $log = LogMigrasi::find($this->logMigrasiId);

        if (! $log) {
            return;
        }

        try {
            // Update status → running
            $log->update([
                'status'     => 'running',
                'started_at' => now(),
                'job_id'     => $this->job?->getJobId(),
            ]);

            // Jalankan artisan command dengan entity spesifik
            Artisan::call('import:legacy', [
                '--entity' => $this->entitas,
                '--log-id' => $this->logMigrasiId,
            ]);

            // Ambil output statistik dari output command
            // (ImportLegacyData akan update log langsung ke DB)

            // Pastikan status done jika command tidak update sendiri
            $log->refresh();
            if ($log->status === 'running') {
                $log->update([
                    'status'      => 'done',
                    'finished_at' => now(),
                ]);
            }

        } catch (\Throwable $e) {
            $log->update([
                'status'      => 'error',
                'finished_at' => now(),
                'pesan_error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle job failure (jika antrian fail di luar handle)
     */
    public function failed(\Throwable $exception): void
    {
        $log = LogMigrasi::find($this->logMigrasiId);
        $log?->update([
            'status'      => 'error',
            'finished_at' => now(),
            'pesan_error' => '[Queue Failed] ' . $exception->getMessage(),
        ]);
    }
}
