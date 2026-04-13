<?php

namespace App\Services;

use App\Models\Bahasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

class BahasaService
{
    public function sumberBahasaTersedia(): array
    {
        $paths = [lang_path()];

        return collect($paths)
            ->filter(fn(string $path) => File::isDirectory($path))
            ->unique()
            ->values()
            ->all();
    }

    public function kodeDariFolder(): array
    {
        return collect($this->sumberBahasaTersedia())
            ->flatMap(function (string $path) {
                return collect(File::directories($path))
                    ->map(fn(string $dir) => basename($dir));
            })
            ->filter(fn(string $kode) => preg_match('/^[a-z]{2}([_-][A-Z]{2})?$/', $kode) === 1)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function sinkronkanDariFolder(): int
    {
        $kodeList = $this->kodeDariFolder();

        if (empty($kodeList)) {
            $kodeList = [config('app.locale', 'id')];
        }

        foreach ($kodeList as $index => $kode) {
            $nama = $this->namaLocale($kode);

            Bahasa::query()->updateOrCreate(
                ['kode' => $kode],
                [
                    'nama' => $nama,
                    'nama_native' => $nama,
                    'urutan' => $index + 1,
                    'is_active' => true,
                ]
            );
        }

        if (! Bahasa::query()->where('is_default', true)->exists()) {
            $kodeDefault = config('app.locale', 'id');
            $bahasa = Bahasa::query()->where('kode', $kodeDefault)->first() ?: Bahasa::query()->first();

            if ($bahasa) {
                Bahasa::query()->update(['is_default' => false]);
                $bahasa->update(['is_default' => true]);
            }
        }

        return count($kodeList);
    }

    public function localeUntukRequest(Request $request): string
    {
        $localeSesi = (string) $request->session()->get('locale', '');

        if ($localeSesi !== '' && $this->localeAktif($localeSesi)) {
            return $localeSesi;
        }

        $default = $this->localeDefault();

        return $this->localeAktif($default) ? $default : config('app.locale', 'id');
    }

    public function terapkanLocale(Request $request): void
    {
        App::setLocale($this->localeUntukRequest($request));
    }

    public function setLocaleSesi(Request $request, string $kode): bool
    {
        if (! $this->localeAktif($kode)) {
            return false;
        }

        $request->session()->put('locale', $kode);

        return true;
    }

    public function localeDefault(): string
    {
        return (string) (Bahasa::query()->where('is_default', true)->value('kode') ?: config('app.locale', 'id'));
    }

    public function localeAktif(string $kode): bool
    {
        return Bahasa::query()->where('kode', $kode)->where('is_active', true)->exists();
    }

    private function namaLocale(string $kode): string
    {
        return [
            'id' => 'Bahasa Indonesia',
            'en' => 'English',
            'ar' => 'Arabic',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'fr' => 'French',
            'de' => 'German',
            'es' => 'Spanish',
        ][$kode] ?? strtoupper($kode);
    }
}
