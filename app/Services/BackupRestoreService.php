<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupRestoreService
{
  private string $restoreLockPath;
  private PengaturanBackupService $pengaturanBackupService;

  public function __construct(PengaturanBackupService $pengaturanBackupService)
  {
    $this->restoreLockPath = storage_path('app/backup-database/.restore.lock');
    $this->pengaturanBackupService = $pengaturanBackupService;
  }

  public function pilihanTipe(): array
  {
    return [
      'full' => 'FULL',
      'transaksi' => 'TRANSAKSI',
      'master' => 'MASTER',
    ];
  }

  public function buatBackup(string $tipe): array
  {
    return $this->buatBackupKustom($tipe, 'backup');
  }

  public function buatBackupKustom(string $tipe, string $prefixNamaFile = 'backup'): array
  {
    $tipe = strtolower($tipe);
    $tables = $this->daftarTabelUntuk($tipe);

    if (empty($tables)) {
      throw new \RuntimeException('Tidak ada tabel yang bisa dibackup untuk tipe yang dipilih.');
    }

    $baseDir = storage_path('app/backup-database');
    File::ensureDirectoryExists($baseDir);

    $timestamp = now()->format('Ymd_His_u');
    $prefix = trim($prefixNamaFile);
    $prefix = preg_replace('/[^a-zA-Z0-9_-]/', '_', $prefix ?: 'backup');
    $namaDasar = strtolower($prefix . '_' . $tipe . '_' . $timestamp);
    $sqlPath = $baseDir . DIRECTORY_SEPARATOR . $namaDasar . '.sql';

    $this->generateSqlDump($tables, $sqlPath);
    $compressedPath = $this->compressSql($sqlPath, $namaDasar);
    @unlink($sqlPath);

    return [
      'nama_file' => basename($compressedPath),
      'path' => $compressedPath,
    ];
  }

  public function restoreDariZip(UploadedFile $file): int
  {
    $sqlContent = $this->extractSqlContent($file);

    if (! $sqlContent || trim($sqlContent) === '') {
      throw new \RuntimeException('File SQL di dalam ZIP tidak ditemukan atau kosong.');
    }

    $statements = $this->splitSqlStatements($sqlContent);

    if (empty($statements)) {
      throw new \RuntimeException('Tidak ada statement SQL yang dapat dieksekusi.');
    }

    DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    foreach ($statements as $statement) {
      DB::unprepared($statement);
    }
    DB::unprepared('SET FOREIGN_KEY_CHECKS=1');

    return count($statements);
  }

  public function restoreAman(UploadedFile $file): array
  {
    $this->kunciRestore();

    try {
      $konfigurasi = $this->pengaturanBackupService->konfigurasiScheduler();
      $backupSebelum = null;

      if ((bool) ($konfigurasi['restore_auto_backup'] ?? true)) {
        $tipeBackup = (string) ($konfigurasi['restore_auto_backup_tipe'] ?? 'full');
        $backupSebelum = $this->buatBackup($tipeBackup);
      }

      $jumlahStatement = $this->restoreDariZip($file);

      return [
        'jumlah_statement' => $jumlahStatement,
        'backup_sebelum' => $backupSebelum,
      ];
    } finally {
      $this->lepasKunciRestore();
    }
  }

  public function riwayatBackup(int $limit = 12): array
  {
    $dir = storage_path('app/backup-database');

    if (! File::exists($dir)) {
      return [];
    }

    $files = collect(File::files($dir))
      ->filter(function (\SplFileInfo $file) {
        $name = strtolower($file->getFilename());

        return str_ends_with($name, '.zip') || str_ends_with($name, '.sql.gz');
      })
      ->sortByDesc(fn(\SplFileInfo $file) => $file->getMTime())
      ->take($limit)
      ->map(function (\SplFileInfo $file) {
        return [
          'nama' => $file->getFilename(),
          'ukuran' => $this->formatUkuran($file->getSize()),
          'waktu' => date('d/m/Y H:i:s', $file->getMTime()),
        ];
      })
      ->values()
      ->all();

    return $files;
  }

  public function hapusBackup(string $namaFile): void
  {
    $namaFile = basename(trim($namaFile));

    if (
      $namaFile === '' ||
      (! str_ends_with(strtolower($namaFile), '.zip') && ! str_ends_with(strtolower($namaFile), '.sql.gz'))
    ) {
      throw new \RuntimeException('Nama file backup tidak valid.');
    }

    $baseDir = storage_path('app/backup-database');

    if (! File::exists($baseDir)) {
      throw new \RuntimeException('Folder backup tidak ditemukan.');
    }

    $targetPath = $baseDir . DIRECTORY_SEPARATOR . $namaFile;

    if (! File::exists($targetPath)) {
      throw new \RuntimeException('File backup tidak ditemukan.');
    }

    $realBase = realpath($baseDir);
    $realTarget = realpath($targetPath);

    if (! $realBase || ! $realTarget || ! str_starts_with($realTarget, $realBase . DIRECTORY_SEPARATOR)) {
      throw new \RuntimeException('Lokasi file backup tidak valid.');
    }

    if (! File::delete($realTarget)) {
      throw new \RuntimeException('Gagal menghapus file backup.');
    }
  }

  public function hapusBackupKadaluarsa(int $retensiHari = 30): int
  {
    $retensiHari = max(1, $retensiHari);
    $baseDir = storage_path('app/backup-database');

    if (! File::exists($baseDir)) {
      return 0;
    }

    $batasWaktu = now()->subDays($retensiHari)->timestamp;
    $jumlahDihapus = 0;

    foreach (File::files($baseDir) as $file) {
      $nama = strtolower($file->getFilename());
      if (! str_ends_with($nama, '.zip') && ! str_ends_with($nama, '.sql.gz')) {
        continue;
      }

      if ($file->getMTime() >= $batasWaktu) {
        continue;
      }

      if (File::delete($file->getRealPath())) {
        $jumlahDihapus++;
      }
    }

    return $jumlahDihapus;
  }

  private function kunciRestore(): void
  {
    $konfigurasi = $this->pengaturanBackupService->konfigurasiScheduler();
    $dir = dirname($this->restoreLockPath);
    File::ensureDirectoryExists($dir);

    if (File::exists($this->restoreLockPath)) {
      $umurLock = time() - (int) filemtime($this->restoreLockPath);
      $timeout = max(60, (int) ($konfigurasi['restore_lock_timeout_detik'] ?? 900));

      if ($umurLock > $timeout) {
        @File::delete($this->restoreLockPath);
      }
    }

    $handle = @fopen($this->restoreLockPath, 'x');

    if (! $handle) {
      throw new \RuntimeException('Restore sedang berjalan oleh proses lain. Silakan coba lagi beberapa saat.');
    }

    fwrite($handle, (string) now());
    fclose($handle);
  }

  private function lepasKunciRestore(): void
  {
    if (File::exists($this->restoreLockPath)) {
      @File::delete($this->restoreLockPath);
    }
  }

  private function daftarTabelUntuk(string $tipe): array
  {
    $allTables = $this->daftarSemuaTabel();

    return match ($tipe) {
      'master' => array_values(array_filter($allTables, fn($table) => str_starts_with($table, 'm_'))),
      'transaksi' => array_values(array_filter($allTables, fn($table) => str_starts_with($table, 't_'))),
      default => $allTables,
    };
  }

  private function daftarSemuaTabel(): array
  {
    $database = DB::getDatabaseName();
    $rows = DB::select('SHOW TABLES');
    $key = 'Tables_in_' . $database;

    return array_values(array_map(fn($row) => (string) $row->{$key}, $rows));
  }

  private function generateSqlDump(array $tables, string $sqlPath): void
  {
    $pdo = DB::connection()->getPdo();
    $handle = fopen($sqlPath, 'wb');

    if (! $handle) {
      throw new \RuntimeException('Gagal membuat file SQL backup.');
    }

    fwrite($handle, "-- Backup database otomatis\n");
    fwrite($handle, '-- Waktu: ' . now()->toDateTimeString() . "\n\n");
    fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

    foreach ($tables as $table) {
      $create = DB::select("SHOW CREATE TABLE `{$table}`");
      if (! isset($create[0])) {
        continue;
      }

      $createData = (array) $create[0];
      $createSql = '';
      foreach ($createData as $value) {
        if (is_string($value) && str_starts_with($value, 'CREATE TABLE')) {
          $createSql = $value;
          break;
        }
      }

      if ($createSql === '') {
        continue;
      }

      fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
      fwrite($handle, $createSql . ";\n\n");

      $rows = DB::table($table)->get();
      if ($rows->isEmpty()) {
        continue;
      }

      $columns = array_keys((array) $rows->first());
      $columnSql = '`' . implode('`, `', $columns) . '`';

      foreach ($rows as $row) {
        $values = [];
        foreach ($columns as $column) {
          $values[] = $this->quoteValue(data_get($row, $column), $pdo);
        }

        fwrite(
          $handle,
          "INSERT INTO `{$table}` ({$columnSql}) VALUES (" . implode(', ', $values) . ");\n"
        );
      }

      fwrite($handle, "\n");
    }

    fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($handle);
  }

  private function compressSql(string $sourcePath, string $namaDasar): string
  {
    $baseDir = dirname($sourcePath);

    if (class_exists(\ZipArchive::class)) {
      $zipPath = $baseDir . DIRECTORY_SEPARATOR . $namaDasar . '.zip';
      $this->compressToZip($sourcePath, $zipPath, basename($sourcePath));

      return $zipPath;
    }

    $gzipPath = $baseDir . DIRECTORY_SEPARATOR . $namaDasar . '.sql.gz';
    $content = file_get_contents($sourcePath);

    if ($content === false) {
      throw new \RuntimeException('Gagal membaca file SQL untuk kompresi.');
    }

    $encoded = gzencode($content, 9);

    if ($encoded === false || file_put_contents($gzipPath, $encoded) === false) {
      throw new \RuntimeException('Gagal membuat file GZ backup.');
    }

    return $gzipPath;
  }

  private function compressToZip(string $sourcePath, string $zipPath, string $entryName): void
  {
    $zip = new \ZipArchive();
    if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
      throw new \RuntimeException('Gagal membuat file ZIP backup.');
    }

    $zip->addFile($sourcePath, $entryName);
    $zip->close();
  }

  private function extractSqlContent(UploadedFile $file): string
  {
    $realPath = $file->getRealPath();
    $namaFile = strtolower((string) $file->getClientOriginalName());

    if (! $realPath) {
      throw new \RuntimeException('File backup tidak dapat dibaca.');
    }

    if (str_ends_with($namaFile, '.zip')) {
      if (! class_exists(\ZipArchive::class)) {
        throw new \RuntimeException('Restore ZIP tidak tersedia karena ZipArchive belum aktif di server.');
      }

      $zip = new \ZipArchive();
      if ($zip->open($realPath) !== true) {
        throw new \RuntimeException('File ZIP backup tidak valid.');
      }

      $sqlContent = null;
      for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (str_ends_with(strtolower((string) $name), '.sql')) {
          $sqlContent = $zip->getFromIndex($i);
          break;
        }
      }

      $zip->close();

      return (string) $sqlContent;
    }

    if (str_ends_with($namaFile, '.gz') || str_ends_with($namaFile, '.sql.gz')) {
      $compressed = file_get_contents($realPath);
      if ($compressed === false) {
        throw new \RuntimeException('File GZ backup tidak valid.');
      }

      $decoded = gzdecode($compressed);
      if ($decoded === false) {
        throw new \RuntimeException('Gagal membaca konten SQL dari file GZ.');
      }

      return $decoded;
    }

    throw new \RuntimeException('Format file backup tidak didukung. Gunakan ZIP atau GZ.');
  }

  private function quoteValue(mixed $value, \PDO $pdo): string
  {
    if ($value === null) {
      return 'NULL';
    }

    if (is_bool($value)) {
      return $value ? '1' : '0';
    }

    if (is_int($value) || is_float($value)) {
      return (string) $value;
    }

    return $pdo->quote((string) $value);
  }

  private function splitSqlStatements(string $sql): array
  {
    $statements = [];
    $buffer = '';
    $inSingle = false;
    $inDouble = false;
    $inBacktick = false;
    $escape = false;

    $len = strlen($sql);

    for ($i = 0; $i < $len; $i++) {
      $char = $sql[$i];
      $buffer .= $char;

      if ($escape) {
        $escape = false;
        continue;
      }

      if ($char === '\\') {
        $escape = true;
        continue;
      }

      if (! $inDouble && ! $inBacktick && $char === "'") {
        $inSingle = ! $inSingle;
        continue;
      }

      if (! $inSingle && ! $inBacktick && $char === '"') {
        $inDouble = ! $inDouble;
        continue;
      }

      if (! $inSingle && ! $inDouble && $char === '`') {
        $inBacktick = ! $inBacktick;
        continue;
      }

      if (! $inSingle && ! $inDouble && ! $inBacktick && $char === ';') {
        $trimmed = trim($buffer);
        if ($trimmed !== '') {
          $statements[] = $trimmed;
        }
        $buffer = '';
      }
    }

    $tail = trim($buffer);
    if ($tail !== '') {
      $statements[] = $tail;
    }

    return $statements;
  }

  private function formatUkuran(int $bytes): string
  {
    if ($bytes < 1024) {
      return $bytes . ' B';
    }

    if ($bytes < 1048576) {
      return number_format($bytes / 1024, 2, ',', '.') . ' KB';
    }

    return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
  }
}
