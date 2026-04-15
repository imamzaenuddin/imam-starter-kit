# Roadmap Implementasi Starter Kit (Point per Point)

Dokumen ini menyusun implementasi fitur lanjutan secara bertahap sesuai aturan di Copilot Instructions proyek SIO:

- Bahasa Indonesia untuk variabel, label, pesan.
- Eloquent ORM, tanpa raw query berbahaya dari input user.
- Pola Livewire Volt single-file, layout Sneat, Boxicons.
- Route admin dalam middleware auth.
- UI mengikuti komponen card/table/button standar Sneat.

## Fase 1 (Prioritas Tinggi)

### 1. Audit Log Viewer Lengkap

Status: [x] Selesai

Tujuan:

- Menyediakan viewer log aktivitas yang bisa difilter dan diexport.

Deliverable:

- Filter: user, modul, metode, rentang tanggal, kata kunci.
- Tabel dengan pagination + sorting waktu.
- Export CSV untuk data hasil filter.

Implementasi Teknis:

- Halaman Volt: resources/views/livewire/laporan/aktivitas/index.blade.php
- Service export: app/Services/LogAktivitasService.php
- Validasi filter wajib di komponen Volt.

Kriteria Selesai:

- Filter bekerja dan konsisten dengan data log_aktivitas.
- Export CSV mengikuti filter aktif.

---

### 2. Role & Permission Granular (Aksi Sensitif)

Status: [x] Selesai

Tujuan:

- Hak akses tidak hanya menu, tapi juga aksi sensitif (backup, restore, delete massal).

Deliverable:

- Permission aksi granular di level menu/aksi.
- Guard pada method Volt sebelum aksi dijalankan.

Implementasi Teknis:

- Tambah mapping izin aksi penting di level_menu.
- Cek izin sebelum method: backup, restore, hapus backup.

Kriteria Selesai:

- User tanpa izin selalu mendapatkan 403.
- Log aktivitas mencatat percobaan aksi sensitif jika diperlukan.

---

### 3. Backup Scheduler + Retensi Otomatis

Status: [x] Selesai

Tujuan:

- Backup otomatis terjadwal dan pembersihan file lama.

Deliverable:

- Perintah artisan backup terjadwal (harian/mingguan).
- Kebijakan retensi (misal 30 hari).
- Notifikasi hasil backup (log aktivitas).

Implementasi Teknis:

- Command: app/Console/Commands/\*
- Scheduler: routes/console.php
- Service: app/Services/BackupRestoreService.php

Kriteria Selesai:

- Jadwal dapat dieksekusi via scheduler.
- File lama terhapus sesuai retensi.

---

### 4. Restore Safety Mode

Status: [x] Selesai

Tujuan:

- Mengurangi risiko saat restore data.

Deliverable:

- Konfirmasi password (sudah ada) tetap dipertahankan.
- Opsi auto-backup sebelum restore.
- Mode maintenance sementara saat restore berjalan.

Implementasi Teknis:

- Tambah flow pre-restore backup di BackupRestoreService.
- Gunakan lock sederhana untuk cegah restore paralel.

Kriteria Selesai:

- Restore membuat backup safeguard terlebih dahulu.
- Proses restore tidak bentrok antar request.

## Fase 2 (Operasional Admin)

Status terkini:

- [x] 5. User Management Lengkap
- [x] 6. Notification Center
- [x] 7. Media/File Manager

### 5. User Management Lengkap

Tujuan:

- Admin bisa kelola akun secara penuh.

Deliverable:

- CRUD user, aktivasi/nonaktif, reset password admin-side.
- Assign level user + filter data user.

Implementasi Teknis:

- Halaman Volt baru di resources/views/livewire/admin/users/index.blade.php
- Validasi kuat untuk email unik dan password.

Kriteria Selesai:

- Alur tambah/edit/hapus user berjalan sesuai izin.

---

