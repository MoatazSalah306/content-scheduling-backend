<?php

namespace App\Jobs;

use App\Enums\PostStatusEnum;
use App\Models\Post;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue , Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Get all posts that are scheduled and their time has come
        $posts = Post::with('platforms')
            ->where('status', 'scheduled')
            ->where('scheduled_time', '<=', now())
            ->get();

        if ($posts->isEmpty()) {
            Log::info('No scheduled posts to publish at this time.');
            return;
        }

        foreach ($posts as $post) {
            try {
                // Mock publishing to each platform
                foreach ($post->platforms as $platform) {
                    $this->publishToPlatform($post, $platform);
                }
                $post->update(['status'=>"published",'pubilshed_at'=>Carbon::now()]);

                Log::info("Successfully published post ID: {$post->id}");
            } catch (\Exception $e) {
                Log::error("Failed to publish post ID: {$post->id}. Error: " . $e->getMessage());
                
                // Update platform status to failed for this post
                $post->platforms()->updateExistingPivot($platform->id, [
                    'platform_status' => 'failed'
                ]);
            }
        }
    }

    protected function publishToPlatform($post, $platform)
    {
         sleep(1); // Simulate API delay

        // Simulate 40% chance of failure
        $failed = rand(1, 100) <= 40;

        $post->platforms()->updateExistingPivot($platform->id, [
            'platform_status' => $failed ? 'failed' : 'published'
        ]);

        if ($failed) {
            Log::warning("Failed to publish to {$platform->name}");
        } else {
            Log::info("Published to {$platform->name}");
        }
    }
}
