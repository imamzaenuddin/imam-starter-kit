<?php

use App\Services\BahasaService;
use App\Services\ChatAiAnalisisService;
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

    Route::post('ganti-level', function (Request $request) {
        $validated = $request->validate([
            'level_id' => 'required|exists:m_level,id',
        ]);
        
        $user = auth()->user();
        $punyaLevel = $user->levels()->where('m_level.id', $validated['level_id'])->exists();
        
        if ($punyaLevel) {
            $user->update(['level_id' => $validated['level_id']]);
            \Illuminate\Support\Facades\Cache::flush();
            session()->flash('sukses', 'Level berhasil dipindahkan ke ' . $user->level->nama_level);
        }
        
        return back();
    })->name('ganti-level');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/two-factor', 'settings.two-factor')->name('settings.two-factor');

    Route::prefix('api/wilayah')->name('api.wilayah.')->group(function () {
        Route::get('provinsi', function () {
            $data = Cache::remember('wilayah.provinsi', now()->addDays(7), function () {
                $response = Http::timeout(15)->get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');

                if (! $response->successful()) {
                    return [];
                }

                return collect($response->json())
                    ->map(fn ($item) => [
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
                    ->map(fn ($item) => [
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
                    ->map(fn ($item) => [
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
                    ->map(fn ($item) => [
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
        Volt::route('levels', 'admin.levels.index')->name('levels');
        Volt::route('menus', 'admin.menus.index')->name('menus');
        Volt::route('hak-akses', 'admin.hak-akses.index')->name('hak-akses');
        Volt::route('identitas', 'admin.identitas.index')->name('identitas');
        Volt::route('dashboard', 'admin.dashboard.index')->name('dashboard');
        Volt::route('import-export', 'admin.import-export.index')->name('import-export');
        Volt::route('pengaturan-aplikasi', 'admin.pengaturan-aplikasi.index')->name('pengaturan-aplikasi');
        Volt::route('pengaturan-chat-ai', 'admin.pengaturan-chat-ai.index')->name('pengaturan-chat-ai');
        Volt::route('bahasa', 'admin.bahasa.index')->name('bahasa');
        Volt::route('pengaturan-email', 'admin.pengaturan-email.index')->name('pengaturan-email');
        Volt::route('users', 'admin.users.index')->name('users');
        Volt::route('media', 'admin.media.index')->name('media');
        Volt::route('backup-restore', 'admin.backup-restore.index')->name('backup-restore');
        Volt::route('pemeliharaan', 'admin.pemeliharaan.index')->name('pemeliharaan');
        Volt::route('form-generator-wizard', 'admin.form-generator.wizard')->name('form-generator.wizard');
        Volt::route('form-generator/{slug}', 'admin.form-generator.runtime')->name('form-generator.runtime');
        Volt::route('migrasi-database', 'admin.migrasi-database.index')->name('migrasi-database');
        Volt::route('preferensi/agama', 'admin.preferensi.agama.index')->name('preferensi.agama');
    });

    // Laporan
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Volt::route('aktivitas', 'laporan.aktivitas.index')->name('aktivitas');
        Volt::route('login-attempts', 'laporan.login-attempts.index')->name('login-attempts');
        Volt::route('chat-ai', 'laporan.chat-ai.index')->name('chat-ai');
        Route::post('chat-ai/ask', function (Request $request, ChatAiAnalisisService $service) {
            if (! $request->user()?->bisaMenu('/laporan/chat-ai', 'dapat_lihat')) {
                abort(403);
            }

            $data = $request->validate([
                'pertanyaan' => 'required|string|min:3|max:1000',
            ]);

            $hasil = $service->analisa($data['pertanyaan'], $request->user());

            if ($request->user()) {
                $service->simpanRiwayat($request->user(), $data['pertanyaan'], (string) $hasil['jawaban'], (string) $hasil['sumber']);
            }

            return response()->json([
                'jawaban' => $hasil['jawaban'],
                'sumber' => $hasil['sumber'],
                'ringkasan_redaksi' => $hasil['ringkasan_redaksi'] ?? [
                    'ada_redaksi' => false,
                    'jumlah_sumber_teredaksi' => 0,
                    'kolom_disensor' => [],
                ],
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

    // Manajemen Konten (Admin)
    Route::prefix('manajemen-konten')->name('konten.')->group(function () {
        Volt::route('berita', 'master.konten.berita')->name('berita');
        Volt::route('slider', 'master.konten.slider')->name('slider');
    });
});

// Google OAuth Routes
Route::get('auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Public: Berita & Artikel
Volt::route('berita', 'publik.berita.index')->name('berita.index');
Volt::route('berita/{slug}', 'publik.berita.detail')->name('berita.detail');

require __DIR__.'/auth.php';

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/agama', 'admin.preferensi.agama.index')->name('admin.preferensi.agama');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenisdosen', 'admin.preferensi.jenisdosen.index')->name('admin.preferensi.jenisdosen');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenisjabatan', 'admin.preferensi.jenisjabatan.index')->name('admin.preferensi.jenisjabatan');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenisbiaya', 'admin.preferensi.jenisbiaya.index')->name('admin.preferensi.jenisbiaya');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/gradeipk', 'admin.preferensi.gradeipk.index')->name('admin.preferensi.gradeipk');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/semester', 'admin.preferensi.semester.index')->name('admin.preferensi.semester');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/trx', 'admin.preferensi.trx.index')->name('admin.preferensi.trx');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/hidup', 'admin.preferensi.hidup.index')->name('admin.preferensi.hidup');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenismbkm', 'admin.preferensi.jenismbkm.index')->name('admin.preferensi.jenismbkm');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenispegawai', 'admin.preferensi.jenispegawai.index')->name('admin.preferensi.jenispegawai');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenissurat', 'admin.preferensi.jenissurat.index')->name('admin.preferensi.jenissurat');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/kelamin', 'admin.preferensi.kelamin.index')->name('admin.preferensi.kelamin');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/pmbusm', 'admin.preferensi.pmbusm.index')->name('admin.preferensi.pmbusm');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/program', 'admin.preferensi.program.index')->name('admin.preferensi.program');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/statuskaryawan', 'admin.preferensi.statuskaryawan.index')->name('admin.preferensi.statuskaryawan');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/statussipil', 'admin.preferensi.statussipil.index')->name('admin.preferensi.statussipil');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/fakultas', 'admin.preferensi.fakultas.index')->name('admin.preferensi.fakultas');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenisanggota', 'admin.preferensi.jenisanggota.index')->name('admin.preferensi.jenisanggota');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenispembiayaan', 'admin.preferensi.jenispembiayaan.index')->name('admin.preferensi.jenispembiayaan');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/lingkupkelas', 'admin.preferensi.lingkupkelas.index')->name('admin.preferensi.lingkupkelas');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/modekuliah', 'admin.preferensi.modekuliah.index')->name('admin.preferensi.modekuliah');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/rfidtime', 'admin.preferensi.rfidtime.index')->name('admin.preferensi.rfidtime');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/saat', 'admin.preferensi.saat.index')->name('admin.preferensi.saat');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/statuskelompok', 'admin.preferensi.statuskelompok.index')->name('admin.preferensi.statuskelompok');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/statuskrs', 'admin.preferensi.statuskrs.index')->name('admin.preferensi.statuskrs');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenisjadwal', 'admin.preferensi.jenisjadwal.index')->name('admin.preferensi.jenisjadwal');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/pmbgrade', 'admin.preferensi.pmbgrade.index')->name('admin.preferensi.pmbgrade');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/statusdosen', 'admin.preferensi.statusdosen.index')->name('admin.preferensi.statusdosen');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/statuskerja', 'admin.preferensi.statuskerja.index')->name('admin.preferensi.statuskerja');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/benua', 'admin.preferensi.benua.index')->name('admin.preferensi.benua');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/tempattinggal', 'admin.preferensi.tempattinggal.index')->name('admin.preferensi.tempattinggal');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenislibur', 'admin.preferensi.jenislibur.index')->name('admin.preferensi.jenislibur');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenispresensi', 'admin.preferensi.jenispresensi.index')->name('admin.preferensi.jenispresensi');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenissekolah', 'admin.preferensi.jenissekolah.index')->name('admin.preferensi.jenissekolah');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/pegawainilai', 'admin.preferensi.pegawainilai.index')->name('admin.preferensi.pegawainilai');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/posisipegawai', 'admin.preferensi.posisipegawai.index')->name('admin.preferensi.posisipegawai');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/statusaplikan', 'admin.preferensi.statusaplikan.index')->name('admin.preferensi.statusaplikan');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenistinggal', 'admin.preferensi.jenistinggal.index')->name('admin.preferensi.jenistinggal');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/baju', 'admin.preferensi.baju.index')->name('admin.preferensi.baju');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/statuspegawai', 'admin.preferensi.statuspegawai.index')->name('admin.preferensi.statuspegawai');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/carabayar', 'admin.preferensi.carabayar.index')->name('admin.preferensi.carabayar');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/unitorganisasi', 'admin.preferensi.unitorganisasi.index')->name('admin.preferensi.unitorganisasi');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/wisudaprasyarat', 'admin.preferensi.wisudaprasyarat.index')->name('admin.preferensi.wisudaprasyarat');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/statusbayar', 'admin.preferensi.statusbayar.index')->name('admin.preferensi.statusbayar');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/statusberkas', 'admin.preferensi.statusberkas.index')->name('admin.preferensi.statusberkas');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/penghasilanortu', 'admin.preferensi.penghasilanortu.index')->name('admin.preferensi.penghasilanortu');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jabatandikti', 'admin.preferensi.jabatandikti.index')->name('admin.preferensi.jabatandikti');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/pmbformsyarat', 'admin.preferensi.pmbformsyarat.index')->name('admin.preferensi.pmbformsyarat');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenisberkas', 'admin.preferensi.jenisberkas.index')->name('admin.preferensi.jenisberkas');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jeniskeluar', 'admin.preferensi.jeniskeluar.index')->name('admin.preferensi.jeniskeluar');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/kampus', 'admin.preferensi.kampus.index')->name('admin.preferensi.kampus');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/arsip', 'admin.preferensi.arsip.index')->name('admin.preferensi.arsip');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/statuskeluar', 'admin.preferensi.statuskeluar.index')->name('admin.preferensi.statuskeluar');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/jenistransportasi', 'admin.preferensi.jenistransportasi.index')->name('admin.preferensi.jenistransportasi');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/preferensi/transportasi', 'admin.preferensi.transportasi.index')->name('admin.preferensi.transportasi');
});
