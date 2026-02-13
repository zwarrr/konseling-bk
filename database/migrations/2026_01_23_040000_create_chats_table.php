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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('wa_message_id')->nullable()->unique();
            $table->string('sender_name')->default('Anonymous');
            $table->string('phone_number')->nullable(); // Store full JID: 122879031136511@lid
            $table->string('room_name')->nullable(); // Store full JID
            $table->enum('direction', ['incoming', 'outgoing'])->default('incoming');
            $table->text('message');
            $table->string('message_type')->default('text'); // Changed to string for flexibility
            $table->string('attachment')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('sent');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            // Index untuk query lebih cepat
            $table->index(['room_name', 'created_at']);
            $table->index('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
