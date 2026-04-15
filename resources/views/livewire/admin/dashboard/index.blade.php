<?php
/**
 * Halaman CRUD Pengelolaan Dashboard Dinamis
 *
 * Route  : GET /admin/dashboard  (name: admin.dashboard)
 * Layout : components.layouts.app
 */

use App\Models\DashboardWidget;
use App\Models\Level;
use App\Services\DashboardService;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $namaWidget = '';
    public string $deskripsi = '';
    public string $sumberData = 'users';
    public string $tipeTampilan = 'statistik';
    public string $tipeQuery = 'count';
    public string $chartTipe = 'bar';
    public int $chartTinggi = 280;
    public string $chartWarna = '';
    public ?string $kolomAgregasi = null;
    public ?string $kolomLabel = null;
    public ?string $kolomNilai = null;
    public ?string $filterKolom = null;
    public string $filterOperator = '=';
    public string $filterNilai = '';
    public string $layoutKolom = 'col-xl-3 col-md-6';
    public string $warna = 'primary';
    public string $icon = 'bx bx-bar-chart-alt-2';
    public int $batasData = 5;
    public int $urutan = 0;
    public bool $isActive = true;
    public ?int $kpiTarget = null;
    public bool $tampilkanProgressBar = false;
    public bool $bandingkanPeriode = false;
    public ?string $bandingkanDengan = null;
    public string $warnaThresholdHijau = '90ddd52';
    public string $warnaThresholdKuning = 'ffc107';
    public string $warnaThresholdMerah = 'dc3545';
    public array $selectedLevels = [];
    public array $presetPaletChart = [
      'dashboard_palette_main' => ['#696cff', '#03c3ec', '#71dd37', '#ffab00'],
      'dashboard_palette_ocean' => ['#0ea5e9', '#06b6d4', '#14b8a6', '#22c55e'],
      'dashboard_palette_sunset' => ['#f97316', '#ef4444', '#ec4899', '#8b5cf6'],
      'dashboard_palette_emerald' => ['#10b981', '#84cc16', '#22c55e', '#14b8a6'],
      'dashboard_palette_slate' => ['#334155', '#475569', '#64748b', '#94a3b8'],
    ];

    public ?int $editId = null;
    public bool $showModal = false;

    public function mount(): void
    {
        if (! auth()->user()->bisaMenu('/admin/dashboard', 'dapat_lihat')) {
            abort(403);
        }

        $this->sinkronkanPilihan();
    }

    public function updatedSumberData(): void
    {
        $this->kolomAgregasi = null;
        $this->kolomLabel = null;
        $this->kolomNilai = null;
        $this->filterKolom = null;
        $this->filterNilai = '';
        $this->sinkronkanPilihan();
    }

    public function updatedTipeTampilan(): void
    {
        $opsiQuery = array_keys($this->service()->tipeQueryTersedia($this->tipeTampilan));
        $this->tipeQuery = $opsiQuery[0] ?? 'count';

        if ($this->tipeTampilan !== 'daftar') {
            $this->kolomNilai = null;
        }

        if ($this->tipeTampilan === 'statistik') {
          $this->kolomLabel = null;
          $this->batasData = 5;
        }

        if ($this->tipeTampilan === 'grafik') {
          $this->kolomNilai = null;
        }

        $this->sinkronkanPilihan();
    }

    public function updatedTipeQuery(): void
    {
        if ($this->tipeQuery === 'count') {
            $this->kolomAgregasi = null;
        }
    }

    public function pilihPaletChart(string $namaPalet): void
    {
      $palet = $this->presetPaletChart[$namaPalet] ?? null;

      if (! $palet) {
        return;
      }

      $this->chartWarna = implode(', ', $palet);
    }

    public function buka(): void
    {
        if (! auth()->user()->bisaMenu('/admin/dashboard', 'dapat_buat')) {
            abort(403);
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        if (! auth()->user()->bisaMenu('/admin/dashboard', 'dapat_ubah')) {
            abort(403);
        }

        $widget = DashboardWidget::with('levels')->findOrFail($id);

        $this->editId = $widget->id;
        $this->namaWidget = $widget->nama_widget;
        $this->deskripsi = $widget->deskripsi ?? '';
        $this->sumberData = $widget->sumber_data;
        $this->tipeTampilan = $widget->tipe_tampilan;
        $this->tipeQuery = $widget->tipe_query;
        $this->chartTipe = $widget->chart_tipe ?: 'bar';
        $this->chartTinggi = $widget->chart_tinggi ?: 280;
        $this->chartWarna = implode(', ', $widget->chart_warna ?? []);
        $this->kolomAgregasi = $widget->kolom_agregasi;
        $this->kolomLabel = $widget->kolom_label;
        $this->kolomNilai = $widget->kolom_nilai;
        $this->filterKolom = $widget->filter_kolom;
        $this->filterOperator = $widget->filter_operator ?: '=';
        $this->filterNilai = $widget->filter_nilai ?? '';
        $this->layoutKolom = $widget->layout_kolom;
        $this->warna = $widget->warna;
        $this->icon = $widget->icon ?? 'bx bx-bar-chart-alt-2';
        $this->batasData = $widget->batas_data;
        $this->urutan = $widget->urutan;
        $this->isActive = $widget->is_active;
        $this->kpiTarget = $widget->kpi_target;
        $this->tampilkanProgressBar = (bool) $widget->tampilkan_progress_bar;
        $this->bandingkanPeriode = (bool) $widget->bandingkan_periode;
        $this->bandingkanDengan = $widget->bandingkan_dengan;
        $this->warnaThresholdHijau = $widget->warna_threshold_hijau ?: '90ddd52';
        $this->warnaThresholdKuning = $widget->warna_threshold_kuning ?: 'ffc107';
        $this->warnaThresholdMerah = $widget->warna_threshold_merah ?: 'dc3545';
        $this->selectedLevels = $widget->levels->pluck('id')->map(fn ($id) => (string) $id)->all();

        $this->sinkronkanPilihan();
        $this->showModal = true;
    }

    public function simpan(): void
    {
        $izin = $this->editId ? 'dapat_ubah' : 'dapat_buat';
        if (! auth()->user()->bisaMenu('/admin/dashboard', $izin)) {
            abort(403);
        }

        $service = $this->service();

        $data = $this->validate([
            'namaWidget' => 'required|string|max:120',
            'deskripsi' => 'nullable|string|max:1000',
            'sumberData' => ['required', 'string', Rule::in(array_keys($service->sumberDataTersedia()))],
            'tipeTampilan' => ['required', 'string', Rule::in(array_keys($service->tipeTampilanTersedia()))],
            'tipeQuery' => ['required', 'string', Rule::in(array_keys($service->tipeQueryTersedia($this->tipeTampilan)))],
            'chartTipe' => ['required', 'string', Rule::in(array_keys($service->chartTipeTersedia()))],
            'chartTinggi' => 'required|integer|min:220|max:520',
            'chartWarna' => 'nullable|string|max:255',
            'kolomAgregasi' => ['nullable', 'string', Rule::in(array_keys($service->kolomTersedia($this->sumberData, 'agregasi')))],
            'kolomLabel' => ['nullable', 'string', Rule::in(array_keys($service->kolomTersedia($this->sumberData, 'filter')))],
            'kolomNilai' => ['nullable', 'string', Rule::in(array_keys($service->kolomTersedia($this->sumberData, 'filter')))],
            'filterKolom' => ['nullable', 'string', Rule::in(array_keys($service->kolomTersedia($this->sumberData, 'filter')))],
            'filterOperator' => ['required', 'string', Rule::in(array_keys($service->operatorFilterTersedia()))],
            'filterNilai' => 'nullable|string|max:255',
            'layoutKolom' => ['required', 'string', Rule::in(array_keys($service->layoutTersedia()))],
            'warna' => ['required', 'string', Rule::in(array_keys($service->warnaTersedia()))],
            'icon' => 'nullable|string|max:100',
            'batasData' => 'required|integer|min:1|max:20',
            'urutan' => 'required|integer|min:0|max:999',
            'isActive' => 'boolean',
            'selectedLevels' => 'required|array|min:1',
            'selectedLevels.*' => 'exists:m_level,id',
            'kpiTarget' => 'nullable|integer|min:1|max:999999',
            'tampilkanProgressBar' => 'boolean',
            'bandingkanPeriode' => 'boolean',
            'bandingkanDengan' => ['nullable', 'string', Rule::in(array_keys($service->bandingkanPeriodeTersedia()))],
            'warnaThresholdHijau' => 'nullable|regex:/^[a-fA-F0-9]{6}$/',
            'warnaThresholdKuning' => 'nullable|regex:/^[a-fA-F0-9]{6}$/',
            'warnaThresholdMerah' => 'nullable|regex:/^[a-fA-F0-9]{6}$/',
        ]);

        if (in_array($this->tipeTampilan, ['statistik', 'grafik'], true) && $this->tipeQuery !== 'count' && ! $this->kolomAgregasi) {
            $this->addError('kolomAgregasi', __('messages.dashboard_error_aggregation_required'));

            return;
        }

        if (in_array($this->tipeTampilan, ['daftar', 'grafik'], true) && ! $this->kolomLabel) {
          $this->addError('kolomLabel', __('messages.dashboard_error_label_required'));

            return;
        }

        $payload = [
            'nama_widget' => $data['namaWidget'],
            'deskripsi' => $data['deskripsi'] ?: null,
            'sumber_data' => $data['sumberData'],
            'tipe_tampilan' => $data['tipeTampilan'],
            'tipe_query' => $data['tipeQuery'],
            'chart_tipe' => $data['tipeTampilan'] === 'grafik' ? $data['chartTipe'] : null,
          'chart_tinggi' => $data['tipeTampilan'] === 'grafik' ? $data['chartTinggi'] : null,
          'chart_warna' => $data['tipeTampilan'] === 'grafik' ? ($service->parseWarnaGrafik($data['chartWarna'] ?? '')) : null,
            'kolom_agregasi' => $data['tipeQuery'] === 'count' ? null : ($data['kolomAgregasi'] ?: null),
            'kolom_label' => in_array($data['tipeTampilan'], ['daftar', 'grafik'], true) ? ($data['kolomLabel'] ?: null) : null,
            'kolom_nilai' => $data['tipeTampilan'] === 'daftar' ? ($data['kolomNilai'] ?: null) : null,
            'filter_kolom' => $data['filterKolom'] ?: null,
            'filter_operator' => $data['filterKolom'] && $data['filterNilai'] !== '' ? $data['filterOperator'] : null,
            'filter_nilai' => $data['filterKolom'] && $data['filterNilai'] !== '' ? $data['filterNilai'] : null,
            'layout_kolom' => $data['layoutKolom'],
            'warna' => $data['warna'],
            'icon' => $data['icon'] ?: null,
            'batas_data' => in_array($data['tipeTampilan'], ['daftar', 'grafik'], true) ? $data['batasData'] : 5,
            'urutan' => $data['urutan'],
            'is_active' => $data['isActive'],
            'kpi_target' => $data['kpiTarget'] ?: null,
            'tampilkan_progress_bar' => (bool) $data['tampilkanProgressBar'],
            'bandingkan_periode' => (bool) $data['bandingkanPeriode'],
            'bandingkan_dengan' => $data['bandingkanPeriode'] ? ($data['bandingkanDengan'] ?: null) : null,
            'warna_threshold_hijau' => $data['warnaThresholdHijau'] ?: '90ddd52',
            'warna_threshold_kuning' => $data['warnaThresholdKuning'] ?: 'ffc107',
            'warna_threshold_merah' => $data['warnaThresholdMerah'] ?: 'dc3545',
        ];

        $widget = $this->editId
            ? tap(DashboardWidget::findOrFail($this->editId))->update($payload)
            : DashboardWidget::create($payload);

        $widget->levels()->sync($data['selectedLevels']);

        app(LogAktivitasService::class)->catatManual(
          __('messages.dashboard'),
          $this->editId
            ? __('messages.dashboard_log_update_widget', ['nama' => $widget->nama_widget])
            : __('messages.dashboard_log_add_widget', ['nama' => $widget->nama_widget]),
          '/admin/dashboard',
          [
            'widget_id' => $widget->id,
            'tipe_tampilan' => $widget->tipe_tampilan,
            'level_ids' => $data['selectedLevels'],
          ]
        );

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function hapus(int $id): void
    {
        if (! auth()->user()->bisaMenu('/admin/dashboard', 'dapat_hapus')) {
            abort(403);
        }

        $widget = DashboardWidget::findOrFail($id);
        app(LogAktivitasService::class)->catatManual(__('messages.dashboard'), __('messages.dashboard_log_delete_widget', ['nama' => $widget->nama_widget]), '/admin/dashboard', [
          'widget_id' => $widget->id,
        ]);
        $widget->delete();
        $this->resetPage();
    }

    public function with(): array
    {
        $service = $this->service();

        return [
            'widgets' => DashboardWidget::query()
                ->with('levels:id,nama_level')
                ->when($this->search, fn ($query) => $query->where('nama_widget', 'like', '%' . $this->search . '%'))
                ->orderBy('urutan')
                ->orderBy('nama_widget')
              ->paginate((int) config('app_runtime.pagination_default', 10)),
            'levels' => Level::query()->where('is_active', true)->orderBy('nama_level')->get(),
            'opsiSumberData' => $service->sumberDataTersedia(),
            'opsiTampilan' => $service->tipeTampilanTersedia(),
            'opsiQuery' => $service->tipeQueryTersedia($this->tipeTampilan),
            'opsiKolomFilter' => $service->kolomTersedia($this->sumberData, 'filter'),
            'opsiKolomAgregasi' => $service->kolomTersedia($this->sumberData, 'agregasi'),
            'opsiLayout' => $service->layoutTersedia(),
            'opsiWarna' => $service->warnaTersedia(),
            'opsiChartTipe' => $service->chartTipeTersedia(),
            'opsiOperatorFilter' => $service->operatorFilterTersedia(),
        ];
    }

    private function resetForm(): void
    {
        $this->reset([
            'namaWidget',
            'deskripsi',
            'chartTipe',
            'chartTinggi',
            'chartWarna',
            'kolomAgregasi',
            'kolomLabel',
            'kolomNilai',
            'filterKolom',
            'filterNilai',
            'selectedLevels',
            'editId',
            'kpiTarget',
            'tampilkanProgressBar',
            'bandingkanPeriode',
            'bandingkanDengan',
        ]);

        $this->sumberData = 'users';
        $this->tipeTampilan = 'statistik';
        $this->tipeQuery = 'count';
        $this->chartTipe = 'bar';
        $this->chartTinggi = 280;
        $this->chartWarna = '';
        $this->filterOperator = '=';
        $this->layoutKolom = 'col-xl-3 col-md-6';
        $this->warna = 'primary';
        $this->icon = 'bx bx-bar-chart-alt-2';
        $this->batasData = 5;
        $this->urutan = 0;
        $this->isActive = true;
        $this->warnaThresholdHijau = '90ddd52';
        $this->warnaThresholdKuning = 'ffc107';
        $this->warnaThresholdMerah = 'dc3545';
        $this->resetValidation();

        $this->sinkronkanPilihan();
    }

    private function sinkronkanPilihan(): void
    {
        $service = $this->service();

        if (! array_key_exists($this->tipeQuery, $service->tipeQueryTersedia($this->tipeTampilan))) {
            $this->tipeQuery = array_key_first($service->tipeQueryTersedia($this->tipeTampilan));
        }

        foreach (['kolomAgregasi' => 'agregasi', 'kolomLabel' => 'filter', 'kolomNilai' => 'filter', 'filterKolom' => 'filter'] as $field => $jenis) {
            $opsi = $service->kolomTersedia($this->sumberData, $jenis);
            if ($this->{$field} && ! array_key_exists($this->{$field}, $opsi)) {
                $this->{$field} = null;
            }
        }
    }

    private function service(): DashboardService
    {
        return app(DashboardService::class);
    }
};
?>
@section('title', __('messages.admin_manage_dashboard_title'))

@section('vendor-script')
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection

@section('page-script')
  <script>
    window.sioDashboardPreview = function () {
      return {
        chart: null,
        tipeSaatIni: 'bar',
        namaWidgetSaatIni: @js(__('messages.dashboard_chart_preview_widget_name')),
        iconSaatIni: 'bx bx-bar-chart-alt-2',
        warnaWidgetSaatIni: 'primary',
        init() {
          this.$nextTick(() => this.render());
        },
        baca(name, fallback = '') {
          return this.$refs[name]?.value ?? fallback;
        },
        badgeClass() {
          return `badge bg-label-${this.warnaWidgetSaatIni}`;
        },
        avatarClass() {
          return `avatar avatar-sm rounded bg-label-${this.warnaWidgetSaatIni}`;
        },
        palet() {
          const warnaUtama = getComputedStyle(document.documentElement).getPropertyValue('--sio-main-color').trim() || '#696cff';
          const warnaKedua = getComputedStyle(document.documentElement).getPropertyValue('--sio-secondary-color').trim() || '#8592a3';
          const fallback = [warnaUtama, warnaKedua, '#71dd37', '#ffab00', '#03c3ec', '#ff3e1d'];

          const manual = String(this.baca('chartColorInput', ''))
            .split(',')
            .map(item => item.trim())
            .filter(Boolean)
            .map(item => item.startsWith('#') ? item : `#${item}`)
            .filter(item => /^#([a-fA-F0-9]{6})$/.test(item));

          return manual.length ? manual : fallback;
        },
        sinkronkanMeta() {
          this.tipeSaatIni = this.baca('chartTypeInput', 'bar') || 'bar';
          const defaultNamaWidget = @js(__('messages.dashboard_chart_preview_widget_name'));
          this.namaWidgetSaatIni = this.baca('widgetNameInput', defaultNamaWidget) || defaultNamaWidget;
          this.iconSaatIni = this.baca('widgetIconInput', 'bx bx-bar-chart-alt-2') || 'bx bx-bar-chart-alt-2';
          this.warnaWidgetSaatIni = this.baca('widgetColorInput', 'primary') || 'primary';
        },
        render() {
          if (typeof ApexCharts === 'undefined' || !this.$refs.chart) {
            return;
          }

          this.sinkronkanMeta();

          if (this.chart) {
            this.chart.destroy();
          }

          const labels = [
            @js(__('messages.dashboard_chart_label_a')),
            @js(__('messages.dashboard_chart_label_b')),
            @js(__('messages.dashboard_chart_label_c')),
            @js(__('messages.dashboard_chart_label_d')),
          ];
          const angka = [42, 28, 18, 12];
          const tinggi = Number(this.baca('chartHeightInput', 280) || 280);
          const warna = this.palet();

          const opsiDasar = {
            chart: {
              height: tinggi,
              fontFamily: 'Public Sans, sans-serif',
              toolbar: { show: false }
            },
            colors: warna,
            dataLabels: { enabled: false },
            labels,
            legend: { position: 'bottom' },
            stroke: { curve: 'smooth', width: 3 }
          };

          const opsi = this.tipeSaatIni === 'donut'
            ? {
                ...opsiDasar,
                chart: { ...opsiDasar.chart, type: 'donut' },
                series: angka,
                plotOptions: { pie: { donut: { size: '62%' } } }
              }
            : {
                ...opsiDasar,
                chart: { ...opsiDasar.chart, type: this.tipeSaatIni || 'bar' },
                xaxis: { categories: labels },
                series: [{ name: @js(__('messages.dashboard_chart_preview_series_name')), data: angka }],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '42%' } },
                yaxis: { labels: { formatter: value => Number(value).toLocaleString(@js(str_replace('_', '-', app()->getLocale()))) } }
              };

          this.chart = new ApexCharts(this.$refs.chart, opsi);
          this.chart.render();
        }
      }
    }
  </script>
