<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get comprehensive analytics data for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $timeframe = $request->get('timeframe', '30'); // days
        $startDate = Carbon::now()->subDays($timeframe);

        // Get posts per platform
        $postsPerPlatform = $this->getPostsPerPlatform($user->id, $startDate);
        
        // Get publishing success rate
        $publishingStats = $this->getPublishingStats($user->id, $startDate);
        
        // Get scheduled vs published counts
        $statusCounts = $this->getStatusCounts($user->id, $startDate);
        
        // Get timeline data for charts
        $timelineData = $this->getTimelineData($user->id, $startDate);
        
        // Get platform performance
        $platformPerformance = $this->getPlatformPerformance($user->id, $startDate);

        $activePlatforms = $user->platforms()->get()->map(function ($platform) {
            return [
                'id' => $platform->id,
                'name' => $platform->name,
                'type' => $platform->type,
                'character_limit' => $platform->character_limit,
            ];
        });

        return response()->json([
            'posts_per_platform' => $postsPerPlatform,
            'publishing_stats' => $publishingStats,
            'status_counts' => $statusCounts,
            'timeline_data' => $timelineData,
            'platform_performance' => $platformPerformance,
            'active_platforms'=> $activePlatforms,
            'timeframe' => $timeframe,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => Carbon::now()->format('Y-m-d')
            ]
        ]);
    }

    /**
     * Get posts count per platform
     */
    private function getPostsPerPlatform($userId, $startDate)
    {
        return DB::table('posts')
            ->join('platform_post', 'posts.id', '=', 'platform_post.post_id')
            ->join('platforms', 'platform_post.platform_id', '=', 'platforms.id')
            ->where('posts.user_id', $userId)
            ->where('posts.created_at', '>=', $startDate)
            ->select('platforms.name', 'platforms.type', DB::raw('count(*) as count'))
            ->groupBy('platforms.id', 'platforms.name', 'platforms.type')
            ->orderBy('count', 'desc')
            ->get();
    }

    /**
     * Get publishing success rate statistics
     */
    private function getPublishingStats($userId, $startDate)
    {
        // Get post status counts
        $postStatusCounts = Post::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Get platform status counts from pivot table
        $platformStatusCounts = DB::table('posts')
            ->join('platform_post', 'posts.id', '=', 'platform_post.post_id')
            ->where('posts.user_id', $userId)
            ->where('posts.created_at', '>=', $startDate)
            ->select('platform_post.platform_status', DB::raw('count(*) as count'))
            ->groupBy('platform_post.platform_status')
            ->pluck('count', 'platform_status');

        $totalPosts = $postStatusCounts->sum();
        $draftPosts = $postStatusCounts->get('draft', 0);
        $scheduledPosts = $postStatusCounts->get('scheduled', 0);
        $publishedPosts = $postStatusCounts->get('published', 0);

        // Platform-level stats
        $platformPublished = $platformStatusCounts->get('published', 0);
        $platformPending = $platformStatusCounts->get('pending', 0);
        $platformFailed = $platformStatusCounts->get('failed', 0);
        $totalPlatformPosts = $platformStatusCounts->sum();

        $successRate = $totalPlatformPosts > 0 ? round(($platformPublished / $totalPlatformPosts) * 100, 1) : 0;

        return [
            'total_posts' => $totalPosts,
            'draft_posts' => $draftPosts,
            'scheduled_posts' => $scheduledPosts,
            'published_posts' => $publishedPosts,
            'platform_published' => $platformPublished,
            'platform_pending' => $platformPending,
            'platform_failed' => $platformFailed,
            'success_rate' => $successRate
        ];
    }

    /**
     * Get status counts for pie chart
     */
    private function getStatusCounts($userId, $startDate)
    {
        // Get post statuses
        $postStatuses = Post::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => ucfirst($item->status),
                    'count' => $item->count,
                    'type' => 'post'
                ];
            });

        // Get platform statuses
        $platformStatuses = DB::table('posts')
            ->join('platform_post', 'posts.id', '=', 'platform_post.post_id')
            ->where('posts.user_id', $userId)
            ->where('posts.created_at', '>=', $startDate)
            ->select('platform_post.platform_status', DB::raw('count(*) as count'))
            ->groupBy('platform_post.platform_status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => ucfirst($item->platform_status),
                    'count' => $item->count,
                    'type' => 'platform'
                ];
            });

        return [
            'post_statuses' => $postStatuses,
            'platform_statuses' => $platformStatuses
        ];
    }

    /**
     * Get timeline data for line charts
     */
    private function getTimelineData($userId, $startDate)
    {
        // Get post timeline data
        $postTimeline = Post::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                'status',
                DB::raw('count(*) as count')
            )
            ->groupBy(DB::raw('DATE(created_at)'), 'status')
            ->orderBy('date')
            ->get();

        // Get platform timeline data
        $platformTimeline = DB::table('posts')
            ->join('platform_post', 'posts.id', '=', 'platform_post.post_id')
            ->where('posts.user_id', $userId)
            ->where('posts.created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(posts.created_at) as date'),
                'platform_post.platform_status',
                DB::raw('count(*) as count')
            )
            ->groupBy(DB::raw('DATE(posts.created_at)'), 'platform_post.platform_status')
            ->orderBy('date')
            ->get();

        // Initialize timeline structure
        $timeline = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte(Carbon::now())) {
            $dateStr = $currentDate->format('Y-m-d');
            $timeline[$dateStr] = [
                'date' => $dateStr,
                // Post statuses
                'draft' => 0,
                'scheduled' => 0,
                'published' => 0,
                // Platform statuses
                'platform_pending' => 0,
                'platform_published' => 0,
                'platform_failed' => 0
            ];
            $currentDate->addDay();
        }

        // Fill in post data
        foreach ($postTimeline as $post) {
            if (isset($timeline[$post->date])) {
                $timeline[$post->date][$post->status] = $post->count;
            }
        }

        // Fill in platform data
        foreach ($platformTimeline as $platform) {
            if (isset($timeline[$platform->date])) {
                $timeline[$platform->date]['platform_' . $platform->platform_status] = $platform->count;
            }
        }

        return array_values($timeline);
    }

    /**
     * Get platform performance metrics
     */
    private function getPlatformPerformance($userId, $startDate)
    {
        return DB::table('posts')
            ->join('platform_post', 'posts.id', '=', 'platform_post.post_id')
            ->join('platforms', 'platform_post.platform_id', '=', 'platforms.id')
            ->where('posts.user_id', $userId)
            ->where('posts.created_at', '>=', $startDate)
            ->select(
                'platforms.name',
                'platforms.type',
                DB::raw('count(*) as total_posts'),
                DB::raw('sum(case when platform_post.platform_status = "published" then 1 else 0 end) as published_posts'),
                DB::raw('sum(case when platform_post.platform_status = "pending" then 1 else 0 end) as pending_posts'),
                DB::raw('sum(case when platform_post.platform_status = "failed" then 1 else 0 end) as failed_posts'),
                DB::raw('round((sum(case when platform_post.platform_status = "published" then 1 else 0 end) / count(*)) * 100, 1) as success_rate')
            )
            ->groupBy('platforms.id', 'platforms.name', 'platforms.type')
            ->having('total_posts', '>', 0)
            ->orderBy('success_rate', 'desc')
            ->get();
    }

    /**
     * Get quick stats for dashboard cards
     */
    public function quickStats(Request $request)
    {
        $user = Auth::user();
        $timeframe = $request->get('timeframe', '7'); // days
        $startDate = Carbon::now()->subDays($timeframe);

        // Platform-level published count (actual publications)
        $platformPublishedToday = DB::table('posts')
            ->join('platform_post', 'posts.id', '=', 'platform_post.post_id')
            ->where('posts.user_id', $user->id)
            ->where('platform_post.platform_status', 'published')
            ->whereDate('platform_post.updated_at', Carbon::today())
            ->count();

        $stats = [
            'total_posts' => Post::where('user_id', $user->id)->count(),
            'published_today' => $platformPublishedToday,
            'scheduled_posts' => Post::where('user_id', $user->id)
                ->where('status', 'scheduled')
                ->where('scheduled_time', '>', Carbon::now())
                ->count(),
            'draft_posts' => Post::where('user_id', $user->id)
                ->where('status', 'draft')
                ->count(),
            'posts_this_week' => Post::where('user_id', $user->id)
                ->where('created_at', '>=', $startDate)
                ->count(),
            'pending_publications' => DB::table('posts')
                ->join('platform_post', 'posts.id', '=', 'platform_post.post_id')
                ->where('posts.user_id', $user->id)
                ->where('platform_post.platform_status', 'pending')
                ->count(),
            'failed_publications' => DB::table('posts')
                ->join('platform_post', 'posts.id', '=', 'platform_post.post_id')
                ->where('posts.user_id', $user->id)
                ->where('platform_post.platform_status', 'failed')
                ->count()
        ];

        return response()->json($stats);
    }

    /**
     * Get platform-specific analytics
     */
    public function platformAnalytics(Request $request, $platformId)
    {
        $user = Auth::user();
        $timeframe = $request->get('timeframe', '30');
        $startDate = Carbon::now()->subDays($timeframe);

        $platform = Platform::findOrFail($platformId);

        // Get platform-specific analytics
        $analytics = DB::table('posts')
            ->join('platform_post', 'posts.id', '=', 'platform_post.post_id')
            ->where('posts.user_id', $user->id)
            ->where('platform_post.platform_id', $platformId)
            ->where('posts.created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(posts.created_at) as date'),
                'platform_post.platform_status',
                DB::raw('count(*) as count')
            )
            ->groupBy(DB::raw('DATE(posts.created_at)'), 'platform_post.platform_status')
            ->orderBy('date')
            ->get();

        return response()->json([
            'platform' => $platform,
            'analytics' => $analytics,
            'timeframe' => $timeframe
        ]);
    }
}