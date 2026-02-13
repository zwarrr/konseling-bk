<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Optional: keep email for compatibility with factory / tooling.
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();

            // Unified numeric login ID (NIP BK / NIS Siswa / ID Admin)
            $table->unsignedBigInteger('account_id_nip_nis')->unique();
            $table->string('role', 20)->default('siswa');

            // Password must remain a string because it is hashed.
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
