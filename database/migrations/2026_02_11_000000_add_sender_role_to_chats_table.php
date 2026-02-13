<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->string('sender_role', 20)->nullable()->after('sender_name');
            $table->index('sender_role');
        });

        // Backfill existing rows based on the original direction semantics:
        // - outgoing => guru
        // - incoming => siswa
        DB::table('chats')
            ->whereNull('sender_role')
            ->update([
                'sender_role' => DB::raw("CASE WHEN direction = 'outgoing' THEN 'guru' ELSE 'siswa' END"),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropIndex(['sender_role']);
            $table->dropColumn('sender_role');
        });
    }
};
