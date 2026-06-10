<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('m_level', function (Blueprint $table) {
            $table->id();
            $table->string('nama_level', 50)->unique();
            $table->string('deskripsi', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('t_user', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nama_panggilan', 100)->nullable();
            $table->string('nomor_ktp', 30)->nullable();
            $table->string('foto_profil')->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 1)->nullable();
            $table->string('alamat_ktp')->nullable();
            $table->string('rt', 3)->nullable();
            $table->string('rw', 3)->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kabupaten_kota', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('agama', 30)->nullable();
            $table->string('status_perkawinan', 30)->nullable();
            $table->string('pekerjaan', 100)->nullable();
            $table->string('kewarganegaraan', 30)->nullable();
            $table->string('email')->unique();
            $table->string('google_id')->nullable()->unique();
            $table->string('google_avatar')->nullable();
            $table->foreignId('level_id')
                ->nullable()
                ->constrained('m_level')
                ->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('two_factor_enabled')->default(false);
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('t_user');
        Schema::dropIfExists('m_level');
        Schema::enableForeignKeyConstraints();
    }
};
