<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class PlatformController extends Controller
{
     use ApiResponse;

    public function index()
    {
        $platforms = Platform::all();
        return $this->success($platforms, 'Available platforms');
    }

    public function toggle(Request $request)
    {

        // Rate limiting: max 5 requests per minute per user
        $executed = RateLimiter::attempt(
            'platform-toggle:'.$request->user()->id,
            5,
            function() {},
            60
        );

        if (!$executed) {
            return $this->error('Too many toggle attempts. Please wait a minute.', 429);
        }
        
        $validated = $request->validate([
            'platform_id' => 'required|exists:platforms,id',
        ]);

        $user = auth()->user();
        $platformId = $validated['platform_id'];

        if ($user->platforms()->where('platform_id', $platformId)->exists()) {
            $user->platforms()->detach($platformId);
            $message = 'Platform deactivated';
        } else {
            $user->platforms()->attach($platformId);
            $message = 'Platform activated';
        }

        return $this->success([], $message);
    }

    public function getEnabledPlatforms()
    {
        $user = Auth::user();

        // Get only platforms attached to the user via the pivot table
        $platforms = $user->platforms()->get()->map(function ($platform) {
            return [
                'id' => $platform->id,
                'name' => $platform->name,
                'type' => $platform->type,
            ];
        });

        return $this->success($platforms, 'Enabled platforms');
    }
}
