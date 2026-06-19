# 🚀 Blueprint Migrasi SIAP V6 (AntiGravity AI Agent Execution)

## 🎯 Tujuan Sistem
Membangun SIAP V6 menggunakan pondasi `imam-starter-kit`. Migrasi menggunakan pendekatan *Data-First Strategy* dengan metode eksekusi *One-Time Dump* dari database legacy (SIAKAD lama) ke database baru yang 100% mematuhi konvensi Laravel.

## ⚠️ Aturan Dasar (Strict Guidelines untuk AI Agent)
Sebelum mengeksekusi langkah apa pun, AI Agent **WAJIB** mematuhi aturan berikut:
1. **Naming Convention (Tabel & Kolom):**
   - Semua tabel baru hasil migrasi wajib menggunakan prefix sesuai klasifikasinya: **Master Data** diberi awalan `m_` (contoh: `m_prodi`) dan **Transaksi Data** diberi awalan `t_` (contoh: `t_krs`).
   - Semua nama kolom wajib menggunakan format `snake_case` standar Laravel (contoh: `ProdiID` -> `prodi_id`, `NmProdi` -> `nm_prodi`).
2. **Tipe Data (Stabilitas API & Schema):**
   - **WAJIB** menggunakan tipe data numerik (`integer`, `boolean`, `unsignedBigInteger`) untuk data referensi ID, relasi (*Foreign Key*), dan status (contoh: status aktif = 1/0). *Payload* JSON saat integrasi API harus berupa angka murni, bukan *string* berawalan kutipan.
   - **WAJIB** menggunakan tipe data `VARCHAR` (`string()`) atau `TEXT` untuk kolom yang memuat karakter seperti "Nama", "Logo", "Foto", atau "Deskripsi". Jangan gunakan `INT` untuk tipe data visual/teks.
3. **Integritas Migrasi & Anchor:** Wajib menyertakan kolom `id_legacy` (tipe menyesuaikan PK sistem lama, *nullable*) di setiap tabel *migration* baru sebagai jangkar relasi data historis.
4. **Memory Management:** Skrip *command* impor wajib memiliki `set_time_limit(0)` dan `DB::connection()->disableQueryLog()`.
5. **Engine Migrasi Dinamis (ETL Dinamis):**
   - Proses pembuatan tabel dan transfer data dikendalikan secara dinamis menggunakan engine migrasi (`MigrasiDatabaseService` dan command `import:legacy`).
   - AI dilarang keras menulis method import manual (*hardcoded*) untuk setiap tabel di `ImportLegacyData.php`, kecuali untuk tabel dengan relasi/transformasi sangat kompleks yang tidak bisa diselesaikan secara otomatis.
   - Skema tabel dibuat secara otomatis dari UI/Service yang melahirkan berkas migrasi Laravel baru di folder `database/migrations/`.
