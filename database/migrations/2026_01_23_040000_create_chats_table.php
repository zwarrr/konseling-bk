<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chats table — one row per message.
 *
 * Key columns:
 *  room_id            — 12-char readable ID: RC + 2-char siswa initials + 2-char guru initials + 2-digit seq (e.g. RCSIGURU01)
 *  siswa_account_id   — account_id of the siswa (e.g. SSWAI01)
 *  guru_account_id    — account_id of the guru  (e.g. BK01)
 *  sender_account_id  — account_id of the sender (string, matches users.account_id)
 *  sender_role        — snapshot of role at send time (guru / siswa / user)
 *  message_type       — 'text' | 'image' | 'video' | 'document'
 *  attachment         — relative storage path when message_type ≠ 'text'
 *  read_at            — null = unread, timestamp = read (drives the blue-tick read receipt)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();

            // Room identification — readable format e.g. RCSIGURU01
            $table->string('room_id', 12)->nullable()->index();

            // Participants (account_id strings, not numeric ids)
            $table->string('siswa_account_id', 20)->nullable()->index();
            $table->string('guru_account_id',  20)->nullable()->index();

            // Sender — account_id string (e.g. SSWAI01, BK01)
            $table->string('sender_account_id', 20)->nullable();
            $table->enum('sender_role', ['guru', 'siswa', 'user', 'system'])->nullable(); // guru|siswa|user|system

            // Payload
            $table->text('message');
            $table->enum('message_type', ['text', 'image', 'video', 'document'])->default('text'); // text|image|video|document
            $table->string('attachment')->nullable();             // storage path

            // Read-receipt — null means unread, timestamp means already read
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            // Efficient polls: GET /api/chat/{roomId}/messages?after={lastId}
            $table->index(['room_id', 'id']);
            // Quick participant lookup
            $table->index(['siswa_account_id', 'guru_account_id']);
            // Quick unread count per room
            $table->index(['room_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
