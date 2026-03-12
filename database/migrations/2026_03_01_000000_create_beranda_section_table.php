<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jika tabel lama (home_section) masih ada, rename saja — data tetap terjaga
        if (Schema::hasTable('home_section') && !Schema::hasTable('beranda_section')) {
            Schema::rename('home_section', 'beranda_section');
            return;
        }

        // Fresh install: buat tabel baru dari awal
        if (!Schema::hasTable('beranda_section')) {
            Schema::create('beranda_section', function (Blueprint $table) {
                $table->id();
                $table->string('title')->default('Tumbuh Bersama');
                $table->string('subtitle')->default('Bimbingan & Konseling');
                $table->text('description')->nullable();
                $table->string('img')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::rename('beranda_section', 'home_section');
    }
};
