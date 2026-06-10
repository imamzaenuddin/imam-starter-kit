<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. m_menu
        Schema::create('m_menu', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('url', 255)->nullable();
            $table->string('icon', 100)->nullable()->comment('Class ikon Boxicons, contoh: bx bx-home');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('m_menu')
                ->nullOnDelete();
            $table->unsignedSmallInteger('urutan')->default(0)->comment('Urutan tampil di sidebar');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. m_level_menu
        Schema::create('m_level_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('m_level')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('m_menu')->cascadeOnDelete();
            $table->boolean('dapat_buat')->default(false);
            $table->boolean('dapat_lihat')->default(true);
            $table->boolean('dapat_ubah')->default(false);
            $table->boolean('dapat_hapus')->default(false);
            $table->boolean('dapat_backup')->default(false);
            $table->boolean('dapat_restore')->default(false);
            $table->boolean('dapat_hapus_backup')->default(false);
            $table->timestamps();
            $table->unique(['level_id', 'menu_id']);
        });

        // 3. m_identitas
        Schema::create('m_identitas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_aplikasi', 120);
            $table->string('singkatan_aplikasi', 30)->nullable();
            $table->string('versi', 30)->default('1.0.0');
            $table->string('icon', 100)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->string('main_color', 10)->default('#696cff');
            $table->string('secondary_color', 10)->default('#8592a3');
            $table->string('email', 120)->nullable();
            $table->string('wa_center', 25)->nullable();
            $table->string('telepon', 25)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->string('slogan', 160)->nullable();
            $table->text('deskripsi')->nullable();
            $table->json('fitur_login')->nullable();
            $table->json('statistik_login')->nullable();
            $table->string('footer_text', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. t_log_aktivitas
        Schema::create('t_log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('t_user')->nullOnDelete();
            $table->string('modul', 100)->nullable();
            $table->string('aktivitas', 255);
            $table->string('url', 255)->nullable();
            $table->string('metode', 10)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['modul']);
        });

        // 5. m_dashboard_widget
        Schema::create('m_dashboard_widget', function (Blueprint $table) {
            $table->id();
            $table->string('nama_widget', 120);
            $table->text('deskripsi')->nullable();
            $table->string('sumber_data', 50);
            $table->string('tipe_tampilan', 30)->default('statistik');
            $table->string('tipe_query', 30)->default('count');
            $table->string('chart_tipe', 20)->nullable();
            $table->unsignedInteger('chart_tinggi')->nullable();
            $table->json('chart_warna')->nullable();
            $table->string('kolom_agregasi', 50)->nullable();
            $table->string('kolom_label', 50)->nullable();
            $table->string('kolom_nilai', 50)->nullable();
            $table->string('filter_kolom', 50)->nullable();
            $table->string('filter_operator', 20)->nullable();
            $table->string('filter_nilai', 255)->nullable();
            $table->string('layout_kolom', 50)->default('col-xl-3 col-md-6');
            $table->string('warna', 20)->default('primary');
            $table->string('icon', 100)->nullable();
            $table->unsignedInteger('batas_data')->default(5);
            $table->boolean('bandingkan_periode')->default(false);
            $table->string('bandingkan_dengan')->nullable();
            $table->integer('kpi_target')->nullable();
            $table->boolean('tampilkan_progress_bar')->default(false);
            $table->string('warna_threshold_hijau')->default('90ddd52');
            $table->string('warna_threshold_kuning')->default('ffc107');
            $table->string('warna_threshold_merah')->default('dc3545');
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. m_dashboard_widget_level
        Schema::create('m_dashboard_widget_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_widget_id')->constrained('m_dashboard_widget')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('m_level')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['dashboard_widget_id', 'level_id'], 'dashboard_widget_level_unique');
        });

        // 7. m_bahasa
        Schema::create('m_bahasa', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama', 100);
            $table->string('nama_native', 100)->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 8. t_chat_ai_riwayat
        Schema::create('t_chat_ai_riwayat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('t_user')->nullOnDelete();
            $table->text('pertanyaan');
            $table->longText('jawaban');
            $table->string('sumber', 40)->default('lokal');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        // 9. m_pengaturan_email
        Schema::create('m_pengaturan_email', function (Blueprint $table) {
            $table->id();
            $table->string('mailer', 20)->default('smtp');
            $table->string('host', 150);
            $table->unsignedSmallInteger('port')->default(587);
            $table->string('enkripsi', 10)->nullable();
            $table->string('username', 150)->nullable();
            $table->text('password')->nullable();
            $table->string('from_address', 150);
            $table->string('from_name', 150);
            $table->string('reply_to', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 10. m_pengaturan_backup
        Schema::create('m_pengaturan_backup', function (Blueprint $table) {
            $table->id();
            $table->string('jadwal_harian_tipe', 20)->default('transaksi');
            $table->string('jadwal_harian_jam', 5)->default('01:00');
            $table->string('jadwal_mingguan_tipe', 20)->default('full');
            $table->string('jadwal_mingguan_hari', 10)->default('sunday');
            $table->string('jadwal_mingguan_jam', 5)->default('02:00');
            $table->unsignedSmallInteger('retensi_hari')->default(30);
            $table->boolean('restore_auto_backup')->default(true);
            $table->string('restore_auto_backup_tipe', 20)->default('full');
            $table->unsignedInteger('restore_lock_timeout_detik')->default(900);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default backup settings
        DB::table('m_pengaturan_backup')->insert([
            'jadwal_harian_tipe' => (string) config('backup.jadwal_harian_tipe', 'transaksi'),
            'jadwal_harian_jam' => (string) config('backup.jadwal_harian_jam', '01:00'),
            'jadwal_mingguan_tipe' => (string) config('backup.jadwal_mingguan_tipe', 'full'),
            'jadwal_mingguan_hari' => (string) config('backup.jadwal_mingguan_hari', 'sunday'),
            'jadwal_mingguan_jam' => (string) config('backup.jadwal_mingguan_jam', '02:00'),
            'retensi_hari' => (int) config('backup.retensi_hari', 30),
            'restore_auto_backup' => (bool) config('backup.restore_auto_backup', true),
            'restore_auto_backup_tipe' => (string) config('backup.restore_auto_backup_tipe', 'full'),
            'restore_lock_timeout_detik' => (int) config('backup.restore_lock_timeout_detik', 900),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 11. m_notifikasi
        Schema::create('m_notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('t_user')->onDelete('cascade');
            $table->string('judul', 255);
            $table->text('pesan');
            $table->enum('tipe', [
                'backup_selesai',
                'restore_selesai',
                'restore_gagal',
                'perubahan_data',
                'aktivitas_penting',
                'peringatan',
                'info',
            ])->default('info');
            $table->string('path_terkait', 255)->nullable();
            $table->boolean('dibaca')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('dibaca');
            $table->index('created_at');
        });

        // 12. m_media
        Schema::create('m_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('t_user')->onDelete('cascade');
            $table->string('nama_asli', 255);
            $table->string('nama_file', 255)->unique();
            $table->string('mime_type', 100);
            $table->bigInteger('ukuran_byte');
            $table->enum('kategori', ['logo', 'profil', 'dokumen', 'lainnya'])->default('lainnya');
            $table->string('path_relatif', 500);
            $table->string('disk', 50)->default('public');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('kategori');
            $table->index('created_at');
        });

        // 13. m_pengaturan_aplikasi
        Schema::create('m_pengaturan_aplikasi', function (Blueprint $table) {
            $table->id();
            $table->string('timezone', 100)->default(config('app.timezone', 'Asia/Jakarta'));
            $table->string('locale_default', 10)->default(config('app.locale', 'id'));
            $table->unsignedInteger('batas_upload_kb')->default(10240);
            $table->unsignedInteger('pagination_default')->default(10);
            $table->string('otp_mode', 20)->default('always');
            $table->unsignedInteger('otp_inactive_days')->default(30);
            $table->unsignedInteger('otp_failed_attempts')->default(3);
            $table->unsignedInteger('otp_failed_window_minutes')->default(15);
            $table->json('chat_ai_konteks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 14. m_login_attempt
        Schema::create('m_login_attempt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('t_user')->nullOnDelete();
            $table->string('email', 150)->nullable()->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('user_agent', 1000)->nullable();
            $table->string('status', 20)->index();
            $table->string('alasan', 255)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 15. m_chat_ai_sumber
        Schema::create('m_chat_ai_sumber', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('sumber_data', 50);
            $table->string('tipe_data', 20)->default('statistik');
            $table->string('tipe_query', 20)->default('count');
            $table->string('kolom_agregasi', 100)->nullable();
            $table->json('kolom_tampil')->nullable();
            $table->string('filter_kolom', 100)->nullable();
            $table->string('filter_operator', 10)->nullable();
            $table->string('filter_nilai', 255)->nullable();
            $table->unsignedInteger('batas_data')->default(10);
            $table->boolean('is_data_personal')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'urutan']);
            $table->index('sumber_data');
        });

        // 16. m_chat_ai_sumber_level
        Schema::create('m_chat_ai_sumber_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_ai_sumber_id')->constrained('m_chat_ai_sumber')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('m_level')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['chat_ai_sumber_id', 'level_id'], 'chat_ai_sumber_level_unique');
        });

        // 17. m_form_generator
        Schema::create('m_form_generator', function (Blueprint $table) {
            $table->id();
            $table->string('nama_modul', 120);
            $table->string('slug', 120)->unique();
            $table->string('nama_menu', 120);
            $table->string('menu_url', 180)->unique();
            $table->string('icon', 100)->nullable();
            $table->foreignId('parent_menu_id')->nullable()->constrained('m_menu')->nullOnDelete();
            $table->string('sumber_import', 30)->nullable();
            $table->enum('tipe_modul', ['master', 'transaksi'])->default('master');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('t_user')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('t_user')->nullOnDelete();
            $table->timestamps();
        });

        // 18. m_form_generator_field
        Schema::create('m_form_generator_field', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_generator_id')->constrained('m_form_generator')->cascadeOnDelete();
            $table->string('nama_field', 120);
            $table->string('label_field', 120);
            $table->string('tipe_data', 30)->default('string');
            $table->string('tipe_input', 40)->default('text');
            $table->json('opsi_pilihan')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_tampil_form')->default(true);
            $table->boolean('is_tampil_list')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
            $table->unique(['form_generator_id', 'nama_field']);
        });

        // 19. t_form_generator_data
        Schema::create('t_form_generator_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_generator_id')->constrained('m_form_generator')->cascadeOnDelete();
            $table->json('payload');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('t_user')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('t_user')->nullOnDelete();
            $table->timestamps();
        });

        // 20. t_berita
        Schema::create('t_berita', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('ringkasan')->nullable();
            $table->longText('isi');
            $table->string('foto')->nullable();
            $table->string('kategori')->default('Berita');
            $table->string('penulis')->nullable();
            $table->date('tanggal_terbit')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('views')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('t_user')->nullOnDelete();
            $table->timestamps();
        });

        // 21. t_konten_slider
        Schema::create('t_konten_slider', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('subjudul')->nullable();
            $table->string('foto');
            $table->string('warna_latar')->default('#2563eb');
            $table->string('label_tombol')->nullable();
            $table->string('url_tombol')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('urutan')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('t_user')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_konten_slider');
        Schema::dropIfExists('t_berita');
        Schema::dropIfExists('t_form_generator_data');
        Schema::dropIfExists('m_form_generator_field');
        Schema::dropIfExists('m_form_generator');
        Schema::dropIfExists('m_chat_ai_sumber_level');
        Schema::dropIfExists('m_chat_ai_sumber');
        Schema::dropIfExists('m_login_attempt');
        Schema::dropIfExists('m_pengaturan_aplikasi');
        Schema::dropIfExists('m_media');
        Schema::dropIfExists('m_notifikasi');
        Schema::dropIfExists('m_pengaturan_backup');
        Schema::dropIfExists('m_pengaturan_email');
        Schema::dropIfExists('t_chat_ai_riwayat');
        Schema::dropIfExists('m_bahasa');
        Schema::dropIfExists('m_dashboard_widget_level');
        Schema::dropIfExists('m_dashboard_widget');
        Schema::dropIfExists('t_log_aktivitas');
        Schema::dropIfExists('m_identitas');
        Schema::dropIfExists('m_level_menu');
        Schema::dropIfExists('m_menu');
    }
};
