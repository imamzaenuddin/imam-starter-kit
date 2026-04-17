<?php

use App\Models\ChatAiSumber;
use App\Models\Level;
use App\Services\ChatAiAnalisisService;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';

    public string $namaForm = '';
    public string $sumberDataForm = 'users';
    public string $tipeDataForm = 'statistik';
    public string $tipeQueryForm = 'count';
    public string $kolomAgregasiForm = '';
    public array $kolomTampilForm = [];
    public string $filterKolomForm = '';
    public string $filterOperatorForm = '=';
    public string $filterNilaiForm = '';
    public int $batasDataForm = 10;
    public int $urutanForm = 0;
    public bool $isDataPersonalForm = false;
    public array $levelDiizinkanForm = [];
    public bool $isSuperadminPengguna = false;
    public bool $isActiveForm = true;

    public array $chatAiKonteks = [];

    public ?int $editId = null;
    public bool $showModal = false;

    public function mount(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pengaturan-chat-ai', 'dapat_lihat')) {
            abort(403);
        }

        $this->isSuperadminPengguna = (bool) auth()->user()?->isSuperadmin();

        // Load konteks dari database
        $service = app(ChatAiAnalisisService::class);
        $konteksDiaktifkan = $service->konteksDiaktifkan();
        $this->chatAiKonteks = $konteksDiaktifkan;
    }

    public function buka(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pengaturan-chat-ai', 'dapat_buat')) {
            abort(403);
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pengaturan-chat-ai', 'dapat_ubah')) {
            abort(403);
        }

        $item = ChatAiSumber::query()->findOrFail($id);

        $this->editId = $item->id;
        $this->namaForm = $item->nama;
        $this->sumberDataForm = $item->sumber_data;
        $this->tipeDataForm = $item->tipe_data;
        $this->tipeQueryForm = $item->tipe_query;
        $this->kolomAgregasiForm = (string) ($item->kolom_agregasi ?? '');
        $this->kolomTampilForm = is_array($item->kolom_tampil) ? $item->kolom_tampil : [];
        $this->filterKolomForm = (string) ($item->filter_kolom ?? '');
        $this->filterOperatorForm = (string) ($item->filter_operator ?: '=');
        $this->filterNilaiForm = (string) ($item->filter_nilai ?? '');
        $this->batasDataForm = max(1, min(50, (int) $item->batas_data));
        $this->urutanForm = (int) $item->urutan;
        $this->isDataPersonalForm = (bool) $item->is_data_personal;
        $this->levelDiizinkanForm = $item->levels()->pluck('m_level.id')->map(fn ($id) => (int) $id)->all();
        $this->isActiveForm = (bool) $item->is_active;

        if ($this->sumberDataForm === 'users' && ! $this->isSuperadminPengguna) {
            $this->kolomTampilForm = array_values(array_diff($this->kolomTampilForm, ['email']));
        }

        $this->showModal = true;
    }

    public function updatedSumberDataForm(): void
    {
        $this->kolomAgregasiForm = '';
        $this->kolomTampilForm = [];
        $this->filterKolomForm = '';

        if ($this->sumberDataForm === 'users' && ! $this->isSuperadminPengguna) {
            $this->kolomTampilForm = array_values(array_diff($this->kolomTampilForm, ['email']));
        }
    }

    public function updatedKolomTampilForm(): void
    {
        if ($this->sumberDataForm === 'users' && ! $this->isSuperadminPengguna) {
            $this->kolomTampilForm = array_values(array_diff($this->kolomTampilForm, ['email']));
        }
    }

    public function updatedTipeDataForm(): void
    {
        if ($this->tipeDataForm === 'daftar') {
            $this->tipeQueryForm = 'count';
            $this->kolomAgregasiForm = '';
        }
    }

    public function simpan(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pengaturan-chat-ai', $this->editId ? 'dapat_ubah' : 'dapat_buat')) {
            abort(403);
        }

        $service = app(ChatAiAnalisisService::class);
        $sumberTersedia = array_keys($service->sumberDataTersedia());
        $tipeDataTersedia = array_keys($service->tipeDataTersedia());
        $tipeQueryTersedia = array_keys($service->tipeQueryTersedia());
        $operatorTersedia = array_keys($service->operatorFilterTersedia());
        $kolomSumber = array_keys($service->kolomSumberTersedia($this->sumberDataForm));
        $kolomNumerik = array_keys($service->kolomNumerikSumberTersedia($this->sumberDataForm));
        $levelIds = Level::query()->pluck('id')->map(fn ($id) => (string) $id)->all();

        $data = $this->validate([
            'namaForm' => 'required|string|max:100',
            'sumberDataForm' => ['required', 'string', 'in:' . implode(',', $sumberTersedia)],
            'tipeDataForm' => ['required', 'string', 'in:' . implode(',', $tipeDataTersedia)],
            'tipeQueryForm' => ['required', 'string', 'in:' . implode(',', $tipeQueryTersedia)],
            'kolomAgregasiForm' => 'nullable|string|max:100',
            'kolomTampilForm' => 'nullable|array',
            'kolomTampilForm.*' => ['string', 'in:' . implode(',', $kolomSumber)],
            'filterKolomForm' => 'nullable|string|max:100',
            'filterOperatorForm' => ['nullable', 'string', 'in:' . implode(',', $operatorTersedia)],
            'filterNilaiForm' => 'nullable|string|max:255',
            'batasDataForm' => 'required|integer|min:1|max:50',
            'urutanForm' => 'required|integer|min:0|max:9999',
            'isDataPersonalForm' => 'boolean',
            'levelDiizinkanForm' => 'nullable|array',
            'levelDiizinkanForm.*' => ['integer', 'in:' . implode(',', $levelIds)],
            'isActiveForm' => 'boolean',
        ]);

        if ($data['tipeDataForm'] === 'statistik' && in_array($data['tipeQueryForm'], ['sum', 'avg', 'min', 'max'], true)) {
            if (! in_array($data['kolomAgregasiForm'], $kolomNumerik, true)) {
                $this->addError('kolomAgregasiForm', __('messages.pengaturan_chat_ai_error_kolom_agregasi'));

                return;
            }
        }

        if ($data['sumberDataForm'] === 'users' && ! $this->isSuperadminPengguna) {
            $data['kolomTampilForm'] = array_values(array_diff($data['kolomTampilForm'] ?? [], ['email']));
        }

        $payload = [
            'nama' => $data['namaForm'],
            'sumber_data' => $data['sumberDataForm'],
            'tipe_data' => $data['tipeDataForm'],
            'tipe_query' => $data['tipeQueryForm'],
            'kolom_agregasi' => ($data['tipeDataForm'] === 'statistik' && in_array($data['tipeQueryForm'], ['sum', 'avg', 'min', 'max'], true))
                ? ($data['kolomAgregasiForm'] ?: null)
                : null,
            'kolom_tampil' => $data['tipeDataForm'] === 'daftar' ? ($data['kolomTampilForm'] ?: null) : null,
            'filter_kolom' => $data['filterKolomForm'] ?: null,
            'filter_operator' => $data['filterKolomForm'] ? ($data['filterOperatorForm'] ?: '=') : null,
            'filter_nilai' => $data['filterKolomForm'] ? ($data['filterNilaiForm'] ?: null) : null,
            'batas_data' => $data['batasDataForm'],
            'is_data_personal' => $data['isDataPersonalForm'],
            'urutan' => $data['urutanForm'],
            'is_active' => $data['isActiveForm'],
        ];

        if ($this->editId) {
            $item = ChatAiSumber::query()->findOrFail($this->editId);
            $item->update($payload);
            $item->levels()->sync(array_map('intval', $data['levelDiizinkanForm'] ?? []));

            app(LogAktivitasService::class)->catatManual(
                __('messages.pengaturan_chat_ai_log_modul'),
                __('messages.pengaturan_chat_ai_log_ubah', ['nama' => $item->nama]),
                '/admin/pengaturan-chat-ai',
                ['chat_ai_sumber_id' => $item->id]
            );
        } else {
            $item = ChatAiSumber::query()->create($payload);
            $item->levels()->sync(array_map('intval', $data['levelDiizinkanForm'] ?? []));

            app(LogAktivitasService::class)->catatManual(
                __('messages.pengaturan_chat_ai_log_modul'),
                __('messages.pengaturan_chat_ai_log_tambah', ['nama' => $item->nama]),
                '/admin/pengaturan-chat-ai',
                ['chat_ai_sumber_id' => $item->id]
            );
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('sukses', __('messages.pengaturan_chat_ai_sukses_simpan'));
    }

    public function hapus(int $id): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pengaturan-chat-ai', 'dapat_hapus')) {
            abort(403);
        }

        $item = ChatAiSumber::query()->findOrFail($id);

        app(LogAktivitasService::class)->catatManual(
            __('messages.pengaturan_chat_ai_log_modul'),
            __('messages.pengaturan_chat_ai_log_hapus', ['nama' => $item->nama]),
            '/admin/pengaturan-chat-ai',
            ['chat_ai_sumber_id' => $item->id]
        );

        $item->delete();
        $this->resetPage();

        session()->flash('sukses', __('messages.pengaturan_chat_ai_sukses_hapus'));
    }

    public function simpanKonteks(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/pengaturan-chat-ai', 'dapat_ubah')) {
            abort(403);
        }

        $service = app(ChatAiAnalisisService::class);
        $whitelist = array_keys($service->konteksAktifTersedia());

        $data = $this->validate([
            'chatAiKonteks' => 'nullable|array',
            'chatAiKonteks.*' => ['string', 'in:' . implode(',', $whitelist)],
        ]);

        $pengaturanService = app(\App\Services\PengaturanAplikasiService::class);
        $konfigAktif = $pengaturanService->konfigurasiAktif();

        $pengaturanService->simpan(array_merge(
            $konfigAktif,
            ['chat_ai_konteks' => $data['chatAiKonteks'] ?? []],
        ));

        session()->flash('sukses', __('messages.pengaturan_chat_ai_konteks_sukses_simpan'));
    }

    public function with(): array
    {
        $service = app(ChatAiAnalisisService::class);

        return [
            'daftarSumber' => ChatAiSumber::query()
                ->when($this->search, fn ($query) => $query->where('nama', 'like', '%' . $this->search . '%'))
                ->orderBy('urutan')
                ->orderBy('nama')
                ->paginate((int) config('app_runtime.pagination_default', 10)),
            'opsiSumberData' => $service->sumberDataTersedia(),
            'opsiTipeData' => $service->tipeDataTersedia(),
            'opsiTipeQuery' => $service->tipeQueryTersedia(),
            'opsiOperator' => $service->operatorFilterTersedia(),
            'opsiKolomSumber' => $service->kolomSumberTersedia($this->sumberDataForm),
            'opsiKolomNumerik' => $service->kolomNumerikSumberTersedia($this->sumberDataForm),
            'daftarLevel' => Level::query()->orderBy('nama_level')->get(['id', 'nama_level']),
            'chatAiKonteksTersedia' => $service->konteksAktifTersedia(),
        ];
    }

    private function resetForm(): void
    {
        $this->reset([
            'namaForm',
            'kolomAgregasiForm',
            'kolomTampilForm',
            'filterKolomForm',
            'filterNilaiForm',
            'levelDiizinkanForm',
            'editId',
        ]);

        $this->sumberDataForm = 'users';
        $this->tipeDataForm = 'statistik';
        $this->tipeQueryForm = 'count';
        $this->filterOperatorForm = '=';
        $this->batasDataForm = 10;
        $this->isDataPersonalForm = false;
        $this->urutanForm = 0;
        $this->isActiveForm = true;
    }
};
?>
@section('title', __('messages.pengaturan_chat_ai_halaman_title'))

