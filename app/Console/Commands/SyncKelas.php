<?php

namespace App\Console\Commands;

use App\Models\Classroom;
use App\Models\Kelas;
use App\Services\KelasSync;
use Illuminate\Console\Command;

class SyncKelas extends Command
{
    protected $signature   = 'kelas:sync';
    protected $description = 'Re-sync Kelas ↔ Classroom.bk_account_id ↔ SiswaAccount.bk_id for all records';

    public function handle(): int
    {
        // 1. For every Kelas that has a bk_id, push down to classrooms + students
        $kelasList = Kelas::all();
        $this->info("Syncing {$kelasList->count()} kelas rows from master...");
        foreach ($kelasList as $kelas) {
            KelasSync::fromKelas($kelas);
            $this->line("  ✓ {$kelas->kelas} {$kelas->jurusan} (bk_id={$kelas->bk_id})");
        }

        // 2. For any Classroom whose class_id is null, keep as-is but still sync students
        $orphaned = Classroom::whereNull('class_id')->get();
        foreach ($orphaned as $cls) {
            KelasSync::fromClassroom($cls->bk_account_id, null, $cls->id);
            $this->line("  ↻ Classroom {$cls->id} {$cls->name} (no class_id)");
        }

        $this->info('Done. All data is now in sync.');
        return self::SUCCESS;
    }
}
