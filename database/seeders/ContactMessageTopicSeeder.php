<?php

namespace Database\Seeders;

use App\Models\ContactMessageTopic;
use Illuminate\Database\Seeder;

class ContactMessageTopicSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            'Konseling Individual',
            'Bimbingan Karier',
            'Masalah Belajar',
            'Kesehatan Mental',
            'Lainnya',
        ];

        foreach ($topics as $index => $name) {
            ContactMessageTopic::firstOrCreate(
                ['name' => $name],
                [
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );
        }
    }
}
