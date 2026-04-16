<?php

use App\Models\FormGenerator;
use App\Models\FormGeneratorData;
use App\Services\LogAktivitasService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $slug = '';
    public ?FormGenerator $generator = null;
    public array $formData = [];
    public ?int $editId = null;
    public string $search = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->muatGenerator();

        $url = '/admin/form-generator/' . $slug;
        if (! auth()->user()?->bisaMenu($url, 'dapat_lihat')) {
            abort(403);
        }

        foreach ($this->generator?->fields ?? [] as $field) {
            $this->formData[$field->nama_field] = '';
        }
    }

    public function simpan(): void
    {
        if (! $this->generator) {
            abort(404);
        }

        $url = '/admin/form-generator/' . $this->slug;
        if (! auth()->user()?->bisaMenu($url, $this->editId ? 'dapat_ubah' : 'dapat_buat')) {
            abort(403);
        }

        $this->resetErrorBag();

        $payload = [];
        foreach ($this->generator->fields as $field) {
            if (! $field->is_tampil_form) {
                continue;
            }

            $nilai = $this->formData[$field->nama_field] ?? null;

            if ($field->is_required && (($nilai === null) || (is_string($nilai) && trim($nilai) === ''))) {
                $this->addError('formData.' . $field->nama_field, 'Field ' . $field->label_field . ' wajib diisi.');
                continue;
            }

            if ($field->tipe_input === 'checkbox') {
                $nilai = (bool) $nilai;
            }

            if ($field->tipe_input === 'number' && $nilai !== '' && $nilai !== null) {
                $nilai = is_numeric((string) $nilai) ? (float) $nilai : null;
            }

            $payload[$field->nama_field] = $nilai;
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        if ($this->editId) {
            $item = FormGeneratorData::query()
                ->where('form_generator_id', $this->generator->id)
                ->findOrFail($this->editId);

            $item->update([
                'payload' => $payload,
                'updated_by' => auth()->id(),
            ]);
        } else {
            FormGeneratorData::query()->create([
                'form_generator_id' => $this->generator->id,
                'payload' => $payload,
                'is_active' => true,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        }

        app(LogAktivitasService::class)->catatManual(
            $this->generator->nama_modul,
            ($this->editId ? 'Ubah' : 'Tambah') . ' data form dinamis',
            '/admin/form-generator/' . $this->slug,
            [
                'form_generator_id' => $this->generator->id,
                'data_id' => $this->editId,
            ]
        );

        $this->resetForm();
        session()->flash('sukses', 'Data berhasil disimpan.');
    }

    public function edit(int $id): void
    {
        if (! $this->generator) {
            return;
        }

        $url = '/admin/form-generator/' . $this->slug;
        if (! auth()->user()?->bisaMenu($url, 'dapat_ubah')) {
            abort(403);
        }

        $item = FormGeneratorData::query()
            ->where('form_generator_id', $this->generator->id)
            ->findOrFail($id);

        $this->editId = $item->id;

        foreach ($this->generator->fields as $field) {
            $this->formData[$field->nama_field] = data_get($item->payload, $field->nama_field);
        }
    }

    public function batalEdit(): void
    {
        $this->resetForm();
    }

    public function hapus(int $id): void
    {
        if (! $this->generator) {
            return;
        }

        $url = '/admin/form-generator/' . $this->slug;
        if (! auth()->user()?->bisaMenu($url, 'dapat_hapus')) {
            abort(403);
        }

        $item = FormGeneratorData::query()
            ->where('form_generator_id', $this->generator->id)
            ->findOrFail($id);

        $item->delete();
        session()->flash('sukses', 'Data berhasil dihapus.');
    }

    public function with(): array
    {
        $kolomList = collect($this->generator?->fields ?? [])->where('is_tampil_list', true)->values();

        $query = FormGeneratorData::query()
            ->when($this->generator, fn($q) => $q->where('form_generator_id', $this->generator->id));

        if ($this->search !== '') {
            $search = strtolower($this->search);
            $query->whereRaw('LOWER(JSON_UNQUOTE(payload)) like ?', ['%' . $search . '%']);
        }

        return [
            'kolomList' => $kolomList,
            'daftarData' => $query->latest('id')->paginate((int) config('app_runtime.pagination_default', 10)),
        ];
    }

    private function muatGenerator(): void
    {
        $this->generator = FormGenerator::query()
            ->with(['fields' => fn($q) => $q->orderBy('urutan')])
            ->where('slug', $this->slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    private function resetForm(): void
    {
        $this->editId = null;
        foreach ($this->generator?->fields ?? [] as $field) {
            $this->formData[$field->nama_field] = $field->tipe_input === 'checkbox' ? false : '';
        }
    }
};
?>
@section('title', $generator?->nama_modul ?? 'Form Dinamis')

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ $generator?->nama_modul }}</h4>
            <p class="text-muted mb-0">CRUD dinamis berdasarkan konfigurasi wizard.</p>
        </div>
    </div>

    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">{{ $editId ? 'Ubah Data' : 'Tambah Data' }}</h5></div>
        <div class="card-body">
            <div class="row g-3">
                @foreach ($generator?->fields ?? [] as $field)
                    @if (!$field->is_tampil_form)
                        @continue
                    @endif
                    <div class="col-md-6">
                        <label class="form-label">{{ $field->label_field }}</label>

                        @if ($field->tipe_input === 'textarea')
                            <textarea class="form-control @error('formData.' . $field->nama_field) is-invalid @enderror" wire:model="formData.{{ $field->nama_field }}"></textarea>
                        @elseif ($field->tipe_input === 'select')
                            <select class="form-select @error('formData.' . $field->nama_field) is-invalid @enderror" wire:model="formData.{{ $field->nama_field }}">
                                <option value="">Pilih</option>
                                @foreach (($field->opsi_pilihan ?? []) as $opsi)
                                    <option value="{{ $opsi }}">{{ $opsi }}</option>
                                @endforeach
                            </select>
                        @elseif ($field->tipe_input === 'checkbox')
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" id="{{ $field->nama_field }}" wire:model="formData.{{ $field->nama_field }}">
                                <label class="form-check-label" for="{{ $field->nama_field }}">Ya</label>
                            </div>
                        @else
                            <input
                                type="{{ $field->tipe_input === 'datetime-local' ? 'datetime-local' : $field->tipe_input }}"
                                class="form-control @error('formData.' . $field->nama_field) is-invalid @enderror"
                                wire:model="formData.{{ $field->nama_field }}"
                            >
                        @endif

                        @error('formData.' . $field->nama_field) <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan">
                <span wire:loading.remove wire:target="simpan">{{ $editId ? 'Simpan Perubahan' : 'Simpan' }}</span>
                <span wire:loading wire:target="simpan" style="display:none"><span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...</span>
            </button>
            @if ($editId)
                <button class="btn btn-outline-secondary" wire:click="batalEdit">Batal</button>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0">Daftar Data</h5>
            <input type="search" class="form-control" style="max-width:300px" wire:model.live.debounce.300ms="search" placeholder="Cari data...">
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        @foreach ($kolomList as $field)
                            <th>{{ $field->label_field }}</th>
                        @endforeach
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarData as $item)
                        <tr>
                            <td>{{ $daftarData->firstItem() + $loop->index }}</td>
                            @foreach ($kolomList as $field)
                                <td>{{ data_get($item->payload, $field->nama_field, '-') }}</td>
                            @endforeach
                            <td class="text-center">
                                <button class="btn btn-sm btn-icon btn-text-primary" wire:click="edit({{ $item->id }})" title="Edit">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                <button class="btn btn-sm btn-icon btn-text-danger" wire:click="hapus({{ $item->id }})" wire:confirm="Hapus data ini?" title="Hapus">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + count($kolomList) }}" class="text-center py-4 text-muted">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $daftarData->links() }}</div>
    </div>
</div>
