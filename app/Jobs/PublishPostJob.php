<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PublishPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue , Queueable;

    protected $post_id;

    /**
     * Create a new job instance.
     */
    public function __construct($post_id)
    {
        $this->post_id = $post_id;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $post = Post::with('platforms')->findOrFail($this->post_id);

        if ($post->status !== 'scheduled') {
            Log::warning("Post {$this->post_id} is not in scheduled status. Skipping.");
            return;
        }
        
        // Mock publishing to each platform
        foreach ($post->platforms as $platform) {
            $this->publishToPlatform($post, $platform);
        }

        Log::info("Successfully published post ID: {$post->id}");
           
        
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
