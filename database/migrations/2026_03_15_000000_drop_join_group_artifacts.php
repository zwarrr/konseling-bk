<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('group_join_requests')) {
            Schema::drop('group_join_requests');
        }

        if (Schema::hasTable('classrooms') && Schema::hasColumn('classrooms', 'join_token')) {
            Schema::table('classrooms', function (Blueprint $table) {
                $table->dropUnique(['join_token']);
                $table->dropColumn('join_token');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('classrooms') && !Schema::hasColumn('classrooms', 'join_token')) {
            Schema::table('classrooms', function (Blueprint $table) {
                $table->string('join_token', 16)->unique()->nullable();
            });
        }

        if (!Schema::hasTable('group_join_requests')) {
            Schema::create('group_join_requests', function (Blueprint $table) {
                $table->id();
                $table->string('classroom_id');
                $table->unsignedBigInteger('user_id');
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('note')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();

                $table->index(['classroom_id', 'status']);
                $table->index(['user_id', 'status']);
            });
        }
    }
};
