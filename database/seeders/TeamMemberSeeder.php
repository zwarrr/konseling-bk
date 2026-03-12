<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeamMember;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        if (TeamMember::count() > 0) return;

        $members = [
            ['name' => 'Ani Kusumawati, S.Pd.', 'quote' => 'Mendampingi siswa menemukan potensi terbaik mereka.',    'img' => 'https://images.unsplash.com/photo-1607746882042-944635dfe10e?w=400&q=80', 'sort_order' => 0],
            ['name' => 'Budi Santoso, M.Pd.',   'quote' => 'Setiap masalah punya solusi, mari kita temukan bersama.', 'img' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&q=80', 'sort_order' => 1],
            ['name' => 'Citra Dewi, S.Psi.',    'quote' => 'Kesehatan mental adalah fondasi keberhasilan belajar.',   'img' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&q=80', 'sort_order' => 2],
        ];

        foreach ($members as $member) {
            TeamMember::create($member);
        }
    }
}
