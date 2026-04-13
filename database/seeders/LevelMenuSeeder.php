<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LevelMenuSeeder extends Seeder
{
  public function run(): void
  {
    // ============================
    // 1. LEVELS
    // ============================
    $superadmin = Level::firstOrCreate(['nama_level' => 'Superadmin'], [
      'deskripsi' => 'Akses penuh ke semua fitur sistem',
      'is_active'  => true,
    ]);

    $admin = Level::firstOrCreate(['nama_level' => 'Admin'], [
      'deskripsi' => 'Akses pengelolaan data umum',
      'is_active'  => true,
    ]);

    $anggota = Level::firstOrCreate(['nama_level' => 'Anggota'], [
      'deskripsi' => 'Akses baca data yang diizinkan',
      'is_active'  => true,
    ]);

    // ============================
    // 2. MENUS
    // ============================

    // --- Sistem (parent) ---
    $administrasi = Menu::firstOrCreate(['nama' => 'Sistem', 'parent_id' => null], [
      'url'       => null,
      'icon'      => 'bx bx-buildings',
      'urutan'    => 10,
      'is_active' => true,
    ]);

    $mLevel = Menu::firstOrCreate(['nama' => 'Kelola Level', 'parent_id' => $administrasi->id], [
      'url'       => '/admin/levels',
      'icon'      => 'bx bx-shield',
      'urutan'    => 11,
      'is_active' => true,
    ]);

    $mMenu = Menu::firstOrCreate(['nama' => 'Kelola Menu', 'parent_id' => $administrasi->id], [
      'url'       => '/admin/menus',
      'icon'      => 'bx bx-menu',
      'urutan'    => 12,
      'is_active' => true,
    ]);

    $mMapping = Menu::firstOrCreate(['nama' => 'Hak Akses', 'parent_id' => $administrasi->id], [
      'url'       => '/admin/hak-akses',
      'icon'      => 'bx bx-key',
      'urutan'    => 13,
      'is_active' => true,
    ]);

    $mIdentitas = Menu::firstOrCreate(['nama' => 'Identitas', 'parent_id' => $administrasi->id], [
      'url'       => '/admin/identitas',
      'icon'      => 'bx bx-id-card',
      'urutan'    => 14,
      'is_active' => true,
    ]);

    $mDashboard = Menu::firstOrCreate(['nama' => 'Kelola Dashboard', 'parent_id' => $administrasi->id], [
      'url'       => '/admin/dashboard',
      'icon'      => 'bx bx-layout',
      'urutan'    => 15,
      'is_active' => true,
    ]);

    $mBahasa = Menu::firstOrCreate(['nama' => 'Bahasa', 'parent_id' => $administrasi->id], [
      'url'       => '/admin/bahasa',
      'icon'      => 'bx bx-translate',
      'urutan'    => 16,
      'is_active' => true,
    ]);

    $mPengaturanEmail = Menu::firstOrCreate(['nama' => 'Pengaturan Email', 'parent_id' => $administrasi->id], [
      'url'       => '/admin/pengaturan-email',
      'icon'      => 'bx bx-envelope',
      'urutan'    => 17,
      'is_active' => true,
    ]);

    // --- Anggota (parent) ---
    $mAnggota = Menu::firstOrCreate(['nama' => 'Anggota', 'parent_id' => null], [
      'url'       => '/anggota',
      'icon'      => 'bx bx-group',
      'urutan'    => 20,
      'is_active' => true,
    ]);

    // --- Laporan (parent) ---
    $laporan = Menu::firstOrCreate(['nama' => 'Laporan', 'parent_id' => null], [
      'url'       => null,
      'icon'      => 'bx bx-bar-chart-alt-2',
      'urutan'    => 30,
      'is_active' => true,
    ]);

    $lAktivitas = Menu::firstOrCreate(['nama' => 'Laporan Aktivitas', 'parent_id' => $laporan->id], [
      'url'       => '/laporan/aktivitas',
      'icon'      => 'bx bx-list-ul',
      'urutan'    => 31,
      'is_active' => true,
    ]);

    $lChatAi = Menu::firstOrCreate(['nama' => 'Chat Asisten Analitik', 'parent_id' => $laporan->id], [
      'url'       => '/laporan/chat-ai',
      'icon'      => 'bx bx-bot',
      'urutan'    => 32,
      'is_active' => true,
    ]);

    // ============================
    // 3. MAPPING LEVEL ↔ MENU
    // ============================

    // Superadmin: akses penuh ke semua menu
    $semuaMenu = Menu::all();
    foreach ($semuaMenu as $m) {
      $superadmin->menus()->syncWithoutDetaching([
        $m->id => [
          'dapat_lihat' => true,
          'dapat_buat'  => true,
          'dapat_ubah'  => true,
          'dapat_hapus' => true,
        ],
      ]);
    }

    // Admin: kelola anggota & laporan, tanpa menu administrasi sistem
    foreach ([$administrasi, $mLevel, $mMenu, $mMapping, $mIdentitas, $mDashboard, $mBahasa, $mPengaturanEmail, $mAnggota, $laporan, $lAktivitas, $lChatAi] as $m) {
      $akses = match ($m->id) {
        $mPengaturanEmail->id => [
          'dapat_lihat' => true,
          'dapat_buat' => false,
          'dapat_ubah'  => true,
          'dapat_hapus' => false,
        ],
        $administrasi->id, $mLevel->id, $mMenu->id, $mMapping->id, $mIdentitas->id, $mDashboard->id, $mBahasa->id => [
          'dapat_lihat' => false,
          'dapat_buat' => false,
          'dapat_ubah'  => false,
          'dapat_hapus' => false,
        ],
        default => [
          'dapat_lihat' => true,
          'dapat_buat' => true,
          'dapat_ubah'  => true,
          'dapat_hapus' => false,
        ],
      };
      $admin->menus()->syncWithoutDetaching([$m->id => $akses]);
    }

    // Anggota: hanya baca laporan aktivitas
    $anggota->menus()->syncWithoutDetaching([
      $laporan->id => [
        'dapat_lihat' => true,
        'dapat_buat' => false,
        'dapat_ubah'  => false,
        'dapat_hapus' => false,
      ],
      $lAktivitas->id => [
        'dapat_lihat' => true,
        'dapat_buat' => false,
        'dapat_ubah'  => false,
        'dapat_hapus' => false,
      ],
      $lChatAi->id => [
        'dapat_lihat' => true,
        'dapat_buat' => false,
        'dapat_ubah'  => false,
        'dapat_hapus' => false,
      ],
    ]);

    // ============================
    // 4. USER DEMO
    // ============================
    User::firstOrCreate(['email' => 'superadmin@sio.id'], [
      'name'     => 'Super Admin',
      'level_id' => $superadmin->id,
      'password' => Hash::make('password'),
    ]);

    User::firstOrCreate(['email' => 'admin@sio.id'], [
      'name'     => 'Admin Umum',
      'level_id' => $admin->id,
      'password' => Hash::make('password'),
    ]);

    User::firstOrCreate(['email' => 'anggota@sio.id'], [
      'name'     => 'Budi Santoso',
      'level_id' => $anggota->id,
      'password' => Hash::make('password'),
    ]);
  }
}