<div>
    <!-- Konteks Chat AI Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="mb-3">
                <h5 class="card-title mb-1">{{ __('messages.pengaturan_chat_ai_title') }}</h5>
                <p class="text-muted small mb-3">{{ __('messages.pengaturan_chat_ai_subheading') }}</p>
            </div>

            <div class="row g-2">
                @foreach ($chatAiKonteksTersedia as $kunci => $labelKonteks)
                    <div class="col-md-4 col-sm-6">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                id="chatai_{{ $kunci }}"
                                class="form-check-input"
                                value="{{ $kunci }}"
                                wire:model="chatAiKonteks"
                            >
                            <label class="form-check-label" for="chatai_{{ $kunci }}">
                                {{ $labelKonteks }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="form-text mt-2">{{ __('messages.pengaturan_chat_ai_konteks_hint') }}</div>

            <div class="mt-3">
                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    wire:click="simpanKonteks"
                    wire:loading.attr="disabled"
                    wire:target="simpanKonteks"
                >
                    <span wire:loading.remove wire:target="simpanKonteks">
                        <i class="bx bx-check me-1"></i>{{ __('messages.simpan') }}
                    </span>
                    <span wire:loading wire:target="simpanKonteks" style="display:none">
                        <span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.menyimpan') }}
                    </span>
                </button>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <!-- Sumber Data Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ __('messages.pengaturan_chat_ai_heading') }}</h4>
            <p class="text-muted mb-0">{{ __('messages.pengaturan_chat_ai_form_sumber') }}</p>
        </div>
        <button class="btn btn-primary" wire:click="buka">
            <i class="bx bx-plus me-1"></i>{{ __('messages.pengaturan_chat_ai_tambah_btn') }}
        </button>
    </div>

    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body py-3">
            <input
                wire:model.live.debounce.300ms="search"
                type="search"
                class="form-control"
                placeholder="{{ __('messages.pengaturan_chat_ai_cari_placeholder') }}"
            >
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.pengaturan_chat_ai_tabel_nama') }}</th>
                        <th>{{ __('messages.pengaturan_chat_ai_tabel_sumber') }}</th>
                        <th>{{ __('messages.pengaturan_chat_ai_tabel_tipe') }}</th>
                        <th>{{ __('messages.pengaturan_chat_ai_tabel_filter') }}</th>
                        <th class="text-center">{{ __('messages.pengaturan_chat_ai_tabel_batas') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th class="text-center">{{ __('messages.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarSumber as $item)
                        <tr>
                            <td>{{ $daftarSumber->firstItem() + $loop->index }}</td>
                            <td class="fw-semibold">{{ $item->nama }}</td>
                            <td>{{ $opsiSumberData[$item->sumber_data] ?? $item->sumber_data }}</td>
                            <td>
                                <span class="badge bg-label-primary">{{ $item->tipe_data }}</span>
                                <span class="badge bg-label-info">{{ $item->tipe_query }}</span>
                            </td>
                            <td class="text-muted">
                                @if ($item->filter_kolom)
                                    {{ $item->filter_kolom }} {{ $item->filter_operator }} {{ $item->filter_nilai }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">{{ $item->batas_data }}</td>
                            <td>
                                @if ($item->is_active)
                                    <span class="badge bg-label-success">{{ __('messages.active') }}</span>
                                @else
                                    <span class="badge bg-label-secondary">{{ __('messages.inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit({{ $item->id }})" title="{{ __('messages.edit') }}">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                <button
                                    class="btn btn-sm btn-icon btn-text-danger"
                                    @click="Swal.fire({
                                        title: '{{ __('messages.confirm_delete') }}',
                                        text: '{{ __('messages.pengaturan_chat_ai_konfirmasi_hapus', ['nama' => addslashes($item->nama)]) }}',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: '{{ __('messages.yes_delete') }}',
                                        cancelButtonText: '{{ __('messages.cancel') }}',
                                    }).then(r => r.isConfirmed && $wire.hapus({{ $item->id }}))"
                                    title="{{ __('messages.delete') }}"
                                >
                                    <i class="bx bx-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">{{ __('messages.pengaturan_chat_ai_tidak_ada_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $daftarSumber->links() }}</div>
    </div>

    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.45)">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editId ? __('messages.pengaturan_chat_ai_edit_title') : __('messages.pengaturan_chat_ai_tambah_title') }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="simpan">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.pengaturan_chat_ai_form_nama') }}</label>
                                    <input wire:model="namaForm" type="text" class="form-control @error('namaForm') is-invalid @enderror">
                                    @error('namaForm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.pengaturan_chat_ai_form_sumber') }}</label>
                                    <select wire:model.live="sumberDataForm" class="form-select @error('sumberDataForm') is-invalid @enderror">
                                        @foreach ($opsiSumberData as $k => $v)
                                            <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                    @error('sumberDataForm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.pengaturan_chat_ai_form_tipe_data') }}</label>
                                    <select wire:model.live="tipeDataForm" class="form-select @error('tipeDataForm') is-invalid @enderror">
                                        @foreach ($opsiTipeData as $k => $v)
                                            <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                    @error('tipeDataForm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('messages.pengaturan_chat_ai_form_tipe_query') }}</label>
                                    <select wire:model="tipeQueryForm" class="form-select @error('tipeQueryForm') is-invalid @enderror">
                                        @foreach ($opsiTipeQuery as $k => $v)
                                            <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                    @error('tipeQueryForm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                @if ($tipeDataForm === 'statistik' && in_array($tipeQueryForm, ['sum', 'avg', 'min', 'max'], true))
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('messages.pengaturan_chat_ai_form_kolom_agregasi') }}</label>
                                        <select wire:model="kolomAgregasiForm" class="form-select @error('kolomAgregasiForm') is-invalid @enderror">
                                            <option value="">{{ __('messages.choose') }}</option>
                                            @foreach ($opsiKolomNumerik as $k => $v)
                                                <option value="{{ $k }}">{{ $v }} ({{ $k }})</option>
                                            @endforeach
                                        </select>
                                        @error('kolomAgregasiForm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                @endif

                                @if ($tipeDataForm === 'daftar')
                                    <div class="col-12">
                                        <label class="form-label">{{ __('messages.pengaturan_chat_ai_form_kolom_tampil') }}</label>
                                        <div class="row g-2">
                                            @foreach ($opsiKolomSumber as $k => $v)
                                                @php
                                                    $emailNonaktifUntukNonSuperadmin = $sumberDataForm === 'users' && ! $isSuperadminPengguna && $k === 'email';
                                                @endphp
                                                <div class="col-md-4 col-sm-6">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" id="kolom_{{ $k }}" value="{{ $k }}" wire:model="kolomTampilForm" @disabled($emailNonaktifUntukNonSuperadmin)>
                                                        <label class="form-check-label" for="kolom_{{ $k }}">{{ $v }} ({{ $k }})</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if ($sumberDataForm === 'users' && ! $isSuperadminPengguna)
                                            <div class="form-text mt-1">{{ __('messages.pengaturan_chat_ai_email_terkunci_non_superadmin') }}</div>
                                        @endif
                                    </div>
                                @endif

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('messages.pengaturan_chat_ai_form_filter_kolom') }}</label>
                                    <select wire:model="filterKolomForm" class="form-select @error('filterKolomForm') is-invalid @enderror">
                                        <option value="">{{ __('messages.none') }}</option>
                                        @foreach ($opsiKolomSumber as $k => $v)
                                            <option value="{{ $k }}">{{ $v }} ({{ $k }})</option>
                                        @endforeach
                                    </select>
                                    @error('filterKolomForm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('messages.pengaturan_chat_ai_form_filter_operator') }}</label>
                                    <select wire:model="filterOperatorForm" class="form-select @error('filterOperatorForm') is-invalid @enderror">
                                        @foreach ($opsiOperator as $k => $v)
                                            <option value="{{ $k }}">{{ $v }} ({{ $k }})</option>
                                        @endforeach
                                    </select>
                                    @error('filterOperatorForm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('messages.pengaturan_chat_ai_form_filter_nilai') }}</label>
                                    <input wire:model="filterNilaiForm" type="text" class="form-control @error('filterNilaiForm') is-invalid @enderror" placeholder="contoh: 1 / hari_ini / 7_hari">
                                    @error('filterNilaiForm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('messages.pengaturan_chat_ai_form_batas_data') }}</label>
                                    <input wire:model="batasDataForm" type="number" min="1" max="50" class="form-control @error('batasDataForm') is-invalid @enderror">
                                    @error('batasDataForm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('messages.pengaturan_chat_ai_form_urutan') }}</label>
                                    <input wire:model="urutanForm" type="number" min="0" max="9999" class="form-control @error('urutanForm') is-invalid @enderror">
                                    @error('urutanForm') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">{{ __('messages.pengaturan_chat_ai_form_level_diizinkan') }}</label>
                                    <div class="row g-2">
                                        @foreach ($daftarLevel as $level)
                                            <div class="col-md-4 col-sm-6">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="level_{{ $level->id }}" value="{{ $level->id }}" wire:model="levelDiizinkanForm">
                                                    <label class="form-check-label" for="level_{{ $level->id }}">{{ $level->nama_level }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-text">{{ __('messages.pengaturan_chat_ai_form_level_diizinkan_hint') }}</div>
                                    @error('levelDiizinkanForm') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <input wire:model="isActiveForm" type="checkbox" class="form-check-input" id="isActiveForm">
                                        <label class="form-check-label" for="isActiveForm">{{ __('messages.pengaturan_chat_ai_form_aktif') }}</label>
                                    </div>
                                </div>

                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <input wire:model="isDataPersonalForm" type="checkbox" class="form-check-input" id="isDataPersonalForm">
                                        <label class="form-check-label" for="isDataPersonalForm">{{ __('messages.pengaturan_chat_ai_form_data_personal') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModal', false)">{{ __('messages.cancel') }}</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpan">
                                <span class="d-flex align-items-center gap-2">
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
