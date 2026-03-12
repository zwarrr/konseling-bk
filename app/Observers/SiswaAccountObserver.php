<?php

namespace App\Observers;

use App\Models\SiswaAccount;
use App\Services\KelasSync;

class SiswaAccountObserver
{
    /**
     * Fires after a SiswaAccount is saved.
     * If classroom_id changed, sync bk_id accordingly.
     */
    public function saved(SiswaAccount $siswa): void
    {
        if ($siswa->wasChanged('classroom_id')) {
            KelasSync::forStudent($siswa);
        }
    }
}
