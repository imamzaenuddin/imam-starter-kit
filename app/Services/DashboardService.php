<?php

namespace App\Services;

use App\Models\DashboardWidget;
use App\Models\Identitas;
use App\Models\Level;
use App\Models\LogAktivitas;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DashboardService
{
  public function sumberDataTersedia(): array
  {
    return collect($this->konfigurasiSumberSemua())
      ->mapWithKeys(fn(array $item, string $key) => [$key => $item['label']])
      ->all();
  }

  public function tipeTampilanTersedia(): array
  {
    return [
      'statistik' => 'Kartu Statistik',
      'daftar' => 'Daftar Data Terbaru',
      'grafik' => 'Grafik Analisis',
    ];
  }

  public function tipeQueryTersedia(?string $tipeTampilan = null): array
  {
    if ($tipeTampilan === 'daftar') {
      return ['latest' => 'Data Terbaru'];
    }

    return [
      'count' => 'Jumlah Data',
      'sum' => 'Total / Sum',
      'avg' => 'Rata-rata',
      'min' => 'Nilai Minimum',
      'max' => 'Nilai Maksimum',
    ];
  }

  public function layoutTersedia(): array
  {
    return [
      'col-xl-3 col-md-6' => '4 kartu per baris',
      'col-xl-4 col-md-6' => '3 kartu per baris',
      'col-xl-6 col-md-12' => '2 kartu per baris',
      'col-12' => 'Lebar penuh',
    ];
  }

  public function warnaTersedia(): array
  {
    return [
      'primary' => 'Primary',
      'success' => 'Success',
      'warning' => 'Warning',
      'danger' => 'Danger',
      'info' => 'Info',
      'secondary' => 'Secondary',
      'dark' => 'Dark',
    ];
  }

  public function chartTipeTersedia(): array
  {
    return [
      'bar' => 'Bar Chart',
      'line' => 'Line Chart',
      'donut' => 'Donut Chart',
    ];
  }

  public function operatorFilterTersedia(): array
  {
    return [
      '=' => 'Sama dengan',
      '!=' => 'Tidak sama dengan',
      '>' => 'Lebih besar dari',
      '>=' => 'Lebih besar / sama dengan',
      '<' => 'Lebih kecil dari',
      '<=' => 'Lebih kecil / sama dengan',
      'like' => 'Mengandung teks',
    ];
  }

  public function kolomTersedia(?string $sumberData, string $jenis = 'filter'): array
  {
    $konfigurasi = $sumberData ? $this->konfigurasiSumber($sumberData) : null;

    if (! $konfigurasi) {
      return [];
    }

    if ($jenis === 'agregasi') {
      return $konfigurasi['kolom_numerik'];
    }

    return $konfigurasi['kolom'];
  }

  public function widgetUntukUser(?User $user): Collection
  {
    if (! $user || ! $user->level_id) {
      return collect();
    }

    return DashboardWidget::query()
      ->with('levels:id,nama_level')
      ->where('is_active', true)
      ->whereHas('levels', fn($query) => $query->where('m_level.id', $user->level_id))
      ->orderBy('urutan')
      ->orderBy('nama_widget')
      ->get()
      ->map(function (DashboardWidget $widget) {
        $widget->setAttribute('hasil_widget', $this->dataWidget($widget));

        return $widget;
      });
  }

  public function dataWidget(DashboardWidget $widget): array
  {
    $konfigurasi = $this->konfigurasiSumber($widget->sumber_data);

    if (! $konfigurasi) {
      return [
        'tipe_tampilan' => $widget->tipe_tampilan,
        'nilai' => 0,
        'nilai_format' => '0',
        'rows' => collect(),
        'sumber_label' => 'Sumber data tidak dikenal',
      ];
    }

    $query = $this->builderUntuk($widget->sumber_data);
    $this->terapkanFilter($query, $widget, $konfigurasi);

    if ($widget->tipe_tampilan === 'daftar' || $widget->tipe_query === 'latest') {
      $kolomLabel = $widget->kolom_label ?: $konfigurasi['default_label'];
      $kolomNilai = $widget->kolom_nilai ?: $konfigurasi['default_value'];
      $kolomOrder = array_key_exists('created_at', $konfigurasi['kolom']) ? 'created_at' : 'id';

      $rows = $query->orderByDesc($kolomOrder)
        ->limit(max(1, $widget->batas_data))
        ->get()
        ->map(function ($item) use ($kolomLabel, $kolomNilai, $kolomOrder) {
          return [
            'label' => data_get($item, $kolomLabel) ?: '-',
            'nilai' => $kolomNilai ? data_get($item, $kolomNilai) : null,
            'waktu' => data_get($item, $kolomOrder),
          ];
        });

      return [
        'tipe_tampilan' => 'daftar',
        'nilai' => $rows->count(),
        'nilai_format' => number_format($rows->count(), 0, ',', '.'),
        'rows' => $rows,
        'sumber_label' => $konfigurasi['label'],
      ];
    }

    if ($widget->tipe_tampilan === 'grafik') {
      return $this->dataGrafik($query, $widget, $konfigurasi);
    }

    $tipeQuery = $widget->tipe_query;
    $nilai = match ($tipeQuery) {
      'sum' => $widget->kolom_agregasi ? (float) $query->sum($widget->kolom_agregasi) : 0,
      'avg' => $widget->kolom_agregasi ? (float) $query->avg($widget->kolom_agregasi) : 0,
      'min' => $widget->kolom_agregasi ? (float) $query->min($widget->kolom_agregasi) : 0,
      'max' => $widget->kolom_agregasi ? (float) $query->max($widget->kolom_agregasi) : 0,
      default => $query->count(),
    };

    return [
      'tipe_tampilan' => 'statistik',
      'nilai' => $nilai,
      'nilai_format' => $this->formatNilai($nilai, $tipeQuery),
      'rows' => collect(),
      'sumber_label' => $konfigurasi['label'],
    ];
  }

  private function dataGrafik(Builder $query, DashboardWidget $widget, array $konfigurasi): array
  {
    $kolomLabel = $widget->kolom_label ?: $konfigurasi['default_label'];
    $kolomAgregasi = $widget->kolom_agregasi;

    $columns = collect([$kolomLabel, $kolomAgregasi])
      ->filter()
      ->unique()
      ->values()
      ->all();

    $records = $query->get($columns ?: [$kolomLabel]);

    $grouped = $records->groupBy(function ($item) use ($kolomLabel) {
      $value = data_get($item, $kolomLabel);

      return filled($value) ? (string) $value : 'Tanpa Label';
    })->map(function (Collection $items, string $label) use ($widget, $kolomAgregasi) {
      $series = match ($widget->tipe_query) {
        'sum' => $kolomAgregasi ? (float) $items->sum($kolomAgregasi) : 0,
        'avg' => $kolomAgregasi ? (float) $items->avg($kolomAgregasi) : 0,
        'min' => $kolomAgregasi ? (float) $items->min($kolomAgregasi) : 0,
        'max' => $kolomAgregasi ? (float) $items->max($kolomAgregasi) : 0,
        default => $items->count(),
      };

      return [
        'label' => Str::limit($label, 30),
        'nilai' => round($series, $widget->tipe_query === 'avg' ? 2 : 0),
      ];
    })->sortByDesc('nilai')
      ->take(max(1, $widget->batas_data))
      ->values();

    return [
      'tipe_tampilan' => 'grafik',
      'nilai' => $grouped->sum('nilai'),
      'nilai_format' => $this->formatNilai((float) $grouped->sum('nilai'), $widget->tipe_query),
      'rows' => collect(),
      'labels' => $grouped->pluck('label')->all(),
      'series' => $grouped->pluck('nilai')->all(),
      'chart_tipe' => $widget->chart_tipe ?: 'bar',
      'chart_tinggi' => $widget->chart_tinggi ?: 280,
      'chart_warna' => $this->normalisasiWarnaGrafik($widget->chart_warna),
      'sumber_label' => $konfigurasi['label'],
    ];
  }

  public function parseWarnaGrafik(?string $input): array
  {
    if (! $input) {
      return [];
    }

    return collect(explode(',', $input))
      ->map(fn($warna) => strtolower(trim($warna)))
      ->map(fn($warna) => str_starts_with($warna, '#') ? $warna : '#' . $warna)
      ->filter(fn($warna) => (bool) preg_match('/^#([a-f0-9]{6})$/i', $warna))
      ->values()
      ->all();
  }

  private function normalisasiWarnaGrafik(null|array|string $warna): array
  {
    if (is_array($warna)) {
      return collect($warna)
        ->filter(fn($item) => is_string($item) && preg_match('/^#([a-f0-9]{6})$/i', $item))
        ->map(fn($item) => strtolower($item))
        ->values()
        ->all();
    }

    if (is_string($warna)) {
      return $this->parseWarnaGrafik($warna);
    }

    return [];
  }

  private function builderUntuk(string $sumberData): Builder
  {
    $modelClass = $this->konfigurasiSumber($sumberData)['model'];

    return $modelClass::query();
  }

  private function terapkanFilter(Builder $query, DashboardWidget $widget, array $konfigurasi): void
  {
    if (! $widget->filter_kolom || ! array_key_exists($widget->filter_kolom, $konfigurasi['kolom'])) {
      return;
    }

    if ($widget->filter_nilai === null || $widget->filter_nilai === '') {
      return;
    }

    $operator = $widget->filter_operator ?: '=';
    $nilai = $this->nilaiFilter($widget->filter_kolom, $widget->filter_nilai, $konfigurasi);

    if ($operator === 'like') {
      $query->where($widget->filter_kolom, 'like', '%' . $widget->filter_nilai . '%');

      return;
    }

    if (! array_key_exists($operator, $this->operatorFilterTersedia())) {
      $operator = '=';
    }

    $query->where($widget->filter_kolom, $operator, $nilai);
  }

  private function nilaiFilter(string $kolom, string $nilai, array $konfigurasi): mixed
  {
    if (array_key_exists($kolom, $konfigurasi['kolom_boolean'])) {
      return filter_var($nilai, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (int) $nilai;
    }

    if (array_key_exists($kolom, $konfigurasi['kolom_numerik'])) {
      return is_numeric($nilai) ? $nilai + 0 : 0;
    }

    return $nilai;
  }

  private function formatNilai(int|float $nilai, string $tipeQuery): string
  {
    $desimal = $tipeQuery === 'avg' ? 2 : 0;

    return number_format($nilai, $desimal, ',', '.');
  }

  public function dataWidgetDenganKPI(DashboardWidget $widget): array
  {
    $data = $this->dataWidget($widget);

    // Tambah KPI calculation jika widget memiliki target
    if ($widget->kpi_target && $widget->kpi_target > 0) {
      $nilaiAktualan = (float) $data['nilai'] ?? 0;
      $persentaseKPI = ($nilaiAktualan / $widget->kpi_target) * 100;

      $data['kpi_target'] = $widget->kpi_target;
      $data['persentase_kpi'] = min(100, round($persentaseKPI, 2));
      $data['progress_bar_color'] = $this->warnaThreshold($persentaseKPI, $widget);
      $data['tampilkan_progress_bar'] = (bool) $widget->tampilkan_progress_bar;
    }

    // Tambah period comparison jika diaktifkan
    if ($widget->bandingkan_periode && $widget->bandingkan_dengan) {
      $dataPeriodePrev = $this->dataPeriodeSebelumnya($widget);
      if ($dataPeriodePrev) {
        $selisih = ((float) $data['nilai'] ?? 0) - ((float) $dataPeriodePrev['nilai'] ?? 0);
        $persentasePerubahan = $dataPeriodePrev['nilai'] ? ($selisih / $dataPeriodePrev['nilai']) * 100 : 0;

        $data['periode_sebelumnya'] = $dataPeriodePrev['nilai_format'];
        $data['selisih'] = $selisih;
        $data['persentase_perubahan'] = round($persentasePerubahan, 2);
        $data['trend'] = $selisih >= 0 ? 'naik' : 'turun';
      }
    }

    return $data;
  }

  public function dataPeriodeSebelumnya(DashboardWidget $widget): ?array
  {
    $konfigurasi = $this->konfigurasiSumber($widget->sumber_data);
    if (!$konfigurasi) {
      return null;
    }

    $query = $this->builderUntuk($widget->sumber_data);

    // Parse periode sebelumnya berdasarkan bandingkan_dengan
    $datePeriode = $this->parsePeriodeSebelumnya($widget->bandingkan_dengan);
    if (!$datePeriode) {
      return null;
    }

    // Terapkan filter date range ke periode sebelumnya
    $query->whereBetween('created_at', [$datePeriode['from'], $datePeriode['to']]);

    // Terapkan filter lainnya (excluding date filters)
    $this->terapkanFilter($query, $widget, $konfigurasi);

    // Execute query sesuai tipe
    $tipeQuery = $widget->tipe_query;
    $nilai = match ($tipeQuery) {
      'sum' => $widget->kolom_agregasi ? (float) $query->sum($widget->kolom_agregasi) : 0,
      'avg' => $widget->kolom_agregasi ? (float) $query->avg($widget->kolom_agregasi) : 0,
      'min' => $widget->kolom_agregasi ? (float) $query->min($widget->kolom_agregasi) : 0,
      'max' => $widget->kolom_agregasi ? (float) $query->max($widget->kolom_agregasi) : 0,
      default => $query->count(),
    };

    return [
      'nilai' => $nilai,
      'nilai_format' => $this->formatNilai($nilai, $tipeQuery),
    ];
  }

  private function parsePeriodeSebelumnya(?string $bandingkan): ?array
  {
    if (!$bandingkan) {
      return null;
    }

    $today = now();

    return match ($bandingkan) {
      'hari_sebelumnya' => [
        'from' => $today->copy()->subDays(1)->startOfDay(),
        'to' => $today->copy()->subDays(1)->endOfDay(),
      ],
      'minggu_lalu' => [
        'from' => $today->copy()->subWeeks(1)->startOfWeek(),
        'to' => $today->copy()->subWeeks(1)->endOfWeek(),
      ],
      'bulan_lalu' => [
        'from' => $today->copy()->subMonths(1)->startOfMonth(),
        'to' => $today->copy()->subMonths(1)->endOfMonth(),
      ],
      'tahun_lalu' => [
        'from' => $today->copy()->subYears(1)->startOfYear(),
        'to' => $today->copy()->subYears(1)->endOfYear(),
      ],
      default => null,
    };
  }

  public function warnaThreshold(float $persentase, DashboardWidget $widget): string
  {
    if ($persentase >= 100) {
      return $widget->warna_threshold_hijau ?: '90ddd52';
    } elseif ($persentase >= 75) {
      return $widget->warna_threshold_kuning ?: 'ffc107';
    } else {
      return $widget->warna_threshold_merah ?: 'dc3545';
    }
  }

  public function bandingkanPeriodeTersedia(): array
  {
    return [
      'hari_sebelumnya' => 'Hari Sebelumnya',
      'minggu_lalu' => 'Minggu Lalu',
      'bulan_lalu' => 'Bulan Lalu',
      'tahun_lalu' => 'Tahun Lalu',
    ];
  }

  public function cacheKeyWidget(DashboardWidget $widget): string
  {
    return 'dashboard_widget_' . $widget->id . '_' . now()->format('YmdH');
  }

  public function dataWidgetCached(DashboardWidget $widget, int $cacheMinutes = 5): array
  {
    $cacheKey = $this->cacheKeyWidget($widget);

    return cache()->remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($widget) {
      return $this->dataWidgetDenganKPI($widget);
    });
  }

  public function buatUlangCacheWidget(DashboardWidget $widget): void
  {
    cache()->forget($this->cacheKeyWidget($widget));
  }

  public function buatUlangCacheSemuaWidget(): void
  {
    DashboardWidget::query()
      ->where('is_active', true)
      ->chunk(10, function ($widgets) {
        foreach ($widgets as $widget) {
          $this->buatUlangCacheWidget($widget);
        }
      });
  }

  private function konfigurasiSumber(string $sumberData): ?array
  {
    return $this->konfigurasiSumberSemua()[$sumberData] ?? null;
  }

  private function konfigurasiSumberSemua(): array
  {
    return [
      'users' => [
        'label' => 'Pengguna',
        'model' => User::class,
        'default_label' => 'name',
        'default_value' => 'email',
        'kolom' => [
          'id' => 'ID',
          'name' => 'Nama',
          'email' => 'Email',
          'level_id' => 'Level',
          'created_at' => 'Tanggal Dibuat',
        ],
        'kolom_numerik' => [
          'id' => 'ID',
          'level_id' => 'Level',
        ],
        'kolom_boolean' => [],
      ],
      'levels' => [
        'label' => 'Level User',
        'model' => Level::class,
        'default_label' => 'nama_level',
        'default_value' => 'deskripsi',
        'kolom' => [
          'id' => 'ID',
          'nama_level' => 'Nama Level',
          'deskripsi' => 'Deskripsi',
          'is_active' => 'Status Aktif',
          'created_at' => 'Tanggal Dibuat',
        ],
        'kolom_numerik' => [
          'id' => 'ID',
        ],
        'kolom_boolean' => [
          'is_active' => 'Status Aktif',
        ],
      ],
      'menus' => [
        'label' => 'Menu Sistem',
        'model' => Menu::class,
        'default_label' => 'nama',
        'default_value' => 'url',
        'kolom' => [
          'id' => 'ID',
          'nama' => 'Nama Menu',
          'url' => 'URL',
          'parent_id' => 'Parent',
          'urutan' => 'Urutan',
          'is_active' => 'Status Aktif',
          'created_at' => 'Tanggal Dibuat',
        ],
        'kolom_numerik' => [
          'id' => 'ID',
          'parent_id' => 'Parent',
          'urutan' => 'Urutan',
        ],
        'kolom_boolean' => [
          'is_active' => 'Status Aktif',
        ],
      ],
      'log_aktivitas' => [
        'label' => 'Log Aktivitas',
        'model' => LogAktivitas::class,
        'default_label' => 'aktivitas',
        'default_value' => 'modul',
        'kolom' => [
          'id' => 'ID',
          'user_id' => 'User',
          'modul' => 'Modul',
          'aktivitas' => 'Aktivitas',
          'url' => 'URL',
          'metode' => 'Metode',
          'ip_address' => 'IP Address',
          'created_at' => 'Tanggal Dibuat',
        ],
        'kolom_numerik' => [
          'id' => 'ID',
          'user_id' => 'User',
        ],
        'kolom_boolean' => [],
      ],
      'identitas' => [
        'label' => 'Identitas Sistem',
        'model' => Identitas::class,
        'default_label' => 'nama_aplikasi',
        'default_value' => 'versi',
        'kolom' => [
          'id' => 'ID',
          'nama_aplikasi' => 'Nama Aplikasi',
          'singkatan_aplikasi' => 'Singkatan',
          'versi' => 'Versi',
          'is_active' => 'Status Aktif',
          'created_at' => 'Tanggal Dibuat',
        ],
        'kolom_numerik' => [
          'id' => 'ID',
        ],
        'kolom_boolean' => [
          'is_active' => 'Status Aktif',
        ],
      ],
    ];
  }
}
