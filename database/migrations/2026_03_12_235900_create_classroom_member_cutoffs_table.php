<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_member_cutoffs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');       // SiswaAccount.id
            $table->string('classroom_id', 10);          // Classroom.id
            $table->unsignedBigInteger('cutoff_message_id')->default(0);
            // Messages with id <= cutoff_message_id are hidden from this user.
            // 0 means show all history (e.g. BK / founding members).
            $table->timestamps();

            $table->unique(['user_id', 'classroom_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_member_cutoffs');
    }
};
