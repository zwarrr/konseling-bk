<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AuthSeeder::class,
            SiswaSeeder::class,
            HomeSectionSeeder::class,
            AboutSectionSeeder::class,
            AboutFeatureSeeder::class,
            ServiceSectionSeeder::class,
            ServiceSeeder::class,
            AgendaSectionSeeder::class,
            AgendaSeeder::class,
            BkNewsSeeder::class,
            TeamMemberSeeder::class,
        ]);
    }
}
