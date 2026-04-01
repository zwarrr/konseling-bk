<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_siswa_i')->nullable()->default(null)->change();
        });

        DB::transaction(function () {
            $mapToRoman = static function (string $kelas): string {
                $k = strtoupper(trim($kelas));
                return match ($k) {
                    '10' => 'X',
                    '11' => 'XI',
                    '12' => 'XII',
                    default => $k,
                };
            };

            $rows = DB::table('classes')->orderBy('id')->get();
            $groups = [];

            foreach ($rows as $row) {
                $key = $mapToRoman((string) $row->kelas) . '|' . strtoupper((string) $row->jurusan);
                $groups[$key][] = $row;
            }

            foreach ($groups as $key => $items) {
                [$kelasRoman] = explode('|', $key, 2);

                $primary = null;
                foreach ($items as $item) {
                    if (strtoupper((string) $item->kelas) === $kelasRoman) {
                        $primary = $item;
                        break;
                    }
                }
                if (!$primary) {
                    $primary = $items[0];
                }

                if ((string) $primary->kelas !== $kelasRoman) {
                    DB::table('classes')->where('id', $primary->id)->update(['kelas' => $kelasRoman]);
                }

                $primaryBk = $primary->bk_id;
                $primaryJumlah = $primary->jumlah_siswa_i;

                foreach ($items as $item) {
                    if ((int) $item->id === (int) $primary->id) {
                        continue;
                    }

                    DB::table('classrooms')
                        ->where('class_id', $item->id)
                        ->update(['class_id' => $primary->id]);

                    if (!$primaryBk && $item->bk_id) {
                        $primaryBk = $item->bk_id;
                    }
                    if ($primaryJumlah === null && $item->jumlah_siswa_i !== null) {
                        $primaryJumlah = $item->jumlah_siswa_i;
                    }

                    DB::table('classes')->where('id', $item->id)->delete();
                }

                DB::table('classes')->where('id', $primary->id)->update([
                    'kelas' => $kelasRoman,
                    'bk_id' => $primaryBk,
                    'jumlah_siswa_i' => $primaryJumlah,
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('classes')->whereNull('jumlah_siswa_i')->update(['jumlah_siswa_i' => 0]);

        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_siswa_i')->default(0)->nullable(false)->change();
        });
    }
};