### 6. Notification Center

Tujuan:

- Menampilkan notifikasi event penting di aplikasi.

Deliverable:

- Notifikasi in-app (read/unread).
- Sumber event: backup selesai, restore, perubahan penting.

Implementasi Teknis:

- Tabel notifikasi + model + service.
- UI dropdown/notifikasi list pada navbar.

Kriteria Selesai:

- Notifikasi muncul real-time sederhana (polling/livewire refresh).

Catatan Implementasi:

- Event backup selesai, restore selesai/gagal, dan perubahan data penting sudah terhubung ke `NotifikasiService`.
- Dropdown notifikasi navbar menerima refresh melalui event `notifikasi:baru`.

---

### 7. Media/File Manager

Tujuan:

- Pengelolaan file upload lebih terstruktur.

Deliverable:

- Daftar file, ukuran, tanggal upload, hapus aman.
- Filter berdasarkan kategori (logo/profil/dokumen).

Implementasi Teknis:

- Gunakan storage disk public.
- Pastikan validasi path dan proteksi traversal.

Kriteria Selesai:

- File manager tidak dapat menghapus file di luar root upload.

## Fase 3 (Analitik & Data)

Status terkini:

- [x] 8. Dashboard Widget Builder Advanced
- [x] 9. Import/Export Data Master

### 8. Dashboard Widget Builder Advanced

Tujuan:

- Widget lebih fleksibel untuk analisis.

Deliverable:

- Komparasi periode, target KPI, threshold warna.
- Cache per widget untuk performa.

Implementasi Teknis:

- Perluasan DashboardService dan konfigurasi widget.
- Tetap whitelist sumber_data/kolom/operator.

Kriteria Selesai:

- Widget tetap aman dan cepat saat query.

---

### 9. Import/Export Data Master

Tujuan:

- Memudahkan migrasi data referensi.

Deliverable:

- Import CSV/XLSX data master (m\_\*) dengan laporan error baris.
- Export data master sesuai filter.

Implementasi Teknis:

- Service importer/exporter + validasi ketat.
- Gunakan transaksi DB untuk konsistensi.

Kriteria Selesai:

- Import menampilkan ringkasan sukses/gagal dengan jelas.

## Fase 4 (Hardening Produksi)

Status terkini:

- [x] 10. Setting App Runtime Terpusat
- [x] 11. Keamanan Tambahan
- [x] 12. Testing & CI Minimum

### 10. Setting App Runtime Terpusat

Tujuan:

- Konfigurasi aplikasi dari admin panel.

Deliverable:

- Setting timezone, locale default, batas upload, pagination.
- Cache setting + tombol refresh cache.

Kriteria Selesai:

- Perubahan setting langsung berdampak tanpa edit manual file env.

---

### 11. Keamanan Tambahan

Tujuan:

- Meningkatkan keamanan akun dan akses.

Deliverable:

- 2FA opsional untuk superadmin.
- Login attempt monitor dan proteksi brute force.

Kriteria Selesai:

- Superadmin dapat mengaktifkan/menonaktifkan 2FA.

---

### 12. Testing & CI Minimum

Tujuan:

- Menjaga stabilitas perubahan.

Deliverable:

- Feature test untuk auth, permission, backup/restore.
- CI workflow lint + test.

Kriteria Selesai:

- Pull request baru memiliki pengecekan otomatis.

## Urutan Eksekusi Disarankan

1. Audit Log Viewer Lengkap
2. Backup Scheduler + Retensi Otomatis
3. Restore Safety Mode
4. User Management Lengkap
5. Testing & CI Minimum

## Catatan Eksekusi

- Setiap poin dikerjakan sebagai unit kecil dengan commit terpisah.
- Setelah tiap poin: jalankan optimize clear, smoke test UI, lalu validasi izin akses.
- Semua komponen baru wajib mengikuti pola Sneat + Volt yang sudah ada.
