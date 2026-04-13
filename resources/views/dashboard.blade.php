@php
  $dashboardService = app(\App\Services\DashboardService::class);
  $widgets = $dashboardService->widgetUntukUser(auth()->user());
  $levelAktif = auth()->user()?->level?->nama_level ?? 'Tanpa Level';
@endphp

@section('vendor-script')
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection

@section('page-script')
  <script>
    function initSioDashboardCharts() {
      if (typeof ApexCharts === 'undefined') {
        return;
      }

      document.querySelectorAll('[data-sio-dashboard-chart]').forEach(function (element) {
        const payloadNode = element.querySelector('[type="application/json"]');
        let payload = {};

        try {
          payload = JSON.parse(payloadNode?.textContent || '{}');
        } catch (error) {
          console.error('Gagal membaca payload chart dashboard:', error);
          element.innerHTML = '<div class="text-center text-danger py-5">{{ __('messages.invalid_chart_payload') }}</div>';
          return;
        }

        if (!payload.series || !payload.series.length) {
          element.innerHTML = '<div class="text-center text-muted py-5">{{ __('messages.no_chart_data') }}</div>';
          return;
        }

        if (element.dataset.chartRendered === 'true') {
          return;
        }

        const warnaUtama = getComputedStyle(document.documentElement).getPropertyValue('--sio-main-color').trim() || '#696cff';
        const warnaKedua = getComputedStyle(document.documentElement).getPropertyValue('--sio-secondary-color').trim() || '#8592a3';
        const palet = [warnaUtama, warnaKedua, '#71dd37', '#ffab00', '#03c3ec', '#ff3e1d', '#233446'];

        const opsiUmum = {
          chart: {
            height: payload.chart_tinggi || 280,
            toolbar: { show: false },
            fontFamily: 'Public Sans, sans-serif'
          },
          colors: payload.chart_warna && payload.chart_warna.length ? payload.chart_warna : palet,
          labels: payload.labels || [],
          series: payload.series || [],
          legend: {
            position: 'bottom'
          },
          dataLabels: {
            enabled: false
          },
          stroke: {
            curve: 'smooth',
            width: 3
          }
        };

        const opsi = payload.chart_tipe === 'donut'
          ? {
              ...opsiUmum,
              chart: { ...opsiUmum.chart, type: 'donut' },
              plotOptions: { pie: { donut: { size: '62%' } } }
            }
          : {
              ...opsiUmum,
              chart: { ...opsiUmum.chart, type: payload.chart_tipe || 'bar' },
              xaxis: { categories: payload.labels || [] },
              series: [{ name: payload.judul || 'Series', data: payload.series || [] }],
              plotOptions: { bar: { borderRadius: 6, columnWidth: '42%' } },
              yaxis: { labels: { formatter: function (value) { return Number(value).toLocaleString('id-ID'); } } }
            };

        new ApexCharts(element, opsi).render();
        element.dataset.chartRendered = 'true';
      });
    }

    document.addEventListener('DOMContentLoaded', initSioDashboardCharts);
    document.addEventListener('livewire:navigated', initSioDashboardCharts);
  </script>
@endsection

