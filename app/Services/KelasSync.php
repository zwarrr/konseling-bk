<?php

namespace App\Services;

use App\Models\BkAccount;
use App\Models\Classroom;
use App\Models\Kelas;
use App\Models\SiswaAccount;

/**
 * Central sync service — keeps Kelas.bk_id, Classroom.bk_account_id,
 * and SiswaAccount.bk_id consistent across all roles.
 *
 * "withoutEvents" is used on inner bulk-updates so that observers
 * do not re-fire in a circular loop.
 */
class KelasSync
{
    // ──────────────────────────────────────────────────────────────────
    // 1. Admin changed Kelas.bk_id
    //    ↓ push to Classroom.bk_account_id
    //    ↓ push to SiswaAccount.bk_id (students in those classrooms)
    // ──────────────────────────────────────────────────────────────────
    public static function fromKelas(Kelas $kelas): void
    {
        $bkAccountId = $kelas->bk_id
            ? BkAccount::where('id', $kelas->bk_id)->value('account_id')
            : null;

        // Use withoutEvents so ClassroomObserver doesn't fire redundantly
        Classroom::withoutEvents(function () use ($kelas, $bkAccountId) {
            Classroom::where('class_id', $kelas->id)
                ->update(['bk_account_id' => $bkAccountId]);
        });

        // Sync students' bk_id in every classroom linked to this Kelas
        $classroomIds = Classroom::where('class_id', $kelas->id)->pluck('id');
        if ($classroomIds->isEmpty()) return;

        SiswaAccount::withoutEvents(function () use ($classroomIds, $kelas) {
            SiswaAccount::whereIn('classroom_id', $classroomIds)
                ->update(['bk_id' => $kelas->bk_id]);
        });
    }

    // ──────────────────────────────────────────────────────────────────
    // 2. BK created / updated / deleted a Classroom
    //    ↑ push bk_id back up to Kelas
    //    ↓ push bk_id down to students in this classroom
    //
    //    $bkAccountId : new value of classroom.bk_account_id (null on delete)
    //    $classId     : classroom.class_id (can be null)
    //    $classroomId : the classroom's own id (for student lookup)
    // ──────────────────────────────────────────────────────────────────
    public static function fromClassroom(
        ?string $bkAccountId,
        ?int    $classId,
        string  $classroomId,
    ): void {
        $bkId = $bkAccountId
            ? BkAccount::where('account_id', $bkAccountId)->value('id')
            : null;

        // ↑ Sync Kelas.bk_id
        if ($classId) {
            Kelas::withoutEvents(function () use ($classId, $bkId) {
                Kelas::where('id', $classId)->update(['bk_id' => $bkId]);
            });
        }

        // ↓ Sync students' bk_id
        SiswaAccount::withoutEvents(function () use ($classroomId, $bkId) {
            SiswaAccount::where('classroom_id', $classroomId)
                ->update(['bk_id' => $bkId]);
        });
    }

    // ──────────────────────────────────────────────────────────────────
    // 3. A student was assigned to (or removed from) a classroom
    //    ↓ update that student's bk_id from the classroom's Kelas
    // ──────────────────────────────────────────────────────────────────
    public static function forStudent(SiswaAccount $student): void
    {
        if ($student->classroom_id) {
            $bkId = Classroom::with('dataKelas')
                ->find($student->classroom_id)
                ?->dataKelas?->bk_id;

            SiswaAccount::withoutEvents(function () use ($student, $bkId) {
                $student->update(['bk_id' => $bkId]);
            });
        } else {
            // Removed from classroom → clear bk linkage
            SiswaAccount::withoutEvents(function () use ($student) {
                $student->update(['bk_id' => null]);
            });
        }
    }
}
