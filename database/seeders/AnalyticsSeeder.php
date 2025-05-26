<?php

namespace Database\Seeders;

use App\Models\Platform;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Testing\Fakes\Fake;

class AnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Fetch existing platforms
        $platformIds = Platform::pluck('id')->toArray();

        if (empty($platformIds)) {
            $this->command->info('No platforms found. Please seed platforms table first.');
            return;
        }

        // Get all users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found. Please seed users table first.');
            return;
        }

        // Possible statuses
        $postStatuses = ['draft', 'scheduled', 'published'];
        $platformStatuses = ['published', 'pending', 'failed'];

        foreach ($users as $user) {
            // Generate 5-20 posts per user
            $postCount = rand(5, 20);

            for ($i = 0; $i < $postCount; $i++) {
                // Random creation date within the last 60 days
                $createdAt = Carbon::now()->subDays(rand(0, 60));

                // Create a post
                $post = Post::create([
                    'user_id' => $user->id,
                    'title' => 'Test Post ' . ($i + 1), 
                    'status' => $postStatuses[array_rand($postStatuses)],
                    'content' => 'Test post content ' . ($i + 1),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'scheduled_time' => $postStatuses[array_rand($postStatuses)] === 'scheduled'
                        ? Carbon::now()->addDays(rand(1, 10))
                        : null,
                ]);

                // Assign 1-3 platforms to each post
                $numPlatforms = rand(1, min(3, count($platformIds)));
                $selectedPlatforms = array_rand(array_flip($platformIds), $numPlatforms);

                // Ensure selectedPlatforms is an array
                if (!is_array($selectedPlatforms)) {
                    $selectedPlatforms = [$selectedPlatforms];
                }

                // Insert platform_post records
                foreach ($selectedPlatforms as $platformId) {
                    DB::table('platform_post')->insert([
                        'post_id' => $post->id,
                        'platform_id' => $platformId,
                        'platform_status' => $platformStatuses[array_rand($platformStatuses)],
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
        }

        $this->command->info('Analytics test data seeded successfully for ' . $users->count() . ' users using ' . count($platformIds) . ' existing platforms.');
    }
}