<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('wbp_id')->constrained('wbp')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('service_categories')->cascadeOnDelete();
            $table->enum('status', [
                'Selesai', 'Belum', 'Dibatalkan',
                'Hadir', 'Tidak Hadir', 'Izin',
                'Proses',
                'Diterima', 'Belum Diterima',
            ]);
            $table->date('tanggal');
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->string('keterangan')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['category_id', 'tanggal']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_logs');
    }
};
