<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeamMember;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        // Clean up legacy placeholder data from an older seed.
        TeamMember::whereIn('name', [
            'Ani Kusumawati, S.Pd.',
            'Budi Santoso, M.Pd.',
            'Citra Dewi, S.Psi.',
        ])->delete();

        $defaultQuote = 'Setiap langkah kecil hari ini adalah awal dari perubahan besar; kami siap mendampingi Anda.';

        $names = [
            'Yusef abdul aziz,M.Pd',
            'Drs.Dadang Nurdin',
            'Pebi Dinastriani,S.Pd',
            'Pia Amanda Nurhusni,S.Pd',
            'Neri Sondari,S.Pd',
            'Dewi Rosita,S.Pd',
            'Tenia Octaviana,S.Pd',
            'Deslita Seniatsaani,S.Pd',
        ];

        foreach ($names as $index => $name) {
            TeamMember::updateOrCreate(
                ['name' => $name],
                [
                    'quote' => $defaultQuote,
                    'img' => null,
                    'sort_order' => $index,
                ]
            );
        }
    }
}