6. **Catatan Progress & Walkthrough:**
   - Segala bentuk perubahan kode, hasil testing, dan petunjuk operasional wajib dicatat dan diakumulasikan ke dalam satu file catatan: [**`dokumen/walkthrough.md`**](file:///d:/PROJECT/WWW/COMPANY/pranata/SIAP_60/dokumen/walkthrough.md).
   - Folder `/dokumen` telah didaftarkan di `.gitignore` agar tidak ter-commit ke dalam repositori Git.
7. **Standarisasi Role-Based Access Control (RBAC):**
   - Seluruh batasan hak akses (Tambah, Ubah, Hapus) pada setiap antarmuka wajib mengacu sepenuhnya pada aturan level di sistem *menu & level* (`/admin/hak-akses`).
   - **DILARANG KERAS** mengimplementasikan fitur individual seperti "Kunci Data" (*Locked Data*) atau parameter pembatas kustom di dalam form komponen. 
   - Validasi aksi selalu dilakukan menggunakan fungsi standar: `auth()->user()?->bisaMenu('/url/path', 'dapat_buat')`, `dapat_ubah`, atau `dapat_hapus`.

---

## 🛠️ Step-by-Step Execution Plan

### Step 1: Inisialisasi Environment & Repositori
**Tugas AI Agent:**
1. Lakukan *cloning* dari repositori *starter kit* ke folder `SIAP_60`.
2. Lakukan instalasi dependensi.
3. Siapkan konfigurasi Dual-Database.

**Terminal Commands:**
```bash
git clone https://github.com/imamzaenuddin/imam-starter-kit.git SIAP_60
cd SIAP_60
composer install
npm install
cp .env.example .env
php artisan key:generate
```

---

### Step 2: Persiapan Engine ETL (One-Time Dump)

**Tujuan:** Membangun mesin ETL yang mampu mengekstrak data dari database legacy, mentransformasinya sesuai konvensi Laravel, dan memuatnya ke database baru secara aman & efisien.

---

#### 2.1 — Konfigurasi Dual-Database (`.env`)

Tambahkan koneksi kedua untuk database legacy di file `.env`:

```env
# === Database Baru (Target) ===
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siap_v6
DB_USERNAME=root
DB_PASSWORD=

# === Database Legacy (Source / Read-Only) ===
DB_LEGACY_HOST=127.0.0.1
DB_LEGACY_PORT=3306
DB_LEGACY_DATABASE=siakad_lama
DB_LEGACY_USERNAME=root
DB_LEGACY_PASSWORD=
```

Daftarkan koneksi `legacy` di `config/database.php`:

```php
// config/database.php — di dalam array 'connections'
'legacy' => [
    'driver'    => 'mysql',
    'host'      => env('DB_LEGACY_HOST', '127.0.0.1'),
    'port'      => env('DB_LEGACY_PORT', '3306'),
    'database'  => env('DB_LEGACY_DATABASE', 'siakad_lama'),
    'username'  => env('DB_LEGACY_USERNAME', 'root'),
    'password'  => env('DB_LEGACY_PASSWORD', ''),
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
    'strict'    => false,
],
```

---

#### 2.2 — Struktur Command ETL: `ImportLegacyData`

File: `app/Console/Commands/ImportLegacyData.php`

**Arsitektur Command:**
- Satu command utama sebagai *orchestrator*.
- Setiap entitas data dikerjakan oleh method `import{Entity}()` masing-masing.
- Urutan eksekusi mengikuti hierarki relasi (induk → anak).

**Skeleton Command (Wajib Diikuti):**

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLegacyData extends Command
{
    protected $signature   = 'import:legacy {--entity= : Nama entitas spesifik, kosong = semua}';
    protected $description = 'One-Time Dump: Migrasi data dari database legacy ke SIAP V6';

    public function handle(): void
    {
        // ✅ ATURAN WAJIB — Memory & Query Log Management
        set_time_limit(0);
        DB::connection()->disableQueryLog();
        DB::connection('legacy')->disableQueryLog();

        $entity = $this->option('entity');

        $this->info('🚀 Memulai proses ETL One-Time Dump...');
        $this->newLine();

        // Pipeline urutan WAJIB: Induk (Level 0) → Anak (Level N)
        $pipeline = [
            'program_studi' => fn() => $this->importProgramStudi(),
            'dosen'         => fn() => $this->importDosen(),
            'mahasiswa'     => fn() => $this->importMahasiswa(),
            // Tambahkan entitas lain di sini mengikuti hierarki relasi
        ];

        if ($entity) {
            if (!array_key_exists($entity, $pipeline)) {
                $this->error("❌ Entitas '{$entity}' tidak ditemukan dalam pipeline.");
                return;
            }
            $pipeline[$entity]();
        } else {
            foreach ($pipeline as $name => $importFn) {
                $this->line("  ➡ Mengimpor: <comment>{$name}</comment>");
                $importFn();
                $this->newLine();
            }
        }

        $this->newLine();
        $this->info('✅ ETL One-Time Dump selesai.');
    }

    // ──────────────────────────────────────────────
    // TEMPLATE METHOD — Wajib diikuti per entitas
    // ──────────────────────────────────────────────

    private function importProgramStudi(): void
    {
        $this->processInChunks(
            legacyTable : 'tb_prodi',
            targetTable : 'program_studis',
            chunkSize   : 500,
            orderByCol  : 'id_prodi',
            transformer : function (object $row): array {
                return [
                    'id_legacy'  => $row->id_prodi,       // ✅ WAJIB
                    'kode'       => $row->kode_prodi,
                    'nama'       => $row->nama_prodi,
                    'is_aktif'   => (int) $row->status,   // ✅ numerik
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        );
    }

    // ──────────────────────────────────────────────
    // CORE ENGINE — Jangan modifikasi tanpa review
    // ──────────────────────────────────────────────

    private function processInChunks(
        string   $legacyTable,
        string   $targetTable,
        int      $chunkSize,
        string   $orderByCol,
        callable $transformer
    ): void {
        $total    = DB::connection('legacy')->table($legacyTable)->count();
        $bar      = $this->output->createProgressBar($total);
        $imported = 0;
        $skipped  = 0;

        $bar->start();

        DB::connection('legacy')
            ->table($legacyTable)
            ->orderBy($orderByCol)
            ->chunk($chunkSize, function ($rows) use (
                $targetTable, $transformer, $bar, &$imported, &$skipped
            ) {
                $batch = [];
                foreach ($rows as $row) {
                    try {
                        $record = $transformer($row);
                        $exists = DB::table($targetTable)
                            ->where('id_legacy', $record['id_legacy'])
                            ->exists();

                        if (!$exists) {
                            $batch[] = $record;
                            $imported++;
                        } else {
                            $skipped++;
                        }
                    } catch (\Throwable $e) {
                        $this->warn('  ⚠ Baris dilewati: ' . $e->getMessage());
                        $skipped++;
                    }
                }

                if (!empty($batch)) {
                    DB::table($targetTable)->insert($batch);
                }

                $bar->advance(count($rows));
            });

        $bar->finish();
        $this->newLine();
        $this->line("     ✅ Imported: <info>{$imported}</info> | ⏭ Skipped: <comment>{$skipped}</comment>");
    }
}
```

---

#### 2.3 — Urutan Eksekusi Pipeline (Hierarki Relasi)

```
Level 0 (Induk / No FK)
├── program_studis
├── fakultas
└── tahun_akademiks

Level 1 (FK ke Level 0)
├── dosens
└── kelas

Level 2 (FK ke Level 1)
└── mahasiswas

Level 3 (FK ke Level 2 — Transaksional)
├── nilai
├── krs
└── pembayaran
```

> **Aturan:** Selalu jalankan impor dari Level 0 → Level N.

---

#### 2.4 — Terminal Commands (Step 2)

```bash
# Impor semua entitas (urutan otomatis sesuai pipeline)
php artisan import:legacy

# Impor entitas spesifik saja
php artisan import:legacy --entity=program_studi

# Dry-run: cek koneksi legacy tanpa insert
php artisan tinker
# >>> DB::connection('legacy')->table('tb_prodi')->count();
```

**✅ Checklist Step 2:**
- [ ] Koneksi `legacy` terdaftar di `config/database.php`
- [ ] Variabel `DB_LEGACY_*` tersedia di `.env`
- [ ] `ImportLegacyData.php` berisi `set_time_limit(0)` & `disableQueryLog()`
- [ ] Setiap method `import{Entity}()` menggunakan `processInChunks()`
- [ ] Kolom `id_legacy` tersedia di setiap tabel target
- [ ] Pipeline diurutkan sesuai hierarki relasi (induk → anak)

---

### Step 2.5: Template Seeder imam-starter-kit

**Tujuan:** Mendefinisikan pola baku pembuatan Seeder untuk entitas baru SIAP V6, mengikuti konvensi yang sudah ada di imam-starter-kit.

---

#### 2.5.1 — Anatomi Seeder imam-starter-kit

Berdasarkan seeder yang sudah ada, terdapat dua pola utama:

| Pola | Digunakan Untuk | Contoh File |
|------|----------------|-------------|
| **`firstOrCreate`** | Data master/referensi (bisa berulang dijalankan) | `LevelMenuSeeder`, `BahasaSeeder` |
| **`updateOrCreate`** | Data konfigurasi tunggal (singleton record) | `IdentitasSeeder`, `DashboardWidgetSeeder` |

---

#### 2.5.2 — Template Seeder untuk Entitas Master Baru

Gunakan template ini untuk setiap entitas master SIAP V6:

**File:** `database/seeders/{NamaEntitas}Seeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\{NamaModel};      // Ganti dengan model yang sesuai
use Illuminate\Database\Seeder;

class {NamaEntitas}Seeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Definisikan data master di sini
            // Contoh:
            [
                'kode'       => 'S1-TI',
                'nama'       => 'Teknik Informatika',
                'is_aktif'   => 1,   // ✅ numerik, bukan 'true'/'false'
                'id_legacy'  => null, // nullable — diisi oleh ETL
            ],
            [
                'kode'       => 'S1-SI',
                'nama'       => 'Sistem Informasi',
                'is_aktif'   => 1,
                'id_legacy'  => null,
            ],
        ];

        foreach ($data as $item) {
            {NamaModel}::firstOrCreate(
                ['kode' => $item['kode']],   // kunci unik pencarian
                $item                         // nilai jika belum ada
            );
        }

        $this->command->info('✅ {NamaEntitas}Seeder: ' . count($data) . ' record diproses.');
    }
}
```

---

#### 2.5.3 — Template Seeder Singleton (Konfigurasi Tunggal)

Gunakan pola `updateOrCreate` untuk data yang hanya boleh ada satu baris (konfigurasi aplikasi):

```php
<?php

