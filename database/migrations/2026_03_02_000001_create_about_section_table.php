<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_section', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Guru BK yang Siap');
            $table->string('subtitle')->default('Melayani Sepenuh Hati');
            $table->text('description')->nullable();
            // 2×2 photo grid
            $table->string('img_1')->nullable();
            $table->string('img_2')->nullable();
            $table->string('img_3')->nullable();
            $table->string('img_4')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_section');
    }
};
