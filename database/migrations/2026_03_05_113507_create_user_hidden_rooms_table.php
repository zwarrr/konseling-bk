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
        Schema::create('user_hidden_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('user_type', ['bk', 'siswa'])->default('siswa'); // bk|siswa
            $table->string('room_key');   // room_id for direct chats, "kelas:{id}" for classrooms
            $table->timestamp('hidden_at')->useCurrent();
            $table->unique(['user_id', 'room_key']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_hidden_rooms');
    }
};
