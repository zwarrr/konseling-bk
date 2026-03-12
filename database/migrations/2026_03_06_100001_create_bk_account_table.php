<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_account', function (Blueprint $table) {
            $table->id();
            $table->string('account_id', 20)->unique()->nullable();  // e.g. BK01
            $table->string('login_id', 20)->unique()->nullable();    // NIP 18 digit
            $table->string('name');
            $table->string('email', 255)->nullable()->unique();
            $table->string('about', 255)->nullable();
            $table->string('profile_photo')->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->string('password');
            $table->boolean('must_change_password')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_account');
    }
};