@endsection

<div>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1" style="color:#1e293b">{{ __('messages.admin_manage_dashboard_heading') }}</h4>
      <p class="text-muted mb-0" style="font-size:.875rem">
        {{ __('messages.admin_manage_dashboard_subheading') }}
      </p>
    </div>
    <button class="btn btn-primary" wire:click="buka">
      <i class="bx bx-plus me-1"></i> {{ __('messages.add_widget') }}
    </button>
  </div>

  <div class="card mb-4">
    <div class="card-body py-3">
      <input wire:model.live.debounce.300ms="search" type="search"
             class="form-control" placeholder="{{ __('messages.search_dashboard_widget_placeholder') }}">
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>{{ __('messages.widget') }}</th>
            <th>{{ __('messages.level') }}</th>
            <th>{{ __('messages.source') }}</th>
            <th>{{ __('messages.display') }}</th>
            <th>{{ __('messages.query') }}</th>
            <th>{{ __('messages.status') }}</th>
            <th class="text-center">{{ __('messages.action') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($widgets as $widget)
            <tr>
              <td>{{ $widgets->firstItem() + $loop->index }}</td>
              <td>
                <div class="d-flex align-items-start gap-2">
                  <span class="avatar avatar-sm rounded bg-label-{{ $widget->warna }}">
                    <i class="{{ $widget->icon ?: 'bx bx-layout' }}"></i>
                  </span>
                  <div>
                    <div class="fw-semibold">{{ $widget->nama_widget }}</div>
                    <small class="text-muted">{{ $widget->deskripsi ?: __('messages.no_description') }}</small>
                  </div>
                </div>
              </td>
              <td>
                {{ $widget->levels->pluck('nama_level')->join(', ') ?: '-' }}
              </td>
              <td>{{ $opsiSumberData[$widget->sumber_data] ?? $widget->sumber_data }}</td>
              <td>{{ $opsiTampilan[$widget->tipe_tampilan] ?? $widget->tipe_tampilan }}</td>
              <td>
                <div>{{ str($widget->tipe_query)->headline() }}</div>
                @if ($widget->tipe_tampilan === 'grafik')
                  <small class="text-muted">{{ __('messages.chart') }}: {{ str($widget->chart_tipe ?: 'bar')->headline() }}</small>
                @endif
                @if ($widget->filter_kolom && $widget->filter_nilai !== null)
                  <small class="text-muted">{{ __('messages.filter') }}: {{ $widget->filter_kolom }} {{ $widget->filter_operator }} {{ $widget->filter_nilai }}</small>
                @endif
              </td>
              <td>
                @if ($widget->is_active)
                  <span class="badge bg-label-success">{{ __('messages.active') }}</span>
                @else
                  <span class="badge bg-label-secondary">{{ __('messages.inactive') }}</span>
                @endif
              </td>
              <td class="text-center">
                <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit({{ $widget->id }})" title="{{ __('messages.edit') }}">
                  <i class="bx bx-edit-alt"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-text-danger"
                        title="{{ __('messages.delete') }}"
                        @click="Swal.fire({
                          title: '{{ __('messages.confirm_delete') }}',
                          text: '{{ __('messages.confirm_delete_widget', ['nama' => addslashes($widget->nama_widget)]) }}',
                          icon: 'warning',
                          showCancelButton: true,
                          confirmButtonText: '{{ __('messages.yes_delete') }}',
                          cancelButtonText: '{{ __('messages.cancel') }}',
                        }).then(r => r.isConfirmed && $wire.hapus({{ $widget->id }}))"
                        >
                  <i class="bx bx-trash"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">{{ __('messages.no_dashboard_widget_data') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $widgets->links() }}</div>
  </div>

  @if ($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.45)">
      <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" style="max-height:calc(100vh - 1.5rem)">
        <div class="modal-content" style="max-height:calc(100vh - 1.5rem)">
          <div class="modal-header">
            <h5 class="modal-title">{{ $editId ? __('messages.edit_dashboard_widget') : __('messages.add_dashboard_widget') }}</h5>
            <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
          </div>

          <form wire:submit="simpan">
            <div class="modal-body overflow-auto" style="max-height:72vh" x-data="window.sioDashboardPreview()" x-init="init()">
              <div class="row">
                <div class="col-md-8 mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.widget_name') }} <span class="text-danger">*</span></label>
                  <input wire:model="namaWidget" type="text" class="form-control @error('namaWidget') is-invalid @enderror"
                         x-ref="widgetNameInput" x-on:input.debounce.150ms="render()"
                        placeholder="{{ __('messages.dashboard_widget_name_placeholder') }}">
                  @error('namaWidget') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.order') }} <span class="text-danger">*</span></label>
                  <input wire:model="urutan" type="number" min="0" class="form-control @error('urutan') is-invalid @enderror">
                  @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.description') }}</label>
                <textarea wire:model="deskripsi" rows="2" class="form-control @error('deskripsi') is-invalid @enderror"
                          placeholder="{{ __('messages.dashboard_widget_description_placeholder') }}"></textarea>
                @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">{{ __('messages.target_level_group') }} <span class="text-danger">*</span></label>
                <div class="row g-2">
                  @foreach ($levels as $level)
                    <div class="col-md-4">
                      <label class="border rounded px-3 py-2 w-100 d-flex align-items-center gap-2" style="cursor:pointer;">
                        <input wire:model="selectedLevels" class="form-check-input mt-0" type="checkbox" value="{{ $level->id }}">
                        <span>{{ $level->nama_level }}</span>
                      </label>
                    </div>
                  @endforeach
                </div>
                @error('selectedLevels') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @error('selectedLevels.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.source_data') }} <span class="text-danger">*</span></label>
                  <select wire:model.live="sumberData" class="form-select @error('sumberData') is-invalid @enderror">
                    @foreach ($opsiSumberData as $value => $label)
                      <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  @error('sumberData') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.widget_display') }} <span class="text-danger">*</span></label>
                  <select wire:model.live="tipeTampilan" class="form-select @error('tipeTampilan') is-invalid @enderror">
                    @foreach ($opsiTampilan as $value => $label)
                      <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  @error('tipeTampilan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.query_type') }} <span class="text-danger">*</span></label>
                  <select wire:model="tipeQuery" class="form-select @error('tipeQuery') is-invalid @enderror">
                    @foreach ($opsiQuery as $value => $label)
                      <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  @error('tipeQuery') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                @if (in_array($tipeTampilan, ['statistik', 'grafik'], true) && $tipeQuery !== 'count')
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">{{ __('messages.aggregation_column') }} <span class="text-danger">*</span></label>
                    <select wire:model="kolomAgregasi" class="form-select @error('kolomAgregasi') is-invalid @enderror">
                      <option value="">{{ __('messages.dashboard_select_column') }}</option>
                      @foreach ($opsiKolomAgregasi as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                      @endforeach
                    </select>
                    @error('kolomAgregasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                @endif

                @if (in_array($tipeTampilan, ['daftar', 'grafik'], true))
                  <div class="col-md-3 mb-3">
                    <label class="form-label fw-semibold">{{ __('messages.label_column') }} <span class="text-danger">*</span></label>
                    <select wire:model="kolomLabel" class="form-select @error('kolomLabel') is-invalid @enderror">
                      <option value="">{{ __('messages.dashboard_select_column') }}</option>
                      @foreach ($opsiKolomFilter as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                      @endforeach
                    </select>
                    @error('kolomLabel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  @if ($tipeTampilan === 'daftar')
                    <div class="col-md-3 mb-3">
                      <label class="form-label fw-semibold">{{ __('messages.value_column') }}</label>
                      <select wire:model="kolomNilai" class="form-select @error('kolomNilai') is-invalid @enderror">
                        <option value="">{{ __('messages.dashboard_optional') }}</option>
                        @foreach ($opsiKolomFilter as $value => $label)
                          <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                      </select>
                      @error('kolomNilai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                  @endif
                  @if ($tipeTampilan === 'grafik')
                    <div class="col-md-3 mb-3">
                      <label class="form-label fw-semibold">{{ __('messages.chart_type') }}</label>
                      <select wire:model="chartTipe" x-ref="chartTypeInput" x-on:change="render()" class="form-select @error('chartTipe') is-invalid @enderror">
                        @foreach ($opsiChartTipe as $value => $label)
                          <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                      </select>
                      @error('chartTipe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                      <label class="form-label fw-semibold">{{ __('messages.chart_height') }}</label>
                      <input wire:model="chartTinggi" x-ref="chartHeightInput" x-on:input.debounce.150ms="render()" type="number" min="220" max="520" class="form-control @error('chartTinggi') is-invalid @enderror">
                      @error('chartTinggi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                  @endif
                  <div class="col-md-3 mb-3">
                    <label class="form-label fw-semibold">{{ __('messages.data_count') }} {{ $tipeTampilan === 'grafik' ? __('messages.chart') : '' }}</label>
                    <input wire:model="batasData" type="number" min="1" max="20" class="form-control @error('batasData') is-invalid @enderror">
                    @error('batasData') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                @endif

              @if ($tipeTampilan === 'grafik')
                <div class="mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.chart_series_color') }}</label>
                  <input wire:model="chartWarna" type="text" class="form-control @error('chartWarna') is-invalid @enderror"
                        x-ref="chartColorInput" x-on:input.debounce.150ms="render()"
                     placeholder="{{ __('messages.dashboard_chart_color_placeholder') }}">
                  <div class="form-text">{{ __('messages.chart_color_hint') }}</div>
                  @error('chartWarna') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.quick_palette_preset') }}</label>
                  <div class="d-flex flex-wrap gap-2">
                    @foreach ($presetPaletChart as $namaPalet => $warnaPalet)
                      <button type="button"
                              class="btn btn-sm btn-outline-secondary"
                              wire:click="pilihPaletChart('{{ $namaPalet }}')">
                        <span class="d-inline-flex align-items-center gap-2">
                          <span class="d-inline-flex align-items-center gap-1">
                            @foreach ($warnaPalet as $warnaItem)
                              <span class="rounded-circle border" style="width:12px;height:12px;background:{{ $warnaItem }}"></span>
                            @endforeach
                          </span>
                          <span>{{ __('messages.' . $namaPalet) }}</span>
                        </span>
                      </button>
                    @endforeach
                  </div>
                </div>

                <div class="card border shadow-none mb-3">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                      <h6 class="mb-1">{{ __('messages.chart_preview') }}</h6>
                      <small class="text-muted">{{ __('messages.chart_preview_hint') }}</small>
                    </div>
                    <span :class="badgeClass()" class="text-uppercase" x-text="tipeSaatIni"></span>
                  </div>
                  <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                      <div class="d-flex align-items-center gap-2">
                        <span :class="avatarClass()">
                          <i :class="iconSaatIni"></i>
                        </span>
                        <div>
                          <div class="fw-semibold" x-text="namaWidgetSaatIni"></div>
                          <small class="text-muted">{{ __('messages.card_color_follow_widget') }}</small>
                        </div>
                      </div>
                      <span :class="badgeClass()" x-text="warnaWidgetSaatIni"></span>
                    </div>
                    <div x-ref="chart"></div>
                  </div>
                </div>
              @endif
              </div>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.filter_column') }}</label>
                  <select wire:model="filterKolom" class="form-select @error('filterKolom') is-invalid @enderror">
                    <option value="">{{ __('messages.no_filter') }}</option>
                    @foreach ($opsiKolomFilter as $value => $label)
                      <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  @error('filterKolom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.filter_operator') }}</label>
                  <select wire:model="filterOperator" class="form-select @error('filterOperator') is-invalid @enderror">
                    @foreach ($opsiOperatorFilter as $value => $label)
                      <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  @error('filterOperator') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.filter_value') }}</label>
                  <input wire:model="filterNilai" type="text" class="form-control @error('filterNilai') is-invalid @enderror"
                        placeholder="{{ __('messages.dashboard_filter_value_placeholder') }}">
                  @error('filterNilai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.card_layout') }} <span class="text-danger">*</span></label>
                  <select wire:model="layoutKolom" class="form-select @error('layoutKolom') is-invalid @enderror">
                    @foreach ($opsiLayout as $value => $label)
                      <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  @error('layoutKolom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.sneat_color') }} <span class="text-danger">*</span></label>
                  <select wire:model="warna" x-ref="widgetColorInput" x-on:change="render()" class="form-select @error('warna') is-invalid @enderror">
                    @foreach ($opsiWarna as $value => $label)
                      <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  @error('warna') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.icon_boxicons') }}</label>
                  <input wire:model="icon" x-ref="widgetIconInput" x-on:input.debounce.150ms="render()" type="text" class="form-control @error('icon') is-invalid @enderror" placeholder="{{ __('messages.dashboard_icon_placeholder') }}">
                  @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <!-- KPI & Perbandingan Periode -->
              <div class="row mt-4 pt-3 border-top">
                <div class="col-12 mb-2">
                  <h6 class="text-muted fw-semibold text-uppercase" style="font-size:.75rem;letter-spacing:.05em">
                    KPI & {{ __('messages.widget_compare_period') }}
                  </h6>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.widget_kpi_target') }}</label>
                  <input wire:model.number="kpiTarget" type="number" min="1" max="999999"
                         class="form-control @error('kpiTarget') is-invalid @enderror"
                         placeholder="{{ __('messages.dashboard_optional') }}">
                  @error('kpiTarget') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">{{ __('messages.widget_compare_period') }}</label>
                  <select wire:model="bandingkanDengan" class="form-select @error('bandingkanDengan') is-invalid @enderror">
                    <option value="">— {{ __('messages.no_filter') }} —</option>
                    <option value="hari_sebelumnya">{{ __('messages.widget_compare_previous_day') }}</option>
                    <option value="minggu_lalu">{{ __('messages.widget_compare_previous_week') }}</option>
                    <option value="bulan_lalu">{{ __('messages.widget_compare_previous_month') }}</option>
                    <option value="tahun_lalu">{{ __('messages.widget_compare_previous_year') }}</option>
                  </select>
                  @error('bandingkanDengan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <div class="form-check">
                    <input wire:model="tampilkanProgressBar" type="checkbox" class="form-check-input" id="progressBarCheck">
                    <label class="form-check-label" for="progressBarCheck">{{ __('messages.widget_show_progress_bar') }}</label>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <div class="form-check">
                    <input wire:model="bandingkanPeriode" type="checkbox" class="form-check-input" id="bandingkanCheck">
                    <label class="form-check-label" for="bandingkanCheck">{{ __('messages.widget_compare_period') }}</label>
                  </div>
                </div>
              </div>

              @if ($kpiTarget || $tampilkanProgressBar)
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">{{ __('messages.widget_color_threshold_green') }}</label>
                    <div class="input-group">
                      <span class="input-group-text p-1">
                        <input wire:model="warnaThresholdHijau" type="color"
                               class="form-control form-control-color border-0 p-0"
                               style="width:32px;height:32px;cursor:pointer">
                      </span>
                      <input type="text" class="form-control" value="#{{ $warnaThresholdHijau }}" readonly>
                    </div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">{{ __('messages.widget_color_threshold_yellow') }}</label>
                    <div class="input-group">
                      <span class="input-group-text p-1">
                        <input wire:model="warnaThresholdKuning" type="color"
                               class="form-control form-control-color border-0 p-0"
                               style="width:32px;height:32px;cursor:pointer">
                      </span>
                      <input type="text" class="form-control" value="#{{ $warnaThresholdKuning }}" readonly>
                    </div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">{{ __('messages.widget_color_threshold_red') }}</label>
                    <div class="input-group">
                      <span class="input-group-text p-1">
                        <input wire:model="warnaThresholdMerah" type="color"
                               class="form-control form-control-color border-0 p-0"
                               style="width:32px;height:32px;cursor:pointer">
                      </span>
                      <input type="text" class="form-control" value="#{{ $warnaThresholdMerah }}" readonly>
                    </div>
                  </div>
                </div>
              @endif

              <div class="form-check mt-3">
                <input wire:model="isActive" type="checkbox" class="form-check-input" id="widgetAktifCheck">
                <label class="form-check-label" for="widgetAktifCheck">{{ __('messages.widget_active_displayed') }}</label>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModal',false)">{{ __('messages.cancel') }}</button>
              <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpan">
                <span class="d-flex align-items-center justify-content-center gap-2">
                  <span wire:loading.remove wire:target="simpan">{{ __('messages.save') }}</span>
                  <span wire:loading wire:target="simpan" style="display:none">
                    <span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.saving') }}
                  </span>
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
</div>
