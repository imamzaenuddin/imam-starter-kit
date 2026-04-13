# Copilot Instructions — Sistem Informasi Organisasi (SIO)

## Sneat Bootstrap + Laravel Livewire Volt Starter Kit

> File ini dibaca otomatis oleh GitHub Copilot (model Claude Sonnet 4.x+).
> Semua instruksi bersifat **mengikat** — jangan mengabaikan aturan di bawah ini.

---

## 1. IDENTITAS PROYEK

| Atribut         | Nilai                                                                         |
| --------------- | ----------------------------------------------------------------------------- |
| **Nama Sistem** | Sistem Informasi Organisasi (SIO)                                             |
| **Stack**       | Laravel 12, Livewire Volt (single-file), Bootstrap 5 (Sneat Theme), Alpine.js |
| **PHP minimum** | 8.2                                                                           |
| **Database**    | MySQL / SQLite (via `.env`)                                                   |
| **Auth**        | Laravel Auth bawaan (Livewire Volt)                                           |
| **Icon**        | Boxicons (`bx bx-*`) — JANGAN pakai Heroicons / FontAwesome                   |
| **Font**        | Public Sans (sudah ada di `partials/styles.blade.php`)                        |

---

## 2. KONVENSI FILE & FOLDER

### Livewire Volt (Komponen Halaman)

- Semua komponen halaman ada di `resources/views/livewire/`
- Gunakan **single-file Volt** dengan blok `<?php ... ?>` di atas, view Blade di bawah
- Wajib deklarasikan `#[Layout('components.layouts.app')]` untuk halaman dalam (authenticated)
- Wajib deklarasikan `#[Layout('components.layouts.auth')]` untuk halaman auth

```
resources/views/livewire/
    auth/               ← halaman login, register, forgot-password, dll.
    admin/
        levels/index.blade.php    ← CRUD Level
        menus/index.blade.php     ← CRUD Menu
        hak-akses/index.blade.php ← Mapping Level ↔ Menu
        identitas/index.blade.php ← CRUD Identitas Sistem
        dashboard/index.blade.php ← CRUD Dashboard Dinamis
    settings/           ← profile, password (bawaan starter kit)
```

### Models

- Semua model di `app/Models/`
- Gunakan `$fillable` (jangan `$guarded = []`)
- Relasi dinamis diberi nama Bahasa Indonesia ringkas

### Services

- Logika bisnis non-trivial di `app/Services/`
- Daftarkan di `AppServiceProvider` jika diperlukan binding

### Layout & Partial

```
resources/views/components/layouts/
    app.blade.php              ← layout utama (authenticated)
    auth.blade.php             ← layout auth (login, register)
    menu/vertical.blade.php    ← sidebar dinamis (JANGAN hardcode menu di sini)
    navbar/default.blade.php
    footer/default.blade.php
resources/views/partials/
    head.blade.php             ← meta, CSS, @livewireStyles
    scripts.blade.php          ← JS, @livewireScripts
    styles.blade.php           ← import SCSS & font
```

---

## 3. ARSITEKTUR MENU DINAMIS

### Struktur Tabel

```
levels          → id, nama_level, deskripsi, is_active
menus           → id, nama, url, icon, parent_id (self-ref), urutan, is_active
level_menu      → level_id FK, menu_id FK, dapat_lihat, dapat_buat, dapat_ubah, dapat_hapus
dashboard_widgets      → konfigurasi kartu dashboard dinamis
dashboard_widget_level → mapping widget dashboard ↔ level
users           → ... level_id FK → levels.id
identitas       → id, nama_aplikasi, versi, icon, email, wa_center, telepon, website, alamat,
                  slogan, deskripsi, footer_text, is_active
```

### Alur Kerja

1. **Tambah Level** di `/admin/levels`
2. **Tambah Menu** di `/admin/menus` (bisa bersarang 1 level)
3. **Set Hak Akses** di `/admin/hak-akses` — pilih level → centang izin per menu
4. **Kelola Identitas Sistem** di `/admin/identitas` — untuk icon, nama aplikasi, versi, email, WA center, dll.
5. **Kelola Dashboard Dinamis** di `/admin/dashboard` — tentukan nama widget, group level, sumber data, filter, query, dan layout kartu
6. **Sidebar** (`vertical.blade.php`) otomatis membaca `MenuService::menuTersedia()` yang di-cache 30 menit

