<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('program_bookings')) {
            return;
        }

        Schema::create('program_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            // Siswa accounts live in siswa_i_account (not users)
            $table->foreignId('user_id')->constrained('siswa_i_account')->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // pending|approved|rejected
            $table->timestamp('responded_at')->nullable();
            // BK accounts live in bk_account
            $table->foreignId('responded_by')->nullable()->constrained('bk_account')->nullOnDelete();
            $table->timestamps();

            $table->index(['program_id', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_bookings');
    }
};
