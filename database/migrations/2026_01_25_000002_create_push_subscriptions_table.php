<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('user_type', 10)->default('siswa'); // 'bk' | 'siswa'
            $table->text('endpoint');
            // MD5 hash of endpoint used for uniqueness (TEXT can't be directly indexed)
            $table->string('endpoint_hash', 32)->unique();
            $table->text('p256dh');
            $table->text('auth');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
