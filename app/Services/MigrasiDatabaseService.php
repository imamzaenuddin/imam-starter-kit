<?php

namespace App\Services;

use App\Jobs\JalankanImporEntitas;
use App\Models\LogMigrasi;
use App\Models\MigrasiPemetaanTabel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class MigrasiDatabaseService
{
    // ──────────────────────────────────────────────────────────────
    // KONEKSI & STATUS
    // ──────────────────────────────────────────────────────────────

    public function testKoneksiLegacy(): array
    {
        try {
            $pdo    = DB::connection('legacy')->getPdo();
            $dbName = DB::connection('legacy')->getDatabaseName();
            $host   = config('database.connections.legacy.host');
            $port   = config('database.connections.legacy.port');
            $tabel  = DB::connection('legacy')->select('SHOW TABLES');

            return [
                'status'    => 'ok',
                'pesan'     => 'Koneksi berhasil',
                'database'  => $dbName,
                'host'      => "{$host}:{$port}",
                'versi'     => $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION),
                'jml_tabel' => count($tabel),
            ];
        } catch (\Throwable $e) {
            return [
                'status'    => 'error',
                'pesan'     => $e->getMessage(),
                'database'  => config('database.connections.legacy.database'),
                'host'      => config('database.connections.legacy.host'),
                'jml_tabel' => 0,
            ];
        }
    }

    // ──────────────────────────────────────────────────────────────
    // SCAN TABEL
    // ──────────────────────────────────────────────────────────────

    /**
     * Scan semua tabel di DB legacy, simpan ke migrasi_pemetaan_tabels.
     * Sudah ada → update statistik. Belum ada → buat baru dengan klasifikasi = 'abaikan'.
     */
    public function scanTabelLegacy(int $userId): array
    {
        try {
            $allTables = DB::connection('legacy')->select('SHOW TABLES');
            $dbName    = DB::connection('legacy')->getDatabaseName();
            $colKey    = 'Tables_in_' . $dbName;

            $baru     = 0;
            $diperbarui = 0;

            foreach ($allTables as $row) {
                $namaLegacy = $row->$colKey ?? array_values((array)$row)[0];

                try {
                    $jmlBaris = (int) DB::connection('legacy')->table($namaLegacy)->count();
                    $koloms   = DB::connection('legacy')->select("DESCRIBE `{$namaLegacy}`");
                    $jmlKolom = count($koloms);

                    $pemetaan = MigrasiPemetaanTabel::where('tabel_legacy', $namaLegacy)->first();

                    if ($pemetaan) {
                        $pemetaan->update([
                            'jml_baris_legacy' => $jmlBaris,
                            'jml_kolom_legacy' => $jmlKolom,
                            'terakhir_scan_at' => now(),
                            'scanned_by'       => $userId,
                        ]);
                        $diperbarui++;
                    } else {
                        MigrasiPemetaanTabel::create([
                            'tabel_legacy'     => $namaLegacy,
                            'jml_baris_legacy' => $jmlBaris,
                            'jml_kolom_legacy' => $jmlKolom,
                            'klasifikasi'      => $this->autoKlasifikasi($namaLegacy, $jmlBaris),
                            'tabel_baru'       => null,
                            'pemetaan_field'   => null,
                            'status_impor'     => 'pending',
                            'terakhir_scan_at' => now(),
                            'scanned_by'       => $userId,
                        ]);
                        $baru++;
                    }
                } catch (\Throwable $e) {
                    // Skip tabel yang tidak bisa dibaca (view, dll)
                }
            }

            return [
                'status'      => 'ok',
                'total'       => count($allTables),
                'baru'        => $baru,
                'diperbarui'  => $diperbarui,
            ];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'pesan' => $e->getMessage()];
        }
    }

    /**
     * Auto-klasifikasi awal berdasarkan nama tabel dan jumlah baris.
     * Heuristik sederhana — admin wajib review dan koreksi.
     */
    private function autoKlasifikasi(string $nama, int $jmlBaris): string
    {
        // Pola nama yang biasanya Master (referensi / lookup)
        $masterPatterns = [
            'agama', 'jenjang', 'prodi', 'program', 'fakultas', 'jurusan',
            'status', 'jenis', 'golongan', 'pangkat', 'jabatan', 'kota',
            'provinsi', 'negara', 'wilayah', 'kecamatan', 'kelurahan',
            'tahun', 'semester', 'kurikulum', 'matakuliah', 'mata_kuliah',
            'ruang', 'gedung', 'bank', 'sumber', 'cara', 'bulan', 'benua',
            'warganegara', 'transportasi', 'almamater', 'bahasa',
        ];

        $namaLower = strtolower($nama);

        foreach ($masterPatterns as $pattern) {
            if (str_contains($namaLower, $pattern)) {
                return 'master';
            }
        }

        // Tabel kecil (≤ 200 baris) biasanya master
        if ($jmlBaris <= 200) {
            return 'master';
        }

        // Default: anggap transaksi jika baris besar
        return $jmlBaris > 200 ? 'transaksi' : 'abaikan';
    }

    // ──────────────────────────────────────────────────────────────
    // PEMETAAN FIELD (snake_case converter)
    // ──────────────────────────────────────────────────────────────

    /**
     * Baca kolom tabel legacy dan buat pemetaan field ke snake_case Laravel.
     */
    public function buildPemetaanField(string $tabelLegacy): array
    {
        $koloms = DB::connection('legacy')->select("DESCRIBE `{$tabelLegacy}`");

        return array_map(function ($k) {
            $fieldBaru = $this->toLaravelField($k->Field);
            return [
                'legacy'    => $k->Field,
                'baru'      => $fieldBaru,
                'tipe'      => $k->Type,
                'null'      => $k->Null === 'YES',
                'key'       => $k->Key,
                'tipe_baru' => $this->toMigrationTipe($k->Type, $k->Field),
                'abaikan'   => false,
            ];
        }, $koloms);
    }

    /**
     * Convert nama field legacy ke Laravel snake_case convention.
     *
     * Contoh:
     * - ProdiID     → prodi_id
     * - NmProdi     → nm_prodi
     * - KdFakultas  → kd_fakultas
     * - tgl_lahir   → tgl_lahir (sudah snake_case)
     * - MhswID      → mhsw_id
     */
    public function toLaravelField(string $field): string
    {
        // Jika sudah snake_case, kembalikan langsung
        if (strtolower($field) === $field) {
            return $field;
        }

        $str = $field;

        // Normalisasi singkatan uppercase di akhir: ProdiID → ProdiId
        $str = preg_replace_callback('/([A-Z]{2,})$/', function ($m) {
            return ucfirst(strtolower($m[1]));
        }, $str);

        // Normalisasi singkatan uppercase di tengah: MhswIDName → MhswIdName
        $str = preg_replace_callback('/([A-Z]{2,})([A-Z][a-z])/', function ($m) {
            return ucfirst(strtolower($m[1])) . $m[2];
        }, $str);

        // Gunakan Str::snake() Laravel standard
        return Str::snake($str);
    }

    /**
     * Konversi tipe MySQL ke tipe Laravel migration.
     */
    private function toMigrationTipe(string $mysqlTipe, string $fieldName): string
    {
        $tipe = strtolower(explode('(', $mysqlTipe)[0]);
        $fieldLower = strtolower($fieldName);

        // Deteksi PK
        if (in_array($fieldLower, ['id', 'no', 'kd', 'code']) && str_contains($mysqlTipe, 'int')) {
            return 'id()';
        }

        return match ($tipe) {
            'int', 'integer'            => 'integer()',
            'tinyint'                   => str_contains($mysqlTipe, '(1)') ? 'boolean()' : 'tinyInteger()',
            'smallint'                  => 'smallInteger()',
            'mediumint'                 => 'mediumInteger()',
            'bigint'                    => 'bigInteger()',
            'varchar', 'char'           => "string({$this->extractLength($mysqlTipe)})",
            'text', 'tinytext'          => 'text()',
            'mediumtext', 'longtext'    => 'longText()',
            'date'                      => 'date()',
            'datetime', 'timestamp'     => 'dateTime()',
            'time'                      => 'time()',
            'decimal', 'numeric'        => "decimal(10, 2)",
            'float', 'double'           => 'float()',
            'enum'                      => "string(50) /* enum */",
            'json'                      => 'json()',
            default                     => 'string(255)',
        };
    }

    private function extractLength(string $tipe): int
    {
        preg_match('/\((\d+)\)/', $tipe, $m);
        return (int)($m[1] ?? 255);
    }

    // ──────────────────────────────────────────────────────────────
    // NAMA TABEL BARU (auto-suggest)
    // ──────────────────────────────────────────────────────────────

    /**
     * Generate nama tabel baru dari nama legacy dan klasifikasi.
     * Master → m_nama | Transaksi → t_nama
     */
    public function suggestNamaTabelBaru(string $tabelLegacy, string $klasifikasi): string
    {
        $prefix = match ($klasifikasi) {
            'master'    => 'm_',
            'transaksi' => 't_',
            default     => '',
        };

        // Bersihkan dan snake_case
        $bersih = Str::snake(str_replace(['-', ' '], '_', $tabelLegacy));

        return $prefix . $bersih;
    }

    // ──────────────────────────────────────────────────────────────
    // DYNAMIC SCHEMA CREATION / MIGRATION
    // ──────────────────────────────────────────────────────────────

    public function generateMigrationFile(int $id): array
    {
        $pemetaan = MigrasiPemetaanTabel::find($id);
        if (! $pemetaan) {
            return ['status' => 'error', 'pesan' => 'Pemetaan tidak ditemukan'];
        }

        if (! $pemetaan->tabel_baru) {
            return ['status' => 'error', 'pesan' => 'Nama tabel baru belum ditentukan'];
        }

        // Pastikan pemetaan_field sudah terisi
        if (empty($pemetaan->pemetaan_field)) {
            $pemetaan->pemetaan_field = $this->buildPemetaanField($pemetaan->tabel_legacy);
            $pemetaan->save();
        }

        $tabelBaru = $pemetaan->tabel_baru;
        $tabelLegacy = $pemetaan->tabel_legacy;

        // Cari apakah file migration untuk tabel ini sudah ada sebelumnya
        $migrationFiles = glob(database_path('migrations/*_create_' . $tabelBaru . '_table.php'));
        if (!empty($migrationFiles)) {
            $filePath = $migrationFiles[0]; // Overwrite yang sudah ada
        } else {
            $timestamp = date('Y_m_d_His');
            $filePath = database_path("migrations/{$timestamp}_create_{$tabelBaru}_table.php");
        }

        // Build columns code
        $columnsCode = [];
        $columnsCode[] = "            \$table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel {$tabelLegacy}');";

        foreach ($pemetaan->pemetaan_field as $field) {
            if ($field['abaikan'] ?? false) {
                continue;
            }

            $name = $field['baru'];
            $typeCall = $field['tipe_baru'];

            if ($name === 'id' || $name === 'id_legacy') {
                continue;
            }

            // Parsing tipe dan argumen, misal string(100) atau integer()
            if (preg_match('/^([a-zA-Z]+)\((.*)\)$/', $typeCall, $matches)) {
                $method = $matches[1];
                $args = $matches[2];

                $argString = "'$name'";
                if ($args !== '') {
                    if (str_contains($args, '/*')) {
                        $args = explode('/*', $args)[0];
                        $args = trim($args);
                    }
                    if ($args !== '') {
                        $argString .= ", " . $args;
                    }
                }

                $nullable = '';
                if (($field['key'] ?? '') === 'PRI') {
                    if (str_contains(strtolower($method), 'integer')) {
                        $method = 'id';
                        $argString = "'$name'";
                    } else {
                        $nullable = '->primary()';
                    }
                } else {
                    if ($field['null'] ?? true) {
                        $nullable = '->nullable()';
                    }
                    if (($field['key'] ?? '') === 'MUL') {
                        $nullable .= '->index()';
                    }
                }

                $columnsCode[] = "            \$table->{$method}({$argString}){$nullable};";
            } else {
                $columnsCode[] = "            \$table->string('{$name}')->nullable();";
            }
        }

        $columnsCode[] = "            \$table->timestamps();";
        $columnsCode[] = "            \$table->softDeletes();";

        $columnsContent = implode("\n", $columnsCode);

        $template = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{$tabelBaru}', function (Blueprint \$table) {
{$columnsContent}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{$tabelBaru}');
    }
};
PHP;

        file_put_contents($filePath, $template);

        return [
            'status' => 'ok',
            'pesan'  => 'File migrasi berhasil dibuat',
            'file'   => basename($filePath),
            'path'   => $filePath,
        ];
    }

    public function jalankanMigrasi(int $id, bool $buatMigration = true, bool $buatSeeder = false): array
    {
        $pemetaan = MigrasiPemetaanTabel::find($id);
        if (! $pemetaan) {
            return ['status' => 'error', 'pesan' => 'Pemetaan tidak ditemukan'];
        }

        if (! $pemetaan->tabel_baru) {
            return ['status' => 'error', 'pesan' => 'Nama tabel baru belum ditentukan'];
        }

        $tableExists = Schema::hasTable($pemetaan->tabel_baru);

        try {
            if ($buatMigration) {
                // Generate file migrasi terlebih dahulu agar up-to-date
                $resGen = $this->generateMigrationFile($id);
                if ($resGen['status'] !== 'ok') {
                    return $resGen;
                }
                
                if (!$tableExists) {
                    $migrationFile = $resGen['file'];
                    Artisan::call('migrate', [
                        '--path' => 'database/migrations/' . $migrationFile,
                        '--force' => true,
                    ]);
                    
                    // Fallback Darurat: Jika Artisan Migrate melewatkan file karena record nyangkut di tabel migrations
                    if (!Schema::hasTable($pemetaan->tabel_baru)) {
                        $this->buatSkemaDinamis($pemetaan);
                    }
                }
            } else {
                if (!$tableExists) {
                    // Buat skema dinamis langsung di RAM tanpa file
                    $this->buatSkemaDinamis($pemetaan);
                }
            }

            if ($buatSeeder) {
                $className = $this->generateSeederFile($pemetaan);
                Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\' . $className,
                    '--force' => true,
                ]);
            }

            $pesan = $tableExists 
                ? "Tabel <strong>{$pemetaan->tabel_baru}</strong> sudah ada di database. " 
                : "Tabel <strong>{$pemetaan->tabel_baru}</strong> berhasil dibuat/dimigrasi. ";

            if ($tableExists && ($buatMigration || $buatSeeder)) {
                $pesan .= "File ";
                if ($buatMigration) $pesan .= "Migration ";
                if ($buatMigration && $buatSeeder) $pesan .= "& ";
                if ($buatSeeder) $pesan .= "Seeder ";
                $pesan .= "berhasil dibuat ulang.";
            }

            return [
                'status' => 'ok',
                'pesan'  => $pesan,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'pesan'  => 'Gagal menjalankan migrasi: ' . $e->getMessage(),
            ];
        }
    }

    private function buatSkemaDinamis(\App\Models\MigrasiPemetaanTabel $pemetaan): void
    {
        $tabelBaru = $pemetaan->tabel_baru;
        $tabelLegacy = $pemetaan->tabel_legacy;

        Schema::create($tabelBaru, function (\Illuminate\Database\Schema\Blueprint $table) use ($pemetaan, $tabelLegacy) {
            $table->string('id_legacy')->nullable()->index()->comment('ID asli dari tabel ' . $tabelLegacy);

            foreach ($pemetaan->pemetaan_field as $field) {
                if ($field['abaikan'] ?? false) continue;

                $name = $field['baru'];
                $typeCall = $field['tipe_baru'];

                if ($name === 'id' || $name === 'id_legacy') continue;

                if (preg_match('/^([a-zA-Z]+)\((.*)\)$/', $typeCall, $matches)) {
                    $method = $matches[1];
                    $args = $matches[2];

                    if (($field['key'] ?? '') === 'PRI' && str_contains(strtolower($method), 'integer')) {
                        $method = 'id';
                        $args = '';
                    }

                    $argArray = [$name];
                    if ($args !== '') {
                        if (str_contains($args, '/*')) {
                            $args = trim(explode('/*', $args)[0]);
                        }
                        if ($args !== '') {
                            $argsParts = array_map('trim', explode(',', $args));
                            $argArray = array_merge($argArray, $argsParts);
                        }
                    }

                    $col = call_user_func_array([$table, $method], $argArray);

                    if (($field['key'] ?? '') === 'PRI' && $method !== 'id') {
                        $col->primary();
                    } else if ($method !== 'id') {
                        if ($field['null'] ?? true) {
                            $col->nullable();
                        }
                        if (($field['key'] ?? '') === 'MUL') {
                            $col->index();
                        }
                    }
                } else {
                    $table->string($name)->nullable();
                }
            }

            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function generateSeederFile(\App\Models\MigrasiPemetaanTabel $pemetaan): string
    {
        $tabelBaru = $pemetaan->tabel_baru;
        $tabelLegacy = $pemetaan->tabel_legacy;
        $className = Str::studly(str_replace('m_', '', $tabelBaru)) . 'Seeder';
        $mapping = $pemetaan->pemetaan_field ?? [];

        // Tarik data dari legacy (limit 500 baris untuk mencegah seeder terlalu gendut)
        $legacyData = DB::connection('legacy')->table($tabelLegacy)->limit(500)->get();
        
        $legacyPkCol = null;
        foreach ($mapping as $field) {
            if (($field['key'] ?? '') === 'PRI') {
                $legacyPkCol = $field['legacy'];
                break;
            }
        }

        $arrayData = "[\n";
        foreach ($legacyData as $row) {
            $arrayData .= "            [\n";
            foreach ($mapping as $field) {
                if ($field['abaikan'] ?? false) continue;
                
                $colLegacy = $field['legacy'];
                $colBaru = $field['baru'];
                
                if ($colBaru === 'id' || $colBaru === 'id_legacy') continue;

                $val = $row->$colLegacy ?? null;
                $valExport = $val === null ? 'null' : "'" . addslashes((string)$val) . "'";
                $arrayData .= "                '$colBaru' => $valExport,\n";
            }
            
            if ($legacyPkCol) {
                $valLegacyPk = $row->$legacyPkCol ?? null;
                $valExportLegacy = $valLegacyPk === null ? 'null' : "'" . addslashes((string)$valLegacyPk) . "'";
                $arrayData .= "                'id_legacy' => $valExportLegacy,\n";
            }
            
            $arrayData .= "                'created_at' => now(),\n";
            $arrayData .= "                'updated_at' => now(),\n";
            $arrayData .= "            ],\n";
        }
        $arrayData .= "        ]";

        $stub = <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class $className extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \$data = $arrayData;

        // Kosongkan tabel sebelum insert
        DB::table('$tabelBaru')->truncate();
        
        // Chunk insert untuk performa
        foreach (array_chunk(\$data, 100) as \$chunk) {
            DB::table('$tabelBaru')->insert(\$chunk);
        }
    }
}
PHP;
        
        $filePath = database_path("seeders/{$className}.php");
        file_put_contents($filePath, $stub);

        return $className;
    }

    // ──────────────────────────────────────────────────────────────
    // DATA UNTUK UI
    // ──────────────────────────────────────────────────────────────

    public function getTabelTerscan(string $filterKlasifikasi = '', string $cari = ''): Collection
    {
        return MigrasiPemetaanTabel::query()
            ->when($filterKlasifikasi, fn($q) => $q->where('klasifikasi', $filterKlasifikasi))
            ->when($cari, fn($q) => $q->where('tabel_legacy', 'like', "%{$cari}%"))
            ->orderByRaw("FIELD(klasifikasi, 'master', 'transaksi', 'abaikan')")
            ->orderBy('jml_baris_legacy')
            ->get();
    }

    public function getRingkasanScan(): array
    {
        return [
            'total'     => MigrasiPemetaanTabel::count(),
            'master'    => MigrasiPemetaanTabel::master()->count(),
            'transaksi' => MigrasiPemetaanTabel::transaksi()->count(),
            'abaikan'   => MigrasiPemetaanTabel::where('klasifikasi', 'abaikan')->count(),
            'done'      => MigrasiPemetaanTabel::where('status_impor', 'done')->count(),
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // PIPELINE LAMA (tetap ada untuk backward compat)
    // ──────────────────────────────────────────────────────────────

    public function adaYangSedangBerjalan(): bool
    {
        return LogMigrasi::where('status', 'running')->exists();
    }

    public function getRiwayat(int $limit = 30): Collection
    {
        return LogMigrasi::with('user')->latest()->limit($limit)->get();
    }

    public function testKoneksiOk(): bool
    {
        return ($this->testKoneksiLegacy()['status'] ?? '') === 'ok';
    }
}
