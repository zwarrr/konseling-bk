<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('user_type', 10)->default('siswa'); // 'bk' | 'siswa'
            // type: 'agenda' | 'news'
            $table->string('type', 30)->default('agenda');
            $table->string('title');
            $table->text('body')->nullable();
            // related record
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('related_type', 60)->nullable(); // e.g. 'App\Models\Agenda'
            // read tracking
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
