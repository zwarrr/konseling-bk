<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_bookings', function (Blueprint $table) {
            $table->timestamp('reminded_lead_at')->nullable()->after('responded_by');
        });
    }

    public function down(): void
    {
        Schema::table('program_bookings', function (Blueprint $table) {
            $table->dropColumn('reminded_lead_at');
        });
    }
};
