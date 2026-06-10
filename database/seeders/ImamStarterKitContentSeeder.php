<?php

namespace Database\Seeders;

use App\Models\Berita;
use App\Models\KontenSlider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ImamStarterKitContentSeeder extends Seeder
{
    public function run(): void
    {
        // ── Seed Konten Slider ──────────────────────────────────────────────
        KontenSlider::updateOrCreate(
            ['judul' => 'Selamat Datang di Imam-StarterKit'],
            [
                'subjudul'     => 'Starter kit Laravel 11 + Livewire Volt + Alpine.js dengan desain premium dan fitur siap pakai.',
                'foto'         => 'slider/slide1.png',
                'warna_latar'  => '#1e40af',
                'label_tombol' => 'Pelajari Fitur',
                'url_tombol'   => '#berita',
                'is_active'    => true,
                'urutan'       => 1,
            ]
        );

        KontenSlider::updateOrCreate(
            ['judul' => 'Dilengkapi Form Generator & Chat AI'],
            [
                'subjudul'     => 'Kembangkan aplikasi web dinamis dengan wizard form generator terintegrasi dan modul asisten AI.',
                'foto'         => 'slider/slide2.png',
                'warna_latar'  => '#7c3aed',
                'label_tombol' => 'Masuk Dashboard',
                'url_tombol'   => '/login',
                'is_active'    => true,
                'urutan'       => 2,
            ]
        );

        // ── Seed Berita / Artikel ───────────────────────────────────────────
        Berita::updateOrCreate(
            ['slug' => 'imam-starterkit-resmi-dirilis-untuk-mempercepat-development'],
            [
                'judul'          => 'Imam-StarterKit Resmi Dirilis untuk Mempercepat Development',
                'ringkasan'      => 'Imam-StarterKit kini tersedia dengan arsitektur modern berbasis Laravel 11, Volt Livewire, dan Tailwind CSS.',
                'isi'            => '<p>Imam-StarterKit hadir untuk menjawab kebutuhan para developer yang menginginkan basis kode starter kit yang bersih, premium, dan kaya akan fitur. Starter kit ini dilengkapi dengan fitur-fitur seperti sistem autentikasi, level & hak akses menu dinamis (RBAC), Form Generator Wizard, manajemen media, pemeliharaan sistem, dan integrasi Chat AI.</p><p>Dengan arsitektur modern berbasis Livewire Volt, Anda dapat membangun interface yang sangat reaktif tanpa meninggalkan ekosistem Laravel.</p>',
                'foto'           => null,
                'kategori'       => 'Berita',
                'penulis'        => 'Imam Zaenuddin',
                'tanggal_terbit' => now(),
                'is_published'   => true,
                'is_featured'    => true,
                'views'          => 120,
            ]
        );

        Berita::updateOrCreate(
            ['slug' => 'panduan-cepat-memulai-proyek-baru-dengan-imam-starterkit'],
            [
                'judul'          => 'Panduan Cepat Memulai Proyek Baru dengan Imam-StarterKit',
                'ringkasan'      => 'Ikuti beberapa langkah mudah berikut untuk menginstal dan menjalankan Imam-StarterKit di server lokal Anda.',
                'isi'            => '<p>Memulai proyek baru dengan Imam-StarterKit sangatlah mudah. Anda hanya perlu melakukan kloning repositori ini, menyalin berkas <code>.env.example</code> menjadi <code>.env</code>, menjalankan perintah <code>composer install</code> dan <code>npm install</code>, lalu menjalankan migrasi basis data dengan <code>php artisan migrate --seed</code>.</p><p>Setelah itu, jalankan server lokal Anda menggunakan <code>php artisan serve</code> dan <code>npm run dev</code>. Anda siap membangun aplikasi hebat berikutnya!</p>',
                'foto'           => null,
                'kategori'       => 'Kegiatan',
                'penulis'        => 'Developer Team',
                'tanggal_terbit' => now(),
                'is_published'   => true,
                'is_featured'    => true,
                'views'          => 85,
            ]
        );
    }
}
