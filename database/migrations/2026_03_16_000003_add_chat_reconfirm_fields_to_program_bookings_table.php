<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('program_bookings', 'chat_reconfirmed_at')) {
                $table->timestamp('chat_reconfirmed_at')->nullable()->after('chat_confirmed_at');
            }
            if (!Schema::hasColumn('program_bookings', 'chat_reconfirm_declined_at')) {
                $table->timestamp('chat_reconfirm_declined_at')->nullable()->after('chat_reconfirmed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('program_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('program_bookings', 'chat_reconfirm_declined_at')) {
                $table->dropColumn('chat_reconfirm_declined_at');
            }
            if (Schema::hasColumn('program_bookings', 'chat_reconfirmed_at')) {
                $table->dropColumn('chat_reconfirmed_at');
            }
        });
    }
};
