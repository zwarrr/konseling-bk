<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * classroom_groups — sub-groups (kelompok) defined by BK within a regular classroom.
 * absen_from / absen_to are nullable so groups without a fixed absen range are supported.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_groups', function (Blueprint $table) {
            $table->id();
            $table->string('classroom_id');
            $table->foreign('classroom_id')
                  ->references('id')
                  ->on('classrooms')
                  ->onDelete('cascade');
            $table->string('name');
            $table->unsignedSmallInteger('absen_from')->nullable();
            $table->unsignedSmallInteger('absen_to')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_groups');
    }
};
