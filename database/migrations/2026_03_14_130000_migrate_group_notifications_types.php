<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('user_notifications')) return;
        if (!DB::getSchemaBuilder()->hasTable('classrooms')) return;

        // Convert legacy group notifications that were stored as kelas_approved/kelas_rejected
        // into group_approved/group_rejected, BUT only when the classroom is a program-group
        // (classrooms.class_id is NULL).
        DB::table('user_notifications')
            ->where('user_type', 'siswa')
            ->whereIn('type', ['kelas_approved', 'kelas_rejected'])
            ->where('related_type', 'like', 'classroom:%')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $rt = (string) ($row->related_type ?? '');
                    $classroomId = str_starts_with($rt, 'classroom:') ? substr($rt, strlen('classroom:')) : null;
                    if (!$classroomId) continue;

                    $classId = DB::table('classrooms')->where('id', $classroomId)->value('class_id');
                    if ($classId !== null) {
                        // Academic kelas: leave as-is (will be hidden from siswa notifications list)
                        continue;
                    }

                    $newType = $row->type === 'kelas_approved' ? 'group_approved' : 'group_rejected';

                    $body = $row->body;
                    if (is_string($body) && $body !== '') {
                        $body = preg_replace('/bergabung\s+ke\s+kelas/i', 'bergabung ke grup', $body);
                    }

                    DB::table('user_notifications')
                        ->where('id', $row->id)
                        ->update([
                            'type' => $newType,
                            'body' => $body,
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Intentionally no-op (data migration)
    }
};
