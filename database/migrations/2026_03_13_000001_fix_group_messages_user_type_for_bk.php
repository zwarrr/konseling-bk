<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Back-fill user_type = 'bk' for group_messages that were sent by BK teachers
 * but saved with the old default 'siswa' before the morph fix was applied.
 *
 * Strategy: for each classroom, look up the BK teacher's numeric id via the
 * classrooms.bk_account_id → bk_account.account_id → bk_account.id chain.
 * Any message in that classroom whose user_id matches the BK's id and still
 * has user_type = 'siswa' must have been sent by the BK teacher.
 */
return new class extends Migration
{
    public function up(): void
    {
                // SQLite does not support JOIN in UPDATE statements.
                if (DB::getDriverName() === 'sqlite') {
                        DB::statement("
                                UPDATE group_messages
                                SET user_type = 'bk'
                                WHERE user_type = 'siswa'
                                    AND EXISTS (
                                        SELECT 1
                                        FROM classrooms c
                                        INNER JOIN bk_account bk ON bk.account_id = c.bk_account_id
                                        WHERE c.id = group_messages.classroom_id
                                            AND bk.id = group_messages.user_id
                                    )
                        ");
                        return;
                }

                // MySQL / MariaDB: use a JOIN-based UPDATE
                DB::statement("
                        UPDATE group_messages gm
                        INNER JOIN classrooms c   ON c.id           = gm.classroom_id
                        INNER JOIN bk_account bk  ON bk.account_id  = c.bk_account_id
                        SET gm.user_type = 'bk'
                        WHERE gm.user_type = 'siswa'
                            AND gm.user_id   = bk.id
                ");
    }

    public function down(): void
    {
        // Cannot reliably reverse — would need to know which BK messages
        // were originally stored with wrong user_type.
    }
};
