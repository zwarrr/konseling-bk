<?php

namespace App\Observers;

use App\Models\GroupMemberCutoff;
use App\Models\SiswaAccount;
use App\Services\KelasSync;

class SiswaAccountObserver
{
    /**
     * Fires after a SiswaAccount is saved.
     * If classroom_id changed, sync bk_id and record the join-history cutoff.
     */
    public function saved(SiswaAccount $siswa): void
    {
        if ($siswa->wasChanged('classroom_id')) {
            KelasSync::forStudent($siswa);

            // When a student is (re-)assigned to a primary classroom, record
            // a cutoff so they only see messages sent after they joined.
            if ($siswa->classroom_id) {
                GroupMemberCutoff::recordJoin($siswa->id, $siswa->classroom_id);
            }
        }
    }
}
