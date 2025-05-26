<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Models\Post;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $cacheKey = 'user_posts_' . auth()->id() . 
                    '_status:' . ($request->status ?? 'all') . 
                    '_date:' . ($request->date ?? 'all') . 
                    '_page:' . ($request->page ?? 1);

        $cacheDuration = now()->addMinutes(1); 

        $posts = Cache::remember($cacheKey, $cacheDuration, function () use ($request) {
            return Post::with('platforms')
                ->where('user_id', auth()->id())
                ->when($request->status, fn($q) => $q->where('status', $request->status))
                ->when($request->date, fn($q) => $q->whereDate('scheduled_time', $request->date))
                ->latest()
                ->get()
                ->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'content' => $post->content,
                        'status' => $post->status,
                        'scheduled_time' => $post->scheduled_time ? $post->scheduled_time->toDateTimeString() : null,
                        'platforms' => $post->platforms,
                    ];
                });
        });

        return $this->success($posts, 'User posts');
    }

    public function show(Post $post)
    {
        return $this->success($post->load("platforms"), 'Show Post');
    }

    public function store(PostStoreRequest $request)
    {
        $data = $request->validated();

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
            $data['image'] = $imagePath; // Assuming 'image' column exists in the posts table
        }

        Log::info($data);
        $post = auth()->user()->posts()->create($data);
        $post->platforms()->attach($data['platforms']);

        // Clear the default cache key
        Cache::forget('user_posts_' . auth()->id() . '_status:all_date:all_page:1');

        return $this->success($post->load('platforms'), 'Post created');
    }


    public function update(PostUpdateRequest $request, Post $post)
    {

        Gate::authorize('modify', $post);

        if ($post->status === 'published') {
            return $this->error('Cannot update a published post', 422);
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($post->image) {
                $oldImagePath = public_path('storage/' . $post->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Store the new image
            $imagePath = $request->file('image')->store('posts', 'public');
            $data['image'] = $imagePath;
        } elseif ($request->has('remove_image') && $request->remove_image) { // has the field and equal true or 1
            // Handle case where user wants to remove the image
            if ($post->image) {
                $oldImagePath = public_path('storage/' . $post->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $data['image'] = null;
        }

        $post->update($data);
        Cache::forget('user_posts_' . auth()->id() . '_status:all_date:all_page:1');

        if ($request->has('platforms')) {
            $post->platforms()->sync($request->platforms);
        }

        return $this->success($post->load('platforms'), 'Post updated');
    }

    public function destroy(Post $post)
    {

        Gate::authorize('modify', $post);

        $post->delete();
        return $this->success([], 'Post deleted', 204);
    }
}
