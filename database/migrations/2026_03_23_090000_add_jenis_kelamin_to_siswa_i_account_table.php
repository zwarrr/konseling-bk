<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa_i_account', function (Blueprint $table) {
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('siswa_i_account', function (Blueprint $table) {
            $table->dropColumn('jenis_kelamin');
        });
    }
};
