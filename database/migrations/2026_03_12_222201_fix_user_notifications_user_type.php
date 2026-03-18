<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // kelas_join_request is always directed to BK — fix rows wrongly defaulted to 'siswa'
        \DB::table('user_notifications')
            ->where('type', 'kelas_join_request')
            ->where('user_type', 'siswa')
            ->update(['user_type' => 'bk']);
    }

    public function down(): void
    {
        // data correction only, not reversible
    }
};
