<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa_i_account', function (Blueprint $table) {
            $table->id();
            $table->string('account_id', 20)->unique()->nullable();  // e.g. SSWAI01
            $table->string('login_id', 20)->unique()->nullable();    // NIS maks 10 digit
            $table->string('name');
            $table->string('email', 255)->nullable()->unique();
            $table->string('about', 255)->nullable();
            $table->string('profile_photo')->nullable();

            // Admin manually assigns a BK teacher to this student
            $table->unsignedBigInteger('bk_id')->nullable();
            $table->foreign('bk_id')->references('id')->on('bk_account')->nullOnDelete();

            // Classroom group membership
            $table->string('classroom_id', 10)->nullable();
            $table->foreign('classroom_id')->references('id')->on('classrooms')->nullOnDelete();
            $table->unsignedSmallInteger('absen')->nullable(); // nomor absen dalam kelas

            $table->timestamp('last_seen_at')->nullable()->index();
            $table->string('password');
            $table->boolean('must_change_password')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa_i_account');
    }
};
