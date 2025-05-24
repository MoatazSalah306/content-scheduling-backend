<?php

namespace App\Console\Commands;

use App\Jobs\PublishScheduledPosts;
use Illuminate\Console\Command;

class PublishPostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publish:posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and publish scheduled posts.';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        // Dispatch job synchronously (no queue needed)
        PublishScheduledPosts::dispatchSync();
        
        $this->info('Posts publishing completed!');
        
        return 0;
    }
}