### Dashboard Dinamis

- Dashboard utama (`/dashboard`) wajib membaca widget berdasarkan level user yang login
- Konfigurasi widget dashboard disimpan di tabel `dashboard_widgets` dan relasinya ke level di `dashboard_widget_level`
- Sumber data widget wajib dibatasi ke model yang diizinkan di service, jangan menerima query SQL mentah dari input admin
- Nilai `sumber_data` wajib diambil dari `DashboardService::sumberDataTersedia()` (konfigurasi `konfigurasiSumberSemua()`), bukan dari string bebas user
- Query real-time dashboard wajib dibangun dengan Eloquent Builder + whitelist kolom/filter/operator
- Widget dashboard boleh berupa kartu statistik, daftar data terbaru, atau grafik analisis berbasis komponen card Sneat
- Grafik dashboard wajib menggunakan library yang konsisten dengan paket UI proyek dan tidak boleh menyisipkan script acak per widget
- Konfigurasi grafik dashboard boleh mengatur tipe grafik, tinggi grafik, dan palet warna seri dari halaman admin dashboard
- Layout tampilan mengikuti komponen card Sneat, bukan membuat dashboard kustom di luar pola tema

### Log Aktivitas Otomatis

- Aktivitas user terautentikasi sebaiknya dicatat otomatis dari request aplikasi melalui middleware web
- Request internal seperti `livewire/*`, health check, dan request gagal tidak perlu dicatat sebagai aktivitas user
- Data log aktivitas menjadi salah satu sumber data resmi untuk dashboard dinamis dan laporan aktivitas
- Aksi CRUD penting yang berjalan via Livewire sebaiknya dicatat manual melalui service logging agar tetap masuk ke `log_aktivitas`

### Cache Menu

- Cache key: `menu_user_{userId}`
- Hapus cache setelah update level/mapping: `app(MenuService::class)->hapusCacheLevel($levelId)`
- Hapus cache satu user: `app(MenuService::class)->hapusCache($userId)`

### Cek Izin di Blade / Volt

```blade
@if (auth()->user()->bisaMenu('/admin/levels', 'dapat_buat'))
  {{-- tombol tambah --}}
@endif
```

```php
// Di Volt component
if (! auth()->user()->bisaMenu('/halaman', 'dapat_ubah')) {
    abort(403);
}
```

---

## 4. ATURAN PENULISAN KODE

### Wajib (DO)

- **Bahasa Indonesia** untuk nama variabel, kolom DB, label UI, pesan error/sukses
- Gunakan **Eloquent ORM** — DILARANG query raw string yang menerima input user
- Semua input dari user wajib divalidasi dengan `$this->validate([...])` sebelum diproses
- Form/modal CRUD baru wajib mengikuti pola standar halaman `admin.levels` dan `admin.menus` (struktur sederhana, `modal-dialog-centered`, field dengan grid dasar/`mb-3`, tidak membuat style form yang berbeda sendiri)
- Gunakan `wire:confirm="..."` untuk semua aksi hapus
- Gunakan `wire:loading.attr="disabled" wire:target="namaMethod"` pada semua tombol submit
- Spinner loading: jangan pakai `d-flex` pada elemen `wire:loading` (konflik `!important` Bootstrap)
- Gunakan `firstOrCreate` / `updateOrCreate` di seeder
- Semua route admin wajib dalam group `middleware(['auth'])`
- Untuk dashboard dinamis, query harus dibangun dari whitelist `sumber_data`, `kolom`, `operator`, dan `tipe_query`

### Dilarang (DON'T)

- ❌ Jangan buat query raw: `DB::statement("SELECT * FROM users WHERE name = '$name'")`
- ❌ Jangan hardcode menu di `vertical.blade.php` (selain Dashboard & Settings statis)
- ❌ Jangan gunakan `$guarded = []`
- ❌ Jangan gunakan icon selain Boxicons
- ❌ Jangan buat route GET yang melakukan write ke DB
- ❌ Jangan hapus `@livewireStyles` / `@livewireScripts`
- ❌ Jangan menambah social login OAuth tanpa permintaan eksplisit

