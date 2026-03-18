<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->json('benefits')->nullable()->after('description');
        });

        // Program is always publish (no draft)
        DB::table('programs')->update(['status' => 'publish']);
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('benefits');
        });

        // Do not revert statuses.
    }
};
