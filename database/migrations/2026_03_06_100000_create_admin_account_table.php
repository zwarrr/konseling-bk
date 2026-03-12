<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_account', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('account_id', 20)->unique()->nullable(); // e.g. ADM1, ADM2
            $table->string('login_id', 20)->unique();   // admin username / ID masuk
            $table->string('password');
            $table->boolean('must_change_password')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_account');
    }
};