---

## 5. SNEAT THEME — KOMPONEN UI

### Card

```blade
<div class="card">
  <div class="card-header"><h5 class="card-title">Judul</h5></div>
  <div class="card-body">...</div>
  <div class="card-footer">...</div>
</div>
```

### Tombol

```blade
<button class="btn btn-primary">         <!-- Aksi utama -->
<button class="btn btn-outline-secondary"> <!-- Batal -->
<button class="btn btn-sm btn-icon btn-text-danger"> <!-- Icon hapus -->
```

### Badge Status

```blade
<span class="badge bg-label-success">Aktif</span>
<span class="badge bg-label-secondary">Nonaktif</span>
<span class="badge bg-label-primary">Info</span>
<span class="badge bg-label-warning">Peringatan</span>
<span class="badge bg-label-danger">Bahaya</span>
```

### Menu Sidebar (Sneat)

```html
<li class="menu-item active">
  <a class="menu-link" href="...">
    <i class="menu-icon tf-icons bx bx-home"></i>
    <div class="text-truncate">Nama Menu</div>
  </a>
</li>
<!-- Sub-menu -->
<li class="menu-item open">
  <a class="menu-link menu-toggle" href="javascript:void(0);">
    <i class="menu-icon tf-icons bx bx-cog"></i>
    <div class="text-truncate">Parent</div>
  </a>
  <ul class="menu-sub">
    <li class="menu-item active">
      <a class="menu-link" href="...">Sub Item</a>
    </li>
  </ul>
</li>
```

### Tabel

```blade
<div class="table-responsive">
  <table class="table table-hover align-middle mb-0">
    <thead class="table-light">...</thead>
    <tbody>...</tbody>
  </table>
</div>
```

---

## 6. LIVEWIRE VOLT — POLA STANDAR

### Single-file Volt component

```php
<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $search = '';

    public function with(): array
    {
        return [
            'data' => Model::query()
                ->when($this->search, fn ($q) => $q->where('kolom', 'like', '%'.$this->search.'%'))
                ->paginate(15),
        ];
    }
};
?>
@section('title', 'Judul Halaman')
<div>
  ...
</div>
```

### Spinner aman (tanpa konflik Bootstrap d-flex)

```blade
<button type="submit" wire:loading.attr="disabled" wire:target="namaMethod">
  <span class="d-flex align-items-center justify-content-center gap-2">
    <span wire:loading.remove wire:target="namaMethod">Simpan</span>
    <span wire:loading wire:target="namaMethod" style="display:none">
      <span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...
    </span>
  </span>
</button>
```

---

## 7. ROUTES

```php
// Semua route halaman dalam wajib di dalam group auth
Route::middleware(['auth'])->group(function () {

    // Admin menu dinamis
    Route::prefix('admin')->name('admin.')->group(function () {
        Volt::route('levels',    'admin.levels.index')->name('levels');
        Volt::route('menus',     'admin.menus.index')->name('menus');
        Volt::route('hak-akses', 'admin.hak-akses.index')->name('hak-akses');
        Volt::route('identitas', 'admin.identitas.index')->name('identitas');
        Volt::route('dashboard', 'admin.dashboard.index')->name('dashboard');
    });

});
```

---

## 8. PERINTAH BERGUNA

```bash
# Jalankan semua migration
php artisan migrate

# Jalankan seeder (Level, Menu, Mapping, User demo)
php artisan db:seed --class=LevelMenuSeeder

# Reset + seed ulang (development only)
php artisan migrate:fresh --seed

# Bersihkan cache view & config
php artisan view:clear && php artisan config:clear && php artisan cache:clear

# Jalankan dev server
php artisan serve
npm run dev
```

---

## 9. AKUN DEMO (setelah seeding)

| Email               | Password   | Level                     |
| ------------------- | ---------- | ------------------------- |
| `superadmin@sio.id` | `password` | Superadmin (penuh)        |
| `admin@sio.id`      | `password` | Admin (anggota + laporan) |
| `anggota@sio.id`    | `password` | Anggota (laporan saja)    |
