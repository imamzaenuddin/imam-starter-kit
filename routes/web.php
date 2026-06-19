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
        Volt::route('referensi/agama', 'admin.referensi.agama.index')->name('referensi.agama');
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
    Volt::route('/admin/referensi/agama', 'admin.referensi.agama.index')->name('admin.referensi.agama');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenisdosen', 'admin.referensi.jenisdosen.index')->name('admin.referensi.jenisdosen');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenisjabatan', 'admin.referensi.jenisjabatan.index')->name('admin.referensi.jenisjabatan');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenisbiaya', 'admin.referensi.jenisbiaya.index')->name('admin.referensi.jenisbiaya');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/gradeipk', 'admin.referensi.gradeipk.index')->name('admin.referensi.gradeipk');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/semester', 'admin.referensi.semester.index')->name('admin.referensi.semester');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/trx', 'admin.referensi.trx.index')->name('admin.referensi.trx');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/hidup', 'admin.referensi.hidup.index')->name('admin.referensi.hidup');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenismbkm', 'admin.referensi.jenismbkm.index')->name('admin.referensi.jenismbkm');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenispegawai', 'admin.referensi.jenispegawai.index')->name('admin.referensi.jenispegawai');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenissurat', 'admin.referensi.jenissurat.index')->name('admin.referensi.jenissurat');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/kelamin', 'admin.referensi.kelamin.index')->name('admin.referensi.kelamin');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/pmbusm', 'admin.referensi.pmbusm.index')->name('admin.referensi.pmbusm');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/program', 'admin.referensi.program.index')->name('admin.referensi.program');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/statuskaryawan', 'admin.referensi.statuskaryawan.index')->name('admin.referensi.statuskaryawan');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/statussipil', 'admin.referensi.statussipil.index')->name('admin.referensi.statussipil');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/fakultas', 'admin.referensi.fakultas.index')->name('admin.referensi.fakultas');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenisanggota', 'admin.referensi.jenisanggota.index')->name('admin.referensi.jenisanggota');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenispembiayaan', 'admin.referensi.jenispembiayaan.index')->name('admin.referensi.jenispembiayaan');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/lingkupkelas', 'admin.referensi.lingkupkelas.index')->name('admin.referensi.lingkupkelas');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/modekuliah', 'admin.referensi.modekuliah.index')->name('admin.referensi.modekuliah');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/rfidtime', 'admin.referensi.rfidtime.index')->name('admin.referensi.rfidtime');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/saat', 'admin.referensi.saat.index')->name('admin.referensi.saat');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/statuskelompok', 'admin.referensi.statuskelompok.index')->name('admin.referensi.statuskelompok');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/statuskrs', 'admin.referensi.statuskrs.index')->name('admin.referensi.statuskrs');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenisjadwal', 'admin.referensi.jenisjadwal.index')->name('admin.referensi.jenisjadwal');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/pmbgrade', 'admin.referensi.pmbgrade.index')->name('admin.referensi.pmbgrade');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/statusdosen', 'admin.referensi.statusdosen.index')->name('admin.referensi.statusdosen');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/statuskerja', 'admin.referensi.statuskerja.index')->name('admin.referensi.statuskerja');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/benua', 'admin.referensi.benua.index')->name('admin.referensi.benua');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/tempattinggal', 'admin.referensi.tempattinggal.index')->name('admin.referensi.tempattinggal');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenislibur', 'admin.referensi.jenislibur.index')->name('admin.referensi.jenislibur');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenispresensi', 'admin.referensi.jenispresensi.index')->name('admin.referensi.jenispresensi');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenissekolah', 'admin.referensi.jenissekolah.index')->name('admin.referensi.jenissekolah');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/pegawainilai', 'admin.referensi.pegawainilai.index')->name('admin.referensi.pegawainilai');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/posisipegawai', 'admin.referensi.posisipegawai.index')->name('admin.referensi.posisipegawai');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/statusaplikan', 'admin.referensi.statusaplikan.index')->name('admin.referensi.statusaplikan');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenistinggal', 'admin.referensi.jenistinggal.index')->name('admin.referensi.jenistinggal');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/baju', 'admin.referensi.baju.index')->name('admin.referensi.baju');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/statuspegawai', 'admin.referensi.statuspegawai.index')->name('admin.referensi.statuspegawai');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/carabayar', 'admin.referensi.carabayar.index')->name('admin.referensi.carabayar');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/unitorganisasi', 'admin.referensi.unitorganisasi.index')->name('admin.referensi.unitorganisasi');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/wisudaprasyarat', 'admin.referensi.wisudaprasyarat.index')->name('admin.referensi.wisudaprasyarat');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/statusbayar', 'admin.referensi.statusbayar.index')->name('admin.referensi.statusbayar');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/statusberkas', 'admin.referensi.statusberkas.index')->name('admin.referensi.statusberkas');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/penghasilanortu', 'admin.referensi.penghasilanortu.index')->name('admin.referensi.penghasilanortu');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jabatandikti', 'admin.referensi.jabatandikti.index')->name('admin.referensi.jabatandikti');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/pmbformsyarat', 'admin.referensi.pmbformsyarat.index')->name('admin.referensi.pmbformsyarat');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenisberkas', 'admin.referensi.jenisberkas.index')->name('admin.referensi.jenisberkas');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jeniskeluar', 'admin.referensi.jeniskeluar.index')->name('admin.referensi.jeniskeluar');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/kampus', 'admin.referensi.kampus.index')->name('admin.referensi.kampus');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/arsip', 'admin.referensi.arsip.index')->name('admin.referensi.arsip');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/statuskeluar', 'admin.referensi.statuskeluar.index')->name('admin.referensi.statuskeluar');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenistransportasi', 'admin.referensi.jenistransportasi.index')->name('admin.referensi.jenistransportasi');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/transportasi', 'admin.referensi.transportasi.index')->name('admin.referensi.transportasi');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/biayastudi', 'admin.referensi.biayastudi.index')->name('admin.referensi.biayastudi');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/bank', 'admin.referensi.bank.index')->name('admin.referensi.bank');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/sumberinfo', 'admin.referensi.sumberinfo.index')->name('admin.referensi.sumberinfo');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenjang', 'admin.referensi.jenjang.index')->name('admin.referensi.jenjang');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/statusmhsw', 'admin.referensi.statusmhsw.index')->name('admin.referensi.statusmhsw');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/pendidikan', 'admin.referensi.pendidikan.index')->name('admin.referensi.pendidikan');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/bipotnama', 'admin.referensi.bipotnama.index')->name('admin.referensi.bipotnama');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/jenispilihan', 'admin.referensi.jenispilihan.index')->name('admin.referensi.jenispilihan');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/ruang', 'admin.referensi.ruang.index')->name('admin.referensi.ruang');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/kurikulum', 'admin.referensi.kurikulum.index')->name('admin.referensi.kurikulum');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/negara', 'admin.referensi.negara.index')->name('admin.referensi.negara');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/warganegara', 'admin.referensi.warganegara.index')->name('admin.referensi.warganegara');
});

// Auto-generated by CRUD Generator
Route::middleware(['auth'])->group(function () {
    Volt::route('/admin/referensi/golongan', 'admin.referensi.golongan.index')->name('admin.referensi.golongan');
});
