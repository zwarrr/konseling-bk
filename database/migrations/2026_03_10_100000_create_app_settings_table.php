<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default values
        DB::table('app_settings')->insert([
            ['key' => 'maintenance_mode',    'value' => '0',                   'created_at' => now(), 'updated_at' => now()],
            ['key' => 'maintenance_message', 'value' => 'Sistem sedang dalam pemeliharaan. Mohon tunggu sebentar.', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
