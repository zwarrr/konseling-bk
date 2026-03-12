<?php

namespace App\Observers;

use App\Models\Classroom;
use App\Services\KelasSync;

class ClassroomObserver
{
    /**
     * Fires after a Classroom is created or updated.
     * Syncs Kelas.bk_id and students' bk_id whenever
     * bk_account_id or class_id changes.
     */
    public function saved(Classroom $classroom): void
    {
        if ($classroom->wasChanged('bk_account_id') || $classroom->wasChanged('class_id')) {
            KelasSync::fromClassroom(
                $classroom->bk_account_id,
                $classroom->class_id,
                $classroom->id,
            );
        }
    }

    /**
     * Fires after a Classroom is deleted.
     * Clears Kelas.bk_id (if no other classroom still covers it)
     * and students' bk linkage is already nullified by the controller.
     */
    public function deleted(Classroom $classroom): void
    {
        if (!$classroom->class_id) return;

        // Only clear Kelas.bk_id if no other classroom points to the same kelas
        $stillLinked = Classroom::where('class_id', $classroom->class_id)
            ->where('id', '!=', $classroom->id)
            ->exists();

        if (!$stillLinked) {
            KelasSync::fromClassroom(null, $classroom->class_id, $classroom->id);
        }
    }
}