namespace Database\Seeders;

use App\Models\{NamaModel};
use Illuminate\Database\Seeder;

class {NamaKonfigurasi}Seeder extends Seeder
{
    public function run(): void
    {
        {NamaModel}::updateOrCreate(
            ['id' => 1],   // selalu id = 1 untuk singleton
            [
                'nama_aplikasi'  => 'SIAP V6',
                'singkatan'      => 'SIAP',
                'versi'          => '6.0.0',
                'main_color'     => '#696cff',
                // ... kolom lain
                'is_active'      => 1, // ✅ numerik
            ]
        );

        $this->command->info('✅ {NamaKonfigurasi}Seeder: konfigurasi diperbarui.');
    }
}
```

---

#### 2.5.4 — Template Seeder dengan Relasi (Level & Menu)

Untuk seeder yang melibatkan relasi antar entitas (mengikuti pola `LevelMenuSeeder`):

```php
<?php

namespace Database\Seeders;

use App\Models\ParentModel;
use App\Models\ChildModel;
use Illuminate\Database\Seeder;

class {Parent}{Child}Seeder extends Seeder
{
    public function run(): void
    {
        // ============================
        // 1. ENTITAS INDUK (Level 0)
        // ============================
        $indukA = ParentModel::firstOrCreate(
            ['kode' => 'A'],
            [
                'nama'     => 'Nama Induk A',
                'is_aktif' => 1,
            ]
        );

        $indukB = ParentModel::firstOrCreate(
            ['kode' => 'B'],
            [
                'nama'     => 'Nama Induk B',
                'is_aktif' => 1,
            ]
        );

        // ============================
        // 2. ENTITAS ANAK (Level 1)
        // ============================
        $anakData = [
            ['parent_kode' => 'A', 'kode' => 'A1', 'nama' => 'Anak A1'],
            ['parent_kode' => 'A', 'kode' => 'A2', 'nama' => 'Anak A2'],
            ['parent_kode' => 'B', 'kode' => 'B1', 'nama' => 'Anak B1'],
        ];

        $parents = ParentModel::pluck('id', 'kode'); // lookup map

        foreach ($anakData as $item) {
            ChildModel::firstOrCreate(
                ['kode' => $item['kode']],
                [
                    'parent_model_id' => $parents[$item['parent_kode']],
                    'nama'            => $item['nama'],
                    'is_aktif'        => 1,  // ✅ numerik
                    'id_legacy'       => null,
                ]
            );
        }

        $this->command->info('✅ Seeder relasi selesai.');
    }
}
```

---

#### 2.5.5 — Daftarkan Seeder ke DatabaseSeeder

Setiap seeder baru WAJIB didaftarkan di `database/seeders/DatabaseSeeder.php` dengan urutan hierarki:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // ── Seeder Bawaan imam-starter-kit (JANGAN UBAH URUTAN) ──
            LevelMenuSeeder::class,
            BahasaSeeder::class,
            IdentitasSeeder::class,
            LogAktivitasSeeder::class,
            DashboardWidgetSeeder::class,
            ChatAiSeeder::class,
            ImamStarterKitContentSeeder::class,

            // ── Seeder SIAP V6 (urutan: induk → anak) ──
            // ProgramStudiSeeder::class,   // Level 0
            // FakultasSeeder::class,        // Level 0
            // TahunAkademikSeeder::class,   // Level 0
            // DosenSeeder::class,           // Level 1
            // MahasiswaSeeder::class,       // Level 2
        ]);
    }
}
```

