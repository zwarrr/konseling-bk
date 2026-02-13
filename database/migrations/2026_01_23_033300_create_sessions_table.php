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
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // session ID sebagai primary key
            $table->foreignId('user_id')->nullable()->index(); // opsional: ID user yang terkait
            $table->string('ip_address', 45)->nullable(); // untuk menyimpan IP pengguna
            $table->text('user_agent')->nullable(); // user agent browser
            $table->text('payload'); // data sesi terenkripsi
            $table->integer('last_activity')->index(); // timestamp terakhir aktif
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
