<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_messages', function (Blueprint $table) {
            $table->id();
            $table->string('classroom_id', 10);
            $table->unsignedBigInteger('user_id');
            $table->string('user_type', 10)->default('siswa'); // 'bk' | 'siswa'
            $table->text('message')->nullable();
            $table->string('message_type', 20)->default('text'); // text | image | document
            $table->string('attachment')->nullable();
            $table->timestamps();

            $table->foreign('classroom_id')
                  ->references('id')->on('classrooms')
                  ->onDelete('cascade');

            $table->index(['classroom_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_messages');
    }
};
