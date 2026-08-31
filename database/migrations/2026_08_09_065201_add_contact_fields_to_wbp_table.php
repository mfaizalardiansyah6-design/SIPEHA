<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wbp', function (Blueprint $table) {
            $table->string('no_hp', 20)->nullable()->after('blok_kamar');
            $table->string('jenis_kelamin', 20)->nullable()->after('agama');
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Buddha', 'Konghucu'])->change();
        });

        DB::table('wbp')->where('agama', 'Budha')->update(['agama' => 'Buddha']);

        Schema::table('wbp', function (Blueprint $table) {
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'])->change();
        });
    }

    public function down(): void
    {
        DB::table('wbp')->where('agama', 'Buddha')->update(['agama' => 'Budha']);

        Schema::table('wbp', function (Blueprint $table) {
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Buddha'])->change();
        });

        Schema::table('wbp', function (Blueprint $table) {
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha'])->change();
            $table->dropColumn(['no_hp', 'jenis_kelamin']);
        });
    }
};
