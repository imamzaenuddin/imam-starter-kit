<?php

use App\Models\DashboardWidget;
use App\Models\Level;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(DashboardService::class);

    // Create test level and user
    $this->level = Level::create(['nama_level' => 'Test Level', 'deskripsi' => 'Test Description']);
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => bcrypt('password'),
        'level_id' => $this->level->id,
    ]);

    // Create test widget with KPI
    $this->widget = DashboardWidget::create([
        'nama' => 'Test Widget KPI',
        'nama_widget' => 'Test Widget KPI',
        'sumber_data' => 'users',
        'kolom_agregasi' => 'id',
        'tipe_query' => 'count',
        'tipe_tampilan' => 'kartu',
        'urutan' => 1,
        'kpi_target' => 10,
        'tampilkan_progress_bar' => true,
        'warna_threshold_hijau' => '28a745',
        'warna_threshold_kuning' => 'ffc107',
        'warna_threshold_merah' => 'dc3545',
        'is_active' => true,
    ]);
});

test('dataWidgetDenganKPI returns KPI percentage calculation', function () {
    $data = $this->service->dataWidgetDenganKPI($this->widget);

    expect($data)->toHaveKeys(['nilai', 'nilai_format', 'kpi_target', 'persentase_kpi', 'progress_bar_color', 'tampilkan_progress_bar'])
        ->and($data['kpi_target'])->toBe(10)
        ->and($data['persentase_kpi'])->toBeLessThanOrEqual(100)
        ->and($data['tampilkan_progress_bar'])->toBeTrue();
});

test('warnaThreshold returns hijau when persentase >= 100', function () {
    $warna = $this->service->warnaThreshold(100, $this->widget);
    expect($warna)->toBe('28a745');
});

test('warnaThreshold returns kuning when persentase >= 75 and < 100', function () {
    $warna = $this->service->warnaThreshold(80, $this->widget);
    expect($warna)->toBe('ffc107');
});

test('warnaThreshold returns merah when persentase < 75', function () {
    $warna = $this->service->warnaThreshold(50, $this->widget);
    expect($warna)->toBe('dc3545');
});

test('bandingkanPeriodeTersedia returns array of period options', function () {
    $options = $this->service->bandingkanPeriodeTersedia();

    expect($options)->toBeArray()
        ->toHaveKeys(['hari_sebelumnya', 'minggu_lalu', 'bulan_lalu', 'tahun_lalu'])
        ->toHaveCount(4);
});

test('cacheKeyWidget generates consistent key format', function () {
    $key1 = $this->service->cacheKeyWidget($this->widget);

    expect($key1)->toContain('dashboard_widget_')
        ->toContain((string) $this->widget->id);
});

test('dataWidgetCached caches data and retrieves from cache', function () {
    $data1 = $this->service->dataWidgetCached($this->widget, 5);

    expect($data1)->toHaveKey('nilai');

    // Verify cache was set
    $cacheKey = $this->service->cacheKeyWidget($this->widget);
    expect(cache()->has($cacheKey))->toBeTrue();
});

test('buatUlangCacheWidget clears widget cache', function () {
    $cacheKey = $this->service->cacheKeyWidget($this->widget);

    // Set cache first
    cache()->put($cacheKey, ['test' => 'data']);
    expect(cache()->has($cacheKey))->toBeTrue();

    // Clear cache
    $this->service->buatUlangCacheWidget($this->widget);
    expect(cache()->has($cacheKey))->toBeFalse();
});

test('dataPeriodeSebelumnya returns array with nilai and nilai_format when bandingkan_dengan is valid', function () {
    // Create widget with period comparison enabled
    $widgetWithComparison = DashboardWidget::create([
        'nama' => 'Test Widget Period',
        'nama_widget' => 'Test Widget Period',
        'sumber_data' => 'users',
        'kolom_agregasi' => 'id',
        'tipe_query' => 'count',
        'tipe_tampilan' => 'kartu',
        'urutan' => 1,
        'bandingkan_periode' => true,
        'bandingkan_dengan' => 'hari_sebelumnya',
        'is_active' => true,
    ]);

    $periodeData = $this->service->dataPeriodeSebelumnya($widgetWithComparison);

    expect($periodeData)->toBeArray()
        ->toHaveKeys(['nilai', 'nilai_format']);
});

test('parsePeriodeSebelumnya correctly parses hari_sebelumnya', function () {
    $reflection = new ReflectionClass($this->service);
    $method = $reflection->getMethod('parsePeriodeSebelumnya');
    $method->setAccessible(true);

    $result = $method->invoke($this->service, 'hari_sebelumnya');

    expect($result)->toBeArray()
        ->toHaveKeys(['from', 'to']);
});

test('dataWidgetDenganKPI includes period comparison data when bandingkan_periode is true', function () {
    // Create widget with period comparison
    $widgetWithComparison = DashboardWidget::create([
        'nama' => 'Test Period Widget',
        'nama_widget' => 'Test Period Widget',
        'sumber_data' => 'users',
        'kolom_agregasi' => 'id',
        'tipe_query' => 'count',
        'tipe_tampilan' => 'kartu',
        'urutan' => 1,
        'bandingkan_periode' => true,
        'bandingkan_dengan' => 'hari_sebelumnya',
        'is_active' => true,
    ]);

    $data = $this->service->dataWidgetDenganKPI($widgetWithComparison);

    // Period comparison data should be present
    expect($data)->toHaveKey('trend')
        ->and(in_array($data['trend'], ['naik', 'turun']))->toBeTrue();
});

test('buatUlangCacheSemuaWidget clears all active widget caches', function () {
    // Create additional widget
    DashboardWidget::create([
        'nama' => 'Test Widget 2',
        'nama_widget' => 'Test Widget 2',
        'sumber_data' => 'users',
        'kolom_agregasi' => 'id',
        'tipe_query' => 'count',
        'tipe_tampilan' => 'kartu',
        'urutan' => 2,
        'is_active' => true,
    ]);

    // Set cache for multiple widgets
    $widget1Key = $this->service->cacheKeyWidget($this->widget);
    cache()->put($widget1Key, ['test' => 'data1']);

    $widget2 = DashboardWidget::latest()->first();
    $widget2Key = $this->service->cacheKeyWidget($widget2);
    cache()->put($widget2Key, ['test' => 'data2']);

    // Clear all caches
    $this->service->buatUlangCacheSemuaWidget();

    expect(cache()->has($widget1Key))->toBeFalse()
        ->and(cache()->has($widget2Key))->toBeFalse();
});
