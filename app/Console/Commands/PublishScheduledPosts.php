<?php

namespace App\Console\Commands;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon as SupportCarbon;

class PublishScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:publish-scheduled-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to publish the users scheduled posts to the specified platforms. ( mocking the publsh process )";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nowUtc = Carbon::now();
 
        $duePosts = Post::where('status', 'scheduled')
            ->where('scheduled_time', '<=', $nowUtc)
            ->get();

        foreach ($duePosts as $post) {
            // Mock publishing logic:
            $post->update(['status' => 'published']);

            foreach ($post->platforms as $platform) {
                $post->platforms()->updateExistingPivot($platform->id, ['platform_status' => 'published']);
            }
        }

        $this->info('Published ' . $duePosts->count() . ' scheduled posts.');
    }


}
