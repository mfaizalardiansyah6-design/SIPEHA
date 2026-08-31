<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wbp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama');
            $table->string('nik');
            $table->string('nik_hash')->unique();
            $table->string('no_register')->unique();
            $table->string('blok_kamar')->index();
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha']);
            $table->enum('status', ['Aktif', 'Non-Aktif'])->default('Aktif');
            $table->string('foto_url')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wbp');
    }
};
