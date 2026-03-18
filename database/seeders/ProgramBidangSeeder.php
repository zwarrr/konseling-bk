<?php

namespace Database\Seeders;

use App\Models\ProgramBidang;
use Illuminate\Database\Seeder;

class ProgramBidangSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'Konseling',
            'Seminar',
            'Workshop',
            'Diskusi',
            'Pelatihan',
        ];

        foreach ($defaults as $name) {
            ProgramBidang::updateOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}
