<?php

use App\Services\ChatAiAnalisisService;
use App\Services\BahasaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
  return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
  ->middleware(['auth', 'verified'])
  ->name('dashboard');

Route::middleware(['auth'])->group(function () {
  Route::redirect('settings', 'settings/profile');

  Route::post('bahasa/ganti', function (Request $request, BahasaService $bahasaService) {
    $data = $request->validate([
      'kode' => 'required|string|max:10',
    ]);

    $bahasaService->setLocaleSesi($request, $data['kode']);

    return back();
  })->name('bahasa.ganti');

  Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
  Volt::route('settings/password', 'settings.password')->name('settings.password');

  Route::prefix('api/wilayah')->name('api.wilayah.')->group(function () {
    Route::get('provinsi', function () {
      $data = Cache::remember('wilayah.provinsi', now()->addDays(7), function () {
        $response = Http::timeout(15)->get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');

        if (! $response->successful()) {
          return [];
        }

        return collect($response->json())
          ->map(fn($item) => [
            'id' => (string) data_get($item, 'id'),
            'nama' => (string) data_get($item, 'name'),
          ])
          ->values()
          ->all();
      });

      return response()->json(['data' => $data]);
    })->name('provinsi');

    Route::get('kabupaten', function (Request $request) {
      $validated = $request->validate([
        'provinsi_id' => 'required|string|max:10',
      ]);

      $provinsiId = $validated['provinsi_id'];

      $data = Cache::remember("wilayah.kabupaten.{$provinsiId}", now()->addDays(7), function () use ($provinsiId) {
        $response = Http::timeout(15)->get("https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$provinsiId}.json");

        if (! $response->successful()) {
          return [];
        }

        return collect($response->json())
          ->map(fn($item) => [
            'id' => (string) data_get($item, 'id'),
            'nama' => (string) data_get($item, 'name'),
          ])
          ->values()
          ->all();
      });

      return response()->json(['data' => $data]);
    })->name('kabupaten');

    Route::get('kecamatan', function (Request $request) {
      $validated = $request->validate([
        'kabupaten_id' => 'required|string|max:10',
      ]);

      $kabupatenId = $validated['kabupaten_id'];

      $data = Cache::remember("wilayah.kecamatan.{$kabupatenId}", now()->addDays(7), function () use ($kabupatenId) {
        $response = Http::timeout(15)->get("https://www.emsifa.com/api-wilayah-indonesia/api/districts/{$kabupatenId}.json");

        if (! $response->successful()) {
          return [];
        }

        return collect($response->json())
          ->map(fn($item) => [
            'id' => (string) data_get($item, 'id'),
            'nama' => (string) data_get($item, 'name'),
          ])
          ->values()
          ->all();
      });

      return response()->json(['data' => $data]);
    })->name('kecamatan');

    Route::get('kelurahan', function (Request $request) {
      $validated = $request->validate([
        'kecamatan_id' => 'required|string|max:13',
      ]);

      $kecamatanId = $validated['kecamatan_id'];

      $data = Cache::remember("wilayah.kelurahan.{$kecamatanId}", now()->addDays(7), function () use ($kecamatanId) {
        $response = Http::timeout(15)->get("https://www.emsifa.com/api-wilayah-indonesia/api/villages/{$kecamatanId}.json");

        if (! $response->successful()) {
          return [];
        }

        return collect($response->json())
          ->map(fn($item) => [
            'id' => (string) data_get($item, 'id'),
            'nama' => (string) data_get($item, 'name'),
          ])
          ->values()
          ->all();
      });

      return response()->json(['data' => $data]);
    })->name('kelurahan');
  });

  // =========================================================
  // Admin: Manajemen Menu Dinamis
  // Proteksi tambahan bisa menggunakan middleware custom role,
  // contoh: ->middleware('role:Superadmin')
  // =========================================================
  Route::prefix('admin')->name('admin.')->group(function () {
    Volt::route('levels',    'admin.levels.index')->name('levels');
    Volt::route('menus',     'admin.menus.index')->name('menus');
    Volt::route('hak-akses', 'admin.hak-akses.index')->name('hak-akses');
    Volt::route('identitas', 'admin.identitas.index')->name('identitas');
    Volt::route('dashboard', 'admin.dashboard.index')->name('dashboard');
    Volt::route('bahasa', 'admin.bahasa.index')->name('bahasa');
    Volt::route('pengaturan-email', 'admin.pengaturan-email.index')->name('pengaturan-email');
  });

  // Laporan
  Route::prefix('laporan')->name('laporan.')->group(function () {
    Volt::route('aktivitas', 'laporan.aktivitas.index')->name('aktivitas');
    Volt::route('chat-ai', 'laporan.chat-ai.index')->name('chat-ai');
    Route::post('chat-ai/ask', function (Request $request, ChatAiAnalisisService $service) {
      if (! $request->user()?->bisaMenu('/laporan/chat-ai', 'dapat_lihat')) {
        abort(403);
      }

      $data = $request->validate([
        'pertanyaan' => 'required|string|min:3|max:1000',
      ]);

      $hasil = $service->analisa($data['pertanyaan']);

      if ($request->user()) {
        $service->simpanRiwayat($request->user(), $data['pertanyaan'], (string) $hasil['jawaban'], (string) $hasil['sumber']);
      }

      return response()->json([
        'jawaban' => $hasil['jawaban'],
        'sumber' => $hasil['sumber'],
        'waktu' => now()->format('d/m/Y H:i:s'),
      ]);
    })->name('chat-ai.ask');

    Route::get('chat-ai/history', function (Request $request, ChatAiAnalisisService $service) {
      if (! $request->user()?->bisaMenu('/laporan/chat-ai', 'dapat_lihat')) {
        abort(403);
      }

      $riwayat = $request->user()
        ? $service->riwayatUser($request->user(), 20)
        : [];

      return response()->json([
        'riwayat' => $riwayat,
      ]);
    })->name('chat-ai.history');

    Route::delete('chat-ai/history', function (Request $request, ChatAiAnalisisService $service) {
      if (! $request->user()?->bisaMenu('/laporan/chat-ai', 'dapat_lihat')) {
        abort(403);
      }

      $jumlah = $request->user()
        ? $service->hapusRiwayat($request->user())
        : 0;

      return response()->json([
        'dihapus' => $jumlah,
      ]);
    })->name('chat-ai.history.delete');
  });
});

require __DIR__ . '/auth.php';
