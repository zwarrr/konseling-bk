<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->string('id', 10)->primary();           // e.g. KLS01
            $table->string('name', 60);                    // e.g. "XII IPA 1"
            $table->string('bk_account_id', 20)->nullable();
            $table->text('description')->nullable();
            $table->string('join_token', 16)->unique()->nullable();
            $table->unsignedInteger('students_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
