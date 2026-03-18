<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_bookings', function (Blueprint $table) {
            $table->timestamp('reminded_24h_at')->nullable()->after('responded_by');
            $table->timestamp('reminded_1h_at')->nullable()->after('reminded_24h_at');

            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::table('program_bookings', function (Blueprint $table) {
            $table->dropIndex(['status', 'scheduled_at']);
            $table->dropColumn(['reminded_24h_at', 'reminded_1h_at']);
        });
    }
};
