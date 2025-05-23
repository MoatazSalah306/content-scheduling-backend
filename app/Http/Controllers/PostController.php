<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Models\Post;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
     use ApiResponse;

    public function index(Request $request)
    {
        $posts = Post::with('platforms')
            ->where('user_id', auth()->id())
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date, fn($q) => $q->whereDate('scheduled_time', $request->date))
            ->latest()
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'scheduled_time' => $post->scheduled_time->toDateTimeString(), // This will use the accessor ( By force using the accessor)
                    'platforms' => $post->platforms,
                ];
            });

        return $this->success($posts, 'User posts');
    }

    public function store(PostStoreRequest $request)
    {

        
        $post = auth()->user()->posts()->create($request->validated());
        $post->platforms()->attach($request->platforms);


        return $this->success($post->load('platforms'), 'Post created');
    }

    public function update(PostUpdateRequest $request, Post $post)
    {

        Gate::authorize('modify',$post);

        if ($post->status === 'published') {
            return $this->error('Cannot update a published post', 422);
        }

        $post->update($request->validated());

        if ($request->has('platforms')) {
            $post->platforms()->sync($request->platforms);
        }

        return $this->success($post->load('platforms'), 'Post updated');
    }

    public function destroy(Post $post)
    {
      
        Gate::authorize('modify',$post);

        $post->delete();
        return $this->success([], 'Post deleted',204);
    }
}
