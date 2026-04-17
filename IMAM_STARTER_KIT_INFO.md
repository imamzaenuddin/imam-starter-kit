# IMAM Starter Kit Metadata

## Ringkasan

- Nama proyek: imam-starter-kit
- Versi saat ini: v1.0.1
- Tipe: Laravel 12 + Livewire Volt + Bootstrap 5 starter kit

## Asal Proyek

- Asal template: Sneat Bootstrap Laravel Livewire Starter Kit
- Penyedia template asal: ThemeSelection
- URL asal: https://github.com/themeselection/sneat-bootstrap-laravel-livewire-starter-kit

## Riwayat Pembuatan

- Tanggal pembuatan repository saat ini (commit awal): 2026-04-10
- Commit awal: eb00cff
- Pesan commit awal: Implement mobile menu toggle functionality and enhance navbar structure

## Pengembang

- Pengembang awal repository saat ini (berdasarkan commit awal): smit patel <bhalodiyasmit2409@gmail.com>
- Pengembangan lanjutan/rebranding: tim imam-starter-kit
- URL pengembang/pemilik saat ini: https://github.com/imamzaenuddin/
- URL repository saat ini: https://github.com/imamzaenuddin/imam-starter-kit

## Catatan Rebranding

- Nama identitas proyek dan konfigurasi utama diubah menjadi imam-starter-kit.
- Referensi asal Sneat tetap dipertahankan di dokumen ini sebagai jejak asal template.

## Riwayat Versi

- v1.0.0: baseline starter kit sebelum rebranding identitas imam-starter-kit.
- v1.0.1 (2026-04-13): rebranding konfigurasi, URL GitHub, dokumentasi, dan metadata proyek.

## Lokasi Sumber

- Folder sumber aktif: D:/TMP/sneat-bootstrap-laravel-livewire-starter-kit
- Nama target branding: imam-starter-kit
- Catatan: jika ingin nama folder fisik juga sama, ubah nama folder root menjadi D:/TMP/imam-starter-kit lalu buka ulang workspace.

## Aturan Pengembangan (Untuk Tim & AI)

Mulai versi ini, proyek menerapkan Arsitektur Modular menggunakan `nwidart/laravel-modules`.

**INSTRUKSI KETAT UNTUK AI DAN DEVELOPER:**
1. **Core vs Aplikasi:** Jangan campurkan kode aplikasi ke dalam direktori core bawaan Laravel (`app/`, `resources/views/`, `routes/`). Direktori bawaan hanya untuk update atau perbaikan Starter Kit.
2. **Wajib Modul:** Semua fitur, entitas, dan logika bisnis aplikasi baru **HARUS** dibuat sebagai Modul terpisah di dalam direktori `Modules/` menggunakan perintah `php artisan module:make <NamaModul>`.
3. **Konteks AI:** Saat diminta membuat fitur baru, AI harus selalu:
   - Mengecek apakah modul yang relevan sudah ada.
   - Jika belum, usulkan atau jalankan `php artisan module:make` untuk membuatnya.
   - Menempatkan semua Controller, Model, View, dan Route di dalam direktori `Modules/<NamaModul>/`.
4. **UI/UX Konfirmasi Dialog:** Semua pesan konfirmasi aksi (hapus, reset, eksekusi, dll) pada antarmuka pengguna (UI) **WAJIB** menggunakan SweetAlert (`Swal.fire`). Dilarang keras menggunakan *native* `confirm()` Javascript atau atribut `wire:confirm` bawaan Livewire. Selalu gunakan *Translations* Laravel (contoh: `{{ __('messages.confirm_delete') }}`) untuk teks SweetAlert agar mendukung *multi-language*.

## Contoh Materi Penggunaan KPI di Dashboard

### Tujuan Pembelajaran

1. Peserta memahami konsep KPI dan perbedaannya dengan metrik biasa.
2. Peserta dapat membuat widget KPI di menu Dashboard.
3. Peserta dapat membaca tren naik atau turun dari perbandingan periode.
4. Peserta dapat menentukan ambang warna untuk monitoring cepat.

### Konsep Dasar

1. KPI adalah indikator kinerja utama yang terukur.
2. Setiap KPI harus memiliki komponen berikut:
   - Nama indikator
   - Target numerik
   - Periode evaluasi
   - Sumber data yang jelas
3. Rumus pencapaian KPI:
   - Persentase KPI = (Nilai Aktual / Target) x 100%

### Contoh KPI Organisasi

1. KPI Keanggotaan Aktif
   - Target: 1.000 anggota aktif
   - Aktual bulan ini: 820
   - Pencapaian: 82%
2. KPI Kegiatan Bulanan
   - Target: 12 kegiatan
   - Aktual bulan ini: 9
   - Pencapaian: 75%
3. KPI Respons Laporan
   - Target: maksimal 2 hari
   - Aktual rata-rata: 1,5 hari
   - Status: tercapai

### Praktik Konfigurasi di Dashboard

1. Buka halaman Kelola Dashboard.
2. Klik Tambah Widget.
3. Isi konfigurasi dasar:
   - Nama Widget: Total Anggota Aktif
   - Sumber Data: users
   - Tipe Query: count
   - Tipe Tampilan: statistik
4. Isi konfigurasi KPI:
   - Target KPI: 1000
   - Tampilkan Progress Bar: aktif
   - Bandingkan Periode: aktif
   - Bandingkan Dengan: bulan_lalu
5. Atur warna threshold:
   - Hijau: capaian >= 100%
   - Kuning: capaian >= 75%
   - Merah: capaian < 75%
6. Simpan widget dan cek hasil di dashboard utama.

### Cara Membaca Hasil

1. Nilai utama menunjukkan capaian saat ini.
2. Progress bar menunjukkan persentase terhadap target.
3. Tren naik atau turun menunjukkan perubahan terhadap periode pembanding.
4. Interpretasi warna:
   - Hijau: aman atau melebihi target
   - Kuning: perlu perhatian
   - Merah: perlu tindakan segera

### Studi Kasus Singkat

1. KPI: Kegiatan Bulanan
   - Target: 12
   - Aktual: 7
   - Bulan lalu: 10
2. Interpretasi:
   - Pencapaian: 58,3% (merah)
   - Tren: turun 30% dari bulan lalu
   - Tindakan: tambah agenda, revisi PIC, dan monitor mingguan

### Tugas Latihan Peserta

1. Buat 3 widget KPI:
   - Anggota aktif
   - Jumlah kegiatan
   - Laporan selesai
2. Tetapkan target realistis untuk tiap KPI.
3. Aktifkan perbandingan periode.
4. Presentasikan analisis: status, tren, dan rencana aksi.