@section('title', __('messages.dashboard'))
<x-layouts.app :title="__('messages.dashboard')">
  <div class="row g-4 mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg, rgba(var(--bs-primary-rgb), .12), rgba(var(--bs-body-bg-rgb), 1));">
        <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
          <div>
            <span class="badge bg-label-primary mb-2">{{ __('messages.dynamic_dashboard') }}</span>
            <h4 class="mb-1">{{ __('messages.level_summary', ['level' => $levelAktif]) }}</h4>
            <p class="text-muted mb-0">{{ __('messages.dashboard_realtime_info') }}</p>
          </div>
          <div class="text-lg-end">
            <div class="text-muted small">{{ __('messages.active_widget_count') }}</div>
            <div class="display-6 fw-bold mb-0">{{ $widgets->count() }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    @forelse ($widgets as $widget)
      @php $hasil = $widget->hasil_widget; @endphp
      <div class="{{ $widget->layout_kolom }}">
        @if ($widget->tipe_tampilan === 'daftar')
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-1">{{ $widget->nama_widget }}</h5>
                <small class="text-muted">{{ $widget->deskripsi ?: ($hasil['sumber_label'] ?? '') }}</small>
              </div>
              <span class="avatar rounded bg-label-{{ $widget->warna }}">
                <i class="{{ $widget->icon ?: 'bx bx-list-ul' }}"></i>
              </span>
            </div>
            <div class="card-body">
              @if ($hasil['rows']->isEmpty())
                <div class="text-center text-muted py-4">
                  <i class="bx bx-data" style="font-size:2rem;opacity:.45"></i>
                  <p class="mt-2 mb-0">{{ __('messages.no_widget_data') }}</p>
                </div>
              @else
                <div class="list-group list-group-flush">
                  @foreach ($hasil['rows'] as $row)
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-start border-0 border-bottom">
                      <div class="pe-3">
                        <div class="fw-semibold">{{ \Illuminate\Support\Str::limit((string) $row['label'], 55) }}</div>
                        @if (! empty($row['nilai']))
                          <small class="text-muted">{{ \Illuminate\Support\Str::limit((string) $row['nilai'], 45) }}</small>
                        @endif
                      </div>
                      <small class="text-muted text-nowrap">
                        {{ ! empty($row['waktu']) ? \Illuminate\Support\Carbon::parse($row['waktu'])->diffForHumans() : '-' }}
                      </small>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
        @elseif ($widget->tipe_tampilan === 'grafik')
          @php
            $chartPayload = json_encode([
                'labels' => $hasil['labels'] ?? [],
                'series' => $hasil['series'] ?? [],
                'chart_tipe' => $hasil['chart_tipe'] ?? 'bar',
                'chart_tinggi' => $hasil['chart_tinggi'] ?? 280,
                'chart_warna' => $hasil['chart_warna'] ?? [],
                'judul' => $widget->nama_widget,
            ]);
          @endphp
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-1">{{ $widget->nama_widget }}</h5>
                <small class="text-muted">{{ $widget->deskripsi ?: ($hasil['sumber_label'] ?? '') }}</small>
              </div>
              <span class="avatar rounded bg-label-{{ $widget->warna }}">
                <i class="{{ $widget->icon ?: 'bx bx-bar-chart-alt-2' }}"></i>
              </span>
            </div>
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                  <div class="text-muted small">{{ __('messages.total_accumulation') }}</div>
                  <h3 class="mb-0">{{ $hasil['nilai_format'] }}</h3>
                </div>
                <span class="badge bg-label-{{ $widget->warna }} text-uppercase">{{ $hasil['chart_tipe'] ?? 'bar' }}</span>
              </div>
              <div
                id="sio-widget-chart-{{ $widget->id }}"
                data-sio-dashboard-chart
              >
                <script type="application/json">{!! $chartPayload !!}</script>
              </div>
            </div>
          </div>
        @else
          <div class="card h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <span class="badge bg-label-{{ $widget->warna }} mb-2">{{ $hasil['sumber_label'] ?? __('messages.realtime') }}</span>
                  <h5 class="mb-1">{{ $widget->nama_widget }}</h5>
                  <p class="text-muted mb-0" style="min-height:42px">{{ $widget->deskripsi ?: __('messages.dashboard_realtime_analysis') }}</p>
                </div>
                <span class="avatar rounded bg-label-{{ $widget->warna }}">
                  <i class="{{ $widget->icon ?: 'bx bx-bar-chart-alt-2' }}"></i>
                </span>
              </div>

              <div class="d-flex align-items-end justify-content-between">
                <div>
                  <h2 class="mb-1 fw-bold">{{ $hasil['nilai_format'] }}</h2>
                  <small class="text-muted">{{ __('messages.realtime_synced_on_load') }}</small>
                </div>
                <span class="badge bg-label-{{ $widget->warna }}">{{ $levelAktif }}</span>
              </div>
            </div>
          </div>
        @endif
      </div>
    @empty
      <div class="col-12">
        <div class="card">
          <div class="card-body text-center py-5 text-muted">
            <i class="bx bx-layout" style="font-size:2.5rem;opacity:.4"></i>
            <h5 class="mt-3">{{ __('messages.dashboard_not_configured_title') }}</h5>
            <p class="mb-0">{{ __('messages.dashboard_not_configured_desc') }}</p>
          </div>
        </div>
      </div>
    @endforelse
  </div>
</x-layouts.app>
