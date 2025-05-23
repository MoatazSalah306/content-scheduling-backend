<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            ['name' => 'Twitter Platform', 'type' => 'twitter', 'character_limit' => 280],
            ['name' => 'Instagram Platform', 'type' => 'instagram', 'character_limit' => 2200],
            ['name' => 'LinkedIn Platform', 'type' => 'linkedin', 'character_limit' => 1300],
            ['name' => 'Facebook Platform', 'type' => 'facebook', 'character_limit' => 63206],
            ['name' => 'TikTok Platform', 'type' => 'qabilah', 'character_limit' => 150],
        ];

        // to check if there's an existing platform with the same 'type' to update it's ['name' & 'character_limit']

        foreach ($platforms as $platform) {
            Platform::updateOrCreate(
                ['type' => $platform['type']],
                $platform
            );
        }
    }
}
