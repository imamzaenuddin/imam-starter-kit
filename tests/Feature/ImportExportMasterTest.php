<?php

use App\Models\Level;
use App\Models\Menu;
use App\Services\ImportExportMasterService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(ImportExportMasterService::class);
});

test('export csv level mengandung header dan data', function () {
    Level::query()->create([
        'nama_level' => 'Operator',
        'deskripsi' => 'Operator data',
        'is_active' => true,
    ]);

    $response = $this->service->exportCsv('levels');
    $content = $response->getCallback();

    ob_start();
    $content();
    $csv = ob_get_clean();

    expect($csv)->toContain('nama_level')
        ->toContain('Operator');
});

test('import csv level berhasil', function () {
    $file = tempnam(sys_get_temp_dir(), 'csv_level_');

    file_put_contents(
        $file,
        "nama_level,deskripsi,is_active\n" .
            "Staff,Staff Operasional,1\n"
    );

    $hasil = $this->service->importCsv('levels', $file);

    expect($hasil['jumlah_berhasil'])->toBe(1)
        ->and($hasil['jumlah_gagal'])->toBe(0)
        ->and(Level::query()->where('nama_level', 'Staff')->exists())->toBeTrue();

    @unlink($file);
});

test('import csv menu dengan parent berhasil', function () {
    $file = tempnam(sys_get_temp_dir(), 'csv_menu_');

    file_put_contents(
        $file,
        "nama,url,icon,parent_nama,urutan,is_active\n" .
            "Master Data,,, ,10,1\n" .
            "Data Anggota,/anggota,bx bx-group,Master Data,11,1\n"
    );

    $hasil = $this->service->importCsv('menus', $file);

    $parent = Menu::query()->where('nama', 'Master Data')->whereNull('parent_id')->first();
    $child = Menu::query()->where('nama', 'Data Anggota')->first();

    expect($hasil['jumlah_gagal'])->toBe(0)
        ->and($hasil['jumlah_berhasil'])->toBe(2)
        ->and($parent)->not->toBeNull()
        ->and($child)->not->toBeNull()
        ->and($child->parent_id)->toBe($parent->id);

    @unlink($file);
});

test('import csv menampilkan error per baris', function () {
    $file = tempnam(sys_get_temp_dir(), 'csv_err_');

    file_put_contents(
        $file,
        "nama_level,deskripsi,is_active\n" .
            ",Tanpa Nama,1\n"
    );

    $hasil = $this->service->importCsv('levels', $file);

    expect($hasil['jumlah_gagal'])->toBe(1)
        ->and($hasil['error_baris'])->toHaveCount(1)
        ->and($hasil['error_baris'][0]['baris'])->toBe(2);

    @unlink($file);
});
