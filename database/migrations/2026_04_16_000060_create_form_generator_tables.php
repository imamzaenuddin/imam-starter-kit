<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_form_generator', function (Blueprint $table) {
            $table->id();
            $table->string('nama_modul', 120);
            $table->string('slug', 120)->unique();
            $table->string('nama_menu', 120);
            $table->string('menu_url', 180)->unique();
            $table->string('icon', 100)->nullable();
            $table->foreignId('parent_menu_id')->nullable()->constrained('m_menu')->nullOnDelete();
            $table->string('sumber_import', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('t_user')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('t_user')->nullOnDelete();
            $table->timestamps();
        });

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

        Schema::create('t_form_generator_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_generator_id')->constrained('m_form_generator')->cascadeOnDelete();
            $table->json('payload');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('t_user')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('t_user')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_form_generator_data');
        Schema::dropIfExists('m_form_generator_field');
        Schema::dropIfExists('m_form_generator');
    }
};
