<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $hasOld = Schema::hasColumn('users', 'account_id_nim_nis');
        $hasNew = Schema::hasColumn('users', 'account_id_nip_nis');

        if (!$hasOld || $hasNew) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id_nip_nis')->nullable()->unique()->after('email_verified_at');
        });

        DB::table('users')->update([
            'account_id_nip_nis' => DB::raw('account_id_nim_nis'),
        ]);

        // Best-effort cleanup: drop old unique + column (safe for fresh installs).
        if (Schema::hasColumn('users', 'account_id_nim_nis')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_account_id_nim_nis_unique');
                $table->dropColumn('account_id_nim_nis');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $hasOld = Schema::hasColumn('users', 'account_id_nim_nis');
        $hasNew = Schema::hasColumn('users', 'account_id_nip_nis');

        if ($hasOld || !$hasNew) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id_nim_nis')->nullable()->unique()->after('email_verified_at');
        });

        DB::table('users')->update([
            'account_id_nim_nis' => DB::raw('account_id_nip_nis'),
        ]);

        if (Schema::hasColumn('users', 'account_id_nip_nis')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_account_id_nip_nis_unique');
                $table->dropColumn('account_id_nip_nis');
            });
        }
    }
};
