<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_bookings', function (Blueprint $table) {
            $table->enum('method', ['tatap_muka', 'chat'])->default('tatap_muka')->after('booking_type'); // tatap_muka|chat
            $table->timestamp('chat_confirmed_at')->nullable()->after('reminded_lead_at');
        });
    }

    public function down(): void
    {
        Schema::table('program_bookings', function (Blueprint $table) {
            $table->dropColumn(['method', 'chat_confirmed_at']);
        });
    }
};
