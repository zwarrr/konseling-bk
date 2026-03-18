<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_read_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('user_type', ['bk', 'siswa'])->default('siswa'); // bk|siswa
            $table->string('classroom_id', 10);
            $table->unsignedBigInteger('last_read_message_id')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'classroom_id']);
            $table->foreign('classroom_id')->references('id')->on('classrooms')->onDelete('cascade');
            $table->index(['classroom_id', 'last_read_message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_read_receipts');
    }
};
