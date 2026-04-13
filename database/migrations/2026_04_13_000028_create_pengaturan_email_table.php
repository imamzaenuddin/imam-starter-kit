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
        Schema::create('pengaturan_email', function (Blueprint $table) {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_email');
    }
};
