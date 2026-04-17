<?php

namespace App\Services;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LogAktivitasService
{
    public function catatManual(string $modul, string $aktivitas, ?string $url = null, array $metadata = []): void
    {
        $user = request()->user();

        if (! $user) {
            return;
        }

        LogAktivitas::create([
            'user_id' => $user->id,
            'modul' => $modul,
            'aktivitas' => $aktivitas,
            'url' => $url ?: '/'.ltrim((string) request()->path(), '/'),
            'metode' => request()->method(),
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 1000, ''),
            'metadata' => $metadata,
        ]);
    }

    public function catatDariRequest(Request $request, SymfonyResponse $response, mixed $userSebelum = null): void
    {
        $user = $userSebelum ?: $request->user();

        if (! $user || ! $this->perluDicatat($request, $response)) {
            return;
        }

        $routeName = $request->route()?->getName();
        $path = '/'.ltrim($request->path(), '/');

        LogAktivitas::create([
            'user_id' => $user->id,
            'modul' => $this->modulDariRequest($request, $routeName),
            'aktivitas' => $this->aktivitasDariRequest($request, $routeName, $path),
            'url' => $path === '/' ? '/' : $path,
            'metode' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'metadata' => [
                'route_name' => $routeName,
                'status_code' => $response->getStatusCode(),
                'query' => $request->query(),
            ],
        ]);
    }

    private function perluDicatat(Request $request, SymfonyResponse $response): bool
    {
        if (! $request->route()) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        if ($request->isMethod('HEAD')) {
            return false;
        }

        if ($request->expectsJson() || $request->ajax()) {
            return false;
        }

        if ($request->is('livewire/*', 'up', '_debugbar/*')) {
            return false;
        }

        return true;
    }

    private function modulDariRequest(Request $request, ?string $routeName): string
    {
        if ($routeName) {
            $prefix = explode('.', $routeName)[0] ?? 'sistem';

            return Str::title(str_replace(['-', '_'], ' ', $prefix));
        }

        return Str::title(str_replace('-', ' ', $request->segment(1) ?: 'sistem'));
    }

    private function aktivitasDariRequest(Request $request, ?string $routeName, string $path): string
    {
        $target = $routeName
            ? Str::title(str_replace(['.', '-', '_'], ' ', $routeName))
            : Str::title(str_replace(['/', '-'], ' ', trim($path, '/')) ?: 'Beranda');

        return match ($request->method()) {
            'POST' => 'Menambahkan data pada '.$target,
            'PUT', 'PATCH' => 'Memperbarui data pada '.$target,
            'DELETE' => 'Menghapus data pada '.$target,
            default => 'Mengakses halaman '.$target,
        };
    }
}
