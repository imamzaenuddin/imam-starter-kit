<?php

namespace Database\Seeders;

use App\Models\DashboardWidget;
use App\Models\Level;
use Illuminate\Database\Seeder;

class DashboardWidgetSeeder extends Seeder
{
  public function run(): void
  {
    $superadmin = Level::query()->where('nama_level', 'Superadmin')->first();
    $admin = Level::query()->where('nama_level', 'Admin')->first();
    $anggota = Level::query()->where('nama_level', 'Anggota')->first();

    $widgetPengguna = DashboardWidget::updateOrCreate(
      ['nama_widget' => 'Total Pengguna'],
      [
        'deskripsi' => 'Menampilkan jumlah seluruh data pengguna.',
        'sumber_data' => 'users',
        'tipe_tampilan' => 'statistik',
        'tipe_query' => 'count',
        'layout_kolom' => 'col-xl-3 col-md-6',
        'warna' => 'primary',
        'icon' => 'bx bx-user',
        'batas_data' => 5,
        'urutan' => 1,
        'is_active' => true,
      ]
    );

    $widgetMenuAktif = DashboardWidget::updateOrCreate(
      ['nama_widget' => 'Menu Aktif'],
      [
        'deskripsi' => 'Jumlah menu sistem yang aktif dan siap diakses.',
        'sumber_data' => 'menus',
        'tipe_tampilan' => 'statistik',
        'tipe_query' => 'count',
        'filter_kolom' => 'is_active',
        'filter_operator' => '=',
        'filter_nilai' => '1',
        'layout_kolom' => 'col-xl-3 col-md-6',
        'warna' => 'success',
        'icon' => 'bx bx-menu-alt-left',
        'batas_data' => 5,
        'urutan' => 2,
        'is_active' => true,
      ]
    );

    $widgetLevelAktif = DashboardWidget::updateOrCreate(
      ['nama_widget' => 'Level Aktif'],
      [
        'deskripsi' => 'Jumlah group level yang sedang aktif.',
        'sumber_data' => 'levels',
        'tipe_tampilan' => 'statistik',
        'tipe_query' => 'count',
        'filter_kolom' => 'is_active',
        'filter_operator' => '=',
        'filter_nilai' => '1',
        'layout_kolom' => 'col-xl-3 col-md-6',
        'warna' => 'warning',
        'icon' => 'bx bx-shield-quarter',
        'batas_data' => 5,
        'urutan' => 3,
        'is_active' => true,
      ]
    );

    $widgetAktivitas = DashboardWidget::updateOrCreate(
      ['nama_widget' => 'Aktivitas Terbaru'],
      [
        'deskripsi' => 'Menampilkan aktivitas terbaru dari sistem secara real-time.',
        'sumber_data' => 'log_aktivitas',
        'tipe_tampilan' => 'daftar',
        'tipe_query' => 'latest',
        'kolom_label' => 'aktivitas',
        'kolom_nilai' => 'modul',
        'layout_kolom' => 'col-xl-6 col-12',
        'warna' => 'info',
        'icon' => 'bx bx-pulse',
        'batas_data' => 5,
        'urutan' => 4,
        'is_active' => true,
      ]
    );

    $widgetGrafikAktivitas = DashboardWidget::updateOrCreate(
      ['nama_widget' => 'Aktivitas per Modul'],
      [
        'deskripsi' => 'Grafik distribusi aktivitas berdasarkan modul yang paling sering diakses.',
        'sumber_data' => 'log_aktivitas',
        'tipe_tampilan' => 'grafik',
        'tipe_query' => 'count',
        'chart_tipe' => 'donut',
        'chart_tinggi' => 300,
        'chart_warna' => ['#696cff', '#03c3ec', '#71dd37', '#ffab00', '#ff3e1d', '#8592a3'],
        'kolom_label' => 'modul',
        'layout_kolom' => 'col-xl-6 col-12',
        'warna' => 'primary',
        'icon' => 'bx bx-doughnut-chart',
        'batas_data' => 6,
        'urutan' => 5,
        'is_active' => true,
      ]
    );

    $levelSemua = collect([$superadmin, $admin, $anggota])->filter()->pluck('id')->all();
    $levelAdmin = collect([$superadmin, $admin])->filter()->pluck('id')->all();

    $widgetPengguna->levels()->sync($levelAdmin);
    $widgetMenuAktif->levels()->sync($levelAdmin);
    $widgetLevelAktif->levels()->sync($levelAdmin);
    $widgetAktivitas->levels()->sync($levelSemua);
    $widgetGrafikAktivitas->levels()->sync($levelSemua);
  }
}
