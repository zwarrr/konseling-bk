<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('kelas', 20);        // e.g. "XII", "X", "9"  (roman & numeric only)
            $table->string('jurusan', 100);     // e.g. "RPL", "IPA", "IPS"
            $table->unsignedInteger('jumlah_siswa_i')->default(0);
            $table->unsignedBigInteger('bk_id')->nullable();
            $table->timestamps();

            $table->unique(['kelas', 'jurusan']);
            $table->foreign('bk_id')->references('id')->on('bk_account')->nullOnDelete();
        });

        // Add class_id FK to classrooms
        Schema::table('classrooms', function (Blueprint $table) {
            $table->unsignedBigInteger('class_id')->nullable()->after('bk_account_id');
            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropColumn('class_id');
        });

        Schema::dropIfExists('classes');
    }
};
