<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixKelasData extends Command
{
    protected $signature   = 'kelas:fix-data';
    protected $description = 'Backfill bk_account_id, fix siswa role, and insert missing system join messages';

    public function handle(): void
    {
        // 1. Fix bk_account_id for classrooms created before boot code
        $guru = DB::table('users')->where('role', 'guru')->first();
        if ($guru) {
            $n = DB::table('classrooms')->whereNull('bk_account_id')
                ->update(['bk_account_id' => $guru->account_id]);
            $this->info("Fixed bk_account_id on {$n} classroom(s) → {$guru->account_id}");
        } else {
            $this->warn('No guru found, skipping bk_account_id fix.');
        }

        // 2. Fix role='user' to role='siswa' for users who have a classroom_id
        $n = DB::table('users')->where('role', 'user')->whereNotNull('classroom_id')
            ->update(['role' => 'siswa']);
        $this->info("Fixed role on {$n} user(s)");

        // 3. Backfill missing system join messages
        $siswaList = DB::table('users')->where('role', 'siswa')->whereNotNull('classroom_id')->get();
        $inserted  = 0;
        foreach ($siswaList as $s) {
            $exists = DB::table('group_messages')
                ->where('classroom_id', $s->classroom_id)
                ->where('user_id', $s->id)
                ->where('message_type', 'system')
                ->exists();
            if (!$exists) {
                DB::table('group_messages')->insert([
                    'classroom_id' => $s->classroom_id,
                    'user_id'      => $s->id,
                    'message'      => $s->name . ' bergabung ke grup ini',
                    'message_type' => 'system',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $inserted++;
                $this->line("  Inserted system message for: {$s->name}");
            }
        }
        $this->info("Inserted {$inserted} system message(s)");
        $this->info('Done.');
    }
}
