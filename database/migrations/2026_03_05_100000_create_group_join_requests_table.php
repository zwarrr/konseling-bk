<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_join_requests', function (Blueprint $table) {
            $table->id();
            $table->string('classroom_id', 10);
            $table->unsignedBigInteger('user_id');
            $table->string('user_type', 10)->default('siswa'); // 'bk' | 'siswa'
            // status: pending | approved | rejected
            $table->string('status', 20)->default('pending')->index();
            $table->text('note')->nullable();          // optional reject reason
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('classroom_id')->references('id')->on('classrooms')->cascadeOnDelete();
            // A student may only have one active pending request per classroom
            $table->unique(['classroom_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_join_requests');
    }
};
