# Catatan Bahasa (i18n) - SIO

Dokumen ini menjadi acuan singkat untuk pengelolaan multi bahasa pada proyek Sistem Informasi Organisasi.

## Sumber Terjemahan

- Satu-satunya sumber dictionary adalah folder `lang/<locale>/messages.php`.
- Jangan gunakan lagi `resources/lang` agar tidak terjadi duplikasi key.
- Locale aktif disimpan di session dan diterapkan lewat middleware.

## File Penting

- `app/Services/BahasaService.php`
- `app/Http/Middleware/SetLocaleMiddleware.php`
- `resources/views/components/layouts/navbar/default.blade.php`
- `lang/id/messages.php`
- `lang/en/messages.php`

## Alur Bahasa

1. User memilih bahasa dari dropdown navbar.
2. Form mengirim `POST` ke route `bahasa.ganti`.
3. `BahasaService::setLocaleSesi()` menyimpan locale ke session.
4. Middleware `SetLocaleMiddleware` menerapkan locale tiap request.
5. Blade membaca teks via `__('messages.key')`.

## Konvensi Key

- Gunakan prefix yang konsisten: `messages.<nama_key>`.
- Untuk menu dinamis DB gunakan pola key:
  - `messages.menu_<slug_nama_menu>`
- Sidebar sudah memakai fallback otomatis:
  - Jika key tidak ada, tampilkan nama menu asli dari database.

Contoh:

- Nama menu DB: `Kelola Dashboard`
- Key yang dicari: `messages.menu_kelola_dashboard`

## Menambah Bahasa Baru

1. Tambahkan folder locale baru: `lang/<kode_locale>/messages.php`.
2. Isi key sesuai struktur `id`/`en`.
3. Sinkronkan dari halaman Admin Bahasa atau jalankan seeder terkait.
4. Pastikan status bahasa aktif di tabel `bahasa`.

## Troubleshooting Cepat

### Teks tidak berubah saat ganti bahasa

- Pastikan locale di session berubah.
- Pastikan key tersedia di file `messages.php` locale terkait.
- Jalankan:

```bash
php artisan optimize:clear
```

### Menu belum ter-translate

- Tambahkan key `menu_<slug_nama_menu>` di semua locale yang dipakai.
- Jika belum ditambahkan, sistem akan fallback ke nama menu asli.

### Bahasa ada di DB tapi tidak muncul

- Cek `is_active = true` di tabel `bahasa`.
- Cek urutan (`urutan`) untuk urutan tampilan.

## Rekomendasi Praktik

- Setiap menambah UI text baru, langsung tambahkan key ke `id` dan `en`.
- Hindari hardcoded string di Blade/Volt.
- Pertahankan key yang sudah dipakai agar tidak memutus terjemahan lama.
