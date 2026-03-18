<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classroom_member_cutoffs', function (Blueprint $table) {
            $table->unsignedBigInteger('left_cutoff_message_id')
                ->nullable()
                ->after('cutoff_message_id');
            // When set, messages with id > left_cutoff_message_id are hidden from this user
            // (used after leaving / being removed).
        });
    }

    public function down(): void
    {
        Schema::table('classroom_member_cutoffs', function (Blueprint $table) {
            $table->dropColumn('left_cutoff_message_id');
        });
    }
};
