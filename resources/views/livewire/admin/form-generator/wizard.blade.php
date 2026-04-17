<?php

use App\Services\FormGeneratorService;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('components.layouts.app')] class extends Component {
    use WithFileUploads;

    public int $step = 1;
    public string $namaModul = '';
    public string $slug = '';
    public string $namaMenu = '';
    public string $icon = 'bx bx-detail';
    public ?int $parentMenuId = null;
    public int $urutanMenu = 100;
    public array $levelIds = [];
    public string $tipeModul = 'master';
    public ?TemporaryUploadedFile $fileImport = null;
    public array $fields = [];

    public function mount(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/form-generator-wizard', 'dapat_lihat')) {
            abort(403);
        }
    }

    public function updatedNamaModul(string $value): void
    {
        $slug = \Illuminate\Support\Str::slug($value);

        if ($this->slug === '') {
            $this->slug = $slug;
        }

        if ($this->namaMenu === '') {
            $this->namaMenu = $value;
        }
    }

    public function analisaImport(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/form-generator-wizard', 'dapat_buat')) {
            abort(403);
        }

        $this->validate([
            'namaModul' => 'required|string|min:3|max:120',
            'slug' => 'required|string|min:3|max:120',
            'namaMenu' => 'required|string|min:3|max:120',
            'fileImport' => 'nullable|file|mimes:csv,txt|max:4096',
        ]);

        if ($this->fileImport) {
            $this->fields = app(FormGeneratorService::class)->inferensiCsv($this->fileImport->getRealPath());

            if (empty($this->fields)) {
                $this->addError('fileImport', 'File CSV tidak valid atau tidak memiliki header.');
                return;
            }
        } else {
            if (empty($this->fields)) {
                $this->tambahFieldAudit();
            }
        }

        $this->step = 2;
    }

    public function tambahField(): void
    {
        $newField = [
            'nama_field' => '',
            'label_field' => '',
            'tipe_data' => 'string',
            'tipe_input' => 'text',
            'opsi_pilihan' => '',
            'is_required' => false,
            'is_tampil_form' => true,
            'is_tampil_list' => true,
        ];

        // Cari index dari field audit untuk menyisipkan field baru di atasnya
        $auditIndex = -1;
        foreach ($this->fields as $index => $field) {
            if (in_array($field['nama_field'], ['created_by', 'updated_by'])) {
                $auditIndex = $index;
                break;
            }
        }

        if ($auditIndex !== -1) {
            array_splice($this->fields, $auditIndex, 0, [$newField]);
        } else {
            $this->fields[] = $newField;
        }
    }

    public function hapusField(int $index): void
    {
        unset($this->fields[$index]);
        $this->fields = array_values($this->fields);
    }

    public function hapusForm(int $id): void
    {
        if (! auth()->user()?->bisaMenu('/admin/form-generator-wizard', 'dapat_hapus')) {
            abort(403);
        }

        $generator = \App\Models\FormGenerator::find($id);
        if ($generator) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($generator) {
                // Hapus data entry terkait form ini
                $generator->dataEntri()->delete();

                // Hapus menu terkait
                \App\Models\Menu::where('url', $generator->menu_url)->delete();

                // Hapus field terkait
                $generator->fields()->delete();

                // Hapus form
                $generator->delete();
            });

            // Hapus cache menu seluruh user agar menu yang terhapus hilang dari sidebar
            \App\Models\User::pluck('id')->each(fn($idUser) => \Illuminate\Support\Facades\Cache::forget("menu_user_{$idUser}"));

            // Refresh komponen
            $this->redirect(route('admin.form-generator.wizard'));
        }
    }

    private function tambahFieldAudit(): void
    {
        $this->fields[] = [
            'nama_field' => 'created_by',
            'label_field' => 'Dibuat Oleh',
            'tipe_data' => 'integer',
            'tipe_input' => 'number',
            'opsi_pilihan' => '',
            'is_required' => false,
            'is_tampil_form' => false,
            'is_tampil_list' => false,
        ];
        $this->fields[] = [
            'nama_field' => 'updated_by',
            'label_field' => 'Diubah Oleh',
            'tipe_data' => 'integer',
            'tipe_input' => 'number',
            'opsi_pilihan' => '',
            'is_required' => false,
            'is_tampil_form' => false,
            'is_tampil_list' => false,
        ];
    }

    public function keReview(): void
    {
        if (empty($this->fields)) {
            $this->addError('fields', 'Field belum tersedia. Silakan analisa file terlebih dahulu.');
            return;
        }

        $this->validate([
            'namaModul' => 'required|string|min:3|max:120',
            'slug' => 'required|string|min:3|max:120',
            'namaMenu' => 'required|string|min:3|max:120',
            'fields.*.nama_field' => 'required|string|max:120',
            'fields.*.label_field' => 'required|string|max:120',
            'fields.*.tipe_data' => 'required|string|max:30',
            'fields.*.tipe_input' => 'required|string|max:40',
        ]);

        $this->step = 3;
    }

    public function kembaliKePemetaan(): void
    {
        $this->step = 2;
    }

    public function simpanKonfigurasi(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/form-generator-wizard', 'dapat_buat')) {
            abort(403);
        }

        $this->validate([
            'namaModul' => 'required|string|min:3|max:120',
            'slug' => 'required|string|min:3|max:120',
            'namaMenu' => 'required|string|min:3|max:120',
            'icon' => 'nullable|string|max:100',
            'parentMenuId' => 'nullable|integer|exists:m_menu,id',
            'urutanMenu' => 'required|integer|min:0|max:9999',
            'levelIds' => 'nullable|array',
            'levelIds.*' => 'integer|exists:m_level,id',
            'fields.*.nama_field' => 'required|string|max:120',
            'fields.*.label_field' => 'required|string|max:120',
            'fields.*.tipe_data' => 'required|string|max:30',
            'fields.*.tipe_input' => 'required|string|max:40',
        ]);

        $fieldsFinal = collect($this->fields)->map(function (array $field) {
            $opsiPilihan = $field['opsi_pilihan'] ?? [];

            if (is_string($opsiPilihan)) {
                $opsiPilihan = collect(explode(',', $opsiPilihan))
                    ->map(fn($item) => trim((string) $item))
                    ->filter()
                    ->values()
                    ->all();
            }

            return [
                'nama_field' => \Illuminate\Support\Str::slug((string) $field['nama_field'], '_'),
                'label_field' => (string) $field['label_field'],
                'tipe_data' => (string) $field['tipe_data'],
                'tipe_input' => (string) $field['tipe_input'],
                'opsi_pilihan' => is_array($opsiPilihan) ? $opsiPilihan : [],
                'is_required' => (bool) ($field['is_required'] ?? false),
                'is_tampil_form' => (bool) ($field['is_tampil_form'] ?? true),
                'is_tampil_list' => (bool) ($field['is_tampil_list'] ?? true),
            ];
        })->values()->all();

        $generator = app(FormGeneratorService::class)->simpanKonfigurasi([
            'nama_modul' => $this->namaModul,
            'slug' => $this->slug,
            'nama_menu' => $this->namaMenu,
            'icon' => $this->icon,
            'parent_menu_id' => $this->parentMenuId,
            'urutan_menu' => $this->urutanMenu,
            'level_ids' => $this->levelIds,
            'sumber_import' => 'csv',
            'tipe_modul' => $this->tipeModul,
            'fields' => $fieldsFinal,
        ], auth()->user());

        app(LogAktivitasService::class)->catatManual(
            'Form Generator Wizard',
            'Membuat/ubah konfigurasi form dinamis: ' . $generator->nama_modul,
            '/admin/form-generator-wizard',
            [
                'form_generator_id' => $generator->id,
                'slug' => $generator->slug,
                'jumlah_field' => count($fieldsFinal),
            ]
        );

        session()->flash('sukses', 'Konfigurasi berhasil disimpan. Form dapat diakses di: ' . $generator->menu_url);

        $this->reset([
            'step',
            'namaModul',
            'slug',
            'namaMenu',
            'icon',
            'parentMenuId',
            'urutanMenu',
            'levelIds',
            'tipeModul',
            'fileImport',
            'fields',
        ]);

        $this->step = 1;
        $this->icon = 'bx bx-detail';
        $this->urutanMenu = 100;
        $this->tipeModul = 'master';
    }

    public function with(): array
    {
        $service = app(FormGeneratorService::class);

        $prefixDinamis = \Illuminate\Support\Facades\Cache::rememberForever('prefix_form_dinamis', function () {
            if (\Illuminate\Support\Facades\Schema::hasTable('m_identitas')) {
                $singkatan = \Illuminate\Support\Facades\DB::table('m_identitas')->where('is_active', true)->value('singkatan_aplikasi');
                return \Illuminate\Support\Str::slug($singkatan ?: 'form-generator') ?: 'form-generator';
            }
            return 'form-generator';
        });

        return [
            'opsiInput' => $service->tipeInputTersedia(),
            'opsiParentMenu' => $service->parentMenuTersedia(),
            'opsiLevel' => $service->levelTersedia(),
            'prefixDinamis' => $prefixDinamis,
            'daftarForm' => \App\Models\FormGenerator::with('fields')->orderBy('id', 'desc')->get(),
        ];
    }
};
?>
@section('title', 'Form Generator Wizard')

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Form Generator Wizard</h4>
            <p class="text-muted mb-0">Import CSV, tentukan field, lalu sistem membuat menu + CRUD dinamis otomatis.</p>
        </div>
    </div>

    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $step === 1 ? 'bg-label-primary' : 'bg-label-secondary' }}">1. Import</span>
                <span class="badge {{ $step === 2 ? 'bg-label-primary' : 'bg-label-secondary' }}">2. Mapping Field</span>
                <span class="badge {{ $step === 3 ? 'bg-label-primary' : 'bg-label-secondary' }}">3. Review & Simpan</span>
            </div>
        </div>
    </div>

    @if ($step === 1)
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Step 1 - Import & Konfigurasi Menu</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Modul</label>
                        <input type="text" class="form-control @error('namaModul') is-invalid @enderror" wire:model.live="namaModul">
                        @error('namaModul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug Modul</label>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror" wire:model="slug">
                        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nama Menu</label>
                        <input type="text" class="form-control @error('namaMenu') is-invalid @enderror" wire:model="namaMenu">
                        @error('namaMenu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipe Modul</label>
                        <select class="form-select @error('tipeModul') is-invalid @enderror" wire:model="tipeModul">
                            <option value="master">Master Data</option>
                            <option value="transaksi">Transaksi</option>
                        </select>
                        @error('tipeModul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Icon Boxicons</label>
                        <input type="text" class="form-control @error('icon') is-invalid @enderror" wire:model="icon" placeholder="bx bx-detail">
                        @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Urutan Menu</label>
                        <input type="number" class="form-control @error('urutanMenu') is-invalid @enderror" wire:model="urutanMenu">
                        @error('urutanMenu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Parent Menu</label>
                        <select class="form-select @error('parentMenuId') is-invalid @enderror" wire:model="parentMenuId">
                            <option value="">(Root)</option>
                            @foreach ($opsiParentMenu as $id => $nama)
                                <option value="{{ $id }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                        @error('parentMenuId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Akses Level</label>
                        <div class="border rounded p-2" style="max-height:140px;overflow:auto">
                            @foreach ($opsiLevel as $id => $nama)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $id }}" id="lvl_{{ $id }}" wire:model="levelIds">
                                    <label class="form-check-label" for="lvl_{{ $id }}">{{ $nama }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text">Jika kosong, otomatis diberikan ke Superadmin.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">File CSV Sampel (Opsional)</label>
                        <input type="file" class="form-control @error('fileImport') is-invalid @enderror" wire:model="fileImport" accept=".csv,.txt">
                        <div class="form-text">Header CSV akan dipakai untuk menghasilkan field form dinamis. Kosongkan jika ingin membuat field secara manual.</div>
                        @error('fileImport') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary" wire:click="analisaImport" wire:loading.attr="disabled" wire:target="analisaImport,fileImport">
                    <span wire:loading.remove wire:target="analisaImport,fileImport"><i class="bx bx-right-arrow-alt me-1"></i>Lanjut Mapping</span>
                    <span wire:loading wire:target="analisaImport,fileImport" style="display:none"><span class="spinner-border spinner-border-sm me-1"></span>Memproses...</span>
                </button>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0"><i class="bx bx-list-ul me-2"></i>{{ __('messages.dynamic_form_list') }}</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('messages.module_name_col') }}</th>
                            <th>{{ __('messages.menu_url_col') }}</th>
                            <th>{{ __('messages.module_type_col') }}</th>
                            <th>{{ __('messages.total_fields_col') }}</th>
                            <th class="text-center">{{ __('messages.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftarForm as $index => $form)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $form->nama_modul }}</strong>
                                    <div class="small text-muted"><i class="{{ $form->icon }}"></i> Menu: {{ $form->nama_menu }}</div>
                                </td>
                                <td><a href="{{ $form->menu_url }}" target="_blank" class="text-primary">{{ $form->menu_url }}</a></td>
                                <td>
                                    <span class="badge {{ $form->tipe_modul === 'master' ? 'bg-label-primary' : 'bg-label-warning' }}">
                                        {{ ucfirst($form->tipe_modul) }}
                                    </span>
                                </td>
                                <td>{{ $form->fields->count() }} Field</td>
                                <td class="text-center">
                                    @if(auth()->user()?->bisaMenu('/admin/form-generator-wizard', 'dapat_hapus'))
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            @click="Swal.fire({
                                                title: '{{ __('messages.confirm_delete') }}',
                                                text: '{{ __('messages.confirm_delete_dynamic_form') }}',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonText: '{{ __('messages.yes_delete') }}',
                                                cancelButtonText: '{{ __('messages.cancel') }}',
                                            }).then(r => r.isConfirmed && $wire.hapusForm({{ $form->id }}))">
                                        <i class="bx bx-trash me-1"></i>{{ __('messages.delete') }}
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">{{ __('messages.no_dynamic_form_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($step === 2)
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Step 2 - Mapping Input Berdasarkan Field</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <button class="btn btn-sm btn-success" wire:click="tambahField"><i class="bx bx-plus me-1"></i>Tambah Field Baru</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Field</th>
                                <th>Label</th>
                                <th>Tipe Data</th>
                                <th>Tipe Input</th>
                                <th>Opsi Select (comma)</th>
                                <th class="text-center">Wajib</th>
                                <th class="text-center">Form</th>
                                <th class="text-center">List</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fields as $i => $field)
                                <tr>
                                    <td><input type="text" class="form-control form-control-sm" wire:model="fields.{{ $i }}.nama_field"></td>
                                    <td><input type="text" class="form-control form-control-sm" wire:model="fields.{{ $i }}.label_field"></td>
                                    <td>
                                        <select class="form-select form-select-sm" wire:model="fields.{{ $i }}.tipe_data">
                                            <option value="string">string</option>
                                            <option value="text">text</option>
                                            <option value="integer">integer</option>
                                            <option value="decimal">decimal</option>
                                            <option value="boolean">boolean</option>
                                            <option value="date">date</option>
                                            <option value="datetime">datetime</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" wire:model="fields.{{ $i }}.tipe_input">
                                            @foreach ($opsiInput as $inputKey => $inputLabel)
                                                <option value="{{ $inputKey }}">{{ $inputLabel }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm" wire:model="fields.{{ $i }}.opsi_pilihan" placeholder="contoh: Jakarta,Bandung,Surabaya"></td>
                                    <td class="text-center"><input type="checkbox" class="form-check-input" wire:model="fields.{{ $i }}.is_required"></td>
                                    <td class="text-center"><input type="checkbox" class="form-check-input" wire:model="fields.{{ $i }}.is_tampil_form"></td>
                                    <td class="text-center"><input type="checkbox" class="form-check-input" wire:model="fields.{{ $i }}.is_tampil_list"></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-icon btn-danger" wire:click="hapusField({{ $i }})"><i class="bx bx-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button class="btn btn-outline-secondary" wire:click="$set('step', 1)">Kembali</button>
                <button class="btn btn-primary" wire:click="keReview">Lanjut Review</button>
            </div>
        </div>
    @endif

    @if ($step === 3)
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Step 3 - Review</h5></div>
            <div class="card-body">
                <p class="mb-1"><strong>Modul:</strong> {{ $namaModul }}</p>
                <p class="mb-1"><strong>Tipe Modul:</strong> <span class="badge {{ $tipeModul === 'master' ? 'bg-label-primary' : 'bg-label-warning' }}">{{ $tipeModul === 'master' ? 'Master Data' : 'Transaksi' }}</span></p>
                <p class="mb-1"><strong>Slug:</strong> {{ $slug }}</p>
                <p class="mb-1"><strong>Menu:</strong> {{ $namaMenu }} ({{ $icon }})</p>
                <p class="mb-3"><strong>URL Runtime:</strong> /admin/{{ $prefixDinamis }}/{{ \Illuminate\Support\Str::slug($slug) }}</p>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Field</th>
                                <th>Label</th>
                                <th>Tipe Input</th>
                                <th>Wajib</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fields as $i => $field)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $field['nama_field'] }}</td>
                                    <td>{{ $field['label_field'] }}</td>
                                    <td>{{ $field['tipe_input'] }}</td>
                                    <td>{{ !empty($field['is_required']) ? 'Ya' : 'Tidak' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button class="btn btn-outline-secondary" wire:click="kembaliKePemetaan">Kembali Mapping</button>
                <button class="btn btn-primary" wire:click="simpanKonfigurasi" wire:loading.attr="disabled" wire:target="simpanKonfigurasi">
                    <span wire:loading.remove wire:target="simpanKonfigurasi"><i class="bx bx-save me-1"></i>Simpan Generator</span>
                    <span wire:loading wire:target="simpanKonfigurasi" style="display:none"><span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...</span>
                </button>
            </div>
        </div>
    @endif
</div>
