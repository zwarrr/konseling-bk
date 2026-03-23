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
            $table->enum('state', ['pending', 'approved', 'rejected'])->default('pending'); // pending|approved|rejected
            $table->enum('booking_type', ['individu', 'group'])->default('individu'); // individu|group
            $table->enum('method', ['tatap_muka', 'chat'])->default('tatap_muka'); // tatap_muka|chat
            $table->json('participants')->nullable();
            $table->timestamp('responded_at')->nullable();
            // BK accounts live in bk_account
            $table->foreignId('responded_by')->nullable()->constrained('bk_account')->nullOnDelete();
            $table->timestamp('reminded_lead_at')->nullable();
            $table->timestamp('chat_confirmed_at')->nullable();
            $table->timestamp('chat_reconfirmed_at')->nullable();
            $table->timestamp('chat_reconfirm_declined_at')->nullable();
            $table->timestamp('reminded_24h_at')->nullable();
            $table->timestamp('reminded_1h_at')->nullable();
            $table->timestamps();

            $table->index(['program_id', 'user_id', 'state']);
            $table->index(['state', 'scheduled_at']);
            $table->index(['program_id', 'booking_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_bookings');
    }
};