---

#### 2.5.6 — Aturan Tambahan Seeder SIAP V6

| Aturan | Detail |
|--------|--------|
| **Idempoten** | Selalu gunakan `firstOrCreate` atau `updateOrCreate`, **tidak boleh** `create()` langsung |
| **Tipe numerik** | Status aktif: `1`/`0` bukan `true`/`false` atau `'aktif'`/`'nonaktif'` |
| **Kolom `id_legacy`** | Selalu `null` di seeder; diisi saat ETL dump berjalan |
| **Feedback CLI** | Akhiri setiap `run()` dengan `$this->command->info('✅ ...')` |
| **Satu file per entitas** | Jangan menggabungkan dua entitas tidak berkaitan dalam satu seeder |

---

**✅ Checklist Step 2.5:**
- [ ] Template seeder sudah dipahami (3 pola: master, singleton, relasi)
- [ ] Setiap seeder baru menggunakan `firstOrCreate` atau `updateOrCreate`
- [ ] Kolom `id_legacy` selalu ada di data seeder (nilai `null`)
- [ ] Status aktif selalu numerik (`1`/`0`)
- [ ] Seeder baru didaftarkan di `DatabaseSeeder` dengan urutan hierarki
- [ ] Seeder bawaan imam-starter-kit tidak diubah atau dihapus

---

---

