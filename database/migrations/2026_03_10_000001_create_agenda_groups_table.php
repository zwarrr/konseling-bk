<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * agenda_groups — sub-groups (kelompok) within an agenda's group chat.
 * Linked directly to agendas.id — completely separate from classroom_groups
 * (which use KLS-prefixed classroom IDs and absen ranges for regular kelas).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agenda_id');
            $table->foreign('agenda_id')
                  ->references('id')
                  ->on('agendas')
                  ->onDelete('cascade');
            $table->string('name', 100);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_groups');
    }
};
