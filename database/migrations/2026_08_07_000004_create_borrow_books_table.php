<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_books', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('wbp_id')->constrained('wbp')->cascadeOnDelete();
            $table->string('judul_buku')->index();
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali');
            $table->enum('status', ['Dipinjam', 'Dikembalikan'])->default('Dipinjam');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrow_books');
    }
};
