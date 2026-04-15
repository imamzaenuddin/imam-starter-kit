<?php

namespace App\Services;

use App\Models\Level;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportExportMasterService
{
  public function entitasTersedia(): array
  {
    return [
      'levels' => 'Master Level',
      'menus' => 'Master Menu',
    ];
  }

  public function exportCsv(string $entitas): StreamedResponse
  {
    $waktu = now()->format('Ymd_His');
    $namaFile = "export_{$entitas}_{$waktu}.csv";

    return response()->streamDownload(function () use ($entitas) {
      $handle = fopen('php://output', 'w');

      if (! $handle) {
        return;
      }

      // BOM UTF-8 agar file CSV terbaca baik di spreadsheet.
      fwrite($handle, "\xEF\xBB\xBF");

      [$header, $rows] = $this->dataExport($entitas);
      fputcsv($handle, $header);

      foreach ($rows as $row) {
        fputcsv($handle, $row);
      }

      fclose($handle);
    }, $namaFile, [
      'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
  }

  public function importCsv(string $entitas, string $lokasiFile): array
  {
    $handle = fopen($lokasiFile, 'r');

    if (! $handle) {
      return [
        'jumlah_total' => 0,
        'jumlah_berhasil' => 0,
        'jumlah_gagal' => 1,
        'error_baris' => [
          ['baris' => 0, 'pesan' => 'File tidak dapat dibaca'],
        ],
      ];
    }

    $headerRaw = fgetcsv($handle);
    if (! $headerRaw) {
      fclose($handle);

      return [
        'jumlah_total' => 0,
        'jumlah_berhasil' => 0,
        'jumlah_gagal' => 1,
        'error_baris' => [
          ['baris' => 0, 'pesan' => 'Header CSV tidak ditemukan'],
        ],
      ];
    }

    $header = array_map(fn($item) => trim((string) $item), $headerRaw);
    $errorBaris = [];
    $records = [];
    $barisKe = 1;

    while (($baris = fgetcsv($handle)) !== false) {
      $barisKe++;

      if ($this->barisKosong($baris)) {
        continue;
      }

      $record = $this->gabungkanHeader($header, $baris);
      $pesanError = $this->validasiRecord($entitas, $record);

      if ($pesanError !== null) {
        $errorBaris[] = [
          'baris' => $barisKe,
          'pesan' => $pesanError,
        ];

        continue;
      }

      $records[] = [
        'baris' => $barisKe,
        'data' => $record,
      ];
    }

    fclose($handle);

    if (count($records) === 0) {
      return [
        'jumlah_total' => 0,
        'jumlah_berhasil' => 0,
        'jumlah_gagal' => count($errorBaris),
        'error_baris' => $errorBaris,
      ];
    }

    // Konsistensi impor: satu kali commit jika tidak ada kegagalan proses DB.
    DB::transaction(function () use ($entitas, $records, &$errorBaris) {
      if ($entitas === 'levels') {
        foreach ($records as $record) {
          try {
            $this->simpanLevel($record['data']);
          } catch (\Throwable $e) {
            $errorBaris[] = [
              'baris' => $record['baris'],
              'pesan' => $e->getMessage(),
            ];
          }
        }

        return;
      }

      if ($entitas === 'menus') {
        $this->simpanMenuBertahap($records, $errorBaris);
      }
    });

    $jumlahTotal = count($records) + count($errorBaris);
    $jumlahGagal = count($errorBaris);
    $jumlahBerhasil = max(0, count($records) - $jumlahGagal);

    return [
      'jumlah_total' => $jumlahTotal,
      'jumlah_berhasil' => $jumlahBerhasil,
      'jumlah_gagal' => $jumlahGagal,
      'error_baris' => $errorBaris,
    ];
  }

  private function dataExport(string $entitas): array
  {
    if ($entitas === 'levels') {
      $header = ['nama_level', 'deskripsi', 'is_active'];
      $rows = Level::query()
        ->orderBy('nama_level')
        ->get(['nama_level', 'deskripsi', 'is_active'])
        ->map(fn(Level $level) => [
          $level->nama_level,
          $level->deskripsi,
          $level->is_active ? 1 : 0,
        ])
        ->all();

      return [$header, $rows];
    }

    if ($entitas === 'menus') {
      $header = ['nama', 'url', 'icon', 'parent_nama', 'urutan', 'is_active'];
      $rows = Menu::query()
        ->with('parent:id,nama')
        ->orderBy('urutan')
        ->orderBy('nama')
        ->get(['nama', 'url', 'icon', 'parent_id', 'urutan', 'is_active'])
        ->map(fn(Menu $menu) => [
          $menu->nama,
          $menu->url,
          $menu->icon,
          $menu->parent?->nama,
          $menu->urutan,
          $menu->is_active ? 1 : 0,
        ])
        ->all();

      return [$header, $rows];
    }

    return [[], []];
  }

  private function barisKosong(array $baris): bool
  {
    foreach ($baris as $kolom) {
      if (trim((string) $kolom) !== '') {
        return false;
      }
    }

    return true;
  }

  private function gabungkanHeader(array $header, array $baris): array
  {
    $record = [];

    foreach ($header as $index => $key) {
      $record[$key] = trim((string) ($baris[$index] ?? ''));
    }

    return $record;
  }

  private function validasiRecord(string $entitas, array $record): ?string
  {
    if ($entitas === 'levels') {
      if (($record['nama_level'] ?? '') === '') {
        return 'Kolom nama_level wajib diisi';
      }

      if (($record['is_active'] ?? '') !== '' && ! $this->nilaiBooleanValid($record['is_active'])) {
        return 'Kolom is_active harus bernilai 1/0/true/false';
      }

      return null;
    }

    if ($entitas === 'menus') {
      if (($record['nama'] ?? '') === '') {
        return 'Kolom nama wajib diisi';
      }

      if (($record['urutan'] ?? '') !== '' && ! ctype_digit((string) $record['urutan'])) {
        return 'Kolom urutan harus angka';
      }

      if (($record['is_active'] ?? '') !== '' && ! $this->nilaiBooleanValid($record['is_active'])) {
        return 'Kolom is_active harus bernilai 1/0/true/false';
      }
    }

    return null;
  }

  private function simpanLevel(array $record): void
  {
    Level::query()->updateOrCreate(
      ['nama_level' => $record['nama_level']],
      [
        'deskripsi' => $record['deskripsi'] ?: null,
        'is_active' => $this->keBoolean($record['is_active'] ?? '1'),
      ]
    );
  }

  private function simpanMenuBertahap(array $records, array &$errorBaris): void
  {
    $idByNama = [];

    foreach ($records as $record) {
      $nama = $record['data']['nama'];
      $menuExisting = Menu::query()->where('nama', $nama)->first();
      if ($menuExisting) {
        $idByNama[$nama] = $menuExisting->id;
      }
    }

    foreach ($records as $record) {
      $data = $record['data'];
      if (($data['parent_nama'] ?? '') !== '') {
        continue;
      }

      try {
        $menu = Menu::query()->updateOrCreate(
          ['nama' => $data['nama'], 'parent_id' => null],
          [
            'url' => $data['url'] ?: null,
            'icon' => $data['icon'] ?: null,
            'urutan' => (int) ($data['urutan'] !== '' ? $data['urutan'] : 0),
            'is_active' => $this->keBoolean($data['is_active'] ?? '1'),
          ]
        );

        $idByNama[$data['nama']] = $menu->id;
      } catch (\Throwable $e) {
        $errorBaris[] = [
          'baris' => $record['baris'],
          'pesan' => $e->getMessage(),
        ];
      }
    }

    foreach ($records as $record) {
      $data = $record['data'];
      $parentNama = $data['parent_nama'] ?? '';

      if ($parentNama === '') {
        continue;
      }

      $parentId = $idByNama[$parentNama] ?? null;

      if (! $parentId) {
        $parent = Menu::query()->where('nama', $parentNama)->first();
        $parentId = $parent?->id;
      }

      if (! $parentId) {
        $errorBaris[] = [
          'baris' => $record['baris'],
          'pesan' => 'Parent menu tidak ditemukan: ' . $parentNama,
        ];

        continue;
      }

      try {
        $menu = Menu::query()->updateOrCreate(
          ['nama' => $data['nama'], 'parent_id' => $parentId],
          [
            'url' => $data['url'] ?: null,
            'icon' => $data['icon'] ?: null,
            'urutan' => (int) ($data['urutan'] !== '' ? $data['urutan'] : 0),
            'is_active' => $this->keBoolean($data['is_active'] ?? '1'),
          ]
        );

        $idByNama[$data['nama']] = $menu->id;
      } catch (\Throwable $e) {
        $errorBaris[] = [
          'baris' => $record['baris'],
          'pesan' => $e->getMessage(),
        ];
      }
    }
  }

  private function nilaiBooleanValid(string $nilai): bool
  {
    $normal = strtolower(trim($nilai));

    return in_array($normal, ['1', '0', 'true', 'false', 'ya', 'tidak', 'yes', 'no'], true);
  }

  private function keBoolean(string $nilai): bool
  {
    $normal = strtolower(trim($nilai));

    return in_array($normal, ['1', 'true', 'ya', 'yes'], true);
  }
}