# 🗺️ Petunjuk Teknis Migrasi — 3 Fase Eksekusi

> Dokumen ini adalah panduan eksekusi wajib bagi AI Agent. Setiap fase harus diselesaikan dan diverifikasi sebelum melanjutkan ke fase berikutnya.

---

## 🔵 FASE 1: Persiapan Alat Migrasi (ETL Engine)

**Tujuan:** Membangun modul Extract, Transform, Load (ETL) menggunakan ekosistem Laravel melalui Custom Artisan Command. Alat ini bertugas menarik data dari database legacy (`sia`), memformat ulangnya, dan menyuntikkannya ke database baru (`siap60`).

### F1.1 — Konteks Database

| Parameter | Nilai Aktif |
|-----------|-------------|
| **DB Target (baru)** | `siap60` (koneksi: `mysql`) |
| **DB Source (legacy)** | `sia` (koneksi: `legacy`) |
| **Host** | `127.0.0.1:3306` |
| **Konvensi tabel legacy** | Prefix `m_***` (master) dan `t_***` (transaksi) |
| **Konvensi tabel baru** | Jamak `snake_case`, tanpa prefix |

### F1.2 — Komponen ETL yang Harus Dibangun

```
app/Console/Commands/
└── ImportLegacyData.php      ← Orchestrator utama (sudah dibuat)

Pola eksekusi:
php artisan import:legacy                      # semua entitas
php artisan import:legacy --entity=<nama>      # entitas spesifik
php artisan import:legacy --fase=master        # khusus Fase 2
php artisan import:legacy --fase=transaksi     # khusus Fase 3
```

### F1.3 — Prinsip Teknis ETL (Non-Negotiable)

| Prinsip | Implementasi |
|---------|-------------|
| **Memory-safe** | `set_time_limit(0)` + `disableQueryLog()` di kedua koneksi |
| **Chunk-based** | `->chunk(500, ...)` — jangan `->get()` massal |
| **Idempoten** | Cek duplikat via `id_legacy` sebelum insert |
| **Error-tolerant** | Setiap baris dibungkus `try/catch(\Throwable)` |
| **Audit trail** | Kolom `id_legacy` di semua tabel target sebagai jangkar |
| **Lookup map** | Gunakan `->pluck('id', 'id_legacy')` untuk resolve FK |

### F1.4 — Struktur Lookup Map (Resolve FK antar DB)

Karena ID di database lama tidak sama dengan ID di database baru, gunakan teknik **lookup map** untuk menyelesaikan Foreign Key:

```php
// Contoh: resolve id program studi dari legacy ke ID baru
$mapProdi = DB::table('program_studis')
    ->whereNotNull('id_legacy')
    ->pluck('id', 'id_legacy');  // [ id_legacy => id_baru ]

// Penggunaan di transformer:
'program_studi_id' => $mapProdi[$row->kd_prodi] ?? null,
```

> **Aturan:** Selalu buat lookup map dari tabel **yang sudah diimpor lebih dulu** (Level 0) sebelum mengimpor entitas yang bergantung padanya (Level 1+).

### F1.5 — Checklist Fase 1

- [ ] `ImportLegacyData.php` selesai dengan `--fase` option
- [ ] Koneksi `legacy` aktif & terverifikasi via `php artisan tinker`
- [ ] Core engine `processInChunks()` telah diimplementasi
- [ ] Pola lookup map dipahami dan siap digunakan

---

## 🟡 FASE 2: Analisis & Pemetaan Data Master (`m_***`)

**Tujuan:** Menganalisis tabel-tabel referensi (Data Master) dari sistem lama dan mengimpornya ke skema baru yang **kompatibel dengan standar Neo Feeder PDDikti**.

### F2.1 — Identifikasi Tabel Master Legacy

Tabel master di DB `sia` menggunakan prefix `m_***`. Sebelum membuat migration, **wajib analisis** setiap tabel dengan query berikut:

```sql
-- Jalankan di DB legacy (sia) untuk audit struktur tabel master
DESCRIBE m_prodi;
DESCRIBE m_jenjang;
DESCRIBE m_status_mahasiswa;
-- dst untuk setiap tabel m_***
```

**Analisis wajib mencakup:**
1. Nama kolom dan tipe data asli
2. Panjang karakter aktual vs. standar Neo Feeder PDDikti
3. Kolom yang perlu dipecah, digabung, atau dinormalisasi
4. Relasi antar tabel master

### F2.2 — Standar Skema Neo Feeder PDDikti

Saat membuat Migration untuk tabel master baru, sesuaikan dengan standar berikut:

