<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique()->nullable();
            $table->string('img')->nullable();
            $table->string('img_detail_1')->nullable();
            $table->string('img_detail_2')->nullable();
            $table->string('category', 60)->default('Kegiatan');
            $table->date('date');
            $table->string('title', 50);
            $table->text('description')->nullable();
            $table->string('info_link', 500)->nullable();
            $table->string('classroom_id', 20)->nullable();
            $table->enum('status', ['publish', 'draft'])->default('publish');
            $table->unsignedInteger('peserta')->default(0);
            $table->string('guru_pembimbing', 100)->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
