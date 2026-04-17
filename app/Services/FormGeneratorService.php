<?php

namespace App\Services;

use App\Models\FormGenerator;
use App\Models\Level;
use App\Models\Menu;
use App\Models\User;
use App\Services\MenuService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormGeneratorService
{
    public function inferensiCsv(string $lokasiFile): array
    {
        $handle = fopen($lokasiFile, 'r');

        if (! $handle) {
            return [];
        }

        $headerRaw = fgetcsv($handle);

        if (! $headerRaw) {
            fclose($handle);

            return [];
        }

        $header = array_map(fn($h) => trim((string) $h), $headerRaw);
        $samples = [];
        $maksimalBarisSampel = 30;

        while (($row = fgetcsv($handle)) !== false && count($samples) < $maksimalBarisSampel) {
            $samples[] = $row;
        }

        fclose($handle);

        $hasil = [];

        foreach ($header as $index => $namaKolom) {
            $namaField = $this->normalisasiNamaField($namaKolom ?: ('field_' . ($index + 1)));
            $sampleKolom = [];

            foreach ($samples as $sample) {
                $nilai = trim((string) ($sample[$index] ?? ''));
                if ($nilai !== '') {
                    $sampleKolom[] = $nilai;
                }
            }

            $tipeData = $this->deteksiTipeData($sampleKolom);

            $hasil[] = [
                'nama_field' => $namaField,
                'label_field' => Str::title(str_replace('_', ' ', $namaField)),
                'tipe_data' => $tipeData,
                'tipe_input' => $this->tipeInputDefault($tipeData),
                'opsi_pilihan' => [],
                'is_required' => false,
                'is_tampil_form' => true,
                'is_tampil_list' => true,
                'urutan' => $index + 1,
            ];
        }

        return $hasil;
    }

    public function tipeInputTersedia(): array
    {
        return [
            'text' => 'Text',
            'email' => 'Email',
            'number' => 'Angka',
            'textarea' => 'Textarea',
            'select' => 'Select Option',
            'checkbox' => 'Checkbox (Ya/Tidak)',
            'date' => 'Tanggal',
            'datetime-local' => 'Tanggal & Waktu',
        ];
    }

    public function parentMenuTersedia(): array
    {
        return Menu::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }

    public function levelTersedia(): array
    {
        return Level::query()
            ->where('is_active', true)
            ->orderBy('nama_level')
            ->pluck('nama_level', 'id')
            ->toArray();
    }

    public function simpanKonfigurasi(array $payload, User $aktor): FormGenerator
    {
        return DB::transaction(function () use ($payload, $aktor) {
            $slug = Str::slug((string) $payload['slug']);
            
            $prefixDinamis = \Illuminate\Support\Facades\Cache::rememberForever('prefix_form_dinamis', function () {
                if (\Illuminate\Support\Facades\Schema::hasTable('m_identitas')) {
                    $singkatan = \Illuminate\Support\Facades\DB::table('m_identitas')->where('is_active', true)->value('singkatan_aplikasi');
                    return \Illuminate\Support\Str::slug($singkatan ?: 'form-generator') ?: 'form-generator';
                }
                return 'form-generator';
            });

            $url = '/admin/' . $prefixDinamis . '/' . $slug;

            $generator = FormGenerator::query()->where('slug', $slug)->first();

            if (! $generator) {
                $generator = new FormGenerator();
                $generator->slug = $slug;
                $generator->created_by = $aktor->id;
            }

            $generator->fill([
                'nama_modul' => (string) $payload['nama_modul'],
                'nama_menu' => (string) $payload['nama_menu'],
                'menu_url' => $url,
                'icon' => (string) ($payload['icon'] ?: 'bx bx-detail'),
                'parent_menu_id' => $payload['parent_menu_id'] ? (int) $payload['parent_menu_id'] : null,
                'sumber_import' => (string) ($payload['sumber_import'] ?? 'csv'),
                'tipe_modul' => in_array($payload['tipe_modul'] ?? '', ['master', 'transaksi']) ? $payload['tipe_modul'] : 'master',
                'is_active' => true,
                'updated_by' => $aktor->id,
            ]);
            $generator->save();

            $generator->fields()->delete();

            foreach ($payload['fields'] as $index => $field) {
                $generator->fields()->create([
                    'nama_field' => (string) $field['nama_field'],
                    'label_field' => (string) $field['label_field'],
                    'tipe_data' => (string) $field['tipe_data'],
                    'tipe_input' => (string) $field['tipe_input'],
                    'opsi_pilihan' => $field['opsi_pilihan'] ?? [],
                    'is_required' => (bool) ($field['is_required'] ?? false),
                    'is_tampil_form' => (bool) ($field['is_tampil_form'] ?? true),
                    'is_tampil_list' => (bool) ($field['is_tampil_list'] ?? true),
                    'urutan' => $index + 1,
                ]);
            }

            $menu = Menu::query()->updateOrCreate(
                ['url' => $url],
                [
                    'nama' => (string) $payload['nama_menu'],
                    'icon' => (string) ($payload['icon'] ?: 'bx bx-detail'),
                    'parent_id' => $payload['parent_menu_id'] ? (int) $payload['parent_menu_id'] : null,
                    'urutan' => (int) ($payload['urutan_menu'] ?? 100),
                    'is_active' => true,
                ]
            );

            $levelDipilih = collect($payload['level_ids'] ?? [])->map(fn($id) => (int) $id)->filter()->values();

            if ($levelDipilih->isEmpty()) {
                $superadmin = Level::query()->whereRaw('LOWER(nama_level) = ?', ['superadmin'])->first();
                if ($superadmin) {
                    $levelDipilih = collect([$superadmin->id]);
                }
            }

            foreach ($levelDipilih as $levelId) {
                $level = Level::query()->find($levelId);
                if (! $level) {
                    continue;
                }

                $level->menus()->syncWithoutDetaching([
                    $menu->id => [
                        'dapat_lihat' => true,
                        'dapat_buat' => true,
                        'dapat_ubah' => true,
                        'dapat_hapus' => true,
                        'dapat_backup' => false,
                        'dapat_restore' => false,
                        'dapat_hapus_backup' => false,
                    ],
                ]);

                app(MenuService::class)->hapusCacheLevel($level->id);
            }

            return $generator->fresh(['fields']);
        });
    }

    private function normalisasiNamaField(string $nama): string
    {
        $slug = Str::slug($nama, '_');

        if ($slug === '') {
            $slug = 'field_' . Str::random(5);
        }

        if (is_numeric(substr($slug, 0, 1))) {
            $slug = 'f_' . $slug;
        }

        return strtolower($slug);
    }

    private function deteksiTipeData(array $samples): string
    {
        if (empty($samples)) {
            return 'string';
        }

        $semuaBoolean = collect($samples)->every(function ($val) {
            return in_array(strtolower((string) $val), ['1', '0', 'true', 'false', 'ya', 'tidak', 'yes', 'no'], true);
        });

        if ($semuaBoolean) {
            return 'boolean';
        }

        $semuaInteger = collect($samples)->every(fn($val) => preg_match('/^-?\d+$/', (string) $val) === 1);
        if ($semuaInteger) {
            return 'integer';
        }

        $semuaDecimal = collect($samples)->every(fn($val) => is_numeric((string) $val));
        if ($semuaDecimal) {
            return 'decimal';
        }

        $semuaDate = collect($samples)->every(fn($val) => strtotime((string) $val) !== false);
        if ($semuaDate) {
            return 'date';
        }

        $rataPanjang = collect($samples)->avg(fn($val) => mb_strlen((string) $val));
        if ($rataPanjang > 80) {
            return 'text';
        }

        return 'string';
    }

    private function tipeInputDefault(string $tipeData): string
    {
        return match ($tipeData) {
            'integer', 'decimal' => 'number',
            'boolean' => 'checkbox',
            'date' => 'date',
            'datetime' => 'datetime-local',
            'text' => 'textarea',
            default => 'text',
        };
    }
}
