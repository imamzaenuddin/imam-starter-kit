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
        Schema::table('users', function (Blueprint $table) {
            $table->string('tempat_lahir', 100)->nullable()->after('nomor_ktp');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('jenis_kelamin', 1)->nullable()->after('tanggal_lahir');
            $table->string('alamat_ktp')->nullable()->after('jenis_kelamin');
            $table->string('rt', 3)->nullable()->after('alamat_ktp');
            $table->string('rw', 3)->nullable()->after('rt');
            $table->string('kelurahan', 100)->nullable()->after('rw');
            $table->string('kecamatan', 100)->nullable()->after('kelurahan');
            $table->string('kabupaten_kota', 100)->nullable()->after('kecamatan');
            $table->string('provinsi', 100)->nullable()->after('kabupaten_kota');
            $table->string('agama', 30)->nullable()->after('provinsi');
            $table->string('status_perkawinan', 30)->nullable()->after('agama');
            $table->string('pekerjaan', 100)->nullable()->after('status_perkawinan');
            $table->string('kewarganegaraan', 30)->nullable()->after('pekerjaan');
            $table->string('berlaku_hingga', 30)->nullable()->after('kewarganegaraan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tempat_lahir',
                'tanggal_lahir',
                'jenis_kelamin',
                'alamat_ktp',
                'rt',
                'rw',
                'kelurahan',
                'kecamatan',
                'kabupaten_kota',
                'provinsi',
                'agama',
                'status_perkawinan',
                'pekerjaan',
                'kewarganegaraan',
                'berlaku_hingga',
            ]);
        });
    }
};
