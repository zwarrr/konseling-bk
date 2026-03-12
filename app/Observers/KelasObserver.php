<?php

namespace App\Observers;

use App\Models\Kelas;
use App\Services\KelasSync;

class KelasObserver
{
    /**
     * Fires after a Kelas is saved.
     * If bk_id changed, cascade to Classroom.bk_account_id and students' bk_id.
     */
    public function saved(Kelas $kelas): void
    {
        if ($kelas->wasChanged('bk_id') || $kelas->wasRecentlyCreated) {
            KelasSync::fromKelas($kelas);
        }
    }
}