| Tipe Kolom | Standar Neo Feeder | Implementasi Laravel |
|------------|-------------------|---------------------|
| Kode Prodi | `VARCHAR(5)` | `$table->string('kode', 5)` |
| Nama Prodi | `VARCHAR(255)` | `$table->string('nama', 255)` |
| Jenjang | `VARCHAR(2)` (D3, S1, S2) | `$table->string('jenjang', 2)` |
| NIM | `VARCHAR(10)` | `$table->string('nim', 10)` |
| NIDN | `VARCHAR(10)` | `$table->string('nidn', 10)` |
| Status (enum) | `TINYINT` | `$table->tinyInteger('status')` |
| Tanggal | `DATE` | `$table->date('tgl_lahir')->nullable()` |
| Boolean | `TINYINT(1)` | `$table->boolean('is_aktif')->default(1)` |

### F2.3 — Template Migration Tabel Master

Setiap tabel master WAJIB mengikuti template ini:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_studis', function (Blueprint $table) {
            // ── Primary Key ──
            $table->id();

            // ── Kolom Legacy Anchor (WAJIB ada) ──
            $table->string('id_legacy')->nullable()->index()
                  ->comment('PK dari tabel m_prodi di DB sia');

            // ── Kolom Data (sesuai standar Neo Feeder) ──
            $table->string('kode', 5)->unique()->comment('Kode Prodi PDDikti');
            $table->string('nama', 255);
            $table->string('jenjang', 2)->comment('D3/S1/S2/S3');
            $table->unsignedBigInteger('fakultas_id')->nullable();

            // ── Status (WAJIB numerik) ──
            $table->boolean('is_aktif')->default(1);

            // ── Timestamps & FK ──
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fakultas_id')
                  ->references('id')->on('fakultas')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_studis');
    }
};
```

### F2.4 — Pipeline Impor Master (Urutan Wajib)

Ikuti urutan ini **tanpa pengecualian** — entitas induk harus ada sebelum entitas anak diimpor:

```
┌─────────────────────────────────────────────────────┐
│ FASE 2 — Data Master (m_***)                        │
├──────────┬──────────────────┬───────────────────────┤
│ Level    │ Tabel Legacy     │ Tabel Target           │
├──────────┼──────────────────┼───────────────────────┤
│ Level 0  │ m_jenjang        │ jenjangs               │
│ Level 0  │ m_fakultas       │ fakultas               │
│ Level 0  │ m_agama          │ agamas                 │
│ Level 0  │ m_kota           │ kotas                  │
│ Level 0  │ m_negara         │ negaras                │
│ Level 0  │ m_tahun_akademik │ tahun_akademiks        │
├──────────┼──────────────────┼───────────────────────┤
│ Level 1  │ m_prodi          │ program_studis         │
│ Level 1  │ m_status_mhs     │ status_mahasiswas      │
│ Level 1  │ m_jalur_masuk    │ jalur_masuks           │
├──────────┼──────────────────┼───────────────────────┤
│ Level 2  │ m_dosen          │ dosens                 │
│ Level 2  │ m_kurikulum      │ kurikulums             │
└──────────┴──────────────────┴───────────────────────┘
```

> ⚠️ **Catatan:** Daftar tabel di atas adalah template umum. AI Agent WAJIB menyesuaikan dengan `DESCRIBE m_***` hasil analisis tabel aktual dari DB `sia`.

### F2.5 — Template Method Impor Master

```php
private function importProgramStudi(): void
{
    // 1. Buat lookup map dari entitas induk yang sudah ada
    $mapFakultas = DB::table('fakultas')
        ->whereNotNull('id_legacy')
        ->pluck('id', 'id_legacy');

    $this->processInChunks(
        legacyTable : 'm_prodi',
        targetTable : 'program_studis',
        chunkSize   : 500,
        orderByCol  : 'kd_prodi',
        transformer : function (object $row) use ($mapFakultas): array {
            return [
                // ── Anchor ──
                'id_legacy'    => $row->kd_prodi,

                // ── Data (sesuai Neo Feeder) ──
                'kode'         => $row->kd_prodi,
                'nama'         => $row->nm_prodi,
                'jenjang'      => $row->jenjang,    // D3, S1, dst

                // ── Resolve FK via lookup map ──
                'fakultas_id'  => $mapFakultas[$row->kd_fak] ?? null,

                // ── Status numerik ──
                'is_aktif'     => (int) ($row->status ?? 1),

                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }
    );
}
```

### F2.6 — Checklist Fase 2

- [ ] Semua tabel `m_***` di DB `sia` sudah diidentifikasi & dianalisis
- [ ] Skema tabel baru sudah disesuaikan dengan standar Neo Feeder PDDikti
- [ ] Migration untuk semua tabel master sudah dibuat & dijalankan
- [ ] Kolom `id_legacy` ada di semua tabel target
- [ ] Method `import{Master}()` sudah ditulis untuk setiap entitas
- [ ] Impor dijalankan sesuai urutan Level 0 → Level 2
- [ ] Verifikasi: `DB::table('program_studis')->count()` mengembalikan jumlah yang benar

---

## 🔴 FASE 3: Transformasi & Normalisasi Data Transaksi (`t_***`)

**Tujuan:** Mengimpor data dinamis/transaksional dari tabel `t_***` legacy. Berbeda dengan Fase 2, proses ini **mentransformasikan** data — memecah satu tabel lama menjadi beberapa tabel relasional di sistem baru sambil mempertahankan integritas Foreign Key.

### F3.1 — Konsep Utama: Satu → Banyak (Table Splitting)

Di sistem lama, data mahasiswa sering ditumpuk dalam satu tabel besar. Di SIAP V6, data tersebut **dipecah** menjadi entitas yang terstandardisasi:

```
LEGACY (t_mahasiswa — 1 tabel)
├── id_mhs, nm_mhs, tgl_lahir, ...        ← data personal
├── kd_prodi, angkatan, nim, ...           ← data akademik
├── tgl_daftar, jalur_masuk, no_reg, ...  ← data PMB
└── status_mhs, tgl_lulus, ...            ← data kelulusan

        ↓ TRANSFORMASI (FASE 3)

TARGET (4 tabel relasional)
├── peserta          ← data personal calon mahasiswa
├── pmb              ← data penerimaan mahasiswa baru
├── mahasiswas       ← data akademik mahasiswa aktif
└── riwayat_status   ← riwayat perubahan status
```

### F3.2 — Aturan Transformasi & Normalisasi

| Aturan | Detail |
|--------|--------|
| **FK harus valid** | Setiap FK ke tabel master harus diselesaikan via lookup map dari Fase 2 |
| **Nullable FK** | Jika lookup gagal (data kotor), simpan sebagai `null` bukan error |
| **Data ganda** | Jika satu baris legacy menghasilkan beberapa baris baru, proses dalam satu transformer |
| **Urutan insert** | Insert tabel induk dulu, simpan ID-nya, baru insert tabel anak |
| **Audit splitting** | Semua tabel hasil pecahan tetap menyimpan `id_legacy` yang sama (referensi ke baris asli) |

### F3.3 — Template Method Transformasi (Splitting)

Gunakan pola ini untuk memecah satu baris legacy menjadi beberapa tabel:

```php
private function importMahasiswa(): void
{
    // Lookup map dari Fase 2 (WAJIB sudah diimpor lebih dulu)
    $mapProdi    = DB::table('program_studis')->pluck('id', 'id_legacy');
    $mapJalur    = DB::table('jalur_masuks')->pluck('id', 'id_legacy');
    $mapAgama    = DB::table('agamas')->pluck('id', 'id_legacy');

    set_time_limit(0);

    $total   = DB::connection('legacy')->table('t_mahasiswa')->count();
    $bar     = $this->output->createProgressBar($total);
    $bar->start();

    DB::connection('legacy')
        ->table('t_mahasiswa')
        ->orderBy('id_mhs')
        ->chunk(200, function ($rows) use ($mapProdi, $mapJalur, $mapAgama, $bar) {

            foreach ($rows as $row) {
                try {
                    DB::transaction(function () use ($row, $mapProdi, $mapJalur, $mapAgama) {

                        // ── STEP 1: Insert ke tabel `peserta` (data personal) ──
                        $pesertaId = DB::table('peserta')->insertGetId([
                            'id_legacy'   => $row->id_mhs,
                            'nama_lengkap'=> $row->nm_mhs,
                            'tgl_lahir'   => $row->tgl_lahir,
                            'jenis_kel'   => $row->jk === 'L' ? 1 : 2, // ✅ numerik
                            'agama_id'    => $mapAgama[$row->kd_agama] ?? null,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);

                        // ── STEP 2: Insert ke tabel `pmb` (data PMB) ──
                        $pmbId = DB::table('pmb')->insertGetId([
                            'id_legacy'      => $row->id_mhs,
                            'peserta_id'     => $pesertaId,
                            'no_registrasi'  => $row->no_reg,
                            'tgl_daftar'     => $row->tgl_daftar,
                            'jalur_masuk_id' => $mapJalur[$row->kd_jalur] ?? null,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);

                        // ── STEP 3: Insert ke tabel `mahasiswas` (data akademik) ──
                        DB::table('mahasiswas')->insert([
                            'id_legacy'        => $row->id_mhs,
                            'peserta_id'       => $pesertaId,
                            'pmb_id'           => $pmbId,
                            'nim'              => $row->nim,
                            'program_studi_id' => $mapProdi[$row->kd_prodi] ?? null,
                            'angkatan'         => (int) $row->angkatan,
                            'is_aktif'         => (int) ($row->status === 'A'),
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);

                    }); // end transaction

                } catch (\Throwable $e) {
                    $this->warn("  ⚠ id_mhs={$row->id_mhs}: " . $e->getMessage());
                }
            }

            $bar->advance(count($rows));
        });

    $bar->finish();
    $this->newLine();
    $this->info('✅ Impor mahasiswa selesai (peserta + pmb + mahasiswas).');
}
```

### F3.4 — Teknik: `DB::transaction()` untuk Atomik Splitting

Setiap proses splitting WAJIB dibungkus dalam `DB::transaction()` untuk memastikan:
- Jika insert `peserta` berhasil tapi insert `mahasiswas` gagal → semua di-rollback
- Tidak ada data orphan (peserta tanpa mahasiswa)

```php
// Pattern wajib untuk splitting multi-tabel:
DB::transaction(function () use ($row, $maps) {
    $parentId = DB::table('tabel_induk')->insertGetId([...]);
    DB::table('tabel_anak')->insert(['parent_id' => $parentId, ...]);
});
```

### F3.5 — Pipeline Impor Transaksi (Urutan Wajib)

```
┌─────────────────────────────────────────────────────────────────┐
│ FASE 3 — Data Transaksi (t_***)                                 │
├──────────┬────────────────────┬─────────────────────────────────┤
│ Level    │ Tabel Legacy       │ Tabel Target (Hasil Pecahan)    │
├──────────┼────────────────────┼─────────────────────────────────┤
│ Level 3  │ t_mahasiswa        │ peserta, pmb, mahasiswas        │
│ Level 4  │ t_krs              │ krs, krs_details                │
│ Level 4  │ t_nilai            │ nilai_mahasiswas                 │
│ Level 4  │ t_pembayaran       │ pembayarans                     │
│ Level 5  │ t_transkrip        │ transkripts, transkrip_details  │
└──────────┴────────────────────┴─────────────────────────────────┘
```

> ⚠️ **Fase 3 hanya boleh dijalankan setelah Fase 2 selesai 100%.** Semua lookup map harus sudah terisi.

### F3.6 — Verifikasi Integritas Pasca-Impor

Jalankan query berikut untuk memverifikasi integritas FK setelah Fase 3:

```sql
-- Cek mahasiswas dengan FK null (data kotor tidak termapping)
SELECT COUNT(*) as orphan
FROM mahasiswas
WHERE program_studi_id IS NULL;

-- Cek peserta tanpa mahasiswa (partial split yang gagal)
SELECT COUNT(*) as orphan
FROM peserta p
LEFT JOIN mahasiswas m ON m.peserta_id = p.id
WHERE m.id IS NULL;

-- Cek total record vs. DB legacy
SELECT 'legacy' as sumber, COUNT(*) as total FROM sia.t_mahasiswa
UNION
SELECT 'baru', COUNT(*) FROM siap60.mahasiswas;
```

### F3.7 — Checklist Fase 3

- [ ] **Prasyarat:** Fase 2 sudah selesai 100% dan terverifikasi
- [ ] Semua tabel `t_***` di DB `sia` sudah diidentifikasi & dianalisis
- [ ] Skema tabel target (hasil pecahan) sudah dibuat via Migration
- [ ] Method `import{Transaksi}()` menggunakan `DB::transaction()` untuk splitting
- [ ] Lookup map dari Fase 2 digunakan untuk resolve semua FK
- [ ] Verifikasi integritas: query orphan mengembalikan `0`
- [ ] Total record di tabel baru sesuai dengan total di DB legacy

---

## 📊 Ringkasan 3 Fase Migrasi

```
FASE 1 ──→ FASE 2 ──→ FASE 3
  ETL       Master     Transaksi
  Engine   (m_***)    (t_***)
  
  [Setup]  [Import]   [Transform]
  [Done✓]  [Pending]  [Pending]
```

| Fase | Fokus | Command | Prasyarat |
|------|-------|---------|-----------|
| **Fase 1** | Bangun ETL Engine | `make:command` | Step 1 selesai |
| **Fase 2** | Impor data master `m_***` | `import:legacy --fase=master` | Fase 1 selesai |
| **Fase 3** | Transformasi data transaksi `t_***` | `import:legacy --fase=transaksi` | Fase 2 selesai 100% |