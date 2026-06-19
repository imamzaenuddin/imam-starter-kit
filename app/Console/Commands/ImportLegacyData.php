<?php

namespace App\Console\Commands;

use App\Models\LogMigrasi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLegacyData extends Command
{
    /**
     * Blueprint Step 2 + Fase 1-3: Engine ETL One-Time Dump
     * Signature dengan opsi --entity, --fase, dan --log-id untuk integrasi UI.
     */
    protected $signature = 'import:legacy
                            {--entity= : Nama entitas spesifik (kosong = semua)}
                            {--fase=   : Filter fase: master | transaksi}
                            {--log-id= : ID LogMigrasi untuk update status dari UI}';

    protected $description = 'One-Time Dump: Migrasi data dari database legacy ke SIAP V6';

    private ?LogMigrasi $logMigrasi = null;

    public function handle(): void
    {
        // ✅ ATURAN WAJIB (Blueprint §4) — Memory & Query Log Management
        set_time_limit(0);
        DB::connection()->disableQueryLog();
        DB::connection('legacy')->disableQueryLog();

        // Bind ke LogMigrasi jika dipanggil dari UI
        if ($this->option('log-id')) {
            $this->logMigrasi = LogMigrasi::find((int) $this->option('log-id'));
        }

        $entity = $this->option('entity');
        $fase   = $this->option('fase');

        $this->info('🚀 Memulai proses ETL One-Time Dump — SIAP V6');
        $this->newLine();

        // ── Pipeline — urutan WAJIB: Induk (Level 0) → Anak (Level N) ──
        $pipelineMaster = [
            // Level 0
            // 'jenjang'         => fn() => $this->importJenjang(),
            // 'fakultas'        => fn() => $this->importFakultas(),
            // 'agama'           => fn() => $this->importAgama(),
            // 'tahun_akademik'  => fn() => $this->importTahunAkademik(),

            // Level 1
            'program_studi'   => fn() => $this->importProgramStudi(),

            // Level 2
            // 'dosen'           => fn() => $this->importDosen(),
        ];

        $pipelineTransaksi = [
            // Level 3 (FK ke Level 2)
            // 'mahasiswa'       => fn() => $this->importMahasiswa(),
            // 'krs'             => fn() => $this->importKrs(),
            // 'nilai'           => fn() => $this->importNilai(),
        ];

        // Tentukan pipeline yang akan dijalankan
        $pipeline = match ($fase) {
            'master'    => $pipelineMaster,
            'transaksi' => $pipelineTransaksi,
            default     => array_merge($pipelineMaster, $pipelineTransaksi),
        };

        if ($entity) {
            if (array_key_exists($entity, $pipeline)) {
                $this->line("  ➡ Mengimpor: <comment>{$entity}</comment>");
                // Disable FK check
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                $pipeline[$entity]();
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } else {
                // Cari di pemetaan_tabels
                $pemetaan = \App\Models\MigrasiPemetaanTabel::where('tabel_legacy', $entity)->first();
                if ($pemetaan) {
                    $this->line("  ➡ Mengimpor Tabel Dinamis: <comment>{$entity}</comment> ➔ <info>{$pemetaan->tabel_baru}</info>");
                    
                    DB::statement('SET FOREIGN_KEY_CHECKS=0');
                    $stats = $this->importTabelDinamis($pemetaan);
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                    
                    if ($this->logMigrasi) {
                        $this->logMigrasi->update([
                            'total_imported' => $stats['imported'],
                            'total_skipped'  => $stats['skipped'],
                            'total_error'    => $stats['errors'],
                        ]);
                    }
                } else {
                    $this->error("❌ Entitas/Tabel '{$entity}' tidak ditemukan.");
                    $this->logMigrasi?->update(['status' => 'error', 'pesan_error' => "Entitas tidak ditemukan: {$entity}", 'finished_at' => now()]);
                    return;
                }
            }
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach ($pipeline as $name => $importFn) {
                $this->line("  ➡ Mengimpor: <comment>{$name}</comment>");
                $importFn();
                $this->newLine();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->newLine();
        $this->info('✅ ETL One-Time Dump selesai.');

        // Update log jika dipanggil dari UI
        $this->logMigrasi?->update(['status' => 'done', 'finished_at' => now()]);
    }

    // ──────────────────────────────────────────────────────────────
    // TEMPLATE METHOD — Ikuti pola ini untuk setiap entitas baru
    // ──────────────────────────────────────────────────────────────

    private function importProgramStudi(): void
    {
        $mapFakultas = DB::table('fakultas')
            ->whereNotNull('id_legacy')
            ->pluck('id', 'id_legacy');

        $stats = $this->processInChunks(
            legacyTable : 'm_prodi',
            targetTable : 'program_studis',
            chunkSize   : 500,
            orderByCol  : 'kd_prodi',
            transformer : function (object $row) use ($mapFakultas): array {
                return [
                    'id_legacy'    => $row->kd_prodi,
                    'kode'         => $row->kd_prodi,
                    'nama'         => $row->nm_prodi,
                    'jenjang'      => $row->jenjang ?? null,
                    'fakultas_id'  => $mapFakultas[$row->kd_fak ?? ''] ?? null,
                    'is_aktif'     => (int) ($row->status ?? 1),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
        );

        // Update statistik ke LogMigrasi
        if ($this->logMigrasi) {
            $this->logMigrasi->update([
                'total_imported' => $stats['imported'],
                'total_skipped'  => $stats['skipped'],
                'total_error'    => $stats['errors'],
            ]);
        }
    }

    private function importTabelDinamis(\App\Models\MigrasiPemetaanTabel $pemetaan): array
    {
        // Cari tahu primary key legacy untuk sorting/chunking
        $pkLegacy = 'id';
        if (!empty($pemetaan->pemetaan_field)) {
            foreach ($pemetaan->pemetaan_field as $field) {
                if (($field['key'] ?? '') === 'PRI') {
                    $pkLegacy = $field['legacy'];
                    break;
                }
            }
        }

        $legacyTable = $pemetaan->tabel_legacy;
        $targetTable = $pemetaan->tabel_baru;
        $mapping     = $pemetaan->pemetaan_field ?? [];

        // Gunakan processInChunks dengan transformer dinamis
        return $this->processInChunks(
            legacyTable : $legacyTable,
            targetTable : $targetTable,
            chunkSize   : 500,
            orderByCol  : $pkLegacy,
            transformer : function (object $row) use ($mapping, $pkLegacy): array {
                $record = [];
                $legacyIdVal = $row->$pkLegacy ?? null;
                $record['id_legacy'] = (string) $legacyIdVal;

                foreach ($mapping as $field) {
                    if ($field['abaikan'] ?? false) {
                        continue;
                    }

                    $colLegacy = $field['legacy'];
                    $colBaru = $field['baru'];

                    if ($colBaru === 'id' || $colBaru === 'id_legacy') {
                        continue;
                    }

                    $record[$colBaru] = $row->$colLegacy ?? null;
                }

                $record['created_at'] = now();
                $record['updated_at'] = now();

                return $record;
            }
        );
    }

    // ──────────────────────────────────────────────────────────────
    // CORE ENGINE — Jangan modifikasi tanpa review Blueprint
    // ──────────────────────────────────────────────────────────────

    /**
     * Proses impor dengan chunking untuk efisiensi memori.
     * Cek duplikat otomatis berdasarkan kolom `id_legacy`.
     *
     * @return array ['imported' => int, 'skipped' => int, 'errors' => int]
     */
    private function processInChunks(
        string   $legacyTable,
        string   $targetTable,
        int      $chunkSize,
        string   $orderByCol,
        callable $transformer
    ): array {
        $total    = DB::connection('legacy')->table($legacyTable)->count();
        $bar      = $this->output->createProgressBar($total);
        $imported = 0;
        $skipped  = 0;
        $errors   = 0;

        // Update total_legacy ke log
        $this->logMigrasi?->update(['total_legacy' => $total]);

        $bar->start();

        DB::connection('legacy')
            ->table($legacyTable)
            ->orderBy($orderByCol)
            ->chunk($chunkSize, function ($rows) use (
                $targetTable, $transformer, $bar, &$imported, &$skipped, &$errors
            ) {
                $batch = [];

                foreach ($rows as $row) {
                    try {
                        $record = $transformer($row);

                        $exists = DB::table($targetTable)
                            ->where('id_legacy', $record['id_legacy'])
                            ->exists();

                        if (! $exists) {
                            $batch[] = $record;
                            $imported++;
                        } else {
                            $skipped++;
                        }
                    } catch (\Throwable $e) {
                        $this->warn('  ⚠ Baris dilewati: ' . $e->getMessage());
                        $errors++;
                    }
                }

                if (! empty($batch)) {
                    DB::table($targetTable)->insert($batch);
                }

                $bar->advance(count($rows));
            });

        $bar->finish();
        $this->newLine();
        $this->line("     ✅ Imported: <info>{$imported}</info> | ⏭ Skipped: <comment>{$skipped}</comment> | ❌ Error: <fg=red>{$errors}</fg=red>");

        return compact('imported', 'skipped', 'errors');
    }
}
