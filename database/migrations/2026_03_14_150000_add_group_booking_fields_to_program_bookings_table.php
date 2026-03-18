<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_bookings', function (Blueprint $table) {
            $table->enum('booking_type', ['individu', 'group'])->default('individu')->after('status'); // individu|group
            $table->json('participants')->nullable()->after('booking_type');

            $table->index(['program_id', 'booking_type']);
        });
    }

    public function down(): void
    {
        Schema::table('program_bookings', function (Blueprint $table) {
            $table->dropIndex(['program_id', 'booking_type']);
            $table->dropColumn(['booking_type', 'participants']);
        });
    }
};
